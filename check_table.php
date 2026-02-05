<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cols = DB::select("SELECT COLUMN_NAME, DATA_TYPE, NUMERIC_PRECISION, NUMERIC_SCALE 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME = 'kpi_scores' 
                    ORDER BY ORDINAL_POSITION");

foreach($cols as $col) {
    echo $col->COLUMN_NAME . ': ' . $col->DATA_TYPE;
    if ($col->NUMERIC_PRECISION) {
        echo '(' . $col->NUMERIC_PRECISION . ',' . $col->NUMERIC_SCALE . ')';
    }
    echo PHP_EOL;
}
