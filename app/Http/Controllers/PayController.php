<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PayService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PayController extends Controller
{
    public $oPayService = null;

    public function __construct(PayService $oPayService)
    {
        $this->oPayService = $oPayService;
    }

    public function pay(Request $oRequest)
    {
        try {

            $sUrlResult = [
                'status' => 1, // 成功固定 1
                'msg' => 'success',
                'url'    => '',
            ];

            $oValidator = Validator::make($oRequest->all(), [
                'price'    => 'required|integer|min:100|max:1000',
                'currency' => 'required|string|size:3', // TODO: 限制幣種
            ]);

            if ($oValidator->fails()) {
                throw new Exception("參數錯誤"); // TODO: 新增參數錯誤處理
            }

            $iPrice    = $oRequest->get('price');
            $sCurrency = $oRequest->get('currency');

            $sUrlResult['url'] = $this->oPayService->pay($iPrice, $sCurrency);

        } catch (Exception $e) {
            // TODO: 利用laravel error handler 統一處理
            Log::error($e->getMessage());

            $sUrlResult['status'] = -1; // 所有錯誤暫時用 -1
            $sUrlResult['msg']    = $e->getMessage();
        }

        return $sUrlResult;

    }
}
