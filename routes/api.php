<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('auth', [ AuthController::class, 'login' ]);


Route::middleware('auth.custom')->group(function() {
    
    // Vento Credit
    Route::post('sku', 'App\Http\Controllers\CreditController@motorcycleSku');
    // Route::get('discovery', 'App\Http\Controllers\CreditController@motorcycleCatalogueInfo');
    
    // Vento mailing
    Route::post('mailing-users', 'App\Http\Controllers\MailerController@getUser');
    
    
    // Vento Discovery
    Route::prefix('discovery')->group(function () {
        Route::get('', [DiscoveryController::class, 'index']);
        Route::post('store', [DiscoveryController::class, 'store']);
        
        Route::get('motorcycle/index', [DiscoveryController::class, 'getDiscoveryMotorcycles']);
        Route::put('change-active', [DiscoveryController::class, 'onChageActive']);
        Route::put('motorcycle', [DiscoveryController::class, 'update']);
        Route::post('motorcycle/upload', [DiscoveryController::class, 'upload']);
        Route::get('skus', [DiscoveryController::class, 'getSkusWoocomerce']);
    });
});

Route::get('discovery/file/{filename}', [DiscoveryController::class, 'getImage']);


// META Vento
Route::get('meta/catalogue-motorcycles', 'App\Http\Controllers\MetaController@motorcycleCatalogue');

