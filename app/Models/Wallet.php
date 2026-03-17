<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\WalletFactory;

class Wallet extends Model
{
    use HasFactory;

    protected $table = 'wallets';

    protected $fillable = [
        'user_id',
        'balance',
        'currency',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    // Explicitly point to WalletFactory
    protected static function newFactory()
    {
        return WalletFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transactions::class);
    }
}