<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'employees';

    protected $primaryKey = 'EmployeeID';

    public $incrementing = false;

    protected $fillable = [
        'EmployeeID',
        'EmployeeName',
        'Department',
        'JobTitle',
        'Email',
    ];

    /**
     * Relationship to Department
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'Department', 'Department');
    }

    /**
     * Relationship to KPI Scores
     */
    public function kpiScores()
    {
        return $this->hasMany(KpiScore::class, 'EmployeeID', 'EmployeeID');
    }

    /**
     * Relationship to Employee Summaries
     */
    public function employeeSummaries()
    {
        return $this->hasMany(EmployeeSummary::class, 'EmployeeID', 'EmployeeID');
    }
}
