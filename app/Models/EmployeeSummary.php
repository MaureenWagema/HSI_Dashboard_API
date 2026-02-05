<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSummary extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'employee_summaries';

    protected $fillable = [
        'EmployeeID',
        'EmployeeName',
        'Department',
        'Period',
        'TotalWeightedScore',
    ];

    protected $casts = [
        'TotalWeightedScore' => 'decimal:2',
    ];

    /**
     * Relationship to Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'EmployeeID');
    }

    /**
     * Relationship to Department
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'Department', 'Department');
    }
}
