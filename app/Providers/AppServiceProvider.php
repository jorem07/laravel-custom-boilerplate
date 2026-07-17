<?php

namespace App\Providers;

use App\Models\User;
use App\Services\SecurityService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $interfacePath = app_path('Repositories/Contracts');

        if (!File::exists($interfacePath)) {
            return;
        }

        foreach (File::files($interfacePath) as $file) {
            
            $interfaceName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            
            if (!Str::endsWith($interfaceName, 'RepositoryInterface')) {
                continue;
            }
            
            $baseName = Str::replaceLast('RepositoryInterface', '', $interfaceName);

            $interface = "App\\Repositories\\Contracts\\{$interfaceName}";
            $repository = "App\\Repositories\\{$baseName}Repository";
            
            if (class_exists($repository)) {
                $this->app->bind($interface, $repository);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {   
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            $endpoint = explode('api/', $url);
            $new_endpoint = $endpoint[1];
            $new_array = explode('/',$new_endpoint);

            $new_url = '/verify/?i='.$new_array[2].'&&t='.$new_array[3];

            return (new MailMessage)

                ->subject('Verify Email Address')

                ->line('Click the button below to verify your email address.')

                ->action('Verify Email Address', env('VUE_URL') . $new_url);

        });


        ResetPassword::createUrlUsing(function (User $user, string $token) {
            $security = new SecurityService();
            $hash = $security->encrypt($user->email);
            return env('VUE_URL')."/forgot-password/reset?t=$token&&k=$hash";
        });
    }
}
