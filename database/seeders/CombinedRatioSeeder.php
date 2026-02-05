<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CombinedRatioSeeder extends Seeder
{
    public function run(): void
    {
        $claims = DB::table('claims_ratios')->get();
        $expenses = DB::table('expense_ratios')->get();

        foreach ($claims as $claim) {
            $expense = $expenses->firstWhere('month', $claim->month);

            if (!$expense) {
                continue; // skip if no matching record
            }

            $combined = $claim->ratio + $expense->ratio;

            DB::table('combined_ratios')->insert([
                'month'            => $claim->month,

                'claims_ratio_id'  => $claim->id,
                'expense_ratio_id' => $expense->id,

                'claims_ratio'     => $claim->ratio,
                'expense_ratio'    => $expense->ratio,
                'combined_ratio'   => round($combined, 2),

                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}
