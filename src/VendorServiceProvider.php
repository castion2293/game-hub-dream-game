<?php

namespace SuperStation\Gamehub\DreamGame;

use SuperStation\Gamehub\VendorProviderAbstract;

class VendorServiceProvider extends VendorProviderAbstract
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // 合併套件 config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/dream_game.php', 'dream_game'
        );
    }

    /**
     * 取得遊戲服務器的代碼
     */
    public function getVendorCode(): string
    {
        return 'dream_game';
    }

    /**
     * 取得支援的「驅動器」類別名稱
     */
    public function getVendorDriver(): string
    {
        return Driver::class;
    }

    /**
     * 取得支援的「抓取器」類別名稱
     */
    public function getVendorFetcher(): string
    {
        return Fetcher::class;
    }

    /**
     * 取得支援的「轉換器」類別名稱
     */
    public function getVendorConverter(): string
    {
        return Converter::class;
    }

    /**
     * 取得支援的「路由注冊器」類別名稱
     */
    public function getVendorRouter(): string
    {
        return Router::class;
    }

    /**
     * 取得支援的「接受器」類別名稱
     */
    public function getVendorReceiver(): string
    {
        return Receiver::class;
    }
}