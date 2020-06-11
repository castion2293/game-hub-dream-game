<?php

namespace SuperStation\Gamehub\DreamGame;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use SuperStation\Gamehub\Abstracts\ConverterAbstract;
use SuperStation\Gamehub\Contracts\ConverterContract;
use SuperStation\Gamehub\Contracts\DriverContract;
use SuperStation\Gamehub\Models\UnitedTicket;
use SuperStation\Gamehub\Traits\CurrencyTrait;

class Converter extends ConverterAbstract implements ConverterContract
{
    use CurrencyTrait;

    /**
     * 遊戲館
     *
     * @var string
     */
    protected $station = '';

    public function __construct(DriverContract $driver)
    {
        parent::__construct($driver);

        $this->station = config('dream_game.station_code');
    }

    /**
     * 從MongoDB 撈取原生注單
     *
     * @param string $fromTime
     * @param string $toTime
     * @return ConverterContract
     */
    public function getRawTicketsFromNoSQL(string $fromTime = '', string $toTime = ''): ConverterContract
    {
        // 自動撈單沒有設定起訖時間 這裡需實作各個遊戲站的起訖時間
        if (empty($fromTime) && empty($toTime)) {
            $fromTime = now()->subMinutes(30)->toDateTimeString();
            $toTime = now()->toDateTimeString();
        }

        $this->rawTickets = DB::connection('mongodb')
            ->collection(config('dream_game.db_collection_name'))
            ->whereBetween('betTime', [Carbon::parse($fromTime), Carbon::parse($toTime)])
            ->where('is_converted', false)
            ->get()
            ->toArray();

        return $this;
    }

    /**
     * 注單轉換
     *
     * @param array $rawTickets (單一錢包 注單資訊使用回戳方式 就帶入這裡)
     * @return ConverterContract
     * @throws \Exception
     */
    public function convert(array $rawTickets = []): ConverterContract
    {
        // 手動撈單或單一錢包回戳使用
        if (!empty($rawTickets)) {
            $this->rawTickets = $rawTickets;
        }

        // 如果沒有注單就返回
        if (empty($this->rawTickets)) {
            return $this;
        }

        // 取得注單內所有的player_username
        $walletAccounts = collect($this->rawTickets)->pluck('userName')->unique()->toArray();

        $playerUsernames = DB::table('station_wallets')
            ->select('account', 'player_username')
            ->whereIn('account', $walletAccounts)
            ->where('station', config('dream_game.station_code'))
            ->get()
            ->mapWithKeys(function ($wallet) {
                return [
                    data_get($wallet, 'account') => data_get($wallet, 'player_username')
                ];
            });

        foreach ($this->rawTickets as $rawTicket) {
            $account = Arr::get($rawTicket, 'userName');
            $playerUsername = Arr::get($playerUsernames, $account, null);

            if (empty($playerUsername)) {
                continue;
            }

            // =============================================
            //    整理「原生注單」對應「整合注單」的各欄位資料
            // =============================================
            if (is_numeric($betNumber = Arr::get($rawTicket, 'id'))) {
                $betNumber = strval($betNumber);
            }

            // 遊戲種類
            $gameType = Arr::get($rawTicket, 'gameType');
            $gameId = Arr::get($rawTicket, 'gameId');
            $tableId = Arr::get($rawTicket, 'tableId');
            $gameScope = Arr::get(config('dream_game.game_scope'), "{$gameType}.{$gameId}.{$tableId}", "");

            // 投注金額及輸贏結果
            $rawBet = Arr::get($rawTicket, 'betPoints');
            $validBet = $rolling = Arr::get($rawTicket, 'availableBet');
            $winnings = Arr::get($rawTicket, 'winOrLoss') - $rawBet;

            // 投注及派彩時間
            $betAt = Arr::get($rawTicket, 'betTime');
            if (!empty($betAt)) {
                $betAt = $betAt->toDateTime()->format('Y-m-d H:i:s');
            }

            $is_payout = false;
            $payoutAt = Arr::get($rawTicket, 'calTime');
            if (!empty($payoutAt)) {
                $is_payout = true;
                $payoutAt = $payoutAt->toDateTime()->format('Y-m-d H:i:s');
            }

            $unitedTicket = [
                // 原生注單的 uuid 識別碼等同於整合注單的識別碼 id
                'id' => Arr::get($rawTicket, 'uuid'),
                // 原生注單編號
                'bet_number' => $betNumber,
                // 玩家識別碼
                'player_username' => $playerUsername,
                // 玩家錢包帳戶帳號
                'account' => $account,
                // 遊戲館名稱
                'station' => $this->station,
                // 遊戲類型
                'game_scope' => $gameScope,
                // 實際投注
                'raw_bet' => $this->currencyTransformer(
                    'convert',
                    $this->driver->getCurrency(),
                    config('dream_game.currency_rate'),
                    $rawBet
                ),
                // 有效投注(一般會跟實際投注相同 除非遊戲館有自己定義)
                'valid_bet' => $this->currencyTransformer(
                    'convert',
                    $this->driver->getCurrency(),
                    config('dream_game.currency_rate'),
                    $validBet
                ),
                // 洗碼量(一般會跟實際投注相同 除非遊戲館有自己定義)
                'rolling' => $this->currencyTransformer(
                    'convert',
                    $this->driver->getCurrency(),
                    config('dream_game.currency_rate'),
                    $rolling
                ),
                // 輸贏結果(看各遊戲館的定義，如果沒有就是派彩金額 - 投注金額)
                'winnings' => $this->currencyTransformer(
                    'convert',
                    $this->driver->getCurrency(),
                    config('dream_game.currency_rate'),
                    $winnings
                ),
                // 注單是否有效
                'is_invalid' => false,
                // 投注時間
                'bet_at' => $betAt,
                // 結算狀態
                'is_payout' => $is_payout,
                // 結算時間
                'payout_at' => $payoutAt,
                // 營運站代碼
                'site_code' => $this->driver->getSiteCode(),
                // 資料建立時間
                'created_at' => now(),
                // 資料最後更新
                'updated_at' => now()
            ];

            array_push($this->unitedTickets, $unitedTicket);
        }

        // 回戳 {SCP域名}/tree_allotments 尋找注單的節點樹及輸贏佔成
        $treeAllotments = $this->findTreeAllotment($this->unitedTickets);

        $this->unitedTickets = collect($this->unitedTickets)->map(
            function ($unitedTicket) use ($treeAllotments) {
                $id = Arr::get($unitedTicket, 'id');

                $unitedTicket['tree_path'] = Arr::get($treeAllotments, "{$id}.tree_path");
                $unitedTicket['allotment_path'] = Arr::get($treeAllotments, "{$id}.allotments");

                return $unitedTicket;
            }
        );

        return $this;
    }

    /**
     * 存整合注單至MySQL
     *
     * @return ConverterContract
     * @throws \Exception
     */
    public function saveIntoDatabase(): ConverterContract
    {
        // 如果沒有注單就返回
        if (empty($this->unitedTickets)) {
            return $this;
        }

        // 儲存整合注單
        try {
            UnitedTicket::replace($this->unitedTickets);
        } catch (\Exception $exception) {
//                event(new ConvertExceptionOccurred(
//                          $exception,
//                          '',
//                          'replace_into',
//                          []
//                      ));
            throw $exception;
        }

        // 修改NoSQL 原生注單的 is_converted = true 標記轉換成功
        $uuids = collect($this->unitedTickets)->pluck('id')->toArray();

        DB::connection('mongodb')
            ->collection(config('dream_game.db_collection_name'))
            ->whereIn('uuid', $uuids)
            ->update(
                [
                    'is_converted' => true
                ]
            );

        return $this;
    }

    /**
     * 回傳遊戲整合注單結果
     *
     * @return array
     */
    public function report(): array
    {
        return [
            'united_tickets' => $this->unitedTickets
        ];
    }

    /**
     * 回傳注單的投注內容及開牌結果
     *
     * @param string $uuid
     * @return array
     */
    public function gameResult(string $uuid): array
    {
        $rawTicket = DB::connection('mongodb')
            ->collection(config('dream_game.db_collection_name'))
            ->where('uuid', $uuid)
            ->first();

        // 找遊戲種類
        $gameType = Arr::get($rawTicket, 'gameType');
        $gameId = Arr::get($rawTicket, 'gameId');
        $tableId = Arr::get($rawTicket, 'tableId');
        $gameScope = Arr::get(config('dream_game.game_scope'), "{$gameType}.{$gameId}.{$tableId}", "");

        return [
            'bet_detail' => Arr::get($rawTicket, 'betDetail'),
            'result' => Arr::get($rawTicket, 'result'),
            'meta' => [
                'flusher' => config('dream_game.game_result.flusher'),
                'type' => config("dream_game.game_result.{$gameScope}")
            ]
        ];
    }
}