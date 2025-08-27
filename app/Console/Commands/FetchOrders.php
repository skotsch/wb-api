<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Carbon;
use App\Services\ApiClient;

class FetchOrders extends Command
{
    protected $signature = 'fetch:orders {accountId}';
    protected $description = 'Fetch orders data from external API and store in database';

    public function __construct(private ApiClient $api)
    {
        parent::__construct();
    }

    public function handle()
    {
        $accountId = (int)$this->argument('accountId');

        $baseUrl = rtrim(env('WB_API_URL'), '/') . '/orders';
        $token   = env('WB_API_KEY');

        // 1) последняя дата изменений для этого аккаунта
        $lastDate = Order::where('account_id', $accountId)->max('last_change_date');

        // 2) если нет данных — берём за последние 30 дней
        $dateFrom = $lastDate
            ? Carbon::parse($lastDate)->format('Y-m-d')
            : now()->subDays(30)->format('Y-m-d');

        $dateTo = now()->format('Y-m-d');

        $this->info("Orders: account={$accountId}, from={$dateFrom}, to={$dateTo}");

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
                Order::updateOrCreate(
                    [
                        'account_id' => $accountId,
                        'g_number'   => $item['g_number'],
                        'nm_id'      => $item['nm_id'],
                        'barcode'    => $item['barcode'],
                        'tech_size'  => $item['tech_size'],
                    ],
                    [
                        'date'             => $item['date'],
                        'last_change_date' => $item['last_change_date'],
                        'supplier_article' => $item['supplier_article'],
                        'total_price'      => $item['total_price'],
                        'discount_percent' => $item['discount_percent'],
                        'warehouse_name'   => $item['warehouse_name'],
                        'oblast'           => $item['oblast'],
                        'income_id'        => $item['income_id'],
                        'odid'             => $item['odid'],
                        'subject'          => $item['subject'],
                        'category'         => $item['category'],
                        'brand'            => $item['brand'],
                        'is_cancel'        => $item['is_cancel'],
                        'cancel_dt'        => $item['cancel_dt'],
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
