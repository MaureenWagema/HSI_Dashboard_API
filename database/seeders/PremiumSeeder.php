<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Premium;

class PremiumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $premiumsData = [
            // Fire Department
            ['department_name' => 'Fire', 'month' => 'Jan', 'year' => 2025, 'annual_budget' => 6339500.00, 'premium_actuals' => 619495.00, 'erm_loading' => 66740.00, 'vat_amount' => 78521.00],
            ['department_name' => 'Fire', 'month' => 'Feb', 'year' => 2025, 'annual_budget' => 6339500.00, 'premium_actuals' => 339968.00, 'erm_loading' => 116211.00, 'vat_amount' => 45206.00],
            ['department_name' => 'Fire', 'month' => 'Mar', 'year' => 2025, 'annual_budget' => 6339500.00, 'premium_actuals' => 860016.00, 'erm_loading' => 70511.00, 'vat_amount' => 56993.00],
            ['department_name' => 'Fire', 'month' => 'Apr', 'year' => 2025, 'annual_budget' => 6339500.00, 'premium_actuals' => 462454.00, 'erm_loading' => 105026.00, 'vat_amount' => 34336.00],
            ['department_name' => 'Fire', 'month' => 'May', 'year' => 2025, 'annual_budget' => 6339500.00, 'premium_actuals' => 719865.00, 'erm_loading' => 79900.00, 'vat_amount' => 89658.00],
            ['department_name' => 'Fire', 'month' => 'Jun', 'year' => 2025, 'annual_budget' => 6339500.00, 'premium_actuals' => 644307.00, 'erm_loading' => 100278.00, 'vat_amount' => 30733.00],
            ['department_name' => 'Fire', 'month' => 'Jul', 'year' => 2025, 'annual_budget' => 6339500.00, 'premium_actuals' => 1056735.00, 'erm_loading' => 144994.00, 'vat_amount' => 69974.00],
            ['department_name' => 'Fire', 'month' => 'Aug', 'year' => 2025, 'annual_budget' => 6339500.00, 'premium_actuals' => 1402824.00, 'erm_loading' => 164317.00, 'vat_amount' => 68027.00],
            ['department_name' => 'Fire', 'month' => 'Sep', 'year' => 2025, 'annual_budget' => 6339500.00, 'premium_actuals' => 566513.00, 'erm_loading' => 104944.00, 'vat_amount' => 79493.00],
            ['department_name' => 'Fire', 'month' => 'Oct', 'year' => 2025, 'annual_budget' => 6339500.00, 'premium_actuals' => 613674.00, 'erm_loading' => 187129.00, 'vat_amount' => 57241.00],
            ['department_name' => 'Fire', 'month' => 'Nov', 'year' => 2025, 'annual_budget' => 6339500.00, 'premium_actuals' => 995036.00, 'erm_loading' => 181553.00, 'vat_amount' => 36864.00],
            ['department_name' => 'Fire', 'month' => 'Dec', 'year' => 2025, 'annual_budget' => 6339500.00, 'premium_actuals' => 400969.00, 'erm_loading' => 60590.00, 'vat_amount' => 69758.00],

            // Marine Hull Department
            ['department_name' => 'Marine Hull', 'month' => 'Jan', 'year' => 2025, 'annual_budget' => 9144853.00, 'premium_actuals' => 620934.00, 'erm_loading' => 106212.00, 'vat_amount' => 28662.00],
            ['department_name' => 'Marine Hull', 'month' => 'Feb', 'year' => 2025, 'annual_budget' => 9144853.00, 'premium_actuals' => 786068.00, 'erm_loading' => 87686.00, 'vat_amount' => 77564.00],
            ['department_name' => 'Marine Hull', 'month' => 'Mar', 'year' => 2025, 'annual_budget' => 9144853.00, 'premium_actuals' => 532606.00, 'erm_loading' => 193390.00, 'vat_amount' => 58515.00],
            ['department_name' => 'Marine Hull', 'month' => 'Apr', 'year' => 2025, 'annual_budget' => 9144853.00, 'premium_actuals' => 354812.00, 'erm_loading' => 149366.00, 'vat_amount' => 70646.00],
            ['department_name' => 'Marine Hull', 'month' => 'May', 'year' => 2025, 'annual_budget' => 9144853.00, 'premium_actuals' => 983449.00, 'erm_loading' => 147345.00, 'vat_amount' => 38894.00],
            ['department_name' => 'Marine Hull', 'month' => 'Jun', 'year' => 2025, 'annual_budget' => 9144853.00, 'premium_actuals' => 1298520.00, 'erm_loading' => 96379.00, 'vat_amount' => 32905.00],
            ['department_name' => 'Marine Hull', 'month' => 'Jul', 'year' => 2025, 'annual_budget' => 9144853.00, 'premium_actuals' => 887889.00, 'erm_loading' => 127966.00, 'vat_amount' => 28014.00],
            ['department_name' => 'Marine Hull', 'month' => 'Aug', 'year' => 2025, 'annual_budget' => 9144853.00, 'premium_actuals' => 612101.00, 'erm_loading' => 146909.00, 'vat_amount' => 57203.00],
            ['department_name' => 'Marine Hull', 'month' => 'Sep', 'year' => 2025, 'annual_budget' => 9144853.00, 'premium_actuals' => 550535.00, 'erm_loading' => 156835.00, 'vat_amount' => 67719.00],
            ['department_name' => 'Marine Hull', 'month' => 'Oct', 'year' => 2025, 'annual_budget' => 9144853.00, 'premium_actuals' => 914569.00, 'erm_loading' => 109420.00, 'vat_amount' => 58092.00],
            ['department_name' => 'Marine Hull', 'month' => 'Nov', 'year' => 2025, 'annual_budget' => 9144853.00, 'premium_actuals' => 639648.00, 'erm_loading' => 108225.00, 'vat_amount' => 68071.00],
            ['department_name' => 'Marine Hull', 'month' => 'Dec', 'year' => 2025, 'annual_budget' => 9144853.00, 'premium_actuals' => 1043906.00, 'erm_loading' => 176883.00, 'vat_amount' => 81568.00],

            // Aviation Department
            ['department_name' => 'Aviation', 'month' => 'Jan', 'year' => 2025, 'annual_budget' => 5441055.00, 'premium_actuals' => 1187762.00, 'erm_loading' => 150094.00, 'vat_amount' => 71131.00],
            ['department_name' => 'Aviation', 'month' => 'Feb', 'year' => 2025, 'annual_budget' => 5441055.00, 'premium_actuals' => 1499366.00, 'erm_loading' => 112061.00, 'vat_amount' => 62457.00],
            ['department_name' => 'Aviation', 'month' => 'Mar', 'year' => 2025, 'annual_budget' => 5441055.00, 'premium_actuals' => 965902.00, 'erm_loading' => 132739.00, 'vat_amount' => 88220.00],
            ['department_name' => 'Aviation', 'month' => 'Apr', 'year' => 2025, 'annual_budget' => 5441055.00, 'premium_actuals' => 994387.00, 'erm_loading' => 72002.00, 'vat_amount' => 29261.00],
            ['department_name' => 'Aviation', 'month' => 'May', 'year' => 2025, 'annual_budget' => 5441055.00, 'premium_actuals' => 1422016.00, 'erm_loading' => 101428.00, 'vat_amount' => 96715.00],
            ['department_name' => 'Aviation', 'month' => 'Jun', 'year' => 2025, 'annual_budget' => 5441055.00, 'premium_actuals' => 1413117.00, 'erm_loading' => 136907.00, 'vat_amount' => 25585.00],
            ['department_name' => 'Aviation', 'month' => 'Jul', 'year' => 2025, 'annual_budget' => 5441055.00, 'premium_actuals' => 310075.00, 'erm_loading' => 137371.00, 'vat_amount' => 38784.00],
            ['department_name' => 'Aviation', 'month' => 'Aug', 'year' => 2025, 'annual_budget' => 5441055.00, 'premium_actuals' => 1023583.00, 'erm_loading' => 84091.00, 'vat_amount' => 45247.00],
            ['department_name' => 'Aviation', 'month' => 'Sep', 'year' => 2025, 'annual_budget' => 5441055.00, 'premium_actuals' => 1011798.00, 'erm_loading' => 55230.00, 'vat_amount' => 54260.00],
            ['department_name' => 'Aviation', 'month' => 'Oct', 'year' => 2025, 'annual_budget' => 5441055.00, 'premium_actuals' => 606804.00, 'erm_loading' => 150685.00, 'vat_amount' => 93175.00],
            ['department_name' => 'Aviation', 'month' => 'Nov', 'year' => 2025, 'annual_budget' => 5441055.00, 'premium_actuals' => 380888.00, 'erm_loading' => 66494.00, 'vat_amount' => 79795.00],
            ['department_name' => 'Aviation', 'month' => 'Dec', 'year' => 2025, 'annual_budget' => 5441055.00, 'premium_actuals' => 675482.00, 'erm_loading' => 110641.00, 'vat_amount' => 93446.00],

            // Marine Cargo Department
            ['department_name' => 'Marine Cargo', 'month' => 'Jan', 'year' => 2025, 'annual_budget' => 8404604.00, 'premium_actuals' => 1173610.00, 'erm_loading' => 167883.00, 'vat_amount' => 36154.00],
            ['department_name' => 'Marine Cargo', 'month' => 'Feb', 'year' => 2025, 'annual_budget' => 8404604.00, 'premium_actuals' => 501470.00, 'erm_loading' => 71247.00, 'vat_amount' => 28360.00],
            ['department_name' => 'Marine Cargo', 'month' => 'Mar', 'year' => 2025, 'annual_budget' => 8404604.00, 'premium_actuals' => 454359.00, 'erm_loading' => 104265.00, 'vat_amount' => 29017.00],
            ['department_name' => 'Marine Cargo', 'month' => 'Apr', 'year' => 2025, 'annual_budget' => 8404604.00, 'premium_actuals' => 1006257.00, 'erm_loading' => 122571.00, 'vat_amount' => 80811.00],
            ['department_name' => 'Marine Cargo', 'month' => 'May', 'year' => 2025, 'annual_budget' => 8404604.00, 'premium_actuals' => 909899.00, 'erm_loading' => 159810.00, 'vat_amount' => 42104.00],
            ['department_name' => 'Marine Cargo', 'month' => 'Jun', 'year' => 2025, 'annual_budget' => 8404604.00, 'premium_actuals' => 1393731.00, 'erm_loading' => 170651.00, 'vat_amount' => 55833.00],
            ['department_name' => 'Marine Cargo', 'month' => 'Jul', 'year' => 2025, 'annual_budget' => 8404604.00, 'premium_actuals' => 456626.00, 'erm_loading' => 91327.00, 'vat_amount' => 65458.00],
            ['department_name' => 'Marine Cargo', 'month' => 'Aug', 'year' => 2025, 'annual_budget' => 8404604.00, 'premium_actuals' => 842896.00, 'erm_loading' => 199735.00, 'vat_amount' => 86637.00],
            ['department_name' => 'Marine Cargo', 'month' => 'Sep', 'year' => 2025, 'annual_budget' => 8404604.00, 'premium_actuals' => 1119247.00, 'erm_loading' => 177423.00, 'vat_amount' => 66940.00],
            ['department_name' => 'Marine Cargo', 'month' => 'Oct', 'year' => 2025, 'annual_budget' => 8404604.00, 'premium_actuals' => 365513.00, 'erm_loading' => 92159.00, 'vat_amount' => 58899.00],
            ['department_name' => 'Marine Cargo', 'month' => 'Nov', 'year' => 2025, 'annual_budget' => 8404604.00, 'premium_actuals' => 666126.00, 'erm_loading' => 140250.00, 'vat_amount' => 88863.00],
            ['department_name' => 'Marine Cargo', 'month' => 'Dec', 'year' => 2025, 'annual_budget' => 8404604.00, 'premium_actuals' => 922726.00, 'erm_loading' => 115494.00, 'vat_amount' => 34330.00],

            // Misc Acc Department
            ['department_name' => 'Misc Acc', 'month' => 'Jan', 'year' => 2025, 'annual_budget' => 10465781.00, 'premium_actuals' => 365033.00, 'erm_loading' => 87324.00, 'vat_amount' => 73383.00],
            ['department_name' => 'Misc Acc', 'month' => 'Feb', 'year' => 2025, 'annual_budget' => 10465781.00, 'premium_actuals' => 370072.00, 'erm_loading' => 176696.00, 'vat_amount' => 27004.00],
            ['department_name' => 'Misc Acc', 'month' => 'Mar', 'year' => 2025, 'annual_budget' => 10465781.00, 'premium_actuals' => 695098.00, 'erm_loading' => 80350.00, 'vat_amount' => 40694.00],
            ['department_name' => 'Misc Acc', 'month' => 'Apr', 'year' => 2025, 'annual_budget' => 10465781.00, 'premium_actuals' => 774652.00, 'erm_loading' => 194200.00, 'vat_amount' => 95662.00],
            ['department_name' => 'Misc Acc', 'month' => 'May', 'year' => 2025, 'annual_budget' => 10465781.00, 'premium_actuals' => 1071464.00, 'erm_loading' => 134719.00, 'vat_amount' => 31286.00],
            ['department_name' => 'Misc Acc', 'month' => 'Jun', 'year' => 2025, 'annual_budget' => 10465781.00, 'premium_actuals' => 1217514.00, 'erm_loading' => 187811.00, 'vat_amount' => 27278.00],
            ['department_name' => 'Misc Acc', 'month' => 'Jul', 'year' => 2025, 'annual_budget' => 10465781.00, 'premium_actuals' => 822150.00, 'erm_loading' => 121855.00, 'vat_amount' => 34558.00],
            ['department_name' => 'Misc Acc', 'month' => 'Aug', 'year' => 2025, 'annual_budget' => 10465781.00, 'premium_actuals' => 744923.00, 'erm_loading' => 142475.00, 'vat_amount' => 49530.00],
            ['department_name' => 'Misc Acc', 'month' => 'Sep', 'year' => 2025, 'annual_budget' => 10465781.00, 'premium_actuals' => 499304.00, 'erm_loading' => 65106.00, 'vat_amount' => 69534.00],
            ['department_name' => 'Misc Acc', 'month' => 'Oct', 'year' => 2025, 'annual_budget' => 10465781.00, 'premium_actuals' => 1482711.00, 'erm_loading' => 165381.00, 'vat_amount' => 31136.00],
            ['department_name' => 'Misc Acc', 'month' => 'Nov', 'year' => 2025, 'annual_budget' => 10465781.00, 'premium_actuals' => 1321197.00, 'erm_loading' => 191344.00, 'vat_amount' => 28857.00],
            ['department_name' => 'Misc Acc', 'month' => 'Dec', 'year' => 2025, 'annual_budget' => 10465781.00, 'premium_actuals' => 410437.00, 'erm_loading' => 90479.00, 'vat_amount' => 75679.00],

            // Motor Department
            ['department_name' => 'Motor', 'month' => 'Jan', 'year' => 2025, 'annual_budget' => 11997922.00, 'premium_actuals' => 1002663.00, 'erm_loading' => 55847.00, 'vat_amount' => 31247.00],
            ['department_name' => 'Motor', 'month' => 'Feb', 'year' => 2025, 'annual_budget' => 11997922.00, 'premium_actuals' => 1434839.00, 'erm_loading' => 105504.00, 'vat_amount' => 53973.00],
            ['department_name' => 'Motor', 'month' => 'Mar', 'year' => 2025, 'annual_budget' => 11997922.00, 'premium_actuals' => 1427997.00, 'erm_loading' => 64693.00, 'vat_amount' => 90895.00],
            ['department_name' => 'Motor', 'month' => 'Apr', 'year' => 2025, 'annual_budget' => 11997922.00, 'premium_actuals' => 679412.00, 'erm_loading' => 120713.00, 'vat_amount' => 74718.00],
            ['department_name' => 'Motor', 'month' => 'May', 'year' => 2025, 'annual_budget' => 11997922.00, 'premium_actuals' => 590492.00, 'erm_loading' => 55141.00, 'vat_amount' => 41239.00],
            ['department_name' => 'Motor', 'month' => 'Jun', 'year' => 2025, 'annual_budget' => 11997922.00, 'premium_actuals' => 1388424.00, 'erm_loading' => 50503.00, 'vat_amount' => 70721.00],
            ['department_name' => 'Motor', 'month' => 'Jul', 'year' => 2025, 'annual_budget' => 11997922.00, 'premium_actuals' => 495914.00, 'erm_loading' => 108984.00, 'vat_amount' => 83214.00],
            ['department_name' => 'Motor', 'month' => 'Aug', 'year' => 2025, 'annual_budget' => 11997922.00, 'premium_actuals' => 360374.00, 'erm_loading' => 102028.00, 'vat_amount' => 66609.00],
            ['department_name' => 'Motor', 'month' => 'Sep', 'year' => 2025, 'annual_budget' => 11997922.00, 'premium_actuals' => 787996.00, 'erm_loading' => 67130.00, 'vat_amount' => 28722.00],
            ['department_name' => 'Motor', 'month' => 'Oct', 'year' => 2025, 'annual_budget' => 11997922.00, 'premium_actuals' => 794490.00, 'erm_loading' => 131742.00, 'vat_amount' => 93113.00],
            ['department_name' => 'Motor', 'month' => 'Nov', 'year' => 2025, 'annual_budget' => 11997922.00, 'premium_actuals' => 494181.00, 'erm_loading' => 72459.00, 'vat_amount' => 38210.00],
            ['department_name' => 'Motor', 'month' => 'Dec', 'year' => 2025, 'annual_budget' => 11997922.00, 'premium_actuals' => 1343929.00, 'erm_loading' => 193778.00, 'vat_amount' => 58438.00],
        ];

        foreach ($premiumsData as $data) {
            Premium::create($data);
        }
    }
};