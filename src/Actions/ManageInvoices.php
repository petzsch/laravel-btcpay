<?php

namespace Petzsch\LaravelBtcpay\Actions;

use BTCPayServer\Client\Invoice;


trait ManageInvoices
{
    /**
     * Get BtcPay Invoice instance.
     *
     * @link https://docs.btcpayserver.org/API/Greenfield/v1/#tag/Invoices
     *
     * @return Invoice
     */
    public static function Invoice(): Invoice
    {
        $thisInstance = new self();
        return new Invoice($thisInstance->config['server_url'], $thisInstance->config['api_key']);
    }

    /**
     * Create a BitPay invoice.
     *
     * @link https://docs.btcpayserver.org/API/Greenfield/v1/#operation/Invoices_CreateInvoice
     *
     * @param $invoice Invoice An Invoice object with request parameters defined.
     *
     * @return Invoice $invoice A BitPay generated Invoice object.
     */
    public static function createInvoice(Invoice $invoice): Invoice
    {
        $thisInstance = new self();

        return $thisInstance->client->createInvoice($invoice);
    }

    /**
     * Retrieve a BitPay invoice by its id.
     *
     * @link https://docs.btcpayserver.org/API/Greenfield/v1/#operation/Invoices_GetInvoices
     *
     * @param $invoiceId string The id of the invoice to retrieve.
     *
     * @return Invoice A BtcPay Invoice object.
     */
    public static function getInvoice(string $invoiceId): Invoice
    {
        $thisInstance = new self();
        return (new self())->client->getInvoice($thisInstance->config['store_id'], $invoiceId);
    }
}
