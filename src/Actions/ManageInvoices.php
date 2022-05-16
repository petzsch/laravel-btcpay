<?php

namespace Petzsch\LaravelBtcpay\Actions;

use BTCPayServer\Client\Invoice;
use BTCPayServer\Client\InvoiceCheckoutOptions;
use BTCPayServer\Util\PreciseNumber;
use JsonException;


trait ManageInvoices
{
    /**
     * Create a BitPay invoice.
     *
     * @link https://docs.btcpayserver.org/API/Greenfield/v1/#operation/Invoices_CreateInvoice
     *
     * @param string                      $currency
     * @param PreciseNumber|null          $amount
     * @param string|null                 $orderId
     * @param string|null                 $buyerEmail
     * @param array|null                  $metaData
     * @param InvoiceCheckoutOptions|null $checkoutOptions
     *
     * @return \BTCPayServer\Result\Invoice $invoice A BitPay generated Invoice object.
     * @throws JsonException
     */
    public static function createInvoice(string $currency, ?PreciseNumber $amount = null, ?string $orderId = null, ?string $buyerEmail = null, ?array $metaData = null, ?InvoiceCheckoutOptions $checkoutOptions = null): \BTCPayServer\Result\Invoice
    {
        $thisInstance = new self();
        $_client      = new Invoice($thisInstance->config['server_url'], $thisInstance->config['api_key']);

        return $_client->createInvoice($thisInstance->config['store_id'], $currency, $amount, $orderId, $buyerEmail, $metaData, $checkoutOptions);
    }

    /**
     * Retrieve a BitPay invoice by its id.
     *
     * @link https://docs.btcpayserver.org/API/Greenfield/v1/#operation/Invoices_GetInvoices
     *
     * @param $invoiceId string The id of the invoice to retrieve.
     *
     * @return \BTCPayServer\Result\Invoice A BtcPay Invoice object.
     * @throws JsonException
     */
    public
    static function getInvoice(string $invoiceId): \BTCPayServer\Result\Invoice
    {
        $thisInstance = new self();
        $_client      = new Invoice($thisInstance->config['server_url'], $thisInstance->config['api_key']);

        return $_client->getInvoice($thisInstance->config['store_id'], $invoiceId);
    }
}
