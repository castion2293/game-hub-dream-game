<?php

namespace SuperStation\Gamehub\DreamGame\Tests\Multiple;


use Illuminate\Support\Facades\Queue;
use SuperStation\Gamehub\Jobs\AutoConvertTicketJob;
use SuperStation\Gamehub\Jobs\AutoFetchTicketJob;

class JobTestMultipleWallet extends DreamGameMultipleWalletBaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * 測試 DG 自動撈單部署
     */
    public function testAutoFetchTicketJob()
    {
        $this->console->writeln('測試 DG 自動撈單部署');

        Queue::fake();

        dispatch(new AutoFetchTicketJob(config('dream_game.station_code'), $this->config));

        // Assert
        Queue::assertPushed(AutoFetchTicketJob::class, function ($job) {
            return $job->station === config('dream_game.station_code');
        });

        Queue::assertPushed(AutoFetchTicketJob::class, 1);
    }

    /**
     * 測試 DG 自動轉單部署
     */
    public function testAutoConvertTicketJob()
    {
        $this->console->writeln('測試 DG 自動轉單部署');

        Queue::fake();

        dispatch(new AutoConvertTicketJob(config('dream_game.station_code'), $this->config));

        // Assert
        Queue::assertPushed(AutoConvertTicketJob::class, function ($job) {
            return $job->station === config('dream_game.station_code');
        });

        Queue::assertPushed(AutoConvertTicketJob::class, 1);
    }
}