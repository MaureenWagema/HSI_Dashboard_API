<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Claim extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv';

    protected $table = 'claims';

    protected $fillable = [
        'ClaimID',
        'PolicyID',
        'ProductLine',
        'AgentName',
        'BrokerName',
        'LossDate',
        'ReportedDate',
        'PaidDate',
        'ClaimPaid',
        'RecoveriesRI',
        'SalvageSubrogation',
    ];

    public function policy()
    {
        return $this->belongsTo(Policy::class, 'PolicyID', 'PolicyID');
    }
}
