<?php

namespace Petzsch\LaravelBtcpay\Traits;

use BTCPayServer\Client\Invoice;
use BTCPayServer\Exception\BTCPayException;
use Petzsch\LaravelBtcpay\Exceptions\InvalidConfigurationException;


trait MakesHttpRequests
{
    /**
     * Get the BtcPay client.
     *
     * @throws InvalidConfigurationException
     * @throws BTCPayException
     */
    public function setupClient()
    {
        $this->validateAndLoadConfig();
        $this->client = new Invoice($this->config['server_url'], $this->config['api_key']);
    }

    /**
     * Validate and load the config.
     *
     * @throws InvalidConfigurationException
     */
    public function validateAndLoadConfig()
    {
        $config = config('laravel-btcpay');

        //$config = function_exists('config') && !empty(config('laravel-btcpay')) ? config('laravel-btcpay') : null;
        if (empty($config['api_key'])) {
            throw InvalidConfigurationException::emptyApiKey();
        }

        if (empty($config['server_url'])) {
            throw InvalidConfigurationException::emptyServerUrl();
        }

        if (empty($config['store_id'])) {
            throw InvalidConfigurationException::emptyStoreID();
        }

        $this->config = $config;
    }
}
