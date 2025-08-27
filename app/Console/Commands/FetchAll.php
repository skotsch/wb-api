<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Throwable;

class FetchAll extends Command
{
    protected $signature = 'fetch:all {accountId}';
    protected $description = 'Fetch all datasets: sales, orders, stocks, incomes';

    public function handle()
    {
        $accountId = (int)$this->argument('accountId');

        $tasks = [
            'fetch:sales'   => '1. Продажи',
            'fetch:orders'  => '2. Заказы',
            'fetch:stocks'  => '3. Остатки',
            'fetch:incomes' => '4. Приходы',
        ];

        $results = [];

        foreach ($tasks as $cmd => $title) {
            $this->info("{$title}: запуск для account={$accountId}");

            try {
                $code = $this->call($cmd, ['accountId' => $accountId]);
            } catch (Throwable $e) {
                $this->error("{$title}: исключение — {$e->getMessage()}");
                $code = 1;
            }

            $results[$cmd] = ($code === 0) ? 'OK' : 'FAIL';
        }

        // Сводка
        $ok   = array_sum(array_map(fn($r) => $r === 'OK' ? 1 : 0, $results));
        $fail = count($results) - $ok;

        $this->line(str_repeat('-', 40));
        $this->info("ИТОГО: OK={$ok}, FAIL={$fail}");
        foreach ($results as $cmd => $r) {
            $this->line(sprintf(" - %-13s : %s", $cmd, $r));
        }

        // Если были ошибки — вернём 1, чтобы планировщик/CI это увидел
        return $fail > 0 ? 1 : 0;
    }
}
