<?php

namespace SuperStation\Gamehub\DreamGame\Tests\Single;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use SuperStation\Gamehub\DreamGame\Receiver;

class ControllerTestSingleWallet extends DreamGameSingleWalletBaseTestCase
{
    /**
     * 接受器
     *
     * @var
     */
    protected $receiver;

    public function setUp(): void
    {
        parent::setUp();

        $this->receiver = new Receiver();

        // Arrange
        // 在MySQL建立玩家遊戲錢包資料
        DB::table('player_accounts')
            ->insert($this->playerAccount);
    }

    /**
     * 測試 DG 取得玩家帳戶餘額
     */
    public function testGetPlayerAccountBalance()
    {
        try {
            $this->console->writeln('測試 DG 取得玩家帳戶餘額');

            $username = Arr::get($this->playerAccount, 'account');
            $data = $this->receiver
                ->getPlayerAccount($username)
                ->checkPlayerAccount([
                    Receiver::$AccountExist,
                    Receiver::$AccountFreezing
                ])
                ->getBalance();

            $this->assertArrayHasKey('balance', $data);
            $this->assertEquals($this->playerAccount['balance'], Arr::get($data, 'balance'));
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * 測試 DG 修改玩家帳戶餘額
     */
    public function testChangePlayerAccountBalance()
    {
        try {
            $this->console->writeln('測試 DG 修改玩家帳戶餘額');

            // Arrange
            $username = Arr::get($this->playerAccount, 'account');
            $amount = -50.0;

            // 轉帳前餘額
            $beforeBalance = $this->receiver
                ->getPlayerAccount($username)
                ->getBalance();

            // Action
            // 轉帳動作
            $playerAccount = $this->receiver
                    ->getPlayerAccount($username)
                    ->checkPlayerAccount([
                         Receiver::$AccountExist,
                         Receiver::$AccountFreezing,
                         Receiver::$BalanceEnough,
                     ],[
                         'amount' => $amount
                     ])
                    ->changeBalance($amount);

            // Assert
            $afterBalance = $this->receiver
                ->getPlayerAccount($username)
                ->getBalance();

            $this->assertEquals(Arr::get($afterBalance, 'balance'), Arr::get($beforeBalance, 'balance') + $amount);
        } catch (\Exception $exception) {
            $this->console->writeln($exception->getMessage());
            throw $exception;
        }
    }
}