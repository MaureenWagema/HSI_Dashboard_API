<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DimAgent extends Model
{
    protected $connection = 'sqlsrv';
    use HasFactory;

    protected $table = 'dim_agents';

    protected $fillable = [
        'AgentName',
    ];
}
