<?php

namespace SuperStation\Gamehub\DreamGame\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SuperStation\Gamehub\DreamGame\Receiver;
use SuperStation\Gamehub\Traits\EventTrait;

class DreamGameController
{
    use EventTrait;

    /**
     * 接受器
     *
     * @var Receiver
     */
    protected $receiver;

    public function __construct(Receiver $receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * 獲取玩家餘額
     *
     * @param Request $request
     * @param $agentName
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getBalance(Request $request, $agentName)
    {
        $account = $request->input('member.username');
        $balance = $this->receiver
            ->getPlayerAccount($account)
            ->checkPlayerAccount(
                [
                    Receiver::$AccountExist,
                    Receiver::$AccountFreezing,
                ]
            )
            ->getBalance();

        $responseData = [
            'codeId' => 0,
            'token' => $request->input('token'),
            'member' => [
                'username' => $account,
                'balance' => round(Arr::get($balance, 'balance'), 2)
            ]
        ];

        return response()->json($responseData, 200);
    }

    /**
     * 存取款接口
     *
     * @param Request $request
     * @param $agentName
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function transfer(Request $request, $agentName)
    {
        $account = $request->input('member.username');
        $amount = $request->input('member.amount');
        $serialNo = $request->input('data');

        // 轉帳前餘額
        $beforeBalance = $this->receiver
            ->getPlayerAccount($account)
            ->getBalance();

        // 寫交易紀錄
        $this->firePlayerAccountTradeEvent(
            [
                'action' => EventTrait::$create,
                'serial_no' => $serialNo,
                'account' => $account,
                'status' => EventTrait::$pending,
                'before_balance' => Arr::get($beforeBalance, 'balance'),
                'amount' => $amount,
                'request' => $request->all(),
                'remark' => config('dream_game.station_code') . '-' . EventTrait::$transfer,
            ]
        );

        // 轉帳動作
        try {
            DB::beginTransaction();

            $this->receiver
                ->getPlayerAccount($account)
                ->checkPlayerAccount(
                    [
                        Receiver::$AccountExist,
                        Receiver::$AccountFreezing,
                        Receiver::$BalanceEnough,
                    ],
                    [
                        'amount' => $amount
                    ]
                )
                ->changeBalance($amount);

            $responseData = [
                'codeId' => 0,
                'token' => $request->input('token'),
                'data' => $serialNo,
                'member' => [
                    'username' => $account,
                    'amount' => $amount,
                    'balance' => round(Arr::get($beforeBalance, 'balance'), 2)
                ]
            ];

            // 寫交易紀錄
            $this->firePlayerAccountTradeEvent(
                [
                    'action' => EventTrait::$update,
                    'serial_no' => $serialNo,
                    'status' => EventTrait::$completed,
                    'after_balance' => Arr::get($beforeBalance, 'balance') + $amount,
                    'response' => $responseData
                ]
            );

            DB::commit();

            return response()->json($responseData, 200);
        } catch (\Exception $exception) {
            DB::rollBack();

            // 寫交易紀錄
            $this->firePlayerAccountTradeEvent(
                [
                    'action' => EventTrait::$update,
                    'serial_no' => $serialNo,
                    'status' => EventTrait::$fail,
                    'after_balance' => Arr::get($beforeBalance, 'balance'),
                ]
            );

            throw $exception;
        }
    }

    /**
     * 確認存取款結果接口
     *
     * @param Request $request
     * @param $agentName
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function checkTransfer(Request $request, $agentName)
    {
        $serialNo = $request->input('data');
        $record = $this->receiver->getPlayerAccountTradeRecord($serialNo);

        if (empty($record)) {
            throw new \Exception('not found player account trade record');
        }

        $status = data_get($record, 'status');
        if ($status === EventTrait::$completed) {
            $responseData = [
                'codeId' => 0,
                'token' => $request->input('token'),
            ];

            return response()->json($responseData, 200);
        } else {
            if ($status === EventTrait::$pending) {
                $responseData = [
                    'codeId' => 98,
                    'token' => $request->input('token'),
                ];

                return response()->json($responseData, 200);
            }
        }
    }

    /**
     * 請求回滾轉帳事務
     *
     * @param Request $request
     * @param $agentName
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function inform(Request $request, $agentName)
    {
        $amount = $request->input('member.amount');
        $responseData = ($amount < 0) ? $this->toGameInform($request->all()) : $this->fromGameInform($request->all());

        return response()->json($responseData, 200);
    }

    /**
     * 請求對帳接口
     *
     * @param Request $request
     * @param $agentName
     * @return \Illuminate\Http\JsonResponse
     */
    public function order(Request $request, $agentName)
    {
        $responseData = [];

        return response()->json($responseData, 200);
    }

    /**
     * 查詢未派彩和未回滾紀錄
     *
     * @param Request $request
     * @param $agentName
     * @return \Illuminate\Http\JsonResponse
     */
    public function unsettle(Request $request, $agentName)
    {
        $responseData = [];

        return response()->json($responseData, 200);
    }

    /**
     * 下注扣款回滾通知
     *
     * 因為遊戲方認定下注失敗需要做交易回滾
     * 1. 如果沒有紀錄 或 交易紀錄未成功 不需做任何動作
     * 2. 如果有交易紀錄成功 玩家帳戶補錢 新增交易回退紀錄
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function toGameInform(array $data): array
    {
        $account = Arr::get($data, 'member.username');
        $serialNo = Arr::get($data, 'data');
        $amount = -Arr::get($data, 'member.amount'); // 取負值代表補錢
        $record = $this->receiver->getPlayerAccountTradeRecord($serialNo);

        // 轉帳前餘額
        $beforeBalance = $this->receiver
            ->getPlayerAccount($account)
            ->getBalance();

        /**
         * 如果沒有紀錄 或 交易紀錄未成功 不需做任何動作 直接回傳成功訊息
         */
        $isRecordNotFinished = empty($record) || (data_get($record, 'status') !== EventTrait::$completed);
        if ($isRecordNotFinished) {
            return [
                'codeId' => 0,
                'token' => Arr::get($data, 'token'),
                'data' => $serialNo,
                'member' => [
                    'username' => $account,
                    'balance' => round(Arr::get($beforeBalance, 'balance'), 2)
                ],
            ];
        }

        /**
         * 如果有交易紀錄成功 玩家帳戶補錢 新增交易回退紀錄
         */
        // 新增交易回退紀錄
        $this->firePlayerAccountTradeEvent(
            [
                'action' => EventTrait::$create,
                'serial_no' => $serialNo,
                'account' => $account,
                'status' => EventTrait::$pending,
                'before_balance' => Arr::get($beforeBalance, 'balance'),
                'amount' => $amount,
                'request' => $data,
                'remark' => config('dream_game.station_code') . '-' . EventTrait::$rollback,
            ]
        );

        try {
            DB::beginTransaction();

            // 玩家帳戶補錢
            $this->receiver
                ->getPlayerAccount($account)
                ->checkPlayerAccount(
                    [
                        Receiver::$AccountExist,
                        Receiver::$AccountFreezing,
                    ]
                )
                ->changeBalance($amount);

            $responseData = [
                'codeId' => 0,
                'token' => Arr::get($data, 'token'),
                'data' => $serialNo,
                'member' => [
                    'username' => $account,
                    'balance' => round(Arr::get($beforeBalance, 'balance'), 2)
                ],
            ];

            // 修改交易回退紀錄
            $this->firePlayerAccountTradeEvent(
                [
                    'action' => EventTrait::$update,
                    'serial_no' => $serialNo,
                    'status' => EventTrait::$completed,
                    'after_balance' => Arr::get($beforeBalance, 'balance') + $amount,
                    'response' => $responseData
                ]
            );

            DB::commit();

            return $responseData;
        } catch (\Exception $exception) {
            DB::rollBack();

            // 修改交易回退紀錄
            $this->firePlayerAccountTradeEvent(
                [
                    'action' => EventTrait::$update,
                    'serial_no' => $serialNo,
                    'status' => EventTrait::$fail,
                    'after_balance' => Arr::get($beforeBalance, 'balance'),
                ]
            );

            throw $exception;
        }
    }

    /**
     * 派彩入款回滾通知
     *
     * 因為遊戲方有建單成功需要補交易紀錄
     * 1. 如果有交易成功紀錄 不需做任何動作
     * 2. 如果沒有紀錄 或 交易紀錄未成功 補交易紀錄 玩家帳戶補錢
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function fromGameInform(array $data): array
    {
        $account = Arr::get($data, 'member.username');
        $serialNo = Arr::get($data, 'data');
        $amount = Arr::get($data, 'member.amount');
        $record = $this->receiver->getPlayerAccountTradeRecord($serialNo);

        // 轉帳前餘額
        $beforeBalance = $this->receiver
            ->getPlayerAccount($account)
            ->getBalance();

        /**
         * 如果有交易成功紀錄 不需做任何動作
         */
        $isRecordFinished = data_get($record, 'status') === EventTrait::$completed;
        if ($isRecordFinished) {
            return [
                'codeId' => 0,
                'token' => Arr::get($data, 'token'),
                'data' => $serialNo,
                'member' => [
                    'username' => $account,
                    'balance' => round(Arr::get($beforeBalance, 'balance'), 2)
                ],
            ];
        }

        /**
         * 如果沒有紀錄 或 交易紀錄未成功 補交易紀錄 玩家帳戶補錢
         */
        // 新增交易回退紀錄
        $this->firePlayerAccountTradeEvent(
            [
                'action' => EventTrait::$create,
                'serial_no' => $serialNo,
                'account' => $account,
                'status' => EventTrait::$pending,
                'before_balance' => Arr::get($beforeBalance, 'balance'),
                'amount' => $amount,
                'request' => $data,
                'remark' => config('dream_game.station_code') . '-' . EventTrait::$rollback,
            ]
        );

        try {
            DB::beginTransaction();

            // 玩家帳戶補錢
            $this->receiver
                ->getPlayerAccount($account)
                ->checkPlayerAccount(
                    [
                        Receiver::$AccountExist,
                        Receiver::$AccountFreezing,
                    ]
                )
                ->changeBalance($amount);

            $responseData = [
                'codeId' => 0,
                'token' => Arr::get($data, 'token'),
                'data' => $serialNo,
                'member' => [
                    'username' => $account,
                    'balance' => round(Arr::get($beforeBalance, 'balance'), 2)
                ],
            ];

            // 修改交易回退紀錄
            $this->firePlayerAccountTradeEvent(
                [
                    'action' => EventTrait::$update,
                    'serial_no' => $serialNo,
                    'status' => EventTrait::$completed,
                    'after_balance' => Arr::get($beforeBalance, 'balance') + $amount,
                    'response' => $responseData
                ]
            );

            DB::commit();

            return $responseData;
        } catch (\Exception $exception) {
            DB::rollBack();

            // 修改交易回退紀錄
            $this->firePlayerAccountTradeEvent(
                [
                    'action' => EventTrait::$update,
                    'serial_no' => $serialNo,
                    'status' => EventTrait::$fail,
                    'after_balance' => Arr::get($beforeBalance, 'balance'),
                ]
            );

            throw $exception;
        }
    }
}