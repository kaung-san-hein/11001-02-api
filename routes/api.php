<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TeamController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::namespace('Api\V1')->group(function () {
    Route::prefix('v1')->group(function () {

        Route::post('io-register', [AuthController::class, 'register']);
        Route::post('io-login', [AuthController::class, 'login']);

        Route::middleware(['auth:user-api', 'scopes:user'])->group(function () {

            Route::get('user', [AuthController::class, 'user']);
            Route::get('logout', [AuthController::class, 'logout']);
            Route::post('io-change-password', [AuthController::class, 'changePassword']);


            Route::get('teams', [TeamController::class, 'index']);
            Route::post('teams', [TeamController::class, 'store']);
            Route::get('teams/{team}', [TeamController::class, 'show']);
            Route::put('teams/{team}', [TeamController::class, 'update']);
            Route::delete('teams/{team}', [TeamController::class, 'destroy']);
        });

        if (App::environment('local')) {
            Route::get('routes', function () {
                $routes = [];

                foreach (Route::getRoutes()->getIterator() as $route) {
                    if (strpos($route->uri, 'api') !== false) {
                        $routes[] = $route->uri;
                    }
                }

                return response()->json($routes);
            });
        }
    });
});


Route::fallback(function () {
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact 09782696857'
    ], 404);
});
