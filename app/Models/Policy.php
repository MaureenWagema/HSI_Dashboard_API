<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Policy extends Model
{
    protected $connection = 'sqlsrv';
    use HasFactory;

    protected $table = 'policies';

    protected $fillable = [
        'PolicyID',
        'FromProposalID',
        'ProductLine',
        'Channel',
        'AgentName',
        'BrokerName',
        'PolicyType',
        'InceptionDate',
        'ExpiryDate',
        'GrossWrittenPremium',
        'CommissionRate',
        'CommissionAmount',
        'ReinsuranceCededPct',
        'CededPremium',
        'RetainedPremium',
        'NetEarnedPremium',
    ];

    public function claims()
    {
        return $this->hasMany(Claim::class, 'PolicyID', 'PolicyID');
    }
}
