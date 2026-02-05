<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departments extends Model
{
    protected $connection = 'sqlsrv';
    use HasFactory;

    protected $table = 'departments';

    protected $primaryKey = 'department_name';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'Id',
        'department_name',
    ];

    
    public function users()
    {
        return $this->hasMany(User::class, 'department', 'department_name');
    }
}
