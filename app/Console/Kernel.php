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
    ];

    
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('sync:account-mappings')
                 ->dailyAt('02:00')
                 ->timezone('Africa/Nairobi')
                 ->onSuccess(function () {
                     \Log::info('Account mappings sync completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('Account mappings sync failed');
                 });

        $schedule->command('sync:info')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/sync-info.log'))
                 ->onSuccess(function () {
                     \Log::info('Info sync completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('Info sync failed');
                 });
                 
        $schedule->command('sync:info --force')
                 ->dailyAt('03:00')
                 ->appendOutputTo(storage_path('logs/sync-info-full.log'))
                 ->onSuccess(function () {
                     \Log::info('Full info sync completed successfully');
                 });

        $schedule->command('data:sync --auto')
                 ->dailyAt('01:00') 
                 ->timezone('Africa/Nairobi')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/data-sync.log'))
                 ->onSuccess(function () {
                     \Log::info('Daily data sync completed successfully');
                 })
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
    }

    
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
