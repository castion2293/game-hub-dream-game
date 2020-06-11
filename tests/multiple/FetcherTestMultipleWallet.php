<?php

namespace SuperStation\Gamehub\DreamGame\Tests\Multiple;

use SuperStation\Gamehub\Facades\Gamehub;

class FetcherTestMultipleWallet extends DreamGameMultipleWalletBaseTestCase
{
    /**
     * 遊戲注單抓取器服務中心
     *
     * @var
     */
    protected $fetcher;

    public function setUp(): void
    {
        parent::setUp();

        // 建立遊戲注單抓取器服務中心
        $this->fetcher = Gamehub::fetcher('dream_game', $this->config);
    }

    /**
     * 測試 DG 遊戲注單撈取器 自動撈單
     */
    public function testFetcherAuto()
    {
        try {
            $tickets = $this->fetcher->setPlayerParams()
                ->autoFetchTimeSpan()
                ->capture()
                ->compare()
                ->saveIntoNoSQL()
                ->report();

            dump($tickets);
            $this->assertArrayHasKey('raw_tickets', $tickets);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 測試 DG 遊戲注單撈取器 手動撈單
     */
    public function testFetcherManual()
    {
        try {
            $tickets = $this->fetcher->setPlayerParams()
                ->setTimeSpan('2020-03-20 00:00:00', '2020-03-20 23:59:59')
                ->capture()
                ->compare()
                ->saveIntoNoSQL()
                ->report();

            dump($tickets);
            $this->assertArrayHasKey('raw_tickets', $tickets);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }
}