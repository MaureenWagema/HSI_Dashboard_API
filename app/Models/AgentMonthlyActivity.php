<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgentMonthlyActivity extends Model
{
    protected $connection = 'sqlsrv';
    use HasFactory;

    protected $table = 'agent_monthly_activities';

    protected $fillable = [
        'AgentName',
        'InceptionMonth',
        'Policies',
        'GWP',
    ];
}
