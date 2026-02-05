<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimsRatio extends Model
{
    use HasFactory;

    protected $table = 'claims_ratios';

    protected $fillable = [
        'month',
        'paid_claims',
        'gross_premium',
        'claims_ratio',
    ];

    protected $casts = [
        'claims_ratio' => 'float',
        'paid_claims' => 'float',
        'gross_premium' => 'float',
    ];
}
