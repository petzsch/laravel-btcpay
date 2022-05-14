<?php

namespace Petzsch\LaravelBtcpay;

use Petzsch\LaravelBtcpay\Actions\ManageInvoices;
use Petzsch\LaravelBtcpay\Traits\MakesHttpRequests;


class LaravelBtcpay
{
    use MakesHttpRequests;
    use ManageInvoices;


    protected $client;
    private   $config;


    /**
     * Setup client while creating the instance.
     *
     * @throws Exceptions\InvalidConfigurationException
     */
    public function __construct()
    {
        $this->setupClient();
    }
}
