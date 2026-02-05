<?php

namespace App\Services\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HandlesDateMapping
{
  
    public function toCalendarDate(int $accountYear, int $accountMonth): array
    {
        $cacheKey = "account_to_calendar_{$accountYear}_{$accountMonth}";
        
        return cache()->remember($cacheKey, now()->addDay(), function() use ($accountYear, $accountMonth) {
            $period = DB::table('accperiodinfo')
                ->select('calYear as calendar_year', 'calMonth as calendar_month')
                ->where('Account_year', $accountYear)
                ->where('Account_month', $accountMonth)
                ->first();
            
            if ($period) {
                return [
                    'calendar_year' => (int)$period->calendar_year,
                    'calendar_month' => (int)$period->calendar_month
                ];
            }
            
            Log::warning('No calendar mapping found for account period', [
                'account_year' => $accountYear,
                'account_month' => $accountMonth
            ]);
            
            // Fallback to account period if no mapping found
            return [
                'calendar_year' => $accountYear,
                'calendar_month' => $accountMonth
            ];
        });
    }

    
    public function getCalendarYear(int $accountYear): int
    {
        $mapping = $this->getYearMapping($accountYear);
        return $mapping['calendar_year'] ?? $accountYear;
    }

   
    public function getCalendarMonth(int $accountYear, int $accountMonth): int
    {
        $calendarDate = $this->toCalendarDate($accountYear, $accountMonth);
        return $calendarDate['calendar_month'];
    }

   
    protected function getYearMapping(int $accountYear): ?array
    {
        $cacheKey = "year_mapping_{$accountYear}";
        
        return cache()->remember($cacheKey, now()->addWeek(), function() use ($accountYear) {
            $result = DB::table('accperiodinfo')
                ->select(
                    'Account_year as account_year',
                    'calYear as calendar_year'
                )
                ->where('Account_year', $accountYear)
                ->groupBy('Account_year', 'calYear')
                ->first();
            
            return $result ? (array)$result : null;
        });
    }

    public function formatCalendarDate(int $accountYear, int $accountMonth, string $format = 'F Y'): string
    {
        $calendarDate = $this->toCalendarDate($accountYear, $accountMonth);
        return date($format, mktime(0, 0, 0, $calendarDate['calendar_month'], 1, $calendarDate['calendar_year']));
    }
}