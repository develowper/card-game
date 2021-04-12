<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    protected function scheduleTimezone()
    {
        return 'Asia/Tehran';
    }

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            DB::table('divar')->delete();
        })->name('update_divar')->withoutOverlapping()->everyMinute()/*->emailOutputOnFailure('moj2raj2@gmail.com')->onFailure(function () {
            // The task failed...
        });*/
        ;
//        $schedule->command('emails:send Taylor --force')->daily();

        //The runInBackground , emailOutputOnFailure  methods may only be used when scheduling tasks via the command and exec methods.
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
