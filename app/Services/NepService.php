<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class NEPService
{
    protected $db;

    
    public function __construct($connection = 'mysql')
    {
        $this->db = DB::connection($connection);
    }

    public function setConnection($connection)
    {
        $this->db = DB::connection($connection);
        return $this;
    }
   
    public function getNEP(?int $year = null, ?int $month = null): array
    {
        $cacheKey = "nep_data_{$year}_{$month}_" . md5(json_encode([$year, $month]));
        
        return Cache::store('file')->remember($cacheKey, now()->addHour(), function() use ($year, $month) {
            $grossAccounts = [
                '040-01-001','040-01-002','040-01-003','040-01-004','040-01-005','040-01-006',
                '040-02-001','040-02-002','040-02-003','040-02-004','040-02-005','040-02-006',
                '040-05-001'
            ];

            $reinsuranceAccounts = [
                '050-03-001','050-03-002','050-03-003','050-03-004','050-03-005','050-03-006',
                '050-04-001','050-04-002','050-04-003','050-04-004','050-04-005','050-04-006',
                '050-02-002'
            ];

            $grossData = $this->fetchRollup($grossAccounts, $year, $month);
            $reinsuranceData = $this->fetchRollup($reinsuranceAccounts, $year, $month);


            $grossMap = [];
            foreach ($grossData as $item) {
                $key = $item->account_year . '-' . $item->account_month;
                $grossMap[$key] = $grossMap[$key] ?? 0;
                $grossMap[$key] += $item->total; 
            }

            $reinsuranceMap = [];
            foreach ($reinsuranceData as $item) {
                $key = $item->account_year . '-' . $item->account_month;
                $reinsuranceMap[$key] = $reinsuranceMap[$key] ?? 0;
                $reinsuranceMap[$key] += $item->total; 
            }

            $nepMonthly = [];
            $yearlySums = [];
            
            $allMonths = array_unique(array_merge(array_keys($grossMap), array_keys($reinsuranceMap)));
            
            foreach ($allMonths as $yearMonth) {
                list($y, $m) = explode('-', $yearMonth);
                $gross = $grossMap[$yearMonth] ?? 0;
                $reins = $reinsuranceMap[$yearMonth] ?? 0;
                //NEP consistently as gross - reinsurance
                $nep = $gross - $reins;
                
                $nepMonthly[$yearMonth] = [
                    'account_year'  => (int)$y,
                    'account_month' => (int)$m,
                    'gross_premium' => $gross,
                    'reinsurance_ceded' => $reins,
                    'nep' => $nep  
                ];
                
                $yearlySums[$y] = ($yearlySums[$y] ?? 0) + $nep;
            }
            
            ksort($nepMonthly);
            
            $nepYearly = [];
            foreach ($yearlySums as $year => $total) {
                $nepYearly[$year] = $total;
            }

            $generalNEP = array_sum(array_column($nepMonthly, 'nep'));

            return [
                'monthly' => array_values($nepMonthly),
                'yearly'  => $nepYearly,
                'general' => $generalNEP
            ];
        });
    }


     
    protected function fetchRollup(array $accountNos, ?int $year = null, ?int $month = null)
    {
        $query = $this->db->table('glmasterinfo')
            ->select(
                'account_year',
                'account_month',
                'displayAccountNo',
                DB::raw('SUM(ABS(monthly_balance)) as total')
            )
            ->whereIn('displayAccountNo', $accountNos)
            ->groupBy('account_year', 'account_month', 'displayAccountNo');

        if ($year) $query->where('account_year', $year);
        if ($month) $query->where('account_month', $month);

        $results = $query
            ->orderBy('account_year')
            ->orderBy('account_month')
            ->get();

       

        if ($results->isNotEmpty()) {
            $sampleQuery = $this->db->table('glmasterinfo')
                ->whereIn('displayAccountNo', $accountNos);
                
            if ($year) $sampleQuery->where('account_year', $year);
            if ($month) $sampleQuery->where('account_month', $month);
                
            $sampleData = $sampleQuery->limit(5)->get();
            \Log::info('Sample Raw Data:', $sampleData->toArray());
        }

        return $results;
    }
}




