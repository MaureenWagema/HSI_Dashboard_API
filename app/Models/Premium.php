<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Premium extends Model
{
    protected $connection = 'sqlsrv';
    use HasFactory;

    protected $table = 'premiums';

    protected $fillable = [
        'department_name',
        'month',
        'year',
        'annual_budget',
        'premium_actuals',
        'erm_loading',
        'vat_amount',
    ];

    // Cast attributes
    // protected $casts = [
    //     'annual_budget' => 'decimal:2',
    //     'premium_actuals' => 'decimal:2',
    //     'erm_loading' => 'decimal:2',
    //     'vat_amount' => 'decimal:2',
    // ];

    public function pdepartment()
    {
        return $this->belongsTo(Pdepartment::class, 'department_name', 'department_name');
    }
}
