<?php

use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'time' => now()->toIso8601String(),
    ]);
});

// Route::middleware('log.route')->post('/auth/login', [AuthController::class, 'login'])->name('api.login');
Route::middleware(['log.route', 'auth:sanctum'])->post('/auth/logout', [AuthController::class, 'logout'])->name('api.logout');

Route::prefix('auth')->middleware(['log.route'])->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    // Route::post('register', [AuthController::class, 'register']);
    // Route::post('register/resend', [AuthController::class, 'resend']);
    // Route::post('register/verify-otp', [AuthController::class, 'verifyOtp']);
});

Route::group(['middleware' => ["auth:sanctum", 'log.route']], function () {

# DYNAMIC ROUTING PER CONTROLLER #######################################################################################
    $controller_directory = app_path('Http/Controllers');
    $controller_files = scandir($controller_directory);
    $excluded_controllers = ['Auth','Mail', 'Dashboard', 'PDF']; // remove post name 'Controller in adding excluded controllers

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
                    Route::post($controller['slug'] . '/store', 'store')->name($controller['slug'] . '.store');
                    Route::match((['GET', 'POST']), $controller['slug'] . '/show/{' . $controller['name'] . '}', 'show')->name($controller['slug'] . '.show');
                    Route::match(['PUT', 'PATCH'], $controller['slug'] . '/{' . $controller['name'] . '}', 'update')->name($controller['slug'] . '.update');
                    Route::delete($controller['slug'] . '/delete/{' . $controller['name'] . '}', 'delete')->name($controller['slug'] . '.delete');

                    // if the model uses soft-deletes enable these routes.
                    // $modelClass = 'App\\Models\\' . $name_case;
                    // if (class_exists($modelClass) && in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses($modelClass))) {
                    //     Route::delete($controller['slug'] . '/delete/{' . $controller['name'] . '}', 'delete')->name($controller['slug'] . '.delete');
                    //     // disabled at the moment
                    //     Route::post($controller['slug'] . '/{' . $controller['name'] . '}' . '/restore', 'restore')->name($controller['slug'] . '.restore');
                    // }
                });
            }
        }
    }

    Route::post('/queues/next', [\App\Http\Controllers\QueueController::class, 'next']);
    Route::post('/queues/complete', [\App\Http\Controllers\QueueController::class, 'complete']);
    Route::post('/queues/skip', [\App\Http\Controllers\QueueController::class, 'skip']);
    Route::post('/queues/recall', [\App\Http\Controllers\QueueController::class, 'recall']);
    Route::post('/queues/transfer', [\App\Http\Controllers\QueueController::class, 'transfer']);

    Route::match(['GET', 'POST'], '/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);

    Route::get('/counters/active', [\App\Http\Controllers\CounterController::class, 'active']);
    Route::get('/counter-user-logs', [\App\Http\Controllers\CounterController::class, 'logs']);
    Route::post('/counters/login', [\App\Http\Controllers\CounterController::class, 'login']);
    Route::post('/counters/logout', [\App\Http\Controllers\CounterController::class, 'logout']);
    Route::get('/counters/performance', [\App\Http\Controllers\CounterController::class, 'performance']);

    Route::match(['POST', 'GET'], 'abilities', [\App\Http\Controllers\RoleController::class, 'getAllAbilities']);

});

Route::middleware(['guest'])->post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');

//TODO: Move it in a Controller
Route::get('/reset-password/{token}', function (string $token) {
    return response()->json(['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified successfully.']);
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

Route::match(['GET', 'POST'], '/pdf/queue', [\App\Http\Controllers\PDFController::class, 'queue']);
Route::post('/queues/store', [\App\Http\Controllers\QueueController::class, 'store']);
Route::get('/queues/show/{queues}', [\App\Http\Controllers\QueueController::class, 'show']);
Route::get('/queues/current', [\App\Http\Controllers\QueueController::class, 'current']);

Route::match(['GET', 'POST'], 'offices', [\App\Http\Controllers\OfficeController::class, 'index']);
Route::match(['GET', 'POST'], 'offices/show/{office}', [\App\Http\Controllers\OfficeController::class, 'show']);
Route::match(['GET', 'POST'], 'queues', [\App\Http\Controllers\QueueController::class, 'index']);
Route::match(['GET', 'POST'], 'office-services', [\App\Http\Controllers\OfficeServiceController::class, 'index']);
Route::match(['GET', 'POST'], 'displays', [\App\Http\Controllers\DisplayController::class, 'index']);
Route::match(['GET', 'POST'], 'displays/show/{display}', [\App\Http\Controllers\DisplayController::class, 'show']);
Route::match(['GET', 'POST'], 'announcements', [\App\Http\Controllers\AnnouncementController::class, 'index']);
Route::match(['GET', 'POST'], 'announcements/show/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'show']);
Route::match(['GET', 'POST'], 'counters', [\App\Http\Controllers\CounterController::class, 'index']);
Route::match(['GET', 'POST'], 'counters/show/{counter}', [\App\Http\Controllers\CounterController::class, 'show']);
Route::get('/counters/active', [\App\Http\Controllers\CounterController::class, 'active']);

Route::match(['POST', 'GET'],'search', function(Request $request){
    $payload = $request->validate([
        'resident_id_no'    => 'required',
        'page'              => 'nullable',
        'show'              => 'nullable'
    ]);

    $response = Http::post(env('BENEFICIARY_SEARCH_API'), $payload);

    if(!$response->json()) abort(404, 'Data not found');

    return response()->json($response->json());
})->name('api.search');