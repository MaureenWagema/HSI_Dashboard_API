<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClaimsRatioService
{
    protected $db;
    protected $nepService;
    protected $dateMappings = null;
    protected $categories = [
        'claims_paid' => [
            '060-06-001','060-06-002','060-06-003','060-06-004','060-06-005','060-06-006'
        ],
        'change_in_reserves' => [
            '060-02-001','060-02-002','060-02-003','060-02-004','060-02-005','060-02-006',
            '060-03-001','060-03-002','060-03-003','060-03-004','060-03-005','060-03-006'
        ],
        'salvage_costs' => [
            '070-08-001','070-08-002','070-08-003','070-08-004','070-08-005','070-08-006','070-09-001','070-09-002','070-09-003','070-09-004','070-09-005','070-09-006','070-07-002'
        ]
    ];

    public function __construct($connection = 'mysql', ?NepService $nepService = null)
    {
        $this->db = DB::connection($connection);
        $this->nepService = $nepService ?? app(NepService::class);
    }

   
    protected function loadDateMappings(): void
    {
        if ($this->dateMappings === null) {
            $this->dateMappings = Cache::remember('period_mappings', now()->addDay(), function() {
                return $this->db->table('accperiodinfo')
                    ->select(
                        'Account_year as account_year',
                        'Account_month as account_month',
                        'calYear as calendar_year',
                        'calMonth as calendar_month'
                    )
                    ->get()
                    ->keyBy(function($item) {
                        return "{$item->account_year}-{$item->account_month}";
                    });
            });
        }
    }

   
    protected function getCalendarDate(int $accountYear, int $accountMonth): array
    {
        $this->loadDateMappings();
        
        $cacheKey = "{$accountYear}-{$accountMonth}";
        $mapping = $this->dateMappings[$cacheKey] ?? null;
        
        return $mapping ? [
            'calendar_year' => (int)$mapping->calendar_year,
            'calendar_month' => (int)$mapping->calendar_month
        ] : [
            'calendar_year' => $accountYear,
            'calendar_month' => $accountMonth
        ];
    }

   
    protected function formatDate(int $year, int $month, string $format = 'F Y'): string
    {
        return Carbon::create($year, $month, 1)->format($format);
    }

    public function getClaimsRatio(?int $year = null, ?int $month = null): array
    {
        $cacheKey = "claims_ratio_{$year}_{$month}_" . md5(json_encode([$year, $month]));
        
        return Cache::store('file')->remember($cacheKey, now()->addHour(), function() use ($year, $month) {
            $claimsData = $this->getClaimsPaid($year, $month);
            
            // Ensure we have salvage_costs in the monthly data
            if (!isset($claimsData['monthly'])) {
                $claimsData['monthly'] = [];
            }
            
            // Ensure each monthly item has salvage_costs
            foreach ($claimsData['monthly'] as &$monthlyItem) {
                if (!isset($monthlyItem['salvage_costs'])) {
                    $monthlyItem['salvage_costs'] = 0;
                }
            }
            
            $nepData = $this->nepService->getNEP($year, $month);
            $nepMonthly = collect($nepData['monthly'] ?? []);

            $result = [
                'monthly' => [],
                'overall' => [
                    'claims_paid' => 0,
                    'change_in_reserves' => 0,
                    'salvage_costs' => 0,
                    'total_claims' => 0,
                    'nep' => 0,
                    'ratio' => 0
                ]
            ];

            foreach (array_chunk($claimsData['monthly'], 100, true) as $chunk) {
                foreach ($chunk as $monthlyData) {
                    $accountYear = $monthlyData['account_year'];
                    $accountMonth = $monthlyData['account_month'];
                    $yearMonthKey = "{$accountYear}-" . str_pad($accountMonth, 2, '0', STR_PAD_LEFT);

                    $calendarDate = $this->getCalendarDate($accountYear, $accountMonth);
                    
                    $nepItem = $nepMonthly->first(function($item) use ($accountYear, $accountMonth) {
                        return $item['account_year'] == $accountYear && 
                               $item['account_month'] == $accountMonth;
                    });

                    $nepValue = $nepItem['nep'] ?? 0;
                    $totalClaims = $monthlyData['total_claims'] ?? 0;
                    $ratio = $nepValue != 0 ? ($totalClaims / $nepValue) * 100 : 0;

                    $resultItem = [
                        'year' => $calendarDate['calendar_year'],
                        'month' => $calendarDate['calendar_month'],
                        'account_year' => $accountYear,
                        'account_month' => $accountMonth,
                        'claims_paid' => $monthlyData['claims_paid'] ?? 0,
                        'change_in_reserves' => $monthlyData['change_in_reserves'] ?? 0,
                        'salvage_costs' => $monthlyData['salvage_costs'] ?? 0,
                        'total_claims' => $totalClaims,
                        'calendar_year' => $calendarDate['calendar_year'],
                        'calendar_month' => $calendarDate['calendar_month'],
                        'formatted_date' => $this->formatDate(
                            $calendarDate['calendar_year'],
                            $calendarDate['calendar_month'],
                            'F Y'
                        ),
                        'nep' => $nepValue,
                        'ratio' => round($ratio, 2)
                    ];

                    $result['monthly'][] = $resultItem;

                    $result['overall']['claims_paid'] += $resultItem['claims_paid'];
                    $result['overall']['change_in_reserves'] += $resultItem['change_in_reserves'];
                    $result['overall']['salvage_costs'] += $resultItem['salvage_costs'];  
                    $result['overall']['total_claims'] += $totalClaims;
                    $result['overall']['nep'] += $nepValue;
                }
                
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }

            if ($result['overall']['nep'] > 0) {
                $result['overall']['ratio'] = round(
                    ($result['overall']['total_claims'] / $result['overall']['nep']) * 100,
                    2
                );
            }

            return $result;
        });
    }

public function getClaimsPaid(?int $year = null, ?int $month = null): array
{
    $cacheKey = "claims_paid_{$year}_{$month}_" . md5(json_encode([$year, $month]));
    
    return Cache::remember($cacheKey, now()->addDay(), function() use ($year, $month) {
        $allAccounts = array_merge($this->categories['claims_paid'], $this->categories['change_in_reserves'], $this->categories['salvage_costs']);

        $accountTypeMap = [];
        foreach ($this->categories as $type => $accounts) {
            foreach ($accounts as $account) {
                $accountTypeMap[$account] = $type;
            }
        }

        $query = $this->db->table('glmasterinfo')
            ->select(
                'account_year',
                'account_month',
                'displayAccountNo',
                DB::raw('SUM(ABS(monthly_balance)) as total')
            )
            ->whereIn('displayAccountNo', $allAccounts)
            ->when($year, function($q) use ($year) {
                $q->where('account_year', $year);
            })
            ->when($month, function($q) use ($month) {
                $q->where('account_month', $month);
            })
            ->groupBy('account_year', 'account_month', 'displayAccountNo')
            ->orderBy('account_year')
            ->orderBy('account_month');

        $results = [];
        $currentPeriod = null;
        $periodData = null;

        $query->chunk(1000, function ($accounts) use (&$results, &$currentPeriod, &$periodData, $accountTypeMap) {
            foreach ($accounts as $account) {
                $yearMonthKey = $account->account_year . '-' . str_pad($account->account_month, 2, '0', STR_PAD_LEFT);
                
                if ($currentPeriod !== $yearMonthKey) {
                    if ($periodData !== null) {
                        $claims_paid = $periodData['claims_paid'] ?? 0;
                        $change_in_reserves = $periodData['change_in_reserves'] ?? 0;
                        $salvage_costs = $periodData['salvage_costs'] ?? 0;
                        
                        $periodData['total_claims'] = $claims_paid + $change_in_reserves - $salvage_costs;
                        $results[] = $periodData;
                    }
                    
                    $currentPeriod = $yearMonthKey;
                    $periodData = [
                        'year' => (int)$account->account_year,
                        'month' => (int)$account->account_month,
                        'account_year' => (int)$account->account_year,
                        'account_month' => (int)$account->account_month,
                        'claims_paid' => 0,
                        'change_in_reserves' => 0,
                        'salvage_costs' => 0,
                        'total_claims' => 0
                    ];
                }

                $accountType = $accountTypeMap[$account->displayAccountNo] ?? null;
                if ($accountType) {
                    $periodData[$accountType] += (float)$account->total;
                }
            }
            
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        });

        if ($periodData !== null) {
            $claims_paid = $periodData['claims_paid'] ?? 0;
            $change_in_reserves = $periodData['change_in_reserves'] ?? 0;
            $salvage_costs = $periodData['salvage_costs'] ?? 0;
            
            $periodData['total_claims'] = $claims_paid + $change_in_reserves - $salvage_costs;
            $results[] = $periodData;
        }

        return ['monthly' => $results];
    });
}

    public function clearClaimsRatioCache(?int $year = null, ?int $month = null): void
    {
        $cacheKey = 'claims_paid_' . ($year ?? 'all') . '_' . ($month ?? 'all');
        Cache::forget($cacheKey);
        
        $ratioCacheKey = "claims_ratio_{$year}_{$month}_" . md5(json_encode([$year, $month]));
        Cache::store('file')->forget($ratioCacheKey);
        
        if ($year === null && $month === null) {
            Cache::forget('period_mappings');
        }
    }
}