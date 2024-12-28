<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Category\CategoryController;
use App\Http\Controllers\Api\Category\WishlistController;
use App\Http\Controllers\Api\Cart\CartController;




Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('send-email-otp', [AuthController::class, 'send_email_otp']);
Route::post('reset-password-email', [AuthController::class, 'reset_password_with_email']);
Route::post('login-social', [AuthController::class, 'login_social']);



  Route::get('/get-categories', [CategoryController::class, 'get_categories']);
  
  Route::get('/get-sub-categories', [CategoryController::class, 'get_sub_categories']);
    
  Route::get('/get-products', [CategoryController::class, 'get_products']);
  
  Route::get('/get-top-selling-products', [CategoryController::class, 'get_top_selling_products']);

 Route::get('/get-trending-products', [CategoryController::class, 'get_trending_products']);
 
 
  Route::get('/get-ads', [CategoryController::class, 'get_ads']);
  Route::get('/get-sliders', [CategoryController::class, 'get_sliders']);
  Route::get('/get-currency', [CategoryController::class, 'get_currency']);
  Route::get('/get-tags', [CategoryController::class, 'get_tags']);


Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/get-profile', [AuthController::class, 'get_profile']);
    Route::post('/update-profile', [AuthController::class, 'update_profile']);

    Route::get('/get-address', [AuthController::class, 'get_address']);

    Route::post('/add-address', [AuthController::class, 'add_address']);

    Route::get('/delete-address', [AuthController::class, 'delete_address']);

    Route::post('/add-wishlist', [WishlistController::class, 'add_wishlist']);
    
    Route::get('/get-wishlist', [WishlistController::class, 'get_wishlist']);
    
    Route::get('/delete-wishlist', [WishlistController::class, 'delete_wishlist']);


    Route::get('/add-cart', [CartController::class, 'add_cart']);
    Route::get('/update-cart', [CartController::class, 'update_cart']);
    Route::get('/get-my-cart', [CartController::class, 'get_my_cart']);
    Route::get('/delete-my-cart', [CartController::class, 'delete_my_cart']);

    Route::get('/get-my-order', [CartController::class, 'get_my_order']);
    Route::get('/delete-account', [AuthController::class, 'delete_account']);
    Route::get('/logout', [AuthController::class, 'logout']);

    
    Route::get('/profile', [AuthController::class, 'getProfile']);

});

