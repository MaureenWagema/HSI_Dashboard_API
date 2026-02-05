<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv';

    protected $table = 'department';

    protected $primaryKey = 'DepartmentID';

    protected $fillable = [
        'Department',
        'CostCentre',
        'HeadOfDepartment',
    ];

    
    public function employees()
    {
        return $this->hasMany(Employee::class, 'Department', 'Department');
    }

    
    public function kpiItems()
    {
        return $this->hasMany(KpiItem::class, 'Department', 'Department');
    }

    public function kpiScores()
    {
        return $this->hasMany(KpiScore::class, 'Department', 'Department');
    }

    
    public function employeeSummaries()
    {
        return $this->hasMany(EmployeeSummary::class, 'Department', 'Department');
    }
}
