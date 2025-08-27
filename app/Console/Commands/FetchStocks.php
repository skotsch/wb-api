<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Stock;
use Illuminate\Support\Carbon;

class FetchStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:stocks {accountId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch stock data from external API and store in database';

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
        
        $baseUrl = env('WB_API_URL') . '/stocks';
        $token = env('WB_API_KEY');
        
        $lastDate = Stock::where('account_id', $accountId)->max('date');
        $dateFrom = $lastDate
            ? Carbon::parse($lastDate)->format('Y-m-d')
            : now()->format('Y-m-d');

        $this->info("Stocks: account={$accountId}, dateFrom={$dateFrom}");
        $limit = 500;
        $page = 1;
        $count = 0;

        do {
            $this->info("Fetching stocks page $page for $dateFrom...");

            $response = Http::get($baseUrl, [
                'dateFrom' => $dateFrom,
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
                Stock::updateOrCreate(
                    [
                        'account_id' => $accountId,
                        'date'       => $item['date'],
                        'nm_id'      => $item['nm_id'],
                        'barcode'    => $item['barcode'],
                        'warehouse_name' => $item['warehouse_name'],
                        'tech_size'  => $item['tech_size'],
                    ],
                    [
                        'last_change_date' => $item['last_change_date'],
                        'supplier_article' => $item['supplier_article'],
                        'quantity'         => $item['quantity'],
                        'quantity_full'    => $item['quantity_full'],
                        'is_supply'        => $item['is_supply'],
                        'is_realization'   => $item['is_realization'],
                        'in_way_to_client' => $item['in_way_to_client'],
                        'in_way_from_client' => $item['in_way_from_client'],
                        'subject'          => $item['subject'],
                        'category'         => $item['category'],
                        'brand'            => $item['brand'],
                        'sc_code'          => $item['sc_code'],
                        'price'            => $item['price'],
                        'discount'         => $item['discount'],
                    ]
                );
                $count++;
            }

            $page++;
        } while ($page <= ($data['meta']['last_page'] ?? $page));

        $this->info("Загрузка завершена. Загружено $count записей.");
        return 0;
    }
}
