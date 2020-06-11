<?php

namespace SuperStation\Gamehub\DreamGame\Tests\Single;

use Illuminate\Support\Arr;
use SuperStation\Gamehub\DreamGame\Tests\BaseTestCase;

class DreamGameSingleWalletBaseTestCase extends BaseTestCase
{
    /**
     * 玩家遊戲錢包
     *
     * @var array
     */
    protected $playerAccount = [];

    /**
     * 環境配置參數
     *
     * @var array
     */
    protected $config = [];

    public function setUp(): void
    {
        parent::setUp();

        // 測試遊戲環境配置參數
        $domain = 'https://super_casino';

        $this->config = [
            'api_url' => 'https://api.dg99web.com',
            'report_url' => 'http://report.dg99.info',
            'api_key' => 'e170f4c5ce2041e4b26980a16d8ffd21',
            'api_agent' => 'DGTE0101X0',
            'api_mobile_suffix' => 'X00',
            'language' => 'en',
            'bet_limit' => 'A',
            'winning_limit' => 0,
            'currency' => 'TWD',
            'domain' => $domain,
            'timezone' => 'Asia/Shanghai',
            'site_code' => 'UYS12',
            'allotment_depth' => 'station',
//            'currency' => 'VND2'
        ];

        // 建立玩家遊戲錢包資料
        $this->playerAccount = [
            "id" => 123456789876,
            "player_username" => "demo123",
            "account" => $account = "TEST1001112",
            "password" => "password123",
            "status" => "active",
            "balance" => 100,
            "site_code" => Arr::get($this->config, 'site_code'),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}