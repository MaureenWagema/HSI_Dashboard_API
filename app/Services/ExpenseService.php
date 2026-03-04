<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpenseService
{

    protected $db;
    protected $nepService;
    protected $dateMappings = null;
    protected $categories = [
        'commissions' => [
            '080-01-001', '080-01-002', '080-01-003', '080-01-004', '080-01-005', '080-01-006',
            '080-02-001', '080-02-002', '080-02-003', '080-02-004', '080-02-005', '080-02-006',
            '080-05-001', '080-05-002', '080-05-003', '080-05-004', '080-05-005', '080-05-006',
            '080-08-001'
        ],
        'premium_collection' => ['060-12-002'],
        'policy_acquisition' => ['060-09-001', '060-09-002']
    ];

    public function __construct($connection = 'mysql', ?NepService $nepService = null)
    {
        $this->db = DB::connection($connection);
        $this->nepService = $nepService ?? app(NepService::class);
    }

    public function getExpenses(?int $year = null, ?int $month = null): array
    {
        $cacheKey = "expenses_{$year}_{$month}_" . md5(json_encode([$year, $month]));

        return Cache::store('file')->remember($cacheKey, now()->addHour(), function() use ($year, $month) {
            Log::info('=== Starting getExpenses ===', ['year' => $year, 'month' => $month]);

            $expenseData = $this->fetchExpenseData($year, $month);
            $nepData = $this->nepService->getNEP($year, $month);
            
            $nepLookup = collect($nepData['monthly'] ?? [])->keyBy(function($item) {
                return $item['account_year'] . '-' . str_pad($item['account_month'], 2, '0', STR_PAD_LEFT);
            });

            $result = [
                'monthly' => [],
                'overall' => [
                    'commissions' => 0,
                    'premium_collection' => 0,
                    'policy_acquisition' => 0,
                    'total_direct_expenses' => 0,
                    'nep' => 0,
                    'expense_ratio' => 0
                ]
            ];

            // Process data in chunks to reduce memory usage
            foreach (array_chunk($expenseData['monthly'], 100, true) as $chunk) {
                foreach ($chunk as $monthlyData) {
                    $accountYear = $monthlyData['account_year'];
                    $accountMonth = $monthlyData['account_month'];
                    $yearMonthKey = "{$accountYear}-" . str_pad($accountMonth, 2, '0', STR_PAD_LEFT);

                    $calendarDate = $this->getCalendarDate($accountYear, $accountMonth);
                    $nepItem = $nepLookup->get($yearMonthKey, ['nep' => 0]);
                    $nepValue = $nepItem['nep'] ?? 0;
                    $totalExpenses = $monthlyData['total_direct_expenses'] ?? 0;

                    $resultItem = [
                        'year' => $calendarDate['calendar_year'],
                        'month' => $calendarDate['calendar_month'],
                        'account_year' => $accountYear,
                        'account_month' => $accountMonth,
                        'commissions' => $monthlyData['commissions'] ?? 0,
                        'premium_collection' => $monthlyData['premium_collection'] ?? 0,
                        'policy_acquisition' => $monthlyData['policy_acquisition'] ?? 0,
                        'total_direct_expenses' => $totalExpenses,
                        'calendar_year' => $calendarDate['calendar_year'],
                        'calendar_month' => $calendarDate['calendar_month'],
                        'formatted_date' => $this->formatDate(
                            $calendarDate['calendar_year'],
                            $calendarDate['calendar_month'],
                            'F Y'
                        ),
                        'nep' => $nepValue,
                        'expense_ratio' => $nepValue > 0 ? round(($totalExpenses / $nepValue) * 100, 2) : 0
                    ];

                    $result['monthly'][] = $resultItem;

                    $result['overall']['commissions'] += $resultItem['commissions'];
                    $result['overall']['premium_collection'] += $resultItem['premium_collection'];
                    $result['overall']['policy_acquisition'] += $resultItem['policy_acquisition'];
                    $result['overall']['total_direct_expenses'] += $totalExpenses;
                    $result['overall']['nep'] += $nepValue;
                }
                
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }

            // Calculate overall ratio
            if ($result['overall']['nep'] > 0) {
                $result['overall']['expense_ratio'] = round(
                    ($result['overall']['total_direct_expenses'] / $result['overall']['nep']) * 100,
                    2
                );
            }

            Log::info('=== Completed getExpenses ===', [
                'months_processed' => count($result['monthly']),
                'overall' => $result['overall']
            ]);

            return $result;
        });
    }

    protected function fetchExpenseData(?int $year = null, ?int $month = null): array
    {
        $cacheKey = 'expense_data_' . ($year ?? 'all') . '_' . ($month ?? 'all');
        
        return Cache::remember($cacheKey, now()->addDay(), function() use ($year, $month) {
            $allAccounts = array_merge(
                $this->categories['commissions'],
                $this->categories['premium_collection'],
                $this->categories['policy_acquisition']
            );

            $accountTypeMap = [];
            foreach ($this->categories as $type => $accounts) {
                foreach ($accounts as $account) {
                    $accountTypeMap[$account] = $type;
                }
            }

            $query = $this->db->table('glmasterinfo')
                ->select([
                    'account_year',
                    'account_month',
                    'displayAccountNo',
                    DB::raw('SUM(ABS(monthly_balance)) as total')
                ])
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

            $groupedResults = [];
            $query->chunk(1000, function ($results) use (&$groupedResults, $accountTypeMap) {
                foreach ($results as $row) {
                    $yearMonth = $row->account_year . '-' . str_pad($row->account_month, 2, '0', STR_PAD_LEFT);
                    
                    if (!isset($groupedResults[$yearMonth])) {
                        $groupedResults[$yearMonth] = [
                            'year' => (int)$row->account_year,
                            'month' => (int)$row->account_month,
                            'account_year' => (int)$row->account_year,
                            'account_month' => (int)$row->account_month,
                            'commissions' => 0,
                            'premium_collection' => 0,
                            'policy_acquisition' => 0,
                            'total_direct_expenses' => 0
                        ];
                    }
                    
                    $accountType = $accountTypeMap[$row->displayAccountNo] ?? null;
                    if ($accountType) {
                        $groupedResults[$yearMonth][$accountType] += (float)$row->total;
                        $groupedResults[$yearMonth]['total_direct_expenses'] += (float)$row->total;
                    }
                }
            });

            return ['monthly' => array_values($groupedResults)];
        });
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

   
    public function getCalendarDatePublic(int $accountYear, int $accountMonth): array
    {
        return $this->getCalendarDate($accountYear, $accountMonth);
    }

   
    public function formatDatePublic(int $year, int $month, string $format = 'F Y'): string
    {
        return $this->formatDate($year, $month, $format);
    }

   
    protected function formatDate(int $year, int $month, string $format = 'F Y'): string
    {
        return Carbon::create($year, $month, 1)->format($format);
    }

    public function clearExpenseCache(?int $year = null, ?int $month = null): void
    {
        $cacheKey = 'expense_data_' . ($year ?? 'all') . '_' . ($month ?? 'all');
        Cache::forget($cacheKey);
        
        $expensesCacheKey = "expenses_{$year}_{$month}_" . md5(json_encode([$year, $month]));
        Cache::store('file')->forget($expensesCacheKey);
        
        // Clear date mappings cache if needed
        if ($year === null && $month === null) {
            Cache::forget('period_mappings');
        }
    }
}