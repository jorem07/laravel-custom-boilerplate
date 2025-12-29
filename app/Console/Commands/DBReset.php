<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DBReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset {--seed : Run database seeders}';
    

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wipe the database and run migrations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Wiping database...');
        $this->call('db:wipe');

        $this->info('Running migrations...');
        $this->call('migrate');

        if ($this->option('seed')) {
            $this->info('Running seeders...');
            $this->call('db:seed');
        }

        $this->info('Database reset complete.');
    }
}
