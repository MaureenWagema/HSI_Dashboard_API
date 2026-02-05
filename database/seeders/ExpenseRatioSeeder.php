<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseRatioSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['month' => 'Jan', 'underwriting_expenses' => 18000, 'written_premiums' => 30000],
            ['month' => 'Feb', 'underwriting_expenses' => 20000, 'written_premiums' => 35000],
            ['month' => 'Mar', 'underwriting_expenses' => 19000, 'written_premiums' => 32000],
            ['month' => 'Apr', 'underwriting_expenses' => 21000, 'written_premiums' => 38000],
        ];

        foreach ($data as $item) {
            $ratio = ($item['underwriting_expenses'] / $item['written_premiums']) * 100;

            DB::table('expense_ratios')->insert([
                'month'                   => $item['month'],
                'underwriting_expenses'   => $item['underwriting_expenses'],
                'written_premiums'        => $item['written_premiums'],
                'ratio'                   => round($ratio, 2),
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);
        }
    }
}
