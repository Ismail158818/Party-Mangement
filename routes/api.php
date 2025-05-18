<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\CommentApiController;
use App\Http\Controllers\Api\PayPalApiController;
use App\Http\Middleware\CheckAdmin;
use Illuminate\Support\Facades\Route;

Route::controller(AuthApiController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware(['auth:sanctum','api.rate.limit'])->group(function () {
    // Event Routes
    Route::controller(EventApiController::class)->group(function () {
        Route::post('index', 'index');
        Route::post('store', 'store')->middleware('check.admin');
        Route::post('showevent', 'showevent');
        Route::post('destroyEvent', 'destroy')->middleware('check.admin');
    });

    // User Routes
    Route::controller(UserApiController::class)->group(function () {
        Route::post('toggleJoin', 'toggleJoin');
        Route::post('showuser', 'showuser');
        Route::post('updateUsers', 'update_user');
        Route::post('destroyUser', 'destroy_user');
        Route::post('searchUsers', 'search');
        Route::post('users_event', 'users_event');
    });

    // Comment Routes
    Route::controller(CommentApiController::class)->group(function () {
        Route::post('storeComment', 'store');
        Route::post('indexComments', 'index');
        Route::post('destroyComment', 'destroy');
        Route::post('updateComment', 'update');
    });

    // PayPal Routes
  
    
});

Route::controller(PayPalApiController::class)->group(function () {
    Route::post('/payment',  'payment');
    Route::get('/payment/capture',  'capturePayment');
    Route::get('/index/invoice',  'index_invoice');
    Route::get('/payment/success', 'success')->name('paypal.success');
    Route::get('/payment/cancel', 'cancel')->name('paypal.cancel');
});