<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSummariesTableSeeder extends Seeder
{
    public function run()
    {
        $employeeSummaries = [
            ['EmployeeID' => 4001, 'EmployeeName' => 'Priya Pillay', 'Department' => 'Accounts', 'Period' => 'Sep-2025', 'TotalWeightedScore' => 86.55],
            ['EmployeeID' => 4002, 'EmployeeName' => 'Daniel Camille', 'Department' => 'Accounts', 'Period' => 'Sep-2025', 'TotalWeightedScore' => 84.9],
            ['EmployeeID' => 4003, 'EmployeeName' => 'Helena Stravens', 'Department' => 'Accounts', 'Period' => 'Sep-2025', 'TotalWeightedScore' => 84.9],
            ['EmployeeID' => 3002, 'EmployeeName' => 'Rina Simeon', 'Department' => 'Claims', 'Period' => 'Sep-2025', 'TotalWeightedScore' => 81.55],
            ['EmployeeID' => 3003, 'EmployeeName' => 'Karl Bastienne', 'Department' => 'Claims', 'Period' => 'Sep-2025', 'TotalWeightedScore' => 78.15],
            ['EmployeeID' => 3001, 'EmployeeName' => 'Joel Ernesta', 'Department' => 'Claims', 'Period' => 'Sep-2025', 'TotalWeightedScore' => 71.45],
            ['EmployeeID' => 1002, 'EmployeeName' => 'Nadia Labonté', 'Department' => 'Sales', 'Period' => 'Sep-2025', 'TotalWeightedScore' => 79.5],
            ['EmployeeID' => 1001, 'EmployeeName' => 'Liam Hoareau', 'Department' => 'Sales', 'Period' => 'Sep-2025', 'TotalWeightedScore' => 74.25],
            ['EmployeeID' => 1003, 'EmployeeName' => 'Trevor Barbé', 'Department' => 'Sales', 'Period' => 'Sep-2025', 'TotalWeightedScore' => 70.75],
            ['EmployeeID' => 2003, 'EmployeeName' => 'Zoë Sophola', 'Department' => 'Underwriting', 'Period' => 'Sep-2025', 'TotalWeightedScore' => 77.95],
            ['EmployeeID' => 2002, 'EmployeeName' => 'Ethan Toussaint', 'Department' => 'Underwriting', 'Period' => 'Sep-2025', 'TotalWeightedScore' => 73.45],
            ['EmployeeID' => 2001, 'EmployeeName' => 'Maya Chang-Tave', 'Department' => 'Underwriting', 'Period' => 'Sep-2025', 'TotalWeightedScore' => 72.8],
        ];

        DB::table('employee_summaries')->insert($employeeSummaries);
    }
}