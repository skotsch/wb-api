<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenType extends Model
{
    protected $fillable = ['code','name'];

    public function tokens()
    {
        return $this->hasMany(ApiToken::class);
    }
}
