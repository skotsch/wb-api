<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FetchAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:all {accountId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all datasets: sales, orders, stocks, incomes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $accountId = (int)$this->argument('accountId');

        $this->info("1. Запуск fetch:sales для account={$accountId}");
        $this->call('fetch:sales', ['accountId' => $accountId]);

        $this->info("2. Запуск fetch:orders для account={$accountId}");
        $this->call('fetch:orders', ['accountId' => $accountId]);

        $this->info("3. Запуск fetch:stocks для account={$accountId}");
        $this->call('fetch:stocks', ['accountId' => $accountId]);

        $this->info("4. Запуск fetch:incomes для account={$accountId}");
        $this->call('fetch:incomes', ['accountId' => $accountId]);

        $this->info('Все данные загружены!');
        return 0;
    }
}
