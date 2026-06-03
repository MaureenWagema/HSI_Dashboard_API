<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AccountMappingService;
use App\Services\ClaimsRatioService;
use App\Services\NEPService;
use App\Services\ExpenseService;
use Illuminate\Database\Connection;

class RatiosController extends Controller
{
   
    public function __construct(
        private ClaimsRatioService $claimsService, 
        private NEPService $nepService,
        private ExpenseService $expenseService,
        private AccountMappingService $accountMappingService
    )
    {
        parent::__construct();
        
    
    }

    
    public function syncData(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        
        $result = $this->accountMappingService->syncToSqlServer($year, $month);
        
        return response()->json($result);
    }


public function getClaimsRatio(Request $request)
{
    try {
        $year = $request->input('year');
        $month = $request->input('month');

        $claimsData = $this->claimsService->getClaimsRatio($year, $month);

        $responseData = [
            'status' => 'success',
            'data' => [
                'monthly' => array_map(function($item) {
                    return [
                        'year' => $item['year'],
                        'month' => $item['month'],
                        'account_year' => $item['account_year'],
                        'account_month' => $item['account_month'],
                        'claims_paid' => $item['claims_paid'],
                        'change_in_reserves' => $item['change_in_reserves'],
                        'salvage_costs' => $item['salvage_costs'],
                        'Direct_UW_Expenses' => $item['total_claims'],
                        'nep' => $item['nep'],
                        'ratio' => $item['ratio'],
                        'formatted_date' => $item['formatted_date'] ?? null
                    ];
                }, $claimsData['monthly']),
                'overall' => [
                    'total_claims' => $claimsData['overall']['total_claims'] ?? 0,
                    'total_nep' => $claimsData['overall']['total_nep'] ?? 0,
                    'ratio' => $claimsData['overall']['ratio'] ?? 0
                ],
                'totals' => array_map(function($item) {
                    return [
                        'year' => $item['year'],
                        'month' => $item['month'],
                        'account_year' => $item['account_year'],
                        'account_month' => $item['account_month'],
                        'claims_paid' => $item['claims_paid'],
                        'change_in_reserves' => $item['change_in_reserves'],
                        'total_claims' => $item['total_claims'],
                        'nep' => $item['nep'],
                        'ratio' => $item['ratio'],
                        'formatted_date' => $item['formatted_date'] ?? null
                    ];
                }, $claimsData['totals'] ?? [])
            ]
        ];

        return response()->json($responseData);

    } catch (\Exception $e) {
        \Log::error('Error in getClaimsRatio: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve claims ratio data',
            'error' => $e->getMessage()
        ], 500);
    }
}

    
    
    
    public function getExpenseRatio(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');

        $expenseData = $this->expenseService->getExpenses($year, $month);

        $overall = [
            'commissions' => collect($expenseData)->sum('commissions'),
            'premium_collection' => collect($expenseData)->sum('premium_collection'),
            'policy_acquisition' => collect($expenseData)->sum('policy_acquisition'),
            'total_direct_expenses' => collect($expenseData)->sum('total_direct_expenses'),
            'nep' => collect($expenseData)->sum('nep')
        ];

        $overall['expense_ratio'] = $overall['nep'] != 0 
            ? ($overall['total_direct_expenses'] / $overall['nep']) * 100 
            : 0;

        $overall = array_map(function($value) {
            return is_float($value) ? round($value, 2) : $value;
        }, $overall);

        return response()->json([
            'status' => 'success',
            'data' => [
                'monthly' => $expenseData,
                'overall' => $overall
            ]
        ]);
        foreach ($yearlyExpenses as $row) {
            $yearKey = $row->account_year;
            $formattedYearly[$yearKey] = [
                'expenses' => $row->total,
                'claims_incurred' => $row->claims_incurred,
                'nep' => $nepYearly[$yearKey] ?? 0,
                'expense_ratio' => $yearlyExpenseRatios[$yearKey] ?? 0,
                'combined_ratio' => $yearlyCombinedRatios[$yearKey] ?? 0
            ];
        }

        $monthlyExpenseData = [];
        foreach ($monthlyExpenses as $row) {
            $key = $row->account_year . '-' . str_pad($row->account_month, 2, '0', STR_PAD_LEFT);
            $monthlyExpenseData[] = [
                'year' => (int)$row->account_year,
                'month' => (int)$row->account_month,
                'Direct Underwriting Expenses' => $row->total,
                'expense_ratio' => $monthlyExpenseRatios[$key] ?? 0,
                //'combined_ratio' => $monthlyCombinedRatios[$key] ?? 0,
                'nep' => $nepMonthly[$key]['nep'] ?? 0,
            ];
        }

        $yearlyExpenseData = [];
        foreach ($yearlyExpenses as $row) {
            $yearlyExpenseData[] = [
                'year' => (int)$row->account_year,
                'Direct Underwriting Expenses' => $row->total,
                'expense_ratio' => $yearlyExpenseRatios[$row->account_year] ?? 0,
                'combined_ratio' => $yearlyCombinedRatios[$row->account_year] ?? 0,
                'nep' => $nepYearly[$row->account_year] ?? 0,
                'claims_incurred' => $row->claims_incurred
            ];
        }


        return response()->json([
            'monthly' => $monthlyExpenseData,
            'yearly' => $yearlyExpenseData,
            'general' => [
                'total_expenses' => $totalExpenses,
                'nep' => $nepGeneral,
                'expense_ratio' => $generalExpenseRatio,
            ],
            //'breakdown' => $expenseBreakdown
        ]);
    }

   
public function getCombinedRatio(Request $request)
{
    $year = $request->input('year');
    $month = $request->input('month');

    try {
        $expenseData = $this->expenseService->getExpenses($year, $month);
        
        $nepData = $this->nepService->getNEP($year, $month);
        
        $claimsData = $this->claimsService->getClaimsPaid($year, $month);
        $claimsPaid = $claimsData['monthly'] ?? [];
        

        $monthlyData = [];
        $yearlyData = [];
        $totalExpenses = 0;
        $totalClaimsIncurred = 0;
        $totalNEP = 0;

        foreach ($claimsPaid as $claim) {
            $accountYear = is_array($claim) ? ($claim['account_year'] ?? null) : ($claim->account_year ?? null);
            $accountMonth = is_array($claim) ? ($claim['account_month'] ?? null) : ($claim->account_month ?? null);
            
            if (!$accountYear || !$accountMonth) {
                \Log::warning('Invalid claim data structure:', ['claim' => $claim]);
                continue;
            }
            
            $calendarDate = $this->getCalendarDate($accountYear, $accountMonth);
            $calendarYear = $calendarDate['calendar_year'];
            $calendarMonth = $calendarDate['calendar_month'];
            
            $key = $accountYear . '-' . str_pad($accountMonth, 2, '0', STR_PAD_LEFT);
            
            $claimsIncurred = 0;
            if (isset($expenseData['monthly'])) {
                foreach ($expenseData['monthly'] as $expense) {
                    $expenseYear = is_object($expense) ? ($expense->account_year ?? null) : ($expense['account_year'] ?? null);
                    $expenseMonth = is_object($expense) ? ($expense->account_month ?? null) : ($expense['account_month'] ?? null);
                    
                    if ($expenseYear == $accountYear && $expenseMonth == $accountMonth) {
                        $claimsIncurred = is_object($expense) ? 
                            ($expense->total_direct_expenses ?? 0) : 
                            ($expense['total_direct_expenses'] ?? 0);
                        break;
                    }
                }
            }

            $claimsPaidValue = is_array($claim) ? ($claim['claims_paid'] ?? 0) : ($claim->claims_paid ?? 0);
            $changeInReserves = is_array($claim) ? ($claim['change_in_reserves'] ?? 0) : ($claim->change_in_reserves ?? 0);
            $salvageCosts = is_array($claim) ? ($claim['salvage_costs'] ?? 0) : ($claim->salvage_costs ?? 0);
            
            $totalClaims = $claimsPaidValue + $changeInReserves - $salvageCosts;
            
            $nep = 0;
            if (isset($nepData['monthly'])) {
                foreach ($nepData['monthly'] as $nepItem) {
                    if ($nepItem['account_year'] == $accountYear && $nepItem['account_month'] == $accountMonth) {
                        $nep = $nepItem['nep'] ?? 0;
                        break;
                    }
                }
            }

            // Calculate combined ratio: (total_direct_expenses + total_claims) / NEP * 100
            $combinedRatio = $nep != 0 ? (($claimsIncurred + $totalClaims) / $nep) * 100 : 0;
            
            $monthlyData[] = [
                'year' => $calendarYear,
                'month' => $calendarMonth,
                'account_year' => (int)$accountYear,
                'account_month' => (int)$accountMonth,
                'total_claims' => $totalClaims,
                'total_direct_expenses' => $claimsIncurred, 
                'nep' => $nep,
                'combined_ratio' => round($combinedRatio, 2),
                'formatted_date' => $this->formatDate($calendarYear, $calendarMonth, 'F Y')
            ];
            
            usort($monthlyData, function($a, $b) {
                if ($a['account_year'] == $b['account_year']) {
                    return $a['account_month'] <=> $b['account_month'];
                }
                return $a['account_year'] <=> $b['account_year'];
            });

            $totalClaimsIncurred += $claimsIncurred;
            $totalNEP += $nep;
        }

        $claimsByYear = [];
        foreach ($claimsPaid as $claim) {
            $accountYear = is_array($claim) ? ($claim['account_year'] ?? null) : ($claim->account_year ?? null);
            if (!$accountYear) continue;
            
            if (!isset($claimsByYear[$accountYear])) {
                $claimsByYear[$accountYear] = [
                    'claims_paid' => 0,
                    'change_in_reserves' => 0,
                    'salvage_costs' => 0,
                    'claims_incurred' => 0
                ];
            }
            
            $claimsByYear[$accountYear]['claims_paid'] += is_array($claim) ? 
                ($claim['claims_paid'] ?? 0) : 
                ($claim->claims_paid ?? 0);
                
            $claimsByYear[$accountYear]['change_in_reserves'] += is_array($claim) ? 
                ($claim['change_in_reserves'] ?? 0) : 
                ($claim->change_in_reserves ?? 0);
                
            $claimsByYear[$accountYear]['salvage_costs'] += is_array($claim) ? 
                ($claim['salvage_costs'] ?? 0) : 
                ($claim->salvage_costs ?? 0);
        }

        if (isset($nepData['yearly'])) {
            foreach ($nepData['yearly'] as $year => $nepValue) {
                if (isset($claimsByYear[$year])) {
                    $yearlyClaim = $claimsByYear[$year];
                    $totalYearlyClaims = $yearlyClaim['claims_paid'] + 
                                       $yearlyClaim['change_in_reserves'] - 
                                       $yearlyClaim['salvage_costs'];
                    
                    $combinedRatio = $nepValue != 0 ? 
                        (($yearlyClaim['claims_incurred'] + $totalYearlyClaims) / $nepValue) * 100 : 0;
                    
                    $yearlyData[] = [
                        'account_year' => (int)$year,
                        'claims_incurred' => $yearlyClaim['claims_incurred'],
                        'total_claims' => $totalYearlyClaims,
                        'total_direct_expenses' => $yearlyClaim['claims_incurred'],
                        'nep' => $nepValue,
                        'combined_ratio' => round($combinedRatio, 2),
                        'formatted_date' => $year 
                    ];
                    
                    usort($yearlyData, function($a, $b) {
                        return $a['account_year'] <=> $b['account_year'];
                    });
                }
            }
        }

        $totalAllClaims = $totalClaimsIncurred + array_sum(array_column($monthlyData, 'total_claims'));
        // Calculate overall combined ratio: (total_direct_expenses + total_claims) / NEP * 100
        $overallCombinedRatio = $totalNEP != 0 ? (($totalClaimsIncurred + array_sum(array_column($monthlyData, 'total_claims'))) / $totalNEP) * 100 : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'monthly' => array_values($monthlyData),
                'yearly' => array_values($yearlyData),
                'overall' => [
                    'claims_incurred' => $totalClaimsIncurred,
                    'total_claims' => array_sum(array_column($monthlyData, 'total_claims')),
                    'total_direct_expenses' => $totalClaimsIncurred,
                    'nep' => $totalNEP,
                    'combined_ratio' => round($overallCombinedRatio, 2)
                ]
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in getCombinedRatio: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to calculate combined ratio: ' . $e->getMessage()
        ], 500);
    }
}   
   
    private function getCalendarDate($accountYear, $accountMonth)
    {
        return $this->expenseService->getCalendarDatePublic((int)$accountYear, (int)$accountMonth);
    }

    private function formatDate($year, $month, $format = 'F Y')
    {
        return $this->expenseService->formatDatePublic((int)$year, (int)$month, $format);
    }

    private function processClaimsForCombinedRatio($claimsPaid, $reserves, $salvage, &$monthlyData, &$yearlyData, &$totalClaimsIncurred)
    {
        foreach ($claimsPaid as $claim) {
            if ($claim->account_month !== null) {
                $key = $claim->account_year . '-' . str_pad($claim->account_month, 2, '0', STR_PAD_LEFT);
                if (isset($monthlyData[$key])) {
                    $monthlyData[$key]['claims_incurred'] += $claim->total;
                }
            } else if ($claim->account_year !== null) {
                $yearKey = $claim->account_year;
                if (isset($yearlyData[$yearKey])) {
                    $yearlyData[$yearKey]['claims_incurred'] += $claim->total;
                }
            }
            $totalClaimsIncurred += $claim->total;
        }

        foreach ($reserves as $reserve) {
            if ($reserve->account_month !== null) {
                $key = $reserve->account_year . '-' . str_pad($reserve->account_month, 2, '0', STR_PAD_LEFT);
                if (isset($monthlyData[$key])) {
                    $monthlyData[$key]['claims_incurred'] += $reserve->total;
                }
            } else if ($reserve->account_year !== null) {
                $yearKey = $reserve->account_year;
                if (isset($yearlyData[$yearKey])) {
                    $yearlyData[$yearKey]['claims_incurred'] += $reserve->total;
                }
            }
            $totalClaimsIncurred += $reserve->total;
        }

        foreach ($salvage as $s) {
            if ($s->account_month !== null) {
                $key = $s->account_year . '-' . str_pad($s->account_month, 2, '0', STR_PAD_LEFT);
                if (isset($monthlyData[$key])) {
                    $monthlyData[$key]['claims_incurred'] -= $s->total;
                }
            } else if ($s->account_year !== null) {
                $yearKey = $s->account_year;
                if (isset($yearlyData[$yearKey])) {
                    $yearlyData[$yearKey]['claims_incurred'] -= $s->total;
                }
            }
            $totalClaimsIncurred -= $s->total;
        }
    }

    
    public function getAccountMappings(Request $request)
    {
        $mappings = $this->accountMappingService->getAllMappings();
        return response()->json([
            'status' => 'success',
            'data' => $mappings
        ]);
    }

   
    public function getOverallRatios(Request $request)
    {
        try {
            $year = $request->input('year');
            $month = $request->input('month');
            
            $claimsData = $this->claimsService->getClaimsRatio($year, $month);
            
            $expenseData = $this->expenseService->getExpenses($year, $month);
            $expenseOverall = [
                'total_direct_expenses' => collect($expenseData['monthly'] ?? [])->sum('total_direct_expenses'),
                'nep' => collect($expenseData['monthly'] ?? [])->sum('nep')
            ];
            $expenseOverall['expense_ratio'] = $expenseOverall['nep'] != 0 
                ? ($expenseOverall['total_direct_expenses'] / $expenseOverall['nep']) * 100 
                : 0;
            
            $combinedRequest = new \Illuminate\Http\Request();
            if ($year) {
                $combinedRequest->query->add(['year' => $year]);
            }
            if ($month) {
                $combinedRequest->query->add(['month' => $month]);
            }
            $combinedResponse = $this->getCombinedRatio($combinedRequest);
            $combinedData = json_decode($combinedResponse->getContent(), true);
            
            $combinedOverall = $combinedData['data']['overall'] ?? [
                'combined_ratio' => null,
                'nep' => null,
                'total_claims' => null,
                'total_direct_expenses' => $expenseOverall['total_direct_expenses'] ?? null
            ];
            
            // Log the structure for debugging
            \Log::info('Combined Ratio Data:', [
                'has_data' => isset($combinedData['data']),
                'has_overall' => isset($combinedData['data']['overall']),
                'overall_keys' => $combinedOverall ? array_keys($combinedOverall) : []
            ]);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'claims_ratio' => $claimsData['overall']['ratio'] ?? null,
                    'expense_ratio' => round($expenseOverall['expense_ratio'], 2),
                    'combined_ratio' => $combinedOverall['combined_ratio'] ?? null,
                    'nep' => $combinedOverall['nep'] ?? null,
                    'total_claims' => $combinedData['overall']['total_claims'] ?? null,
                    'total_direct_expenses' => $expenseOverall['total_direct_expenses'] ?? null,
                    'as_of_date' => now()->format('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getOverallRatios: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve overall ratios',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
