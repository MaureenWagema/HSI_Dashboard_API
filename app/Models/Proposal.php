<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proposal extends Model
{
    use HasFactory;

    protected $table = 'proposals';

    protected $fillable = [
        'ProposalID',
        'ProposalDate',
        'ProductLine',
        'Channel',
        'AgentName',
        'BrokerName',
        'QuotedPremium',
        'Converted',
        'ConversionDate',
        'PolicyTypeIfConverted',
    ];
}
