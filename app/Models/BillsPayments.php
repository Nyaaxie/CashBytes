<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillsPayments extends Model
{
    /** @use HasFactory<\Database\Factories\BillsPaymentsFactory> */
    use HasFactory;

     protected $fillable = [
        'wallet_id', 'biller_id', 'account_number', 'amount', 'confirmation_no',
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

    public function biller()
    {
        return $this->belongsTo(Billers::class);
    }
}
