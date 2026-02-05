<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiItem extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'kpi_items';

    protected $fillable = [
        'Department',
        'KPIItem',
        'Weight',
    ];

    
    public function department()
    {
        return $this->belongsTo(Department::class, 'Department', 'Department');
    }

    
    public function kpiScores()
    {
        return $this->hasMany(KpiScore::class, 'KPIItem', 'KPIItem')
                    ->where('kpi_scores.Department', $this->Department);
    }
}
