<?php

namespace SuperStation\Gamehub\DreamGame\Tests\Multiple;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use SuperStation\Gamehub\Supports\TicketSupport;

class TicketSupportTestMultipleWallet extends DreamGameMultipleWalletBaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * 測試 ManualFetchAndConvertTickets
     * 供super console專案 UnitedTicketFetchCommand使用
     */
    public function testManualFetchAndConvertTickets()
    {
        try {
            $startTime = '2020-03-20 00:00:00';
            $endTime = '2020-03-20 23:59:59';

            $unitedTickets = TicketSupport::manualFetchAndConvertTickets(config('dream_game.station_code'), $startTime, $endTime, $this->config);
            $this->assertArrayHasKey('united_tickets', $unitedTickets);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 測試 AutoFetchTickets
     * 供super console專案 FetchTicketsJob 使用
     */
    public function testAutoFetchTickets()
    {
        try {
            $rawTickets = TicketSupport::autoFetchTickets(config('dream_game.station_code'), $this->config);

            $this->assertArrayHasKey('raw_tickets', $rawTickets);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 測試 AutoConvertTickets
     * 供super console專案 ConvertTicketsJob 使用
     *
     * @throws \Exception
     */
    public function testAutoConvertTickets()
    {
        // Arrange
        // 在MySQL建立玩家遊戲錢包資料
        DB::table('station_wallets')
            ->insert($this->stationWallet);

        // 在MongoDB建立測試原生注單資料
        DB::connection('mongodb')
            ->collection(config('dream_game.db_collection_name'))
            ->insert($this->rawTicket);

        try {
            $unitedTickets = TicketSupport::autoConvertTickets(config('dream_game.station_code'), $this->config);

            $this->assertArrayHasKey('united_tickets', $unitedTickets);

            // 檢查MongoDB 原生注單 is_converted: true
            $rawTicket = DB::connection('mongodb')
                ->collection(config('dream_game.db_collection_name'))
                ->where('uuid', Arr::get($this->rawTicket, 'uuid'))
                ->get()
                ->first();

            $this->assertTrue(Arr::get($rawTicket, 'is_converted'));
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 回傳注單的投注內容及開牌結果
     * 供SCP API使用
     *
     * @throws \Exception
     */
    public function testGetGameResult()
    {
        // Arrange 在MongoDB建立測試原生注單資料
        DB::connection('mongodb')
            ->collection(config('dream_game.db_collection_name'))
            ->insert($this->rawTicket);

        try {
            $uuid = Arr::get($this->rawTicket, 'uuid');

            $gameResult = TicketSupport::getGameResult(config('dream_game.station_code'), $this->config, $uuid);

            $this->assertArrayHasKey('bet_detail', $gameResult);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }
}