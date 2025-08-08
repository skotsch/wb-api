<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Order;

class FetchOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch orders data from external API and store in database';

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
        $baseUrl = env('WB_API_URL') . '/orders';
        $token = env('WB_API_KEY');

        $dateFrom = '2025-08-01';
        $dateTo = now()->format('Y-m-d');
        $limit = 500;
        $page = 1;
        $count = 0;

        do {
            $this->info("Fetching page $page...");

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
                Order::create([
                    'g_number' => $item['g_number'],
                    'date' => $item['date'],
                    'last_change_date' => $item['last_change_date'],
                    'supplier_article' => $item['supplier_article'],
                    'tech_size' => $item['tech_size'],
                    'barcode' => $item['barcode'],
                    'total_price' => $item['total_price'],
                    'discount_percent' => $item['discount_percent'],
                    'warehouse_name' => $item['warehouse_name'],
                    'oblast' => $item['oblast'],
                    'income_id' => $item['income_id'],
                    'odid' => $item['odid'],
                    'nm_id' => $item['nm_id'],
                    'subject' => $item['subject'],
                    'category' => $item['category'],
                    'brand' => $item['brand'],
                    'is_cancel' => $item['is_cancel'],
                    'cancel_dt' => $item['cancel_dt'],
                ]);
                $count++;
            }

            $page++;
        } while ($page <= ($data['meta']['last_page'] ?? $page));

        $this->info("Загрузка завершена. Загружено $count записей.");
        return 0;
    }
}
