<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BrokerMonthlyActivity extends Model
{
    protected $connection = 'sqlsrv';
    use HasFactory;

    protected $table = 'broker_monthly_activities';

    protected $fillable = [
        'BrokerName',
        'InceptionMonth',
        'Policies',
        'GWP',
    ];
}
