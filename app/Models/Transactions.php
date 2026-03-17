<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Transactions extends Model
{

     /** @use HasFactory<\Database\Factories\TransactionsFactory> */
    use HasFactory;

 protected $fillable = [
        'wallet_id', 'type', 'direction',
        'amount', 'reference_no', 'description', 'status',
        'transactable_id', 'transactable_type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Polymorphic
    public function transactable()
    {
        return $this->morphTo();
    }

    // Relationships
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function savingsAllocation()
    {
        return $this->hasOne(SavingsAllocations::class);
    }



   
}
