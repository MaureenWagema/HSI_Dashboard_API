<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\KpiScore;

class KpiScoresTableSeeder extends Seeder
{
    public function run()
    {
        $kpiScores = [
            ['EmployeeID' => 1001, 'EmployeeName' => 'Liam Hoareau', 'Department' => 'Sales', 'KPIItem' => 'New Policies Written', 'Weight' => 25, 'Score' => 64, 'WeightedScore' => 16, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1001, 'EmployeeName' => 'Liam Hoareau', 'Department' => 'Sales', 'KPIItem' => 'GWP vs Target (%)', 'Weight' => 30, 'Score' => 85, 'WeightedScore' => 25.5, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1001, 'EmployeeName' => 'Liam Hoareau', 'Department' => 'Sales', 'KPIItem' => 'Conversion Rate (%)', 'Weight' => 20, 'Score' => 63, 'WeightedScore' => 12.6, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1001, 'EmployeeName' => 'Liam Hoareau', 'Department' => 'Sales', 'KPIItem' => 'Renewal Retention (%)', 'Weight' => 15, 'Score' => 79, 'WeightedScore' => 11.85, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1001, 'EmployeeName' => 'Liam Hoareau', 'Department' => 'Sales', 'KPIItem' => 'Pipeline Hygiene', 'Weight' => 10, 'Score' => 83, 'WeightedScore' => 8.3, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1002, 'EmployeeName' => 'Nadia Labonté', 'Department' => 'Sales', 'KPIItem' => 'New Policies Written', 'Weight' => 25, 'Score' => 88, 'WeightedScore' => 22, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1002, 'EmployeeName' => 'Nadia Labonté', 'Department' => 'Sales', 'KPIItem' => 'GWP vs Target (%)', 'Weight' => 30, 'Score' => 74, 'WeightedScore' => 22.2, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1002, 'EmployeeName' => 'Nadia Labonté', 'Department' => 'Sales', 'KPIItem' => 'Conversion Rate (%)', 'Weight' => 20, 'Score' => 83, 'WeightedScore' => 16.6, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1002, 'EmployeeName' => 'Nadia Labonté', 'Department' => 'Sales', 'KPIItem' => 'Renewal Retention (%)', 'Weight' => 15, 'Score' => 68, 'WeightedScore' => 10.2, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1002, 'EmployeeName' => 'Nadia Labonté', 'Department' => 'Sales', 'KPIItem' => 'Pipeline Hygiene', 'Weight' => 10, 'Score' => 85, 'WeightedScore' => 8.5, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1003, 'EmployeeName' => 'Trevor Barbé', 'Department' => 'Sales', 'KPIItem' => 'New Policies Written', 'Weight' => 25, 'Score' => 86, 'WeightedScore' => 21.5, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1003, 'EmployeeName' => 'Trevor Barbé', 'Department' => 'Sales', 'KPIItem' => 'GWP vs Target (%)', 'Weight' => 30, 'Score' => 68, 'WeightedScore' => 20.4, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1003, 'EmployeeName' => 'Trevor Barbé', 'Department' => 'Sales', 'KPIItem' => 'Conversion Rate (%)', 'Weight' => 20, 'Score' => 64, 'WeightedScore' => 12.8, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1003, 'EmployeeName' => 'Trevor Barbé', 'Department' => 'Sales', 'KPIItem' => 'Renewal Retention (%)', 'Weight' => 15, 'Score' => 67, 'WeightedScore' => 10.05, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 1003, 'EmployeeName' => 'Trevor Barbé', 'Department' => 'Sales', 'KPIItem' => 'Pipeline Hygiene', 'Weight' => 10, 'Score' => 60, 'WeightedScore' => 6, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2001, 'EmployeeName' => 'Maya Chang-Tave', 'Department' => 'Underwriting', 'KPIItem' => 'Quote TAT (hrs)', 'Weight' => 20, 'Score' => 71, 'WeightedScore' => 14.2, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2001, 'EmployeeName' => 'Maya Chang-Tave', 'Department' => 'Underwriting', 'KPIItem' => 'Loss Ratio (Expected vs Actual)', 'Weight' => 25, 'Score' => 66, 'WeightedScore' => 16.5, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2001, 'EmployeeName' => 'Maya Chang-Tave', 'Department' => 'Underwriting', 'KPIItem' => 'Pricing Adequacy', 'Weight' => 25, 'Score' => 79, 'WeightedScore' => 19.75, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2001, 'EmployeeName' => 'Maya Chang-Tave', 'Department' => 'Underwriting', 'KPIItem' => 'Referral Quality', 'Weight' => 15, 'Score' => 65, 'WeightedScore' => 9.75, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2001, 'EmployeeName' => 'Maya Chang-Tave', 'Department' => 'Underwriting', 'KPIItem' => 'Policy Issuance TAT (hrs)', 'Weight' => 15, 'Score' => 84, 'WeightedScore' => 12.6, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2002, 'EmployeeName' => 'Ethan Toussaint', 'Department' => 'Underwriting', 'KPIItem' => 'Quote TAT (hrs)', 'Weight' => 20, 'Score' => 60, 'WeightedScore' => 12, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2002, 'EmployeeName' => 'Ethan Toussaint', 'Department' => 'Underwriting', 'KPIItem' => 'Loss Ratio (Expected vs Actual)', 'Weight' => 25, 'Score' => 94, 'WeightedScore' => 23.5, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2002, 'EmployeeName' => 'Ethan Toussaint', 'Department' => 'Underwriting', 'KPIItem' => 'Pricing Adequacy', 'Weight' => 25, 'Score' => 69, 'WeightedScore' => 17.25, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2002, 'EmployeeName' => 'Ethan Toussaint', 'Department' => 'Underwriting', 'KPIItem' => 'Referral Quality', 'Weight' => 15, 'Score' => 74, 'WeightedScore' => 11.1, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2002, 'EmployeeName' => 'Ethan Toussaint', 'Department' => 'Underwriting', 'KPIItem' => 'Policy Issuance TAT (hrs)', 'Weight' => 15, 'Score' => 64, 'WeightedScore' => 9.6, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2003, 'EmployeeName' => 'Zoë Sophola', 'Department' => 'Underwriting', 'KPIItem' => 'Quote TAT (hrs)', 'Weight' => 20, 'Score' => 69, 'WeightedScore' => 13.8, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2003, 'EmployeeName' => 'Zoë Sophola', 'Department' => 'Underwriting', 'KPIItem' => 'Loss Ratio (Expected vs Actual)', 'Weight' => 25, 'Score' => 83, 'WeightedScore' => 20.75, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2003, 'EmployeeName' => 'Zoë Sophola', 'Department' => 'Underwriting', 'KPIItem' => 'Pricing Adequacy', 'Weight' => 25, 'Score' => 95, 'WeightedScore' => 23.75, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2003, 'EmployeeName' => 'Zoë Sophola', 'Department' => 'Underwriting', 'KPIItem' => 'Referral Quality', 'Weight' => 15, 'Score' => 63, 'WeightedScore' => 9.45, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 2003, 'EmployeeName' => 'Zoë Sophola', 'Department' => 'Underwriting', 'KPIItem' => 'Policy Issuance TAT (hrs)', 'Weight' => 15, 'Score' => 68, 'WeightedScore' => 10.2, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3001, 'EmployeeName' => 'Joel Ernesta', 'Department' => 'Claims', 'KPIItem' => 'Claim TAT (FNOL to Settlement)', 'Weight' => 30, 'Score' => 79, 'WeightedScore' => 23.7, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3001, 'EmployeeName' => 'Joel Ernesta', 'Department' => 'Claims', 'KPIItem' => 'Leakage Control / Accuracy', 'Weight' => 25, 'Score' => 61, 'WeightedScore' => 15.25, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3001, 'EmployeeName' => 'Joel Ernesta', 'Department' => 'Claims', 'KPIItem' => 'Customer Satisfaction (CSAT)', 'Weight' => 20, 'Score' => 60, 'WeightedScore' => 12, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3001, 'EmployeeName' => 'Joel Ernesta', 'Department' => 'Claims', 'KPIItem' => 'Recoveries/Subrogation', 'Weight' => 15, 'Score' => 76, 'WeightedScore' => 11.4, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3001, 'EmployeeName' => 'Joel Ernesta', 'Department' => 'Claims', 'KPIItem' => 'Documentation Quality', 'Weight' => 10, 'Score' => 91, 'WeightedScore' => 9.1, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3002, 'EmployeeName' => 'Rina Simeon', 'Department' => 'Claims', 'KPIItem' => 'Claim TAT (FNOL to Settlement)', 'Weight' => 30, 'Score' => 89, 'WeightedScore' => 26.7, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3002, 'EmployeeName' => 'Rina Simeon', 'Department' => 'Claims', 'KPIItem' => 'Leakage Control / Accuracy', 'Weight' => 25, 'Score' => 88, 'WeightedScore' => 22, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3002, 'EmployeeName' => 'Rina Simeon', 'Department' => 'Claims', 'KPIItem' => 'Customer Satisfaction (CSAT)', 'Weight' => 20, 'Score' => 67, 'WeightedScore' => 13.4, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3002, 'EmployeeName' => 'Rina Simeon', 'Department' => 'Claims', 'KPIItem' => 'Recoveries/Subrogation', 'Weight' => 15, 'Score' => 83, 'WeightedScore' => 12.45, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3002, 'EmployeeName' => 'Rina Simeon', 'Department' => 'Claims', 'KPIItem' => 'Documentation Quality', 'Weight' => 10, 'Score' => 70, 'WeightedScore' => 7, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3003, 'EmployeeName' => 'Karl Bastienne', 'Department' => 'Claims', 'KPIItem' => 'Claim TAT (FNOL to Settlement)', 'Weight' => 30, 'Score' => 95, 'WeightedScore' => 28.5, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3003, 'EmployeeName' => 'Karl Bastienne', 'Department' => 'Claims', 'KPIItem' => 'Leakage Control / Accuracy', 'Weight' => 25, 'Score' => 60, 'WeightedScore' => 15, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3003, 'EmployeeName' => 'Karl Bastienne', 'Department' => 'Claims', 'KPIItem' => 'Customer Satisfaction (CSAT)', 'Weight' => 20, 'Score' => 67, 'WeightedScore' => 13.4, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3003, 'EmployeeName' => 'Karl Bastienne', 'Department' => 'Claims', 'KPIItem' => 'Recoveries/Subrogation', 'Weight' => 15, 'Score' => 83, 'WeightedScore' => 12.45, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 3003, 'EmployeeName' => 'Karl Bastienne', 'Department' => 'Claims', 'KPIItem' => 'Documentation Quality', 'Weight' => 10, 'Score' => 88, 'WeightedScore' => 8.8, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4001, 'EmployeeName' => 'Priya Pillay', 'Department' => 'Accounts', 'KPIItem' => 'Premium Allocation Timeliness', 'Weight' => 25, 'Score' => 92, 'WeightedScore' => 23, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4001, 'EmployeeName' => 'Priya Pillay', 'Department' => 'Accounts', 'KPIItem' => 'Reconciliation Accuracy', 'Weight' => 30, 'Score' => 81, 'WeightedScore' => 24.3, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4001, 'EmployeeName' => 'Priya Pillay', 'Department' => 'Accounts', 'KPIItem' => 'AR Days (Collections)', 'Weight' => 20, 'Score' => 95, 'WeightedScore' => 19, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4001, 'EmployeeName' => 'Priya Pillay', 'Department' => 'Accounts', 'KPIItem' => 'Regulatory Reporting Timeliness', 'Weight' => 15, 'Score' => 93, 'WeightedScore' => 13.95, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4001, 'EmployeeName' => 'Priya Pillay', 'Department' => 'Accounts', 'KPIItem' => 'Expense Accrual Accuracy', 'Weight' => 10, 'Score' => 63, 'WeightedScore' => 6.3, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4002, 'EmployeeName' => 'Daniel Camille', 'Department' => 'Accounts', 'KPIItem' => 'Premium Allocation Timeliness', 'Weight' => 25, 'Score' => 87, 'WeightedScore' => 21.75, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4002, 'EmployeeName' => 'Daniel Camille', 'Department' => 'Accounts', 'KPIItem' => 'Reconciliation Accuracy', 'Weight' => 30, 'Score' => 93, 'WeightedScore' => 27.9, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4002, 'EmployeeName' => 'Daniel Camille', 'Department' => 'Accounts', 'KPIItem' => 'AR Days (Collections)', 'Weight' => 20, 'Score' => 79, 'WeightedScore' => 15.8, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4002, 'EmployeeName' => 'Daniel Camille', 'Department' => 'Accounts', 'KPIItem' => 'Regulatory Reporting Timeliness', 'Weight' => 15, 'Score' => 87, 'WeightedScore' => 13.05, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4002, 'EmployeeName' => 'Daniel Camille', 'Department' => 'Accounts', 'KPIItem' => 'Expense Accrual Accuracy', 'Weight' => 10, 'Score' => 64, 'WeightedScore' => 6.4, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4003, 'EmployeeName' => 'Helena Stravens', 'Department' => 'Accounts', 'KPIItem' => 'Premium Allocation Timeliness', 'Weight' => 25, 'Score' => 79, 'WeightedScore' => 19.75, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4003, 'EmployeeName' => 'Helena Stravens', 'Department' => 'Accounts', 'KPIItem' => 'Reconciliation Accuracy', 'Weight' => 30, 'Score' => 93, 'WeightedScore' => 27.9, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4003, 'EmployeeName' => 'Helena Stravens', 'Department' => 'Accounts', 'KPIItem' => 'AR Days (Collections)', 'Weight' => 20, 'Score' => 90, 'WeightedScore' => 18, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4003, 'EmployeeName' => 'Helena Stravens', 'Department' => 'Accounts', 'KPIItem' => 'Regulatory Reporting Timeliness', 'Weight' => 15, 'Score' => 69, 'WeightedScore' => 10.35, 'Period' => 'Sep-2025'],
            ['EmployeeID' => 4003, 'EmployeeName' => 'Helena Stravens', 'Department' => 'Accounts', 'KPIItem' => 'Expense Accrual Accuracy', 'Weight' => 10, 'Score' => 89, 'WeightedScore' => 8.9, 'Period' => 'Sep-2025'],
        ];

        // Insert one by one using the model to ensure proper type casting
        foreach ($kpiScores as $score) {
            KpiScore::create($score);
        }
    }
}