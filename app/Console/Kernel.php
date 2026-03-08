<?php

namespace App\Console;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command as IlluminateCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected function getArtisan()
    {
        if (is_null($this->artisan)) {
            $this->artisan = (new Artisan($this->app, $this->events, $this->app->version()))
                ->resolveCommands($this->commands)
                ->setContainerCommandLoader();

            $applicationReflection = new \ReflectionClass($this->artisan);
            if ($applicationReflection->hasProperty('commandMap')) {
                $commandMapProperty = $applicationReflection->getProperty('commandMap');
                $commandMapProperty->setAccessible(true);
                $commandMap = $commandMapProperty->getValue($this->artisan);

                foreach (array_unique(array_values($commandMap)) as $commandClass) {
                    try {
                        $command = $this->app->make($commandClass);

                        if ($command instanceof IlluminateCommand) {
                            $command->setLaravel($this->app);
                        }

                        $this->artisan->add($command);
                    } catch (\Throwable $e) {
                    }
                }

                if ($applicationReflection->hasProperty('commandLoader')) {
                    $loaderProperty = $applicationReflection->getProperty('commandLoader');
                    $loaderProperty->setAccessible(true);
                    $loaderProperty->setValue($this->artisan, null);
                }
            }

            if ($this->symfonyDispatcher instanceof EventDispatcher) {
                $this->artisan->setDispatcher($this->symfonyDispatcher);
                $this->artisan->setSignalsToDispatchEvent();
            }
        }

        return $this->artisan;
    }
}