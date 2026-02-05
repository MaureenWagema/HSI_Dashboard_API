# Data Sync Automation Script
# Path to your Laravel project
$projectPath = "C:\Users\SoftClansUser\Documents\Task\HSI_Dashboard_Admin\HsiDashboard\HsiDashboard"

# Log file path
$logPath = "$projectPath\storage\logs\automation-sync.log"

function Write-Log {
    param([string]$message)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    "$timestamp - $message" | Out-File -FilePath $logPath -Append
    Write-Host $message
}

try {
    Write-Log "Starting automated data sync"
    
    # Change to project directory
    Set-Location $projectPath
    
    # Run the sync command
    $result = php artisan data:sync --auto 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        Write-Log "Sync completed successfully"
        Write-Log "Output: $result"
    } else {
        Write-Log "Sync failed with exit code $LASTEXITCODE"
        Write-Log "Error: $result"
        exit 1
    }
    
} catch {
    Write-Log "Exception occurred: $($_.Exception.Message)"
    exit 1
}

Write-Log "Data sync automation completed"
