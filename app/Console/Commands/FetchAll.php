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
    protected $signature = 'fetch:all';

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
        $this->info('1. Запуск fetch:sales');
        $this->call('fetch:sales');

        $this->info('2. Запуск fetch:orders');
        $this->call('fetch:orders');

        $this->info('3. Запуск fetch:stocks');
        $this->call('fetch:stocks');

        $this->info('4. Запуск fetch:incomes');
        $this->call('fetch:incomes');

        $this->info('Все данные загружены!');
        return 0;    }
}
