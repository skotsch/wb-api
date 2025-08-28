<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stock;
use App\Models\ApiService;
use Illuminate\Support\Carbon;
use App\Services\ApiClient;
use App\Services\TokenResolver;

class FetchStocks extends Command
{
    protected $signature = 'fetch:stocks {accountId} {service}';
    protected $description = 'Fetch stock data from external API and store in database';

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

        $baseUrl = rtrim($service->base_url, '/') . '/stocks';

        // Берём токен из БД по account_id + serviceCode + token_type=api_key
        $token = $this->tokens->getApiKeyForAccount($accountId, $serviceCode);
        if (!$token) {
            $this->error("Нет активного api_key для account={$accountId}, service={$serviceCode}.");
            return 1;
        }

        // Берём последнюю "срезовую" дату для аккаунта; если нет — сегодня
        $lastDate = Stock::where('account_id', $accountId)->max('date');
        $dateFrom = $lastDate
            ? Carbon::parse($lastDate)->format('Y-m-d')
            : now()->format('Y-m-d');

        $this->info("Stocks: account={$accountId}, dateFrom={$dateFrom}");

        $limit = 500;
        $page  = 1;
        $count = 0;

        do {
            $this->line("Запрашиваю страницу {$page}…");

            $response = $this->api->get($baseUrl, [
                'dateFrom' => $dateFrom,
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
                Stock::updateOrCreate(
                    [
                        'account_id'     => $accountId,
                        'date'           => $item['date'],
                        'nm_id'          => $item['nm_id'],
                        'barcode'        => $item['barcode'],
                        'warehouse_name' => $item['warehouse_name'],
                        'tech_size'      => $item['tech_size'],
                    ],
                    [
                        'last_change_date'  => $item['last_change_date'],
                        'supplier_article'  => $item['supplier_article'],
                        'quantity'          => $item['quantity'],
                        'quantity_full'     => $item['quantity_full'],
                        'is_supply'         => $item['is_supply'],
                        'is_realization'    => $item['is_realization'],
                        'in_way_to_client'  => $item['in_way_to_client'],
                        'in_way_from_client'=> $item['in_way_from_client'],
                        'subject'           => $item['subject'],
                        'category'          => $item['category'],
                        'brand'             => $item['brand'],
                        'sc_code'           => $item['sc_code'],
                        'price'             => $item['price'],
                        'discount'          => $item['discount'],
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
