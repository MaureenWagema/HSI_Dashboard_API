-- Add Debit_ID and sub_department columns to actual_premium table
-- Run this on SQL Server database

USE [your_database_name];
GO

-- Check if Debit_ID column exists, if not add it
IF NOT EXISTS (
    SELECT 1 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'actual_premium' 
    AND COLUMN_NAME = 'Debit_ID'
)
BEGIN
    ALTER TABLE actual_premium 
    ADD Debit_ID BIGINT NULL;
    
    PRINT 'Debit_ID column added to actual_premium table';
END
ELSE
BEGIN
    PRINT 'Debit_ID column already exists in actual_premium table';
END
GO

-- Check if sub_department column exists, if not add it
IF NOT EXISTS (
    SELECT 1 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'actual_premium' 
    AND COLUMN_NAME = 'sub_department'
)
BEGIN
    ALTER TABLE actual_premium 
    ADD sub_department NVARCHAR(255) NULL;
    
    PRINT 'sub_department column added to actual_premium table';
END
ELSE
BEGIN
    PRINT 'sub_department column already exists in actual_premium table';
END
GO

-- Optional: Add indexes for better performance if these columns will be used in queries
IF NOT EXISTS (
    SELECT 1 
    FROM sys.indexes 
    WHERE name = 'IX_actual_premium_Debit_ID' 
    AND object_id = OBJECT_ID('actual_premium')
)
BEGIN
    CREATE INDEX IX_actual_premium_Debit_ID 
    ON actual_premium (Debit_ID);
    
    PRINT 'Index IX_actual_premium_Debit_ID created';
END
GO

IF NOT EXISTS (
    SELECT 1 
    FROM sys.indexes 
    WHERE name = 'IX_actual_premium_sub_department' 
    AND object_id = OBJECT_ID('actual_premium')
)
BEGIN
    CREATE INDEX IX_actual_premium_sub_department 
    ON actual_premium (sub_department);
    
    PRINT 'Index IX_actual_premium_sub_department created';
END
GO

PRINT 'Migration completed successfully';
GO
