<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\VouchersController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

$rate_limit = config('api.api_limit');

//Public Routes - client passport needed
Route::group(['middleware' => 'client'], function () {
    Route::post('send-email', [AuthController::class, 'sendEmail']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

//Protected Routes - require authentication
Route::group(['namespace' => 'App\Http\Controllers\API', 'middleware' => ['auth:api']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('generate-vouchers', [VouchersController::class, 'generateVouchers']);
    Route::resources([
        'users'     => UsersController::class,
        'vouchers'  => VouchersController::class,
    ]);
});
