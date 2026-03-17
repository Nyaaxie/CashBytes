<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingGoals extends Model
{
    /** @use HasFactory<\Database\Factories\SavingGoalsFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'category',
        'target_amount', 'current_amount', 'target_date', 'status',
    ];

    protected $casts = [
        'target_amount'  => 'decimal:2',
        'current_amount' => 'decimal:2',
        'target_date'    => 'date',
    ];

    // Helpers
    public function progressPercentage(): float
    {
        if ($this->target_amount == 0) return 0;
        return min(100, ($this->current_amount / $this->target_amount) * 100);
    }
    

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function savingsAllocations()
    {
        return $this->hasMany(SavingsAllocations::class);
    }
}
