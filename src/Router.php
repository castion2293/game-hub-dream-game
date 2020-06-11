<?php

namespace SuperStation\Gamehub\DreamGame;

use Illuminate\Support\Facades\Route;
use SuperStation\Gamehub\Contracts\RouterContract;

class Router implements RouterContract
{
    /**
     * 註冊路由
     */
    public function register(): void
    {
        Route::group(
            [
                'namespace' => '\SuperStation\Gamehub\DreamGame\Http\Controllers',
                'prefix' => 'dream_game',
                'middleware' => 'ip_whitelist:43.245.200.169,43.245.200.170,43.245.200.171,96.9.85.73'
            ],
            function () {
                // 獲取玩家餘額
                Route::post('/user/getBalance/{agentName}', 'DreamGameController@getBalance')->name(
                    'dream_game.get_balance'
                );

                // 存取款接口
                Route::post('/account/transfer/{agentName}', 'DreamGameController@transfer')->name(
                    'dream_game.transfer'
                );

                // 確認存取款結果接口
                Route::post('/account/checkTransfer/{agentName}', 'DreamGameController@checkTransfer')->name(
                    'dream_game.check_transfer'
                );

                // 請求回滾轉帳事務
                Route::post('/account/inform/{agentName}', 'DreamGameController@inform')->name('dream_game.inform');

                // 請求對帳接口
                Route::post('/account/order/{agentName}', 'DreamGameController@order')->name('dream_game.order');

                // 查詢未派彩和未回滾紀錄
                Route::post('/account/unsettle/{agentName}', 'DreamGameController@unsettle')->name(
                    'dream_game.unsettle'
                );
            }
        );
    }
}