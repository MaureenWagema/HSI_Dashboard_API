<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pdepartment extends Model
{
    protected $connection = 'sqlsrv';
    use HasFactory;

    protected $table = 'pdepartment';

    protected $primaryKey = 'department_name';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'department_name',
        'annual_budget',
    ];

    

    public function premiums()
    {
        return $this->hasMany(Premium::class, 'department_name', 'department_name');
    }
}
