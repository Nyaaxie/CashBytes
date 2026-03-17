<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billers extends Model
{
    /** @use HasFactory<\Database\Factories\BillersFactory> */
    use HasFactory;


  protected $fillable = [
        'name', 'category', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function billPayments()
    {
        return $this->hasMany(BillsPayments::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
