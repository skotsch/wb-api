<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use App\Models\Sale;
use Illuminate\Support\Carbon;

class FetchSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:sales {accountId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch sales data from external API and store in database';

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

        $baseUrl = env('WB_API_URL') . '/sales';
        $token = env('WB_API_KEY');

        // 1) Берём последнюю дату изменений для этого аккаунта
        $lastDate = Sale::where('account_id', $accountId)->max('last_change_date');

        // 2) Если данных нет — берём "с запасом" за последние 30 дней
        $dateFrom = $lastDate
            ? Carbon::parse($lastDate)->format('Y-m-d')
            : now()->subDays(30)->format('Y-m-d');

        $dateTo = now()->format('Y-m-d');

        $this->info("Sales: account={$accountId}, from={$dateFrom}, to={$dateTo}");

        $limit = 500;
        $page = 1;
        $count = 0;

        do {
            $this->line("Запрашиваю страницу {$page}…");

            $response = Http::get($baseUrl, [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'limit' => $limit,
                'page' => $page,
                'key' => $token,
            ]);

            if (!$response->successful()) {
                $this->error("Ошибка запроса: " . $response->status());
                return 1;
            }

            $data = $response->json();
            $items = $data['data'] ?? [];

            foreach ($items as $item) {
                Sale::updateOrCreate(
                    [
                        'account_id' => $accountId,
                        'g_number' => $item['g_number'],
                    ],
                    [
                        'date' => $item['date'],
                        'last_change_date' => $item['last_change_date'],
                        'supplier_article' => $item['supplier_article'],
                        'tech_size' => $item['tech_size'],
                        'barcode' => $item['barcode'],
                        'total_price' => $item['total_price'],
                        'discount_percent' => $item['discount_percent'],
                        'is_supply' => $item['is_supply'],
                        'is_realization' => $item['is_realization'],
                        'promo_code_discount' => $item['promo_code_discount'],
                        'warehouse_name' => $item['warehouse_name'],
                        'country_name' => $item['country_name'],
                        'oblast_okrug_name' => $item['oblast_okrug_name'],
                        'region_name' => $item['region_name'],
                        'income_id' => $item['income_id'],
                        'sale_id' => $item['sale_id'],
                        'odid' => $item['odid'],
                        'spp' => $item['spp'],
                        'for_pay' => $item['for_pay'],
                        'finished_price' => $item['finished_price'],
                        'price_with_disc' => $item['price_with_disc'],
                        'nm_id' => $item['nm_id'],
                        'subject' => $item['subject'],
                        'category' => $item['category'],
                        'brand' => $item['brand'],
                        'is_storno' => $item['is_storno'],
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
