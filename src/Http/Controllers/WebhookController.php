<?php

namespace Petzsch\LaravelBtcpay\Http\Controllers;

use Illuminate\Http\Request;
use Petzsch\LaravelBtcpay\Events\BtcpayWebhookReceived;
use Petzsch\LaravelBtcpay\Http\Middleware\VerifyWebhookSignature;

class WebhookController extends Controller
{
    public function __construct()
    {
        $this->middleware(VerifyWebhookSignature::class);
    }

    public function handleWebhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        BtcpayWebhookReceived::dispatch($payload);

        return response('OK', 200);
    }
}
