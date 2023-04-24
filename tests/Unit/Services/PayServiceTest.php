<?php

namespace Tests\Feature;

use App\Services\PayService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Tests\TestCase;

class PayServiceTest extends TestCase
{

    /**
     *
     * 取得支付連結成功
     * @author Wright
     * @date   2023-04-24
     *
     */
    public function testPaySuccess()
    {
        $aCheckParams = [
            'line_items'  => [
                [
                    'price_data' => [
                        'product_data' => ['name' => 'test'],
                        'currency'     => 'twd',
                        'unit_amount'  => 10000,
                    ],
                    'quantity'   => 1,
                ],
            ],
            'mode'        => 'payment',
            'success_url' => 'http://google.com',
        ];

        $aCheckKey = 'sk_test_51Mzn0JBiXgImlwgFBOrI1z9pi0W4kViN6XMRawTfU5rdpbHlfCYzklbis8TazW4TMxum7g9WR947bew7g3AkMQ0o00R9hyuBbG';

        $sCheckUrl = 'https://api.stripe.com/v1/checkout/sessions';

        $oFakeStream = Mockery::mock(Response::class);
        $oFakeStream->shouldReceive('getBody')
            ->once()
            ->andReturn(json_encode(['status' => 'open', 'url' => 'abc']));

        $this->mock(Client::class)
            ->shouldReceive('request')
            ->with(
                'POST',
                $sCheckUrl,
                [
                    'http_errors' => false,
                    'auth'        => [
                        $aCheckKey, '',
                    ],
                    'form_params' => $aCheckParams,
                ]
            )
            ->once()
            ->andReturn($oFakeStream);

        $oPayService = app(PayService::class);
        $sUrl        = $oPayService->pay(100, 'twd');

        $this->assertEquals($sUrl, 'abc');
    }

    /**
     *
     * 取得支付連結失敗
     * @author Wright
     * @date   2023-04-24
     *
     */
    public function testPayFail()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(99999);

        $aCheckParams = [
            'line_items'  => [
                [
                    'price_data' => [
                        'product_data' => ['name' => 'test'],
                        'currency'     => 'twd',
                        'unit_amount'  => 10000,
                    ],
                    'quantity'   => 1,
                ],
            ],
            'mode'        => 'payment',
            'success_url' => 'http://google.com',
        ];

        $aCheckKey = 'sk_test_51Mzn0JBiXgImlwgFBOrI1z9pi0W4kViN6XMRawTfU5rdpbHlfCYzklbis8TazW4TMxum7g9WR947bew7g3AkMQ0o00R9hyuBbG';

        $sCheckUrl = 'https://api.stripe.com/v1/checkout/sessions';

        $oFakeStream = Mockery::mock(Response::class);
        $oFakeStream->shouldReceive('getBody')
            ->once()
            ->andReturn(json_encode([
                'error' => ['code' => '測試外部服務錯誤'],
            ]));

        $this->mock(Client::class)
            ->shouldReceive('request')
            ->with(
                'POST',
                $sCheckUrl,
                [
                    'http_errors' => false,
                    'auth'        => [
                        $aCheckKey, '',
                    ],
                    'form_params' => $aCheckParams,
                ]
            )
            ->once()
            ->andReturn($oFakeStream);

        $oPayService = app(PayService::class);
        $sUrl        = $oPayService->pay(100, 'twd');
    }
}
