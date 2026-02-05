<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CombinedRatio extends Model
{
    use HasFactory;

    protected $table = 'combined_ratios';

    protected $fillable = [
        'month',
        'claims_ratio',
        'expense_ratio',
        'combined_ratio',
        'claims_ratio_id',
        'expense_ratio_id',
    ];

    protected $casts = [
        'claims_ratio' => 'float',
        'expense_ratio' => 'float',
        'combined_ratio' => 'float',
    ];

    // Optional relationships to underlying ratios
    public function claims()
    {
        return $this->belongsTo(ClaimsRatio::class, 'claims_ratio_id');
    }

    public function expenses()
    {
        return $this->belongsTo(ExpenseRatio::class, 'expense_ratio_id');
    }
}
