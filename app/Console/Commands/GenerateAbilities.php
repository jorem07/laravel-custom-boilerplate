<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Console\Command;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GenerateAbilities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:abilities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "generate abilities for controller's methods.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $super_admin = Bouncer::role()->firstOrCreate([
            'name' => 'super-admin',
            'title' => 'Super Administrator',
        ]);

        $super_admin->wasRecentlyCreated? $this->info('Role super-admin created.') : $this->error('Role super-admin existed. Skipped.');

        Bouncer::allow('super-admin')->everything();

        # Added for view temp first
        Permission::updateOrCreate([
            'ability_id' => 1,
            'entity_id' => $super_admin->id,
            'entity_type' => 'App\Models\Role'
        ]);

        User::updateOrCreate([
            'email' =>env('FIRST_USER_EMAIL')
        ], [
            'first_name' => env('FIRST_USER_FIRST_NAME'),
            'middle_name' => env('FIRST_USER_MIDDLE_NAME'),
            'last_name' => env('FIRST_USER_LAST_NAME'),
            'email' =>env('FIRST_USER_EMAIL'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(99),
            'password' => Hash::make(env('FIRST_USER_PASSWORD')),
            'status' => true,
            'allow_login' => true
        ]);

        Bouncer::assign('super-admin')->to(User::where('email', env('FIRST_USER_EMAIL'))->first());

        $methods = ['Index','Store','Show','Edit','Update','Destroy','ForceDelete'];
        $controllerDirectory = app_path('Http/Controllers');
        $controllerFiles = scandir($controllerDirectory);
        $excluded =['Auth','Attachment','Pdf','Assessment', 'ExportExcel', 'Dashboard', 'Log', 'Mail'];

        foreach ($controllerFiles as $controllerFile) {
            if (is_file($controllerDirectory.'/'.$controllerFile)) {
                // Remove the ".php" extension and "Controller" postfix
                $controllerName = pathinfo($controllerFile, PATHINFO_FILENAME);
                $name_case = str_replace('Controller', '', $controllerName);

                if ($name_case != "" && !in_array($name_case,$excluded)) {
                    $model_name = ucfirst($name_case);

                    foreach ($methods as $method) {
                        $this->createAbilities($method, $model_name);
                    }
                }
            }
        }

        // CUSTOM ABILITIES
        $this->createAbilities('Approve','assessments');
        $this->createAbilities('Current','abilities');
        $this->createAbilities('arrayUpload','attachments');
        $this->createAbilities('Upload', 'attachments');
        // $this->createAbilities('GetDate', 'export_excels');
        // $this->createAbilities('ExportBeneficiary', 'export_excels');
        $this->createAbilities('SignatoryPassword', 'users');

    }

    public function createAbilities($method, $model_name): void
    {
        $ability = Str::plural(Str::snake($model_name, '-')).'.'.strtolower(Str::kebab($method));

        $created = Bouncer::ability()->firstOrCreate([
            'name' => $ability,
            'title' =>  Str::title($method == 'Index'? 'Browse '.(Str::plural(Str::snake($model_name,'-'))) : Str::snake($method,' ').' '.(Str::snake($model_name,'-'))),
        ]);

        if (!$created->wasRecentlyCreated) {
            $this->error('Ability '.$created->name.' already existed. Skipped.');
        } else
            $this->info('Ability '.$created->name.' created.');
    }
}
