<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FactForPivot extends Model
{
    use HasFactory;

    protected $table = 'facts_for_pivots';

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
        'InceptionMonth',
        'ClaimsPaid',
        'RecoveriesRI',
        'SalvageSubrogation',
        'ClaimCount',
        'Year',
        'Quarter',
        'Month',
        'NetClaims',
        'Profitability',
        'LossRatio',
        'CommissionRatio',
    ];
}
