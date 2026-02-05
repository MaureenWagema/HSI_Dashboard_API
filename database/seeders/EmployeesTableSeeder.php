<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeesTableSeeder extends Seeder
{
    public function run()
    {
        $employees = [
            ['EmployeeID' => 1001, 'EmployeeName' => 'Liam Hoareau', 'Department' => 'Sales', 'JobTitle' => 'Senior Sales Executive', 'Email' => 'liam.h@hsi.sc'],
            ['EmployeeID' => 1002, 'EmployeeName' => 'Nadia Labonté', 'Department' => 'Sales', 'JobTitle' => 'Sales Executive', 'Email' => 'nadia.l@hsi.sc'],
            ['EmployeeID' => 1003, 'EmployeeName' => 'Trevor Barbé', 'Department' => 'Sales', 'JobTitle' => 'Corporate Sales Officer', 'Email' => 'trevor.b@hsi.sc'],
            ['EmployeeID' => 2001, 'EmployeeName' => 'Maya Chang-Tave', 'Department' => 'Underwriting', 'JobTitle' => 'Senior Underwriter', 'Email' => 'maya.ct@hsi.sc'],
            ['EmployeeID' => 2002, 'EmployeeName' => 'Ethan Toussaint', 'Department' => 'Underwriting', 'JobTitle' => 'Underwriter', 'Email' => 'ethan.t@hsi.sc'],
            ['EmployeeID' => 2003, 'EmployeeName' => 'Zoë Sophola', 'Department' => 'Underwriting', 'JobTitle' => 'Assistant Underwriter', 'Email' => 'zoe.s@hsi.sc'],
            ['EmployeeID' => 3001, 'EmployeeName' => 'Joel Ernesta', 'Department' => 'Claims', 'JobTitle' => 'Claims Lead', 'Email' => 'joel.e@hsi.sc'],
            ['EmployeeID' => 3002, 'EmployeeName' => 'Rina Simeon', 'Department' => 'Claims', 'JobTitle' => 'Claims Officer', 'Email' => 'rina.s@hsi.sc'],
            ['EmployeeID' => 3003, 'EmployeeName' => 'Karl Bastienne', 'Department' => 'Claims', 'JobTitle' => 'Claims Adjuster', 'Email' => 'karl.b@hsi.sc'],
            ['EmployeeID' => 4001, 'EmployeeName' => 'Priya Pillay', 'Department' => 'Accounts', 'JobTitle' => 'Accountant', 'Email' => 'priya.p@hsi.sc'],
            ['EmployeeID' => 4002, 'EmployeeName' => 'Daniel Camille', 'Department' => 'Accounts', 'JobTitle' => 'Senior Accountant', 'Email' => 'daniel.c@hsi.sc'],
            ['EmployeeID' => 4003, 'EmployeeName' => 'Helena Stravens', 'Department' => 'Accounts', 'JobTitle' => 'Finance Officer', 'Email' => 'helena.s@hsi.sc'],
        ];

        DB::table('employees')->insert($employees);
    }
}