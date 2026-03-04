<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TatController extends Controller
{
    
    public function getTatData()
    {
        $tatData = DB::select("
            SELECT 
                c.claim_no AS `Claim No`,
                d.policy_no AS `Policy No`,
                d.name AS `Name`,
                f.company_dept_name AS `Dept`,
                DATE(c.datereported) AS `Date Reported`,
                DATE(t.StatusEffectiveDate) AS `Offer Date`,
                t.statusdescription,
                DATEDIFF(t.StatusEffectiveDate, c.datereported) AS `Time to Make Offer` 
            FROM claimsinfo c
            INNER JOIN claimstatusinfo t ON t.claim_no = c.claim_no
            INNER JOIN claimsstatuscodes o ON o.StatusCode_ID = t.StatusCode_ID
            INNER JOIN claimsregistryinfo d ON d.claim_no = c.claim_no
            INNER JOIN classinfo e ON e.class_code = c.class_code
            INNER JOIN companydeptinfo f ON f.company_dept_code = e.company_dept_code
            WHERE t.statusdescription LIKE '%FORMAL OFFER MADE%'
        ");

        return response()->json([
            'success' => true,
            'data' => $tatData
        ]);
    }
}
