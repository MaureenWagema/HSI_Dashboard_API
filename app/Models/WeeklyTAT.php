<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyTAT extends Model
{
    use HasFactory;

    protected $table = 'weekly_tat';

    protected $fillable = [
        'week',
        'claim_processing',
        'policy_issuance',
        'underwriting',
        'customer_queries',
        'premium_collection'
    ];
}
