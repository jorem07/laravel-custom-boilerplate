<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Bouncer;

class GenerateMultipleRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:request {modelName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate multiple request files [ app:request {modelName} ]';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $methods = ['Index','Store','Show', 'Update','Delete'];
        $model_name = ucfirst($this->argument('modelName'));
        $model_class = "App\\Models\\$model_name";

        if (!class_exists($model_class)) {
            $this->error("No found {$model_name} model. Please create this model first.");
        }

        // GENERATE FORM REQUEST FOR EVERY METHODS.
        foreach ($methods as $method) {
            $directory = app_path("Http/Requests/{$model_name}");
            $this->checkDirectory($directory);

            $file_path = "{$directory}/{$method}.php";

            if (!file_exists($file_path)) {
                $ability = Str::plural(Str::snake($model_name, '-')).'.'.strtolower(Str::kebab($method));
                // Create a request file
                File::put($file_path, $this->generateRequestStub($model_name,$method,$ability));
                $this->info("Request file {$file_path} generated.");

                Bouncer::role()->firstOrCreate([
                    'name' => 'super-admin',
                    'title' => 'Super Administrator',
                ]);

                Bouncer::allow('super-admin')->everything();

                Bouncer::ability()->firstOrCreate([
                    'name' => $ability,
                    'title' =>  Str::title($method == 'Index'? 'Browse '.(Str::plural(Str::snake($model_name,' '))) : Str::snake($method,' ').' '.(Str::snake($model_name,' '))),
                ]);
            } else
                $this->error("{$file_path} already exist. Skipped.");
        }

        // GENERATE SERVICE CLASS
        $directory = app_path("Repositories");
        $this->checkDirectory($directory);

        $file_path = "{$directory}/{$model_name}Repository.php";

        if (!file_exists($file_path)) {
            File::put($file_path, $this->generateRepositoryStub($model_name));
            $this->info("Repository file {$file_path}  generated.");
        } else
            $this->error("{$file_path} already exist. Skipped.");

        // GENERATE CONTROLLER CLASS
        $directory = app_path("Http/Controllers");
        $this->checkDirectory($directory);

        $controller_path = "{$directory}/{$model_name}Controller.php";

        if (!file_exists($controller_path)) {
            File::put($controller_path, $this->generateControllerStub($model_name));
            $this->info("Controller file {$controller_path}  generated.");
        } else
            $this->error("{$controller_path} already exist. Skipped.");
    }

    public function checkDirectory($directory): void
    {
        if (!File::isDirectory($directory))
            File::makeDirectory($directory, 0755, true);
    }

    protected function generateRequestStub($model,$method,$ability): string
    {
        $stub_content = File::get(base_path('stubs/request.stub'));
        $replacements = [
            'namespace' => "App\\Http\\Requests\\$model",
            'class' => ucfirst($method),
            'ability' => $ability,
            'subname' => Str::plural(Str::snake($model))
        ];

        foreach ($replacements as $key => $value) {
            $stub_content = str_replace("{{ $key }}", $value, $stub_content);
        }

        return $stub_content;
    }

    protected function generateRepositoryStub($model_name): string
    {
        $stub_content = File::get(base_path('stubs/repository.stub'));
        $replacements = [
            'namespace' => "App\\Repositories",
            'class' => ucfirst($model_name)
        ];

        foreach ($replacements as $key => $value)
            $stub_content = str_replace("{{ $key }}", $value, $stub_content);

        return $stub_content;
    }

    protected function generateControllerStub($model_name): string
    {
        $stub_content = File::get(base_path('stubs/controller.stub'));
        $replacements = [
            'namespace' => "App\\Http\\Controllers",
            'class' => ucfirst($model_name),
            'subname' => strtolower($model_name)
        ];

        foreach ($replacements as $key => $value)
            $stub_content = str_replace("{{ $key }}", $value, $stub_content);

        return $stub_content;
    }
}
