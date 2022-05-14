# LaravelBtcPay

![LaravelBtcPay Social Image](https://banners.beyondco.de/LaravelBtcPay.png?theme=light&packageManager=composer+require&packageName=petzsch%2Flaravel-btcpay&pattern=circuitBoard&style=style_1&description=Transact+in+Bitcoin%2C+Litecoin+and+10%2B+other+BtcPay-supported+cryptocurrencies+within+your+Laravel+application.&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/petzsch/laravel-btcpay.svg?style=for-the-badge)](https://packagist.org/packages/petzsch/laravel-btcpay)
[![Total Downloads](https://img.shields.io/packagist/dt/petzsch/laravel-btcpay.svg?style=for-the-badge)](https://packagist.org/packages/petzsch/laravel-btcpay)

LaravelBtcPay enables you and your business to transact in Bitcoin, Litecoin and 10+ other BtcPay-supported
cryptocurrencies within your Laravel application.

> Requires PHP ^7.3

## Supported Resources

- :white_check_mark: [Invoices](https://docs.btcpayserver.org/API/Greenfield/v1/#tag/Invoices)

## Contents

- [Installation](#installation)
    + [Install Package](#install-package)
    + [Publish config file](#publish-config-file)
    + [Add configuration values](#add-configuration-values)
    + [Configure Webhooks (Optional)](#configure-webhooks-optional)
        + [1. Setup your webhook route](#1-setup-your-webhook-route)
        + [2. Setup your webhook listener](#2-setup-your-webhook-listener)
- [Examples](#examples)
    + [Invoices](#invoices)
        + [Create an invoice](#create-an-invoice)
        + [Retrieve an existing invoice](#retrieve-an-existing-invoice)
        + [Retrieve a list of existing invoices](#retrieve-a-list-of-existing-invoices)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## Installation

### Install package

You can install the package via composer:

```bash
composer require petzsch/laravel-btcpay
```

### Publish config file

Publish config file with:

```bash
php artisan vendor:publish --provider="Petzsch\LaravelBtcpay\LaravelBtcpayServiceProvider"
```

This will create a `laravel-btcpay.php` file inside your **config** directory.

### Add configuration values

Add the following keys to your `.env` file and update the values to match your
preferences ([read more about configuration](https://github.com/btcpayserver/btcpayserver-greenfield-php#where-to-get-the-api-key-from)):

```dotenv
BTCPAY_API_KEY=YourRandomApiKeyThatYouOptainedFromYourBTCPayUserSettings
BTCPAY_SERVER_URL=https://btcpay.your.server.tld
```

### Configure Webhooks (Optional)

BtcPay resource status updates are completely based on webhooks (IPNs). LaravelBtcPay is fully capable of automatically
handling webhook requests. Whenever a webhook is received from BtcPay's server, `BtcpayWebhookReceived` event is
dispatched. Take the following steps to configure your application for webhook listening:

#### 1. Setup your webhook route

Resolve the `btcPayWebhook` route macro in your desired route file (`web.php` is recommended). The macro accepts a
single, optional argument, which is the URI path at which you want to receive BtcPay webhook `POST` requests. If none is
provided, it defaults to `'laravel-btcpay/webhook'`:

```php
// ... your other 'web' routes

Route::btcPayWebhook(); // https://example.com/laravel-btcpay/webhook

// OR ...

Route::btcPayWebhook('receive/webhooks/here'); // https://example.com/receive/webhooks/here
```

> :information_source: To retrieve your newly created webhook route anywhere in your application, use: `route('laravel-btcpay.webhook.capture')`

LaravelBtcPay also offers the convenience of auto-populating your configured webhook url on applicable resources.
Specifically when:

- [Creating an Invoice](#create-an-invoice)

You may enable this feature per-resource by uncommenting the respective entry within the `auto_populate_webhook` array
found in the `laravel-btcpay.php` config file.

:warning: **If a value is manually set, most likely via `$resource->setNotificationURL('https://...')` during resource
initialization, auto-population is overridden.**

#### 2. Setup your webhook listener

Start by generating an event listener:

```bash
php artisan make:listener BtcPayWebhookListener --event=\Petzsch\LaravelBtcpay\Events\BtcpayWebhookReceived
```

Then, implement your application-specific logic in the `handle(...)` function of the generated listener.

In the following example, we assume you have previously [created an invoice](#create-an-invoice), storing its `token`
on your internal `Order` model:

```php
/**
 * Handle the webhook event, keeping in mind that the server doesn't trust the client (us), so neither should
 * we trust the server. Well, trust, but verify.
 *
 * @param BtcpayWebhookReceived $event
 * @return void
 */
public function handle(BtcpayWebhookReceived $event)
{
    // Extract event payload
    $payload = $event->payload;

    // Verify that webhook is for a BtcPay Invoice resource
    if (in_array($payload['event']['code'], array_keys(BtcPayConstants::INVOICE_WEBHOOK_CODES))) {
        try {
            // Do not trust the webhook data. Pull the referenced Invoice from BtcPay's server
            $invoice = LaravelBtcpay::getInvoice($payload['data']['id']);

            // Now grab our internal Order instance for this supposed Invoice
            $order = Order::whereOrderId($invoice->getOrderId())->first();

            // Verify Invoice token, previously stored at time of creation
            // Learn more at: https://github.com/petzsch/laravel-btcpay#create-an-invoice
            if ($invoice->getToken() !== $order->invoice_token) {
                return;
            }

            $invoice_status = $invoice->getStatus();

            // Do something about the new Invoice status
            if ($invoice_status === InvoiceStatus::Paid) {
                $order->update(['status' => $invoice_status]) && OrderStatusChanged::dispatch($order->refresh());
            }
        } catch (BtcPayException $e) {
            Log::error($e);
        }
    }
}
```

Finally, map your listener to the `BtcpayWebhookReceived` event inside the `$listen` array of
your `EventServiceProvider`:

```php
/**
 * The event listener mappings for the application.
 *
 * @var array
 */
protected $listen = [
    // ... other event-listener mappings
    BtcpayWebhookReceived::class => [
        BtcPayWebhookListener::class,
    ],
]
```

## Examples

### Invoices

Invoices are time-sensitive payment requests addressed to specific buyers. An invoice has a fixed price, typically
denominated in fiat currency. It also has an equivalent price in the supported cryptocurrencies, calculated by BtcPay,
at a locked exchange rate with an expiration time of 15(or whatever you configured) minutes.

#### Create an invoice

In this example we assume you've already created an instance of your equivalent `Order` model, to be associated with
this Invoice (referred to as `$order`):

TODO: Check if all of this works with the invoice object exposed by greenfield!!!
```php
// Create instance of Invoice
$invoice = LaravelBtcpay::Invoice(449.99, 'USD');

// Set item details (Only 1 item per Invoice)
$invoice->setItemDesc('You "Joe Goldberg" Life-Size Wax Figure');
$invoice->setItemCode('sku-1234');
$invoice->setPhysical(true); // Set to false for digital/virtual items

// Ensure you provide a unique OrderId for each Invoice
$invoice->setOrderId($order->order_id);

// Create Buyer Instance
$buyer = LaravelBtcpay::Buyer();
$buyer->setName('John Doe');
$buyer->setEmail('john.doe@example.com');
$buyer->setAddress1('2630 Hegal Place');
$buyer->setAddress2('Apt 42');
$buyer->setLocality('Alexandria');
$buyer->setRegion('VA');
$buyer->setPostalCode(23242);
$buyer->setCountry('US');
$buyer->setNotify(true); // Instructs BtcPay to email Buyer about their Invoice

// Attach Buyer to Invoice
$invoice->setBuyer($buyer);

// Set URL that Buyer will be redirected to after completing the payment, via GET Request
$invoice->setRedirectURL(route('your-btcpay-success-url'));
// Set URL that Buyer will be redirected to after closing the invoice or after the invoice expires, via GET Request
$invoice->setCloseURL(route('your-btcpay-cancel-url'));
$invoice->setAutoRedirect(true);

// Optional. Learn more at: https://github.com/vrajroham/laravel-btcpay#1-setup-your-webhook-route
$invoice->setNotificationUrl('https://example.com/your-custom-webhook-url');

// This is the recommended IPN format that BtcPay advises for all new implementations
$invoice->setExtendedNotifications(true);

// Create invoice on BtcPay's server
$invoice = LaravelBtcpay::createInvoice($invoice);

$invoiceId = $invoice->getId();
$invoiceToken = $invoice->getToken();

// You should save Invoice ID and Token, for your reference
$order->update(['invoice_id' => $invoiceId, 'invoice_token' => $invoiceToken]);

// Redirect user to the Invoice's hosted URL to complete payment
// This could be done more elegantly with our JS modal!
$paymentUrl = $invoice->getUrl();
return Redirect::to($paymentUrl);
```

> :information_source: It is highly recommended you store the Invoice ID and Token on your internal model(s). The token
> can come in handy when verifying webhooks.

#### Retrieve an existing invoice

```php
$invoice = LaravelBtcpay::getInvoice('invoiceId_sGsdVsgheF');
```

#### Retrieve a list of existing invoices

In this example we retrieve all MTD (Month-To-Date) invoices:
TODO: unsupported by Greenfield!!!

```php
$startDate = date('Y-m-d', strtotime('first day of this month'));
$endDate = date('Y-m-d');

$invoices = LaravelBtcpay::getInvoices($startDate, $endDate);
```

#### Refund an invoice

TODO: Add support for pull payments to implement refunds (not currently included)

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email vaibhavraj@vrajroham.me instead of using the issue tracker.

## Credits

- [Vaibhavraj Roham](https://github.com/vrajroham)
- [Alex Stewart](https://github.com/alexstewartja)
- [Markus Petzsch](https://github.com/petzsch)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
