<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ApiClient
{
    public function __construct(
        private int $timeoutSeconds = 20,
        private int $maxAttempts = 5
    ) {}

    public function get(string $url, array $query = [])
    {
        $attempt = 0;

        while (true) {
            $attempt++;

            try {
                $resp = Http::timeout($this->timeoutSeconds)->get($url, $query);
            } catch (ConnectionException $e) {
                if ($attempt >= $this->maxAttempts) {
                    throw $e;
                }
                $this->sleepWithBackoff($attempt);
                continue;
            }

            if ($resp->successful()) {
                $json = $resp->json();
                if (!is_array($json)) {
                    if ($attempt >= $this->maxAttempts) {
                        return $resp;
                    }
                    $this->sleepWithBackoff($attempt);
                    continue;
                }
                return $resp;
            }

            if ($resp->status() === 429) {
                if ($attempt >= $this->maxAttempts) {
                    return $resp;
                }
                $retryAfter = (int)($resp->header('Retry-After') ?? 0);
                if ($retryAfter > 0) {
                    sleep($retryAfter);
                } else {
                    $this->sleepWithBackoff($attempt);
                }
                continue;
            }

            if ($resp->serverError()) {
                if ($attempt >= $this->maxAttempts) {
                    return $resp;
                }
                $this->sleepWithBackoff($attempt);
                continue;
            }

            return $resp; // 4xx (кроме 429) — отдаём как есть
        }
    }

    private function sleepWithBackoff(int $attempt): void
    {
        $base = min(1 << ($attempt - 1), 32); // 1,2,4,8,16,32 сек
        $jitterMs = random_int(100, 300);
        usleep(($base * 1000 + $jitterMs) * 1000);
    }
}
