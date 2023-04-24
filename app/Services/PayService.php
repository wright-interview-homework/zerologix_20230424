<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;

class PayService
{
    // TODO: 放進env or config 會更好
    const URL = 'https://api.stripe.com/v1/checkout/sessions';
    const KEY = 'sk_test_51Mzn0JBiXgImlwgFBOrI1z9pi0W4kViN6XMRawTfU5rdpbHlfCYzklbis8TazW4TMxum7g9WR947bew7g3AkMQ0o00R9hyuBbG';

    public $oClient = null;

    public function __construct(Client $oClient)
    {
        $this->oClient = $oClient;
    }

    public function pay($iPrice, $sCurrency)
    {
        $aParams = [
            'line_items'  => [
                [
                    'price_data' => [
                        'product_data' => ['name' => 'test'],
                        'currency'     => $sCurrency,
                        'unit_amount'  => bcmul($iPrice, 100), // 他以分為單位要做轉換
                    ],
                    'quantity'   => 1,
                ],
            ],
            'mode'        => 'payment',
            'success_url' => 'http://google.com',
        ];

        $oResponse = $this->oClient->request(
            'POST',
            self::URL,
            [
                'http_errors' => false,
                'auth'        => [self::KEY, ''],
                'form_params' => $aParams,
            ]
        );

        $oBody = $oResponse->getBody();
        $aBody = json_decode($oBody, true);

        // 正確時回傳 url
        if (isset($aBody['status']) && $aBody['status'] == null) {
            return $aBody['url'];
        }

        if (isset($aBody['error']['code'])) {
            throw new Exception('支付服務錯誤錯誤訊息為：' . $aBody['error']['code']);
        }
    }
}
