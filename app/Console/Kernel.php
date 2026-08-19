<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\RenewLeaveBalanceMonthly;
use App\Jobs\RenewLeaveBalanceYearly;
use App\Jobs\ClearExpiredLeave;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
	   'App\Console\Commands\GenerateAttendances',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
		$schedule->command('route:call ' . route('report.cronGenerateAttendances'))->dailyAt('1:00')->withoutOverlapping();
        // $schedule->command('route:call ' . route('leave.cronRenewLeaveBalanceMonthly'))->monthlyOn(1, '01:00')->withoutOverlapping();
        // $schedule->command('route:call ' . route('leave.cronRenewLeaveBalanceYearly'))->yearlyOn(1, 1, '01:00')->withoutOverlapping();
        // $schedule->command('route:call ' . route('leave.cronClearExpiredLeave'))->monthlyOn(1, '01:00')->withoutOverlapping();
        $schedule->job(new RenewLeaveBalanceMonthly)->monthlyOn(1, '01:00')->withoutOverlapping();
        $schedule->job(new RenewLeaveBalanceYearly)->yearlyOn(1, 1, '01:00')->withoutOverlapping();
        $schedule->job(new ClearExpiredLeave)->dailyAt('1:00')->withoutOverlapping();
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
       // $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
