<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $fillable = [
        'account_id','api_service_id','token_type_id',
        'value','login','password','expires_at','is_active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active'  => 'bool',
    ];

    public function account() { return $this->belongsTo(Account::class); }
    public function service() { return $this->belongsTo(ApiService::class, 'api_service_id'); }
    public function type()    { return $this->belongsTo(TokenType::class, 'token_type_id'); }
}
