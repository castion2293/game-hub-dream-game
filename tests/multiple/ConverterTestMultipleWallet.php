<?php

namespace SuperStation\Gamehub\DreamGame\Tests\Multiple;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use SuperStation\Gamehub\Facades\Gamehub;

class ConverterTestMultipleWallet extends DreamGameMultipleWalletBaseTestCase
{
    /**
     * 遊戲注單轉換器服務中心
     *
     * @var
     */
    protected $converter;

    public function setUp(): void
    {
        parent::setUp();

        // 建立遊戲注單轉換器服務中心
        $this->converter = Gamehub::converter('dream_game', $this->config);

        // Arrange
        // 在MySQL建立玩家遊戲錢包資料
        DB::table('station_wallets')
            ->insert($this->stationWallet);

        // 在MongoDB建立測試原生注單資料
        DB::connection('mongodb')
            ->collection(config('dream_game.db_collection_name'))
            ->insert($this->rawTicket);
    }

    /**
     * 測試 DG 轉換整合注單
     */
    public function testConverterTickets()
    {
        try {
            // Act
            $unitedTickets = $this->converter
                ->getRawTicketsFromNoSQL()
                ->convert()
                ->saveIntoDatabase()
                ->report();

            // Assert
            $this->assertArrayHasKey('united_tickets', $unitedTickets);
            $this->assertEquals($this->rawTicket['uuid'], Arr::first(Arr::get($unitedTickets, 'united_tickets'))['id']);

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
     * 測試 DG 回傳注單的投注內容及開牌結果
     */
    public function testGameResult()
    {
        try {
            $uuid = Arr::get($this->rawTicket, 'uuid');

            $gameResults = $this->converter->gameResult($uuid);
            $this->assertArrayHasKey('bet_detail', $gameResults);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }
}