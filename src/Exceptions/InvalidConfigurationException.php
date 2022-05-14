<?php

namespace Petzsch\LaravelBtcpay\Exceptions;

use Exception;

class InvalidConfigurationException extends Exception
{
    public static function emptyServerUrl(): InvalidConfigurationException
    {
        return new static('Server URL is empty. Set BTCPAY_SERVER_URL in your .env file.');
    }

    public static function emptyApiKey(): InvalidConfigurationException
    {
        return new static('BtcPay API-Key is empty. Set BTCPAY_API_KEY in your .env file.');
    }

    public static function emptyStoreID(): InvalidConfigurationException
    {
        return new static('BtcPay Store ID is empty. Set BTCPAY_STORE_ID in your .env file.');
    }
}
