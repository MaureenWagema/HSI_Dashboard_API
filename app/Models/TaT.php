<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tat extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'TaT';
    
    protected $fillable = [
        'Claim_No',
        'Policy_No', 
        'Name',
        'Dept',
        'Date_Reported',
        'Offer_Date',
        'statusdescription',
        'Time_to_Make_Offer'
    ];

    protected $casts = [
        'Date_Reported' => 'date',
        'Offer_Date' => 'date',
        'Time_to_Make_Offer' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public $timestamps = true;

    public static function getAverageTimeByDept()
    {
        return self::select('Dept', 
                \DB::raw('AVG(Time_to_Make_Offer) as avg_time'),
                \DB::raw('COUNT(*) as total_claims'))
            ->groupBy('Dept')
            ->orderBy('avg_time')
            ->get();
    }

    public static function getMonthlyStats()
    {
        return self::select(
                \DB::raw('FORMAT(Offer_Date, \'yyyy-MM\') as month'),
                \DB::raw('AVG(Time_to_Make_Offer) as avg_time'),
                \DB::raw('COUNT(*) as total_claims')
            )
            ->groupBy(\DB::raw('FORMAT(Offer_Date, \'yyyy-MM\')'))
            ->orderBy('month')
            ->get();
    }
}
