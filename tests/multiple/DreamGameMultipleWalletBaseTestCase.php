<?php

namespace SuperStation\Gamehub\DreamGame\Tests\Multiple;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use SuperStation\Gamehub\DreamGame\Tests\BaseTestCase;
use SuperStation\Gamehub\Traits\DateTimeTrait;

class DreamGameMultipleWalletBaseTestCase extends BaseTestCase
{
    use DateTimeTrait;

    /**
     * 環境配置參數
     *
     * @var array
     */
    protected $config = [];

    /**
     *  原生注單
     *
     * @var array
     */
    protected $rawTicket = [];

    /**
     * 玩家遊戲錢包
     *
     * @var array
     */
    protected $stationWallet = [];

    public function setUp(): void
    {
        parent::setUp();

        // --- 描述 mock 的事件故事 ---
        // 在整個測試過程，建立一個StationWallet 類別物件
        // 並且被呼叫 getPlayerUsernameByAccount() 並傳入 $account, $station 參數
        // 而且回傳了 stationWalletData 內定義的假資料
//        $this->mock = $this->initMock('SuperStation\Service\StationWalletService');
//        $this->mock
//            ->shouldReceive('getPlayerUsernameByAccount')
//            ->andReturnUsing(function (array $accounts, string $station) {
//                $players = [];
//
//                foreach ($accounts as $account) {
//                    $players[$account] = 'demo123';
//                }
//
//                return $players;
//            });

        // 測試遊戲環境配置參數
        $domain = 'https://super_casino';

        $this->config = [
            'api_url' => 'https://api.dg99web.com',
            'report_url' => 'http://report.dg99.info',
            'api_key' => '68d13d249f6f4182b4b3b7c457467b49',
            'api_agent' => 'DGTE0101C7',
            'api_mobile_suffix' => 'XX7',
            'language' => 'en',
            'bet_limit' => 'A',
            'winning_limit' => 0,
            'currency' => 'TWD',
            'domain' => $domain,
            'timezone' => 'Asia/Shanghai',
            'site_code' => 'ABC12',
            'allotment_depth' => 'station',
//            'currency' => 'VND2'
        ];

        // Mocker SCP回戳網域並回傳祖先節點樹及輸贏佔成
        Http::fake([
           "{$domain}/*" => function ($request) {
               $requestData = Arr::get($request->data(), 'tickets');

               $responseData = collect($requestData)->map(function ($data) {
                   return [
                       'tree_path' => 'AAA-BBB-CCC',
                       'allotments' => '1.00-0.99-0.98',
                   ];
               });

               return Http::response([
                     'tree_allotments' => $responseData
                 ], 200);
           }]);

        // 建立玩家遊戲錢包資料
        $this->stationWallet = [
            "id" => 123456789876,
            "player_username" => "demo123",
            "account" => $account = "TEST1001112",
            "password" => "password123",
            "station" => config('dream_game.station_code'),
            "status" => "active",
            "is_activated" => true,
            "balance" => 100,
            "site_code" => Arr::get($this->config, 'site_code'),
            'created_at' => now(),
            'updated_at' => now()
        ];

        // 建立原生注單資料
        $this->rawTicket = [
            "id" => 4218749852,
            "tableId" => 10101,
            "lobbyId" => 1,
            "gameType" => 1,
            "gameId" => 1,
            "shoeId" => 33,
            "playId" => 23,
            "memberId" => 14409364,
            "betTime" => $this->MongoDdDateTime(now()->timezone($this->config['timezone'])->toDateTimeString(), $this->config['timezone']),
            "calTime" => $this->MongoDdDateTime(now()->timezone($this->config['timezone'])->toDateTimeString(), $this->config['timezone']),
            "winOrLoss" => 117.0,
            "balanceBefore" => 1027.62,
            "betPoints" => 60.0,
            "betPointsz" => 0.0,
            "availableBet" => 57.0,
            "userName" => $account,
            "result" => '{"result":"2,2,4","poker":{"banker":"46-20-0","player":"41-51-51"}}',
            "betDetail" => '{"banker":60.0,"bankerW":117.0}',
            "ip" => "149.28.128.232",
            "ext" => "200320B011649",
            "isRevocation" => 1,
            "currencyId" => 8,
            "deviceType" => 1,
            "pluginid" => 0,
            "uuid" => "1c24bad4-1255-3b20-a57e-7979ad75eb98",
            "md5_hash" => "688b600cdacd94f6eed31669fffa5a96",
            "is_converted" => false,
        ];
    }
}