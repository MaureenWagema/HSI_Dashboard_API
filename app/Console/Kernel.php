<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\SyncAccountMappings::class,
        Commands\SyncDataCommand::class,
        Commands\SyncActualPremiums::class,
        Commands\SyncTatData::class,
        Commands\RefreshPowerBITokens::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // TAT Data Sync - Daily at 2:30 AM
        $schedule->command('sync:tat-data')
                 ->dailyAt('02:30')
                 ->timezone('Africa/Nairobi')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/tat-data.log'))
                 ->onFailure(function () {
                     \Log::error('TAT data sync failed');
                 });

        $schedule->command('sync:actual-premiums')
                 ->dailyAt('03:00')
                 ->timezone('Africa/Nairobi')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/actual-premiums.log'))
                 ->onFailure(function () {
                     \Log::error('Actual premiums sync failed');
                 });

        // Account Mappings Sync - Daily at 2:00 AM
        $schedule->command('sync:account-mappings')
                 ->dailyAt('02:00')
                 ->timezone('Africa/Nairobi')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/account-mappings.log'))
                 ->onFailure(function () {
                     \Log::error('Account mappings sync failed');
                 });

        // Current Month Data Sync - Daily at 1:00 AM + Every 6 hours
        $schedule->command('data:sync --auto')
                 ->dailyAt('01:00')
                 ->timezone('Africa/Nairobi')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/data-sync.log'))
                 ->onFailure(function () {
                     \Log::error('Daily data sync failed');
                 });

        $schedule->command('data:sync --auto')
                 ->everySixHours()
                 ->withoutOverlapping()
                 ->when(function () {
                     $hour = now()->hour;
                     return $hour >= 6 && $hour <= 22;
                 })
                 ->appendOutputTo(storage_path('logs/data-sync-frequent.log'));

        // Power BI Token Refresh - Every 30 minutes during business hours
        $schedule->command('powerbi:refresh-tokens')
                 ->everyThirtyMinutes()
                 ->timezone('Africa/Nairobi')
                 ->withoutOverlapping()
                 ->when(function () {
                     $hour = now()->hour;
                     return $hour >= 6 && $hour <= 22; // Business hours 6AM - 10PM
                 })
                 ->appendOutputTo(storage_path('logs/powerbi-token-refresh.log'))
                 ->onSuccess(function () {
                     \Log::info('Power BI token refresh completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('Power BI token refresh failed');
                 });

        // Power BI Token Refresh - Force refresh at midnight
        $schedule->command('powerbi:refresh-tokens --force')
                 ->dailyAt('00:05')
                 ->timezone('Africa/Nairobi')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/powerbi-token-refresh.log'))
                 ->description('Daily forced refresh of Power BI tokens');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
