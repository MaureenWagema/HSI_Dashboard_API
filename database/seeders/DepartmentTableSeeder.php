<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentTableSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            ['Department' => 'Sales', 'CostCentre' => 'SAL-01', 'HeadOfDepartment' => 'A. Payet'],
            ['Department' => 'Underwriting', 'CostCentre' => 'UND-01', 'HeadOfDepartment' => 'M. Camille'],
            ['Department' => 'Claims', 'CostCentre' => 'CLM-01', 'HeadOfDepartment' => 'S. Ernesta'],
            ['Department' => 'Accounts', 'CostCentre' => 'ACC-01', 'HeadOfDepartment' => 'K. Pillay'],
        ];

        DB::table('department')->insert($departments);
    }
}