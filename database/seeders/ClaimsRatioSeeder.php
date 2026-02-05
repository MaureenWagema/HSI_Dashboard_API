<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClaimsRatioSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['month' => 'Jan', 'incurred_claims' => 12000, 'earned_premiums' => 30000],
            ['month' => 'Feb', 'incurred_claims' => 15000, 'earned_premiums' => 35000],
            ['month' => 'Mar', 'incurred_claims' => 14000, 'earned_premiums' => 32000],
            ['month' => 'Apr', 'incurred_claims' => 16000, 'earned_premiums' => 38000],
        ];

        foreach ($data as $item) {
            $ratio = ($item['incurred_claims'] / $item['earned_premiums']) * 100;

            DB::table('claims_ratios')->insert([
                'month'            => $item['month'],
                'incurred_claims'  => $item['incurred_claims'],
                'earned_premiums'  => $item['earned_premiums'],
                'ratio'            => round($ratio, 2),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}
