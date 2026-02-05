<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DimBroker extends Model
{
    protected $connection = 'sqlsrv';
    use HasFactory;

    protected $table = 'dim_brokers';

    protected $fillable = [
        'BrokerName',
    ];
}
