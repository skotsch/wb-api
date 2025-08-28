<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\ApiService;

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
    protected $description = 'Fetch all datasets for all accounts and all services that have active tokens twice a day for all accounts';

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

        $ok = 0; $fail = 0;

        foreach ($accounts as $accountId) {
            // все сервисы, по которым у аккаунта есть активные токены
            $serviceCodes = ApiService::query()
                ->select('api_services.code')
                ->join('api_tokens', 'api_tokens.api_service_id', '=', 'api_services.id')
                ->where('api_tokens.account_id', $accountId)
                ->where('api_tokens.is_active', true)
                ->distinct()
                ->pluck('code');

            if ($serviceCodes->isEmpty()) {
                $this->warn("Account #{$accountId}: нет активных токенов — пропуск.");
                continue;
            }

            foreach ($serviceCodes as $serviceCode) {
                $this->info("=== Account #{$accountId}, service={$serviceCode} ===");
                $code = $this->call('fetch:all', [
                    'accountId' => $accountId,
                    'service'   => $serviceCode,
                ]);

                if ($code === 0) { $ok++; } else { $fail++; }
            }
        }

        $this->info("UpdateData завершён. OK={$ok}, FAIL={$fail}");
        return $fail > 0 ? 1 : 0;
    }
}
