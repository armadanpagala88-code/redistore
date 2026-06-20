<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        $settingServerKey = \App\Models\Setting::where('key', 'midtrans_server_key')->first();
        $settingIsProd = \App\Models\Setting::where('key', 'midtrans_is_production')->first();

        // Set your Merchant Server Key
        Config::$serverKey = $settingServerKey && !empty($settingServerKey->value) ? $settingServerKey->value : env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-YOUR-KEY-HERE');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        Config::$isProduction = $settingIsProd && $settingIsProd->value === '1' ? true : env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        Config::$is3ds = true;
    }

    public function createTransaction($orderId, $grossAmount, $customerDetails, $itemDetails)
    {
        $params = array(
            'transaction_details' => array(
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ),
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        );

        try {
            // Get Snap Payment Page URL
            $snapToken = Snap::getSnapToken($params);
            return $snapToken;
        } catch (\Exception $e) {
            \Log::error('Midtrans Error: ' . $e->getMessage());
            return null;
        }
    }
}
