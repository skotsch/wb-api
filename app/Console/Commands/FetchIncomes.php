<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Income;

class FetchIncomes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:incomes';

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
        $baseUrl = env('WB_API_URL') . '/incomes';
        $token = env('WB_API_KEY');

        $dateFrom = '2024-08-01';
        $dateTo = '2025-07-31';
        $limit = 500;
        $page = 1;
        $count = 0;

        do {
            $this->info("Fetching incomes page $page...");

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
                Income::create([
                    'income_id' => $item['income_id'],
                    'number' => $item['number'],
                    'date' => $item['date'],
                    'last_change_date' => $item['last_change_date'],
                    'supplier_article' => $item['supplier_article'],
                    'tech_size' => $item['tech_size'],
                    'barcode' => $item['barcode'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total_price'],
                    'date_close' => $item['date_close'],
                    'warehouse_name' => $item['warehouse_name'],
                    'nm_id' => $item['nm_id'],
                ]);
                $count++;
            }

            $page++;
        } while ($page <= ($data['meta']['last_page'] ?? $page));

        $this->info("Загрузка завершена. Загружено $count записей.");
        return 0;
    }
}
