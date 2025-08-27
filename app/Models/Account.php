<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['company_id', 'name', 'external_id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function tokens()
    {
        return $this->hasMany(ApiToken::class);
    }

    // Примеры удобных связей к данным
    public function sales()   { return $this->hasMany(Sale::class); }
    public function orders()  { return $this->hasMany(Order::class); }
    public function stocks()  { return $this->hasMany(Stock::class); }
    public function incomes() { return $this->hasMany(Income::class); }

    public function activeTokenFor(string $serviceCode)
    {
        return $this->tokens()
            ->whereHas('service', fn($q) => $q->where('code', $serviceCode))
            ->where('is_active', true)
            ->orderByDesc('expires_at')
            ->first();
    }
}
