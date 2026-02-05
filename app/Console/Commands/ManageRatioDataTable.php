<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageRatioDataTable extends Command
{
    protected $signature = 'ratio-data:table {action : create|drop|truncate} {--force : Skip confirmation}';
    protected $description = 'Manage the ratio_data table in SQL Server';

    public function handle()
    {
        $action = $this->argument('action');
        
        if (!in_array($action, ['create', 'drop', 'truncate'])) {
            $this->error('Invalid action. Use create, drop, or truncate');
            return 1;
        }

        $method = 'handle' . ucfirst($action);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        $this->error("Action {$action} is not implemented");
        return 1;
    }

    protected function handleCreate()
    {
        try {
            if (DB::connection('sqlsrv')->getSchemaBuilder()->hasTable('ratio_data')) {
                $this->info('Table ratio_data already exists');
                return 0;
            }

            DB::connection('sqlsrv')->statement('CREATE TABLE ratio_data (
                id INT IDENTITY(1,1) PRIMARY KEY,
                display_account_number VARCHAR(50) NOT NULL,
                account_name NVARCHAR(255),
                account_year INT NOT NULL,
                account_month INT NOT NULL,
                monthly_credit DECIMAL(15,2) DEFAULT 0,
                monthly_debit DECIMAL(15,2) DEFAULT 0,
                category VARCHAR(100),
                created_at DATETIME2,
                updated_at DATETIME2,
                CONSTRAINT UQ_ratio_data_unique UNIQUE (display_account_number, account_year, account_month)
            )');
            
            // Add indexes for better performance
            DB::connection('sqlsrv')->statement('CREATE INDEX idx_ratio_data_account ON ratio_data (display_account_number)');
            DB::connection('sqlsrv')->statement('CREATE INDEX idx_ratio_data_period ON ratio_data (account_year, account_month)');
            DB::connection('sqlsrv')->statement('CREATE INDEX idx_ratio_data_category ON ratio_data (category)');
            
            $this->info('Table ratio_data created successfully');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error creating table: ' . $e->getMessage());
            return 1;
        }
    }

    protected function handleDrop()
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to drop the ratio_data table? This cannot be undone.')) {
            $this->info('Operation cancelled');
            return 0;
        }

        try {
            if (!DB::connection('sqlsrv')->getSchemaBuilder()->hasTable('ratio_data')) {
                $this->info('Table ratio_data does not exist');
                return 0;
            }

            // Drop indexes first
            try {
                DB::connection('sqlsrv')->statement('DROP INDEX IF EXISTS idx_ratio_data_account ON ratio_data');
                DB::connection('sqlsrv')->statement('DROP INDEX IF EXISTS idx_ratio_data_period ON ratio_data');
                DB::connection('sqlsrv')->statement('DROP INDEX IF EXISTS idx_ratio_data_category ON ratio_data');
            } catch (\Exception $e) {
                $this->warn('Warning: Could not drop indexes: ' . $e->getMessage());
            }

            // Then drop the table
            DB::connection('sqlsrv')->statement('DROP TABLE ratio_data');
            $this->info('Table ratio_data dropped successfully');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error dropping table: ' . $e->getMessage());
            return 1;
        }
    }

    protected function handleTruncate()
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to truncate the ratio_data table? This cannot be undone.')) {
            $this->info('Operation cancelled');
            return 0;
        }

        try {
            if (!DB::connection('sqlsrv')->getSchemaBuilder()->hasTable('ratio_data')) {
                $this->error('Table ratio_data does not exist');
                return 1;
            }

            DB::connection('sqlsrv')->statement('TRUNCATE TABLE ratio_data');
            $this->info('Table ratio_data truncated successfully');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error truncating table: ' . $e->getMessage());
            return 1;
        }
    }
}
