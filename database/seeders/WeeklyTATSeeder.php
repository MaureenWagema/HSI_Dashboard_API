<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeeklyTATSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['week'=>1,'claim_processing'=>48,'policy_issuance'=>24,'underwriting'=>72,'customer_queries'=>12,'premium_collection'=>18],
            ['week'=>2,'claim_processing'=>50,'policy_issuance'=>25,'underwriting'=>70,'customer_queries'=>13,'premium_collection'=>19],
            ['week'=>3,'claim_processing'=>46,'policy_issuance'=>23,'underwriting'=>71,'customer_queries'=>12,'premium_collection'=>18],
            ['week'=>4,'claim_processing'=>52,'policy_issuance'=>26,'underwriting'=>74,'customer_queries'=>14,'premium_collection'=>20],
            ['week'=>5,'claim_processing'=>47,'policy_issuance'=>24,'underwriting'=>69,'customer_queries'=>13,'premium_collection'=>18],
            ['week'=>6,'claim_processing'=>49,'policy_issuance'=>25,'underwriting'=>72,'customer_queries'=>12,'premium_collection'=>19],
            ['week'=>7,'claim_processing'=>45,'policy_issuance'=>23,'underwriting'=>68,'customer_queries'=>12,'premium_collection'=>18],
            ['week'=>8,'claim_processing'=>48,'policy_issuance'=>24,'underwriting'=>70,'customer_queries'=>13,'premium_collection'=>19],
            ['week'=>9,'claim_processing'=>51,'policy_issuance'=>25,'underwriting'=>73,'customer_queries'=>14,'premium_collection'=>20],
            ['week'=>10,'claim_processing'=>46,'policy_issuance'=>23,'underwriting'=>69,'customer_queries'=>12,'premium_collection'=>18],
            // … add remaining weeks up to 52
        ];

        DB::table('weekly_tat')->insert($data);
    }
}
