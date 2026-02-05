<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Calendar extends Model
{
    use HasFactory;

    protected $table = 'calendars';

    protected $fillable = [
        'Date',
        'Year',
        'Quarter',
        'Month',
        'MonthName',
        'Week',
        'DayOfMonth',
        'DayName',
    ];
}
