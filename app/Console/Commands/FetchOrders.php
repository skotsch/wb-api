<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\ApiService;
use Illuminate\Support\Carbon;
use App\Services\ApiClient;
use App\Services\TokenResolver;

class FetchOrders extends Command
{
    protected $signature = 'fetch:orders {accountId} {service}';
    protected $description = 'Fetch orders data from external API and store in database';

    public function __construct(
        private ApiClient $api, 
        private TokenResolver $tokens
        )
    {
        parent::__construct();
    }

    public function handle()
    {
        $accountId = (int)$this->argument('accountId');
        $serviceCode = (string)$this->argument('service');

        // Берём сервис из БД
        $service = ApiService::where('code', $serviceCode)->first();
        if (!$service || empty($service->base_url)) {
            $this->error("API service '{$serviceCode}' не найден или не задан base_url в БД.");
            return 1;
        }

        $baseUrl = rtrim($service->base_url, '/') . '/orders';
        // Берём токен из БД по account_id + serviceCode + token_type=api_key
        $token = $this->tokens->getApiKeyForAccount($accountId, $serviceCode);
        if (!$token) {
            $this->error("Нет активного api_key для account={$accountId}, service={$serviceCode}.");
            return 1;
        }

        // Последняя дата изменений для этого аккаунта
        $lastDate = Order::where('account_id', $accountId)->max('last_change_date');

        // Если нет данных — берём за последние 30 дней
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
                $this->error("HTTP " . $response->status() . " BODY: " . $response->body());
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
