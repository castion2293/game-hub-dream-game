<?php

namespace SuperStation\Gamehub\DreamGame;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use SuperStation\Gamehub\Abstracts\FetcherAbstract;
use SuperStation\Gamehub\Contracts\DriverContract;
use SuperStation\Gamehub\Contracts\FetcherContract;
use SuperStation\Gamehub\DreamGame\Models\DreamGameTicket;
use SuperStation\Gamehub\Traits\DateTimeTrait;

class Fetcher extends FetcherAbstract implements FetcherContract
{
    use DateTimeTrait;

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
     * 設定手動撈單時間區間
     * @param string $fromTime
     * @param string $toTime
     * @return FetcherContract
     */
    public function setTimeSpan(string $fromTime = '', string $toTime = ''): FetcherContract
    {
        $this->formParams['mode'] = 'manual';

        if (empty($fromTime) && empty($toTime)) {
            $this->formParams['beginTime'] = Carbon::parse($fromTime)->subHour(1)->toDateTimeString();
            $this->formParams['endTime'] = Carbon::parse($toTime)->toDateTimeString();
            return $this;
        }

        $this->formParams['beginTime'] = Carbon::parse($fromTime)->toDateTimeString();
        $this->formParams['endTime'] = Carbon::parse($toTime)->toDateTimeString();

        return $this;
    }

    /**
     * 設定自動撈單時間區間
     */
    public function autoFetchTimeSpan(): FetcherContract
    {
        $this->formParams['mode'] = 'auto';

        return $this;
    }

    /**
     * 設定玩家資料 DG是用代理撈單不需要做設定
     * @param array $wallet
     * @return FetcherContract
     */
    public function setPlayerParams(array $wallet = []): FetcherContract
    {
        return $this;
    }

    /**
     * 撈取遊戲原生注單
     */
    public function capture(): FetcherContract
    {
        // 撈單開始執行並取得開始時間
        $startTime = Arr::get($this->formParams, 'beginTime', '');
        $endTime = Arr::get($this->formParams, 'endTime', '');
        $this->captureBegin($this->station, $startTime, $endTime);

        // 以遞迴方式取出回應內容
        $this->curl();

        // 撈單結束執行並取得花費時間
        $this->captureEnd();

        return $this;
    }

    /**
     * 比對這次原生注單與上一次撈取到的注單狀態
     */
    public function compare(): FetcherContract
    {
        // 如果沒有注單就返回
        if (empty($this->rawTickets)) {
            return $this;
        }

        $uuids = collect($this->rawTickets)->pluck('uuid')->toArray();

        $dbTickets = DB::connection('mongodb')
            ->collection(config('dream_game.db_collection_name'))
            ->select('_id', 'md5_hash')
            ->whereIn('uuid', $uuids)
            ->get()
            ->mapWithKeys(
                function ($dbTicket) {
                    return [
                        data_get($dbTicket, 'uuid') => data_get($dbTicket, 'md5_hash')
                    ];
                }
            )
            ->toArray();

        $this->rawTickets = collect($this->rawTickets)->filter(
            function ($rawTicket) use ($dbTickets) {
                $rawTicketMd5HashValue = Arr::get($rawTicket, 'md5_hash');
                $dbTicketMd5HashValue = Arr::get($dbTickets, Arr::get($rawTicket, 'uuid'));

                return $rawTicketMd5HashValue != $dbTicketMd5HashValue;
            }
        );

        return $this;
    }

    /**
     * 存原生注單至MongoDB
     *
     * @param bool $isConverted (true: 手動撈單 直接做轉換, false: 自動撈單)
     * @param array $rawTickets (單一錢包 注單資訊使用回戳方式 就帶入這裡)
     * @return FetcherContract
     * @throws \Exception
     */
    public function saveIntoNoSQL(bool $isConverted = false, array $rawTickets = []): FetcherContract
    {
        // 單一錢包遊戲回戳使用
        if (!empty($rawTickets)) {
            $this->rawTickets = $rawTickets;
        }

        // 如果沒有注單就返回
        if (empty($this->rawTickets)) {
            return $this;
        }

        $this->rawTickets = collect($this->rawTickets)->map(function ($rawTicket) use ($isConverted) {
            $rawTicket['is_converted'] = $isConverted;

            // 轉成MongoDB的時間格式 UTC+0 for mongoDB Timestamp
            $rawTicket['betTime'] = $this->MongoDdDateTime(
                Arr::get($rawTicket, 'betTime'),
                $this->driver->getStationTimeZone()
            );
            $rawTicket['calTime'] = $this->MongoDdDateTime(
                Arr::get($rawTicket, 'calTime'),
                $this->driver->getStationTimeZone()
            );

            DB::connection('mongodb')
                ->collection(config('dream_game.db_collection_name'))
                ->where('uuid', Arr::get($rawTicket, 'uuid'))
                ->update($rawTicket, ['upsert' => true]);

            return $rawTicket;
        })->toArray();

        // 標記回戳處理過的注單
        $lists = collect($this->rawTickets)->pluck('id')->toArray();
        $this->driver->markTickets($lists);

        return $this;
    }

    /**
     * 回傳遊戲原生注單結果
     */
    public function report(): array
    {
        return [
            'raw_tickets' => $this->rawTickets
        ];
    }

    /**
     * 遞迴式 CURL 請求
     */
    private function curl()
    {
        $response = $this->driver->fetchTickets($this->formParams);

        $tickets = Arr::get($response, 'response.data');

        // 如果沒有注單就返回
        if (empty($tickets)) {
            return;
        }

        // 因為如果是單一一張注單，需把它加到一個陣列中的元素，避免錯誤
        $tempArray = $tickets;

        if (!is_array(array_shift($tempArray))) {
            $tickets = [$tickets];
        };

        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //   重要！！每個遊戲站都要為自己處理這一塊
        //
        //  增加注單唯一識別碼uuid
        //  增加注單比對碼 md5_hash
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        foreach ($tickets as $ticket) {
            $rawTicketModel = new DreamGameTicket($ticket);
            array_push($this->rawTickets, $rawTicketModel->toArray());
        }

        return;
    }
}