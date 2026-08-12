<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Bouncer::role()->firstOrCreate([
            'name' => 'admin',
        ], [
            'title' => 'System Administrator',
        ]);

        Bouncer::role()->firstOrCreate([
            'name' => 'officer',
        ], [
            'title' => 'Service Officer',
        ]);

        Bouncer::allow('admin')->everything();
    }
}
