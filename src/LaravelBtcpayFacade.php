<?php

namespace Petzsch\LaravelBtcpay;

use Illuminate\Support\Facades\Facade;

/**
 * Class LaravelBtcpayFacade.
 */
class LaravelBtcpayFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-btcpay';
    }
}
