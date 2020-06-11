<?php

namespace SuperStation\Gamehub\DreamGame\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use SuperStation\Gamehub\Traits\ApiExceptionTrait;
use Throwable;

class DreamGameException extends Exception
{
    use ApiExceptionTrait;

    /**
     * DG 官方 API錯誤碼
     *
     * @var array
     */
    protected $stationErrorCodes = [
        '1' => '參數錯誤',
        '2' => 'Token 錯誤',
        '4' => '非法操作',
        '10' => '日期格式錯誤',
        '11' => '數據格式錯誤',
        '97' => '沒有權限',
        '98' => '操作失敗',
        '99' => '未知錯誤',
        '100' => '帳號被鎖定',
        '101' => '帳號格式錯誤',
        '102' => '帳號不存在',
        '103' => '此帳號被佔用',
        '104' => '密碼格式錯誤',
        '105' => '密碼錯誤',
        '106' => '新舊密碼相同',
        '107' => '會員帳號不可用',
        '108' => '登入失敗',
        '109' => '註冊失敗',
        '113' => '傳入的代理帳號不是代理',
        '114' => '找不到會員',
        '116' => '帳號已占用',
        '117' => '找不到會員所屬的分公司',
        '118' => '找不到指定的代理',
        '119' => '存取款時代理點數不足',
        '120' => '餘額不足',
        '121' => '盈利限制必須大於或等於0',
        '150' => '免費試玩賬號用完',
        '300' => '系統維護',
        '320' => 'API Key 錯誤',
        '321' => '找不到相應的限紅組',
        '322' => '找不到指定的貨幣類型',
        '323' => '轉賬流水號占用',
        '324' => '轉賬失敗',
        '325' => '代理狀態不可用',
        '326' => '會員代理沒有視頻組',
        '328' => 'API 類型找不到',
        '329' => '會員代理信息不完整',
        '400' => '客戶端 IP 受限',
        '401' => '網路延遲',
        '402' => '連接關閉',
        '403' => '客戶端來源受限',
        '404' => '請求的資源不存在',
        '405' => '請求太頻繁',
        '406' => '請求超時',
        '407' => '找不到游戲地址',
        '500' => '空指針異常',
        '501' => '系統異常',
        '502' => '系統忙',
        '503' => '數據操作異',
    ];

    public function __construct(Response $response)
    {
        $arrayData = $response->json();

        // 無回應內容 表示HTTP有錯誤，就直接回傳 Psr7/src/Response 錯誤訊息
        if (empty($arrayData)) {
            $message = $response->toPsrResponse()->getReasonPhrase();
            parent::__construct($message, 500);

            return;
        }

        $errorData = [
            'error_code' => $errorCode = Arr::get($response->json(), 'codeId'),
            'error_msg' => Arr::get($this->stationErrorCodes, $errorCode),
        ];

        $message = $this->makeExceptionMessage($errorData);

        if (empty($errorCode)) {

            $message = '遊戲方沒有提供對應的錯誤碼，錯誤未知，內容:' . $this->makeExceptionMessage($response->json());
        }

        parent::__construct($message, 500);
    }
}