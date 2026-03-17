<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadPurchases extends Model
{
    /** @use HasFactory<\Database\Factories\LoadPurchasesFactory> */
    use HasFactory;

    protected $fillable = [
        'wallet_id', 'mobile_number', 'network', 'promo_code', 'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Polymorphic
    public function transaction()
    {
        return $this->morphOne(Transactions::class, 'transactable');
    }

    // Relationships
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
