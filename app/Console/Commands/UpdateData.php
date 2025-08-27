<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;

class UpdateData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all datasets twice a day for all accounts';

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
        $accounts = Account::query()->pluck('id');

        if ($accounts->isEmpty()) {
            $this->warn('Нет аккаунтов для загрузки.');
            return 0;
        }

        foreach ($accounts as $accountId) {
            $this->info("=== Account #{$accountId} ===");
            $this->call('fetch:all', ['accountId' => $accountId]);
        }

        $this->info('UpdateData завершён для всех аккаунтов.');
        return 0;
    }
}
