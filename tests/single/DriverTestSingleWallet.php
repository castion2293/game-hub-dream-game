<?php

namespace SuperStation\Gamehub\DreamGame\Tests\Single;

use SuperStation\Gamehub\Facades\Gamehub;

class DriverTestSingleWallet extends DreamGameSingleWalletBaseTestCase
{
    /**
     * 遊戲錢包
     *
     * @var array
     */
    protected $wallet = [];

    /**
     * 其他欲帶入API的參數
     *
     * @var array
     */
    protected $options = [];

    /**
     * 遊戲服務中心
     *
     * @var
     */
    protected $driver;

    public function setUp(): void
    {
        parent::setUp();

        // 測試錢包參數
        $this->wallet = [
            'account' => 'TEST1001113',
            'password' => 'qqq333',
        ];

        // 測試其他參數
        $this->options = [
            'device' => 'desktop',
            'station_wallet_trade_record_id' => '1111112222336',
        ];

        // 初始化遊戲服務中心
        $this->driver = Gamehub::vendor('dream_game', $this->config);
    }

    /**
     * 測試建立 DG 帳號
     */
    public function testCreateAccount()
    {
        try {
            $this->console->writeln('測試建立 DG 帳號');

            $response = $this->driver->createAccount($this->wallet, $this->options);

            $this->console->writeln('result: ' . $response['response']['result']);
            $this->assertEquals('OK', $response['response']['result']);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 測試取得 DG 遊戲通行證
     */
    public function testGetPassport()
    {
        try {
            $this->console->writeln('測試取得 DG 遊戲通行證');

            $response = $this->driver->passport($this->wallet, $this->options);

            $this->console->writeln('URL: ' . $response['response']['url']);
            $this->assertArrayHasKey('url', $response['response']);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 測試撈取 DG 注單 手動
     */
    public function testFetchTicketsManual()
    {
        try {
            $this->console->writeln('測試撈取 DG 注單 手動');

            // 手動撈單API
            $params = [
                'mode' => 'manual',
                'beginTime' => '2020-04-08 00:00:00',
                'endTime' => '2020-04-08 23:59:59',
            ];
            $response = $this->driver->fetchTickets($params);
            dump($response['response']['data']);
            $this->assertArrayHasKey('data', $response['response']);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }
}