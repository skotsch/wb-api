<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use App\Services\ApiClient;

class FetchSales extends Command
{
    protected $signature = 'fetch:sales {accountId}';
    protected $description = 'Fetch sales data from external API and store in database';

    public function __construct(private ApiClient $api)
    {
        parent::__construct();
    }

    public function handle()
    {
        $accountId = (int)$this->argument('accountId');

        $baseUrl = rtrim(env('WB_API_URL'), '/') . '/sales';
        $token   = env('WB_API_KEY');

        // 1) Последняя дата изменений по этому аккаунту
        $lastDate = Sale::where('account_id', $accountId)->max('last_change_date');

        // 2) Если нет данных — берём последние 30 дней
        $dateFrom = $lastDate
            ? Carbon::parse($lastDate)->format('Y-m-d')
            : now()->subDays(30)->format('Y-m-d');

        $dateTo = now()->format('Y-m-d');

        $this->info("Sales: account={$accountId}, from={$dateFrom}, to={$dateTo}");

        $limit = 500;
        $page  = 1;
        $count = 0;

        do {
            $this->line("Запрашиваю страницу {$page}…");

            $response = $this->api->get($baseUrl, [
                'dateFrom' => $dateFrom,
                'dateTo'   => $dateTo,
                'limit'    => $limit,
                'page'     => $page,
                'key'      => $token,
            ]);

            if (!$response->successful()) {
                $this->error("HTTP " . $response->status());
                return 1;
            }

            $data  = $response->json();
            $items = $data['data'] ?? [];

            foreach ($items as $item) {
                Sale::updateOrCreate(
                    [
                        'account_id' => $accountId,
                        'g_number'   => $item['g_number'],
                    ],
                    [
                        'date'               => $item['date'],
                        'last_change_date'   => $item['last_change_date'],
                        'supplier_article'   => $item['supplier_article'],
                        'tech_size'          => $item['tech_size'],
                        'barcode'            => $item['barcode'],
                        'total_price'        => $item['total_price'],
                        'discount_percent'   => $item['discount_percent'],
                        'is_supply'          => $item['is_supply'],
                        'is_realization'     => $item['is_realization'],
                        'promo_code_discount'=> $item['promo_code_discount'],
                        'warehouse_name'     => $item['warehouse_name'],
                        'country_name'       => $item['country_name'],
                        'oblast_okrug_name'  => $item['oblast_okrug_name'],
                        'region_name'        => $item['region_name'],
                        'income_id'          => $item['income_id'],
                        'sale_id'            => $item['sale_id'],
                        'odid'               => $item['odid'],
                        'spp'                => $item['spp'],
                        'for_pay'            => $item['for_pay'],
                        'finished_price'     => $item['finished_price'],
                        'price_with_disc'    => $item['price_with_disc'],
                        'nm_id'              => $item['nm_id'],
                        'subject'            => $item['subject'],
                        'category'           => $item['category'],
                        'brand'              => $item['brand'],
                        'is_storno'          => $item['is_storno'],
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
