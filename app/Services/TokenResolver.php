<?php

namespace App\Services;

use App\Models\ApiService;
use App\Models\TokenType;
use App\Models\ApiToken;

class TokenResolver
{
    public function getApiKeyForAccount(int $accountId, string $serviceCode): ?string
    {
        $service = ApiService::where('code', $serviceCode)->first();
        $type = TokenType::where('code', 'api_key')->first();

        if (!$service || !$type) {
            return null;
        }

        $token = ApiToken::where([
            'account_id'     => $accountId,
            'api_service_id' => $service->id,
            'token_type_id'  => $type->id,
            'is_active'      => 1,
        ])->first();

        return $token?->value;
    }
}
