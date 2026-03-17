<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsAllocations extends Model
{   
    /** @use HasFactory<\Database\Factories\SavingsAllocationsFactory> */
    use HasFactory;

    protected $fillable = [
        'savings_goal_id', 'transaction_id', 'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function savingsGoal()
    {
        return $this->belongsTo(SavingGoals::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transactions::class);
    }
}
