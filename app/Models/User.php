<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Passport\HasApiTokens;
use App\Models\Department;


class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;
    protected $connection = 'sqlsrv';
    protected $table = 'user'; 



    protected $fillable = [
        'name',
        'email',
        'password',
        'is_superadmin',
        'is_active',
        'department',
        'password_changed_at',
        'last_login_at',
        'last_logout_at',
    ];

     public function department()
    {
        return $this->belongsTo(Department::class, 'department', 'department_name');
    }

    public function getStatusAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }
        protected $appends = ['status'];


    protected $hidden = [
        'password',
        'is_active',
    ];
}
