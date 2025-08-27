<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Income;
use Illuminate\Support\Carbon;

class FetchIncomes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:incomes {accountId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch incomes data from external API and store in database';

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

        $baseUrl = env('WB_API_URL') . '/incomes';
        $token = env('WB_API_KEY');

        // 1) Берём последнюю дату изменений для этого аккаунта
        $lastDate = Income::where('account_id', $accountId)->max('last_change_date');

        // 2) Если данных нет — берём "с запасом" за последние 30 дней
        $dateFrom = $lastDate
            ? Carbon::parse($lastDate)->format('Y-m-d')
            : now()->subDays(30)->format('Y-m-d');

        $dateTo = now()->format('Y-m-d');

        $this->info("Incomes: account={$accountId}, from={$dateFrom}, to={$dateTo}");

        $limit = 500;
        $page = 1;
        $count = 0;

        do {
            $this->line("Запрашиваю страницу {$page}…");

            $response = Http::timeout(20)->get($baseUrl, [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'limit' => $limit,
                'page' => $page,
                'key' => $token,
            ]);

            if (!$response->successful()) {
            $this->error("Ошибка запроса: HTTP {$response->status()} {$response->body()}");
            return 1;
            }

            $data = $response->json();
            $items = $data['data'] ?? [];

            foreach ($items as $item) {
                Income::updateOrCreate(
                    [
                        'account_id' => $accountId,
                        'income_id'  => $item['income_id'],
                        'nm_id'      => $item['nm_id'],
                        'barcode'    => $item['barcode'],
                        'tech_size'  => $item['tech_size'],
                    ],
                    [
                        'number'           => $item['number'],
                        'date'             => $item['date'],
                        'last_change_date' => $item['last_change_date'],
                        'supplier_article' => $item['supplier_article'],
                        'quantity'         => $item['quantity'],
                        'total_price'      => $item['total_price'],
                        'date_close'       => $item['date_close'],
                        'warehouse_name'   => $item['warehouse_name'],
                    ]
                );
                $count++;
            }

            $page++;
        } while ($page <= ($data['meta']['last_page'] ?? $page));

        $this->info("Загрузка завершена. Загружено {$count} записей.");
        return 0;
    }
}
