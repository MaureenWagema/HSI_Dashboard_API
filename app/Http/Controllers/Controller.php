<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    /**
     * SQL Server Database Connection (default)
     * @var \Illuminate\Database\Connection
     */
    protected $db;
    
    /**
     * MySQL Database Connection (SLAMS)
     * @var \Illuminate\Database\Connection
     */
    protected $slams_db;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Default SQL Server connection (target database)
        $this->db = DB::connection('sqlsrv');
        
        // MySQL connection for SLAMS (source database)
        $this->dbslamsgb_hsv_live = DB::connection('mysql');
    }
}

