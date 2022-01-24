<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShoppingOrderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\GoodsController;
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


Route::group(['prefix' => 'user', 'middleware' => ['auth:api-users']], function(){
    Route::post('/password/edit', [UserController::class, 'changePassword']);
});

Route::group(['prefix' => 'vendor', 'middleware' => ['auth:api-users']], function(){
    Route::get('/myorders/{id}', [ShoppingOrderController::class, 'myOrders']);
    Route::get('/myorders/pending/{id}', [ShoppingOrderController::class, 'myPendingOrders']);
    Route::get('/myorders/processed/{id}', [ShoppingOrderController::class, 'myProcessedOrders']);
});

// Customer Login and Registration (done)
Route::post('/customer/login', [CustomerController::class, 'customerLogin']); // done
Route::post('/customer/register', [CustomerController::class, 'register']); // done


// Customer Routes
Route::group(['prefix' => 'customer', 'middleware' => ['auth:api-customers']], function(){

    Route::get('/details', [CustomerController::class, 'findCustomer']); //done  
    Route::post('/password/change', [CustomerController::class, 'changePassword']); // done
    Route::put('/profile/update', [CustomerController::class, 'updateProfile']); // done
    Route::post('/change/profile-picture', [CustomerController::class, 'uploadProfilePicture']); // done
    
    Route::post('/payment/create', [PaymentController::class, 'create']); // done
    Route::post('/account/topup', [PaymentController::class, 'topupUserAccount']); //done
    Route::get('/transactions', [PaymentController::class, 'getTransactionHistory']); // done
    Route::get('/notifications', [PaymentController::class, 'getNotifications']); // done
    Route::post('/suggestion', [MailController::class, 'postCustomerSuggestion']); // done

    Route::get('/orders/{id}', [ShoppingOrderController::class, 'getOrdersDetails']);


});

Route::group(['middleware' => 'auth:api-users'], function(){
 
    Route::post('/user/change-account/{id}', [UserController::class, 'changeAccountStatus']);
    Route::post('/shopping-orders/change-status/{id}', [ShoppingOrderController::class, 'changeOrderStatus']);
   
    Route::get('/shopping-orders/pending', [ShoppingOrderController::class, 'pendingOrders']);
    Route::get('/shopping-orders/processed', [ShoppingOrderController::class, 'processedOrders']);

    Route::resources([
        'users' => UserController::class,
        'roles' => RoleController::class,
        'shopping-orders' => ShoppingOrderController::class,
        'company' => CompanySettingsController::class,
        'customers' => CustomerController::class,
        'goods' => GoodsController::class,
    ]);
});

// REPORTS
Route::group(['prefix' => 'reports', 'middleware' => ['auth:api-users']], function(){
    Route::get('/', [ReportController::class, 'index']);
    Route::get('/system-audit', [ReportController::class, 'fetchLogs']);
});


Route::group(['prefix' => 'device', 'middleware' => ['auth:api-customers']], function(){

    Route::get('/vendors', [UserController::class, 'getVendors']); 
    Route::get('/vendors/{id}', [UserController::class, 'fetchVendorDetails']); 
    Route::get('orders/customer/{id}', [ShoppingOrderController::class, 'getCustomerOrders']);

    Route::resources([
        'shopping-orders' => ShoppingOrderController::class,
        'goods' => GoodsController::class,
    ]);
    

});
