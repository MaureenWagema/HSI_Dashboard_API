<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiScore extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'kpi_scores';

    protected $fillable = [
        'EmployeeID',
        'EmployeeName',
        'Department',
        'KPIItem',
        'Weight',
        'Score',
        'WeightedScore',
        'Period',
    ];

    protected $casts = [
        'Weight' => 'integer',
        'Score' => 'integer',
        'WeightedScore' => 'decimal:2',
    ];

  
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'EmployeeID');
    }

    public function kpiItem()
    {
        return $this->belongsTo(KpiItem::class, 'KPIItem', 'KPIItem')
                    ->where('kpi_items.Department', $this->Department);
    }

   
    public function department()
    {
        return $this->belongsTo(Department::class, 'Department', 'Department');
    }
}
