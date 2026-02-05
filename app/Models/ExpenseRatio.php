<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseRatio extends Model
{
    use HasFactory;

    protected $table = 'expense_ratios';

    protected $fillable = [
        'month',
        'underwriting_expenses',
        'written_premiums',
        'expense_ratio',
    ];

    protected $casts = [
        'expense_ratio' => 'float',
        'underwriting_expenses' => 'float',
        'written_premiums' => 'float',
    ];
}
