<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pdepartment; // Adjust the namespace if your model is in a different location

class PdepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departmentsData = [
            ['department_name' => 'Fire', 'annual_budget' => 6339500.00],
            ['department_name' => 'Marine Hull', 'annual_budget' => 9144853.00],
            ['department_name' => 'Aviation', 'annual_budget' => 5441055.00],
            ['department_name' => 'Marine Cargo', 'annual_budget' => 8404604.00],
            ['department_name' => 'Misc Acc', 'annual_budget' => 10465781.00],
            ['department_name' => 'Motor', 'annual_budget' => 11997922.00],
        ];

        foreach ($departmentsData as $data) {
            Pdepartment::create($data);
        }
    }
}