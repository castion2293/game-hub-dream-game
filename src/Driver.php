<?php

namespace SuperStation\Gamehub\DreamGame;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use SuperStation\Gamehub\Abstracts\DriverAbstract;
use SuperStation\Gamehub\DreamGame\Exceptions\DreamGameException;
use SuperStation\Gamehub\Traits\CurrencyTrait;
use SuperStation\Gamehub\Traits\EventTrait;

class Driver extends DriverAbstract
{
    use CurrencyTrait;
    use EventTrait;

    /**
     * Driver constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    /**
     * 建立帳號
     *
     * @param array $wallet
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function createAccount(array $wallet, array $options = []): array
    {
        try {
            // 取得傳送位置
            $submitUrl = Arr::get($this->config, 'api_url') . '/user/signup/' . Arr::get($this->config, 'api_agent');

            // 組合表單參數
            $random = mt_rand();
            $formParams = [
                'random' => $random,
                'token' => $this->generateToken($random),
                'data' => Arr::get($this->config, 'bet_limit'),
                'member' => [
                    'username' => Arr::get($wallet, 'account'),
                    'password' => md5(Arr::get($wallet, 'password')),
                    'currencyName' => Arr::get($this->config, 'currency'),
                    'winLimit' => Arr::get($this->config, 'winning_limit')
                ]
            ];

            $response = Http::withHeaders(
                [
                    'Content-Type' => 'application/json'
                ]
            )
                ->timeout(10)
                ->post($submitUrl, $formParams);

            // 只有在正確成功完成 API 的動作，才會將結果回傳，不然就是統一丟例外
            if ((string)Arr::get($response->json(), 'codeId') === '0') {
                $arrayData = [
                    'result' => 'OK'
                ];

                return $this->responseFormatter($response, $arrayData);
            }

            throw new DreamGameException($response);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 取得登入通行證 / 連結
     *
     * @param array $wallet
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function passport(array $wallet, array $options = []): array
    {
        try {
            // 取得傳送位置
            $submitUrl = Arr::get($this->config, 'api_url') . '/user/login/' . Arr::get($this->config, 'api_agent');

            // 組合表單參數
            $random = mt_rand();
            $formParams = [
                'random' => $random,
                'token' => $this->generateToken($random),
                'lang' => Arr::get($options, 'language'),
                'member' => [
                    'username' => Arr::get($wallet, 'account'),
                    'password' => md5(Arr::get($wallet, 'password'))
                ]
            ];

            $response = Http::withHeaders(
                [
                    'Content-Type' => 'application/json'
                ]
            )
                ->timeout(10)
                ->post($submitUrl, $formParams);

            // 只有在正確成功完成 API 的動作，才會將結果回傳，不然就是統一丟例外
            if ((string)Arr::get($response->json(), 'codeId') === '0') {
                $arrayData = [
                    'method' => 'redirect',
                    'url' => Arr::get($response->json(), 'list.1') . Arr::get(
                            $response->json(),
                            'token'
                        ) . '&language=' . Arr::get($response->json(), 'lang'),
                    'params' => []
                ];

                return $this->responseFormatter($response, $arrayData);
            }

            throw new DreamGameException($response);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 取得餘額
     *
     * @param array $wallet
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function balance(array $wallet, array $options = []): array
    {
        try {
            // 取得傳送位置
            $submitUrl = Arr::get($this->config, 'api_url') . '/user/getBalance/' . Arr::get(
                    $this->config,
                    'api_agent'
                );

            // 組合表單參數
            $random = mt_rand();
            $formParams = [
                'random' => $random,
                'token' => $this->generateToken($random),
                'member' => [
                    'username' => Arr::get($wallet, 'account')
                ]
            ];

            // 寫入交易紀錄 normal_request
            $this->fireStationWalletTradeEvent('normal_request', $formParams, $options);

            $response = Http::withHeaders(
                [
                    'Content-Type' => 'application/json'
                ]
            )
                ->timeout(10)
                ->post($submitUrl, $formParams);

            // 寫入交易紀錄 normal_response
            $this->fireStationWalletTradeEvent('normal_response', $response->json(), $options);

            // 只有在正確成功完成 API 的動作，才會將結果回傳，不然就是統一丟例外
            if ((string)Arr::get($response->json(), 'codeId') === '0') {
                // 幣別轉換
                $balance = $this->currencyTransformer(
                    'balance',
                    $this->getCurrency(),
                    config('dream_game.currency_rate'),
                    Arr::get($response->json(), 'member.balance')
                );

                $arrayData = [
                    'balance' => number_format($balance, 4, '.', '')
                ];
                return $this->responseFormatter($response, $arrayData);
            }

            throw new DreamGameException($response);
        } catch (\Exception $exception) {
            // 寫入交易紀錄 fail_reason
            $this->fireStationWalletTradeEvent('fail_reason', $exception->getMessage(), $options);
            throw $exception;
        }
    }

    /**
     * 儲值點數
     *
     * @param array $wallet
     * @param float $amount
     * @param string $serialNo
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function deposit(array $wallet, float $amount, string $serialNo, array $options = []): array
    {
        try {
            // 取得傳送位置
            $submitUrl = Arr::get($this->config, 'api_url') . '/account/transfer/' . Arr::get(
                    $this->config,
                    'api_agent'
                );

            // 幣別轉換
            $amount = $this->currencyTransformer(
                'deposit',
                $this->getCurrency(),
                config('dream_game.currency_rate'),
                $amount
            );

            // 組合表單參數
            $random = mt_rand();
            $formParams = [
                'random' => $random,
                'token' => $this->generateToken($random),
                'data' => $serialNo,
                'member' => [
                    'username' => Arr::get($wallet, 'account'),
                    'amount' => round($amount, 2) // DG只接受至小數第二位
                ]
            ];

            // 寫入交易紀錄 normal_request
            $this->fireStationWalletTradeEvent('normal_request', $formParams, $options);

            $response = Http::withHeaders(
                [
                    'Content-Type' => 'application/json'
                ]
            )
                ->timeout(10)
                ->post($submitUrl, $formParams);

            // 寫入交易紀錄 normal_response
            $this->fireStationWalletTradeEvent('normal_response', $response->json(), $options);

            // 只有在正確成功完成 API 的動作，才會將結果回傳，不然就是統一丟例外
            if ((string)Arr::get($response->json(), 'codeId') === '0') {
                $arrayData = [
                    'result' => 'OK'
                ];

                return $this->responseFormatter($response, $arrayData);
            }

            throw new DreamGameException($response);
        } catch (\Exception $exception) {
            // 寫入交易紀錄 fail_reason
            $this->fireStationWalletTradeEvent('fail_reason', $exception->getMessage(), $options);
            throw $exception;
        }
    }

    /**
     * 回收點數
     *
     * @param array $wallet
     * @param float $amount
     * @param string $serialNo
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function withdraw(array $wallet, float $amount, string $serialNo, array $options = []): array
    {
        try {
            // 取得傳送位置
            $submitUrl = Arr::get($this->config, 'api_url') . '/account/transfer/' . Arr::get(
                    $this->config,
                    'api_agent'
                );

            // 幣別轉換
            $amount = $this->currencyTransformer(
                'withdraw',
                $this->getCurrency(),
                config('dream_game.currency_rate'),
                $amount
            );

            // 組合表單參數
            $random = mt_rand();
            $formParams = [
                'random' => $random,
                'token' => $this->generateToken($random),
                'data' => $serialNo,
                'member' => [
                    'username' => Arr::get($wallet, 'account'),
                    'amount' => -(round($amount, 2)) // DG只接受至小數第二位 負值表示轉出
                ]
            ];

            // 寫入交易紀錄 normal_request
            $this->fireStationWalletTradeEvent('normal_request', $formParams, $options);

            $response = Http::withHeaders(
                [
                    'Content-Type' => 'application/json'
                ]
            )
                ->timeout(10)
                ->post($submitUrl, $formParams);

            // 寫入交易紀錄 normal_response
            $this->fireStationWalletTradeEvent('normal_response', $response->json(), $options);

            // 只有在正確成功完成 API 的動作，才會將結果回傳，不然就是統一丟例外
            if ((string)Arr::get($response->json(), 'codeId') === '0') {
                $arrayData = [
                    'result' => 'OK'
                ];

                return $this->responseFormatter($response, $arrayData);
            }

            throw new DreamGameException($response);
        } catch (\Exception $exception) {
            // 寫入交易紀錄 fail_reason
            $this->fireStationWalletTradeEvent('fail_reason', $exception->getMessage(), $options);
            throw $exception;
        }
    }

    /**
     * 檢查交易流水號
     *
     * @param array $wallet
     * @param string $serialNo
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function checkTransfer(array $wallet, string $serialNo, array $options = []): array
    {
        try {
            // 取得傳送位置
            $submitUrl = Arr::get($this->config, 'api_url') . '/account/checkTransfer/' . Arr::get(
                    $this->config,
                    'api_agent'
                );

            // 組合表單參數
            $random = mt_rand();
            $formParams = [
                'random' => $random,
                'token' => $this->generateToken($random),
                'data' => $serialNo,
            ];

            // 寫入交易紀錄 normal_request
            $this->fireStationWalletTradeEvent('transfer_check_request', $formParams, $options);

            $response = Http::withHeaders(
                [
                    'Content-Type' => 'application/json'
                ]
            )
                ->timeout(10)
                ->post($submitUrl, $formParams);

            // 寫入交易紀錄 normal_response
            $this->fireStationWalletTradeEvent('transfer_check_response', $response->json(), $options);

            // 只有在正確成功完成 API 的動作，才會將結果回傳，不然就是統一丟例外
            if ((string)Arr::get($response->json(), 'codeId') === '0') {
                $arrayData = [
                    'result' => 'OK'
                ];

                return $this->responseFormatter($response, $arrayData);
            }

            throw new DreamGameException($response);
        } catch (\Exception $exception) {
            // 寫入交易紀錄 fail_reason
            $this->fireStationWalletTradeEvent('fail_reason', $exception->getMessage(), $options);
            throw $exception;
        }
    }

    /**
     * 撈取注單
     *
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function fetchTickets(array $params = []): array
    {
        try {
            // 取得傳送位置
            $submitUrl = Arr::get($this->config, 'api_url') . '/game/getReport/' . Arr::get($this->config, 'api_agent');

            // 組合表單參數
            $random = mt_rand();
            $formParams = [
                'random' => $random,
                'token' => $this->generateToken($random),
            ];

            // 手動模式
            if (Arr::get($params, 'mode') === 'manual') {
                // 取得傳送位置
                $submitUrl = Arr::get($this->config, 'report_url') . '/game/getReport/';

                // 組合表單參數
                $formParams = [
                    'token' => md5(Arr::get($this->config, 'api_agent') . Arr::get($this->config, 'api_key')),
                    'beginTime' => Arr::get($params, 'beginTime'),
                    'endTime' => Arr::get($params, 'endTime'),
                    'agentName' => Arr::get($this->config, 'api_agent')
                ];
            }

            $response = Http::withHeaders(
                [
                    'Content-Type' => 'application/json'
                ]
            )
                ->timeout(10)
                ->post($submitUrl, $formParams);

            // 只有在正確成功完成 API 的動作，才會將結果回傳，不然就是統一丟例外
            if ((string)Arr::get($response->json(), 'codeId') === '0') {
                $data = Arr::get($response->json(), 'list');

                // 手動模式
                if (Arr::get($params, 'mode') === 'manual') {
                    $data = Arr::get($response->json(), 'data.records');
                }

                $arrayData = [
                    'data' => $data,
                ];

                return $this->responseFormatter($response, $arrayData);
            }
            throw new DreamGameException($response);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 修改限紅
     *
     * @param array $wallet
     * @param $betLimit
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function updateLimit(array $wallet, $betLimit, array $options = []): array
    {
        try {
            // 取得傳送位置
            $submitUrl = Arr::get($this->config, 'api_url') . '/game/updateLimit/' . Arr::get(
                    $this->config,
                    'api_agent'
                );

            // 組合表單參數
            $random = mt_rand();
            $formParams = [
                'random' => $random,
                'token' => $this->generateToken($random),
                'data' => $betLimit,
                'member' => [
                    'username' => Arr::get($wallet, 'account'),
                ]
            ];

            $response = Http::withHeaders(
                [
                    'Content-Type' => 'application/json'
                ]
            )
                ->timeout(10)
                ->post($submitUrl, $formParams);

            // 只有在正確成功完成 API 的動作，才會將結果回傳，不然就是統一丟例外
            if ((string)Arr::get($response->json(), 'codeId') === '0') {
                $arrayData = [
                    'result' => 'OK'
                ];

                return $this->responseFormatter($response, $arrayData);
            }

            throw new DreamGameException($response);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 標記回戳處理過的注單
     *
     * @param array $lists
     * @return array
     * @throws \Exception
     */
    public function markTickets(array $lists = [])
    {
        try {
            // 取得傳送位置
            $submitUrl = Arr::get($this->config, 'api_url') . '/game/markReport/' . Arr::get(
                    $this->config,
                    'api_agent'
                );

            // 組合表單參數
            $random = mt_rand();
            $formParams = [
                'random' => $random,
                'token' => $this->generateToken($random),
                'list' => $lists
            ];

            $response = Http::withHeaders(
                [
                    'Content-Type' => 'application/json'
                ]
            )
                ->timeout(10)
                ->post($submitUrl, $formParams);

            // 只有在正確成功完成 API 的動作，才會將結果回傳，不然就是統一丟例外
            if ((string)Arr::get($response->json(), 'codeId') === '0') {
                $arrayData = [
                    'result' => 'OK'
                ];

                return $this->responseFormatter($response, $arrayData);
            }

            throw new DreamGameException($response);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 產生必要參數 token
     *
     * token = md5(agent + api_key + random)
     * @return string
     */
    private function generateToken($random)
    {
        return md5($this->config['api_agent'] . $this->config['api_key'] . $random);
    }
}