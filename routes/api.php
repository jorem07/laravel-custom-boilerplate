<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Route::middleware('log.route')->post('/auth/login', [AuthController::class, 'login'])->name('api.login');
// Route::middleware('log.route')->post('/auth/register', [AuthController::class, 'register'])->name('api.register');

Route::prefix('auth')->middleware(['log.route'])->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('register', [AuthController::class, 'register']);
});

Route::group(['middleware' => ["auth:sanctum", 'log.route']], function () {

# DYNAMIC ROUTING PER CONTROLLER #######################################################################################
    $controller_directory = app_path('Http/Controllers');
    $controller_files = scandir($controller_directory);
    $excluded_controllers = ['Auth','Mail', 'Dashboard']; // remove post name 'Controller in adding excluded controllers

    foreach ($controller_files as $controller_file) {
        if (is_file($controller_directory . '/' . $controller_file)) {
            // Remove the ".php" extension and "Controller" postfix
            $controller_name = pathinfo($controller_file, PATHINFO_FILENAME);
            $name_case = str_replace('Controller', '', $controller_name);

            if ($name_case != "" && !in_array($name_case, $excluded_controllers)) {
                // Transform the name into your desired format
                $name = Str::plural(Str::snake($name_case));
                $slug = Str::plural(Str::snake($name_case, '-'));

                $controller = [
                    'controller' => 'App\\Http\\Controllers\\' . $controller_name,
                    'slug' => $slug,
                    'name' => $name
                ];

                Route::controller(app($controller['controller'])::class)->group(function () use ($controller, $name_case) {

                    Route::match((['GET', 'POST']), $controller['slug'], 'index')->name($controller['slug'] . '.index');
                    Route::post($controller['slug'] . '/create', 'create')->name($controller['slug'] . '.create');
                    Route::match((['GET', 'POST']), $controller['slug'] . '/edit' . '/{' . $controller['name'] . '}', 'edit')->name($controller['slug'] . '.edit');
                    Route::post($controller['slug'] . '/store', 'store')->name($controller['slug'] . '.store');
                    Route::match((['GET', 'POST']), $controller['slug'] . '/show/{' . $controller['name'] . '}', 'show')->name($controller['slug'] . '.show');
                    Route::match(['PUT', 'PATCH'], $controller['slug'] . '/{' . $controller['name'] . '}', 'update')->name($controller['slug'] . '.update');
                    Route::delete($controller['slug'] . '/force-delete/{' . $controller['name'] . '}', 'forceDelete')->name($controller['slug'] . '.force-delete');

                    // if the model uses soft-deletes enable these routes.
                    $modelClass = 'App\\Models\\' . $name_case;
                    if (class_exists($modelClass) && in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses($modelClass))) {
                        Route::delete($controller['slug'] . '/delete/{' . $controller['name'] . '}', 'destroy')->name($controller['slug'] . '.destroy');
                        // disabled at the moment
                        Route::post($controller['slug'] . '/{' . $controller['name'] . '}' . '/restore', 'restore')->name($controller['slug'] . '.restore');
                    }
                });
            }
        }
    }

});
