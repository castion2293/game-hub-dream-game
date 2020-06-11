<?php

namespace SuperStation\Gamehub\DreamGame\Tests\Multiple;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use SuperStation\Gamehub\Events\StationWalletTradeEvent;
use SuperStation\Gamehub\Facades\Gamehub;

class DriverTestMultipleWallet extends DreamGameMultipleWalletBaseTestCase
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
            'account' => 'TEST1001112',
            'password' => 'qqq333',
        ];

        // 測試其他參數
        $this->options = [
            'device' => 'desktop',
            'station_wallet_trade_record_id' => '1111112222333',
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
     * 測試取得 DG 玩家錢包餘額
     */
    public function testGetBalance()
    {
        try {
            $this->console->writeln('測試取得 DG 玩家錢包餘額');

            $response = $this->driver->balance($this->wallet, $this->options);

            $this->console->writeln('balance: ' . $response['response']['balance']);
            $this->assertArrayHasKey('balance', $response['response']);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 測試 DG 儲值點數
     */
    public function testDeposit()
    {
        try {
            $this->console->writeln('測試 DG 儲值點數');

            $response = $this->driver->deposit($this->wallet, 1.234, Str::random(32), $this->options);

            $this->console->writeln('result: ' . $response['response']['result']);
            $this->assertEquals('OK', $response['response']['result']);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 測試 DG 回收點數
     */
    public function testWithdraw()
    {
        try {
            $this->console->writeln('測試 DG 回收點數');

            $response = $this->driver->withdraw($this->wallet, 1.234, Str::random(32), $this->options);

            $this->console->writeln('result: ' . $response['response']['result']);
            $this->assertEquals('OK', $response['response']['result']);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 測試檢查 DG 交易流水號
     */
    public function testCheckTransfer()
    {
        try {
            $this->console->writeln('測試檢查 DG 交易流水號');

            // 先做儲值點數
            $serialNo = Str::random(32);
            $this->driver->deposit($this->wallet, 1.23, $serialNo, $this->options);

            // 再做交易流水號檢查
            $response = $this->driver->checkTransfer($this->wallet, $serialNo, $this->options);

            $this->console->writeln('result: ' . $response['response']['result']);
            $this->assertEquals('OK', $response['response']['result']);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 測試修改 DG 限紅
     */
    public function testUpdateLimit()
    {
        try {
            $this->console->writeln('測試修改 DG 限紅');

            $betLimit = 'C';
            $response = $this->driver->updateLimit($this->wallet, $betLimit, $this->options);

            $this->console->writeln('result: ' . $response['response']['result']);
            $this->assertEquals('OK', $response['response']['result']);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 測試撈取 DG 注單 自動
     */
    public function testFetchTicketsAuto()
    {
        try {
            $this->console->writeln('測試撈取 DG 注單 自動');

            // 自動撈單API
            $params = [
                'mode' => 'auto'
            ];
            $response = $this->driver->fetchTickets($params);
            $this->assertArrayHasKey('data', $response['response']);
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
                'beginTime' => '2020-03-20 00:00:00',
                'endTime' => '2020-03-20 23:59:59',
            ];
            $response = $this->driver->fetchTickets($params);
            dump($response['response']['data']);
            $this->assertArrayHasKey('data', $response['response']);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 測試 DG 標記回戳處理過的注單
     */
    public function testMarkTickets()
    {
        try {
            $this->console->writeln('測試撈取 DG 注單 手動');

            $lists = ['4218749852'];
            $response = $this->driver->markTickets($lists);

            $this->console->writeln('result: ' . $response['response']['result']);
            $this->assertEquals('OK', $response['response']['result']);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 測試 取得 DG 玩家錢包餘額 事件觸發
     */
    public function testGetBalanceEventFire()
    {
        $this->console->writeln('測試 取得 DG 玩家錢包餘額 事件觸發');

        Event::fake();

        $this->driver->balance($this->wallet, $this->options);

        // Assert
        $stationWalletRecordId = Arr::get($this->options, 'station_wallet_trade_record_id');
        Event::assertDispatched(StationWalletTradeEvent::class, function ($event) use ($stationWalletRecordId) {
            return $event->stationWalletTradeRecordId === $stationWalletRecordId;
        });
        Event::assertDispatched(StationWalletTradeEvent::class, 2);
    }

    /**
     * 測試 DG 儲值點數 事件觸發
     */
    public function testDepositEventFire()
    {
        $this->console->writeln('測試 DG 儲值點數 事件觸發');

        Event::fake();

        $this->driver->deposit($this->wallet, 1.23, Str::random(32), $this->options);

        // Assert
        $stationWalletRecordId = Arr::get($this->options, 'station_wallet_trade_record_id');
        Event::assertDispatched(StationWalletTradeEvent::class, function ($event) use ($stationWalletRecordId) {
            return $event->stationWalletTradeRecordId === $stationWalletRecordId;
        });
        Event::assertDispatched(StationWalletTradeEvent::class, 2);
    }

    /**
     * 測試 DG 回收點數 事件觸發
     */
    public function testWithdrawEventFire()
    {
        $this->console->writeln('測試 DG 回收點數 事件觸發');

        Event::fake();

        $this->driver->withdraw($this->wallet, 1.23, Str::random(32), $this->options);

        // Assert
        $stationWalletRecordId = Arr::get($this->options, 'station_wallet_trade_record_id');
        Event::assertDispatched(StationWalletTradeEvent::class, function ($event) use ($stationWalletRecordId) {
            return $event->stationWalletTradeRecordId === $stationWalletRecordId;
        });
        Event::assertDispatched(StationWalletTradeEvent::class, 2);
    }

    /**
     * 測試檢查 DG 交易流水號 事件觸發
     */
    public function testCheckTransferEventFire()
    {
        $this->console->writeln('測試檢查 DG 交易流水號 事件觸發');

        Event::fake();

        // 先做儲值點數
        $serialNo = Str::random(32);
        $this->driver->deposit($this->wallet, 1.23, $serialNo, $this->options);

        // 再做交易流水號檢查
        $this->driver->checkTransfer($this->wallet, $serialNo, $this->options);

        // Assert
        $stationWalletRecordId = Arr::get($this->options, 'station_wallet_trade_record_id');
        Event::assertDispatched(StationWalletTradeEvent::class, function ($event) use ($stationWalletRecordId) {
            return $event->stationWalletTradeRecordId === $stationWalletRecordId;
        });
        Event::assertDispatched(StationWalletTradeEvent::class, 4);
    }
}