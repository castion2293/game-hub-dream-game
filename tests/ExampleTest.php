<?php

namespace SuperStation\Gamehub\DreamGame\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use SuperStation\Gamehub\DreamGame\Models\DreamGameTicket;
use SuperStation\Gamehub\Facades\Gamehub;
use SuperStation\Gamehub\Supports\TicketSupport;

class ExampleTest extends BaseTestCase
{
    use DatabaseMigrations;

    public function testCase()
    {
        $this->assertTrue(true);
    }

    public function testMongoDBModel()
    {
        $ticket = new DreamGameTicket();
//        $ticket->_id = md5('dream_game');
        $ticket->station = 'dream_game';
        $ticket->raw_bet = 120;
        $ticket->save();

        $this->assertTrue(true);
    }

    public function testFetcherTest()
    {
        $fetcher = Gamehub::make('dream_game', [
            'api_url' => 'https://api.dg99web.com',
            'report_url' => 'http://report.dg99.info',
            'api_key' => '68d13d249f6f4182b4b3b7c457467b49',
            'api_agent' => 'DGTE0101C7',
            'api_mobile_suffix' => 'XX7',
            'language' => 'en',
            'bet_limit' => 'A',
            'winning_limit' => 0,
            'currency' => 'TWD'
        ]);

        $params = [
            'mode' => 'manual',
            'beginTime' => '2020-03-20 00:00:00',
            'endTime' => '2020-03-20 23:59:59',
        ];

        dd($fetcher->fetch($params));
    }

    public function testExampl2()
    {
        $foos = DreamGameTicket::select('uuid', 'md5_hash')
            ->get();
dump($foos);
        foreach ($foos as $foo) {
            dd($foo->getOriginal());
        }
    }

    public function testConverter()
    {
        // 測試遊戲環境配置參數
        $config = [
            'api_url' => 'https://api.dg99web.com',
            'report_url' => 'http://report.dg99.info',
            'api_key' => '68d13d249f6f4182b4b3b7c457467b49',
            'api_agent' => 'DGTE0101C7',
            'api_mobile_suffix' => 'XX7',
            'language' => 'en',
            'bet_limit' => 'A',
            'winning_limit' => 0,
            'currency' => 'TWD'
//            'currency' => 'VND2'
        ];

        // 建立遊戲注單服務中心
        $converter = Gamehub::converter('dream_game', $config);

        dd($converter->report());
    }

    public function testTicketSupport()
    {
        // 測試遊戲環境配置參數
        $config = [
            'api_url' => 'https://api.dg99web.com',
            'report_url' => 'http://report.dg99.info',
            'api_key' => '68d13d249f6f4182b4b3b7c457467b49',
            'api_agent' => 'DGTE0101C7',
            'api_mobile_suffix' => 'XX7',
            'language' => 'en',
            'bet_limit' => 'A',
            'winning_limit' => 0,
            'currency' => 'TWD'
//            'currency' => 'VND2'
        ];

        TicketSupport::manualFetchAndConvertTickets('dream_game', $config);
    }
}