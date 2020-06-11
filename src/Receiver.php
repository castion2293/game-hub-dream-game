<?php

namespace SuperStation\Gamehub\DreamGame;

use Illuminate\Support\Facades\DB;
use SuperStation\Gamehub\Abstracts\ReceiverAbstract;

class Receiver extends ReceiverAbstract
{
    /**
     * 取得玩家帳戶交易紀錄
     *
     * @param string $serialNo
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function getPlayerAccountTradeRecord(string $serialNo)
    {
        return DB::table('player_account_trade_records')
            ->select('serial_no', 'status')
            ->where('serial_no', $serialNo)
            ->latest()
            ->first();
    }
}