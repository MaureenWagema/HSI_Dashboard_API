<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KpiItemsTableSeeder extends Seeder
{
    public function run()
    {
        $kpiItems = [
            ['Department' => 'Sales', 'KPIItem' => 'New Policies Written', 'Weight' => 25],
            ['Department' => 'Sales', 'KPIItem' => 'GWP vs Target (%)', 'Weight' => 30],
            ['Department' => 'Sales', 'KPIItem' => 'Conversion Rate (%)', 'Weight' => 20],
            ['Department' => 'Sales', 'KPIItem' => 'Renewal Retention (%)', 'Weight' => 15],
            ['Department' => 'Sales', 'KPIItem' => 'Pipeline Hygiene', 'Weight' => 10],
            ['Department' => 'Underwriting', 'KPIItem' => 'Quote TAT (hrs)', 'Weight' => 20],
            ['Department' => 'Underwriting', 'KPIItem' => 'Loss Ratio (Expected vs Actual)', 'Weight' => 25],
            ['Department' => 'Underwriting', 'KPIItem' => 'Pricing Adequacy', 'Weight' => 25],
            ['Department' => 'Underwriting', 'KPIItem' => 'Referral Quality', 'Weight' => 15],
            ['Department' => 'Underwriting', 'KPIItem' => 'Policy Issuance TAT (hrs)', 'Weight' => 15],
            ['Department' => 'Claims', 'KPIItem' => 'Claim TAT (FNOL to Settlement)', 'Weight' => 30],
            ['Department' => 'Claims', 'KPIItem' => 'Leakage Control / Accuracy', 'Weight' => 25],
            ['Department' => 'Claims', 'KPIItem' => 'Customer Satisfaction (CSAT)', 'Weight' => 20],
            ['Department' => 'Claims', 'KPIItem' => 'Recoveries/Subrogation', 'Weight' => 15],
            ['Department' => 'Claims', 'KPIItem' => 'Documentation Quality', 'Weight' => 10],
            ['Department' => 'Accounts', 'KPIItem' => 'Premium Allocation Timeliness', 'Weight' => 25],
            ['Department' => 'Accounts', 'KPIItem' => 'Reconciliation Accuracy', 'Weight' => 30],
            ['Department' => 'Accounts', 'KPIItem' => 'AR Days (Collections)', 'Weight' => 20],
            ['Department' => 'Accounts', 'KPIItem' => 'Regulatory Reporting Timeliness', 'Weight' => 15],
            ['Department' => 'Accounts', 'KPIItem' => 'Expense Accrual Accuracy', 'Weight' => 10],
        ];

        DB::table('kpi_items')->insert($kpiItems);
    }
}