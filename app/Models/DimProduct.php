<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DimProduct extends Model
{
    protected $connection = 'sqlsrv';
    use HasFactory;

    protected $table = 'dim_products';

    protected $fillable = [
        'ProductLine',
        'AssumedConversionRate',
    ];
}
