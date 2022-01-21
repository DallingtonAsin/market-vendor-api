<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShoppingOrderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CompanySettingsController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// MARKET VENDOR DASHBOARD AUTH
Route::post('/vendor/login', [UserController::class, 'authenticate']); 


Route::post('/report', [ReportController::class, 'getSystemStats']); 

// USER ACCOUNT 
Route::group(['prefix' => 'user', 'middleware' => ['auth:api-users']], function(){

    Route::post('/password/edit', [UserController::class, 'changePassword']);
    
});

// Customer Login and Registration (done)
Route::post('/customer/login', [CustomerController::class, 'customerLogin']); // done
Route::post('/customer/register', [CustomerController::class, 'register']); // done


Route::group(['middleware' => 'auth:api-users'], function(){
 
    Route::post('/user/change-account/{id}', [UserController::class, 'changeAccountStatus']);
    Route::post('/shopping-orders/change-status/{id}', [ShoppingOrderController::class, 'changeOrderStatus']);


    Route::resources([
        'users' => UserController::class,
        'roles' => RoleController::class,
        'shopping-orders' => ShoppingOrderController::class,
        'company' => CompanySettingsController::class,
        'customers' => CustomerController::class,
    ]);
});

// REPORTS
Route::group(['prefix' => 'reports', 'middleware' => ['auth:api-users']], function(){
    Route::get('/', [ReportController::class, 'index']);
    Route::get('/system-audit', [ReportController::class, 'fetchLogs']);
});
