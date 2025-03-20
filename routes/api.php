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
Route::get('guest-token', [AuthController::class, 'generateGuestToken']);

Route::post('signin-apple', [AuthController::class, 'sign_in_apple']);



Route::post('update-verision', [AuthController::class, 'update_vergin']);
Route::get('/get-update', [AuthController::class, 'get_vergin']);

Route::get('/get-categories', [CategoryController::class, 'get_categories']);
Route::post('/save-category-ids-to-file', [CategoryController::class, 'saveCategoryIdsToFile']);
  

  
  Route::get('/get-sub-categories', [CategoryController::class, 'get_sub_categories']);
    
  Route::get('/get-products', [CategoryController::class, 'get_products']);
  Route::get('/is-feature-products', [CategoryController::class, 'is_feature_products']);
  
  
  Route::get('/get-top-selling-products', [CategoryController::class, 'get_top_selling_products']);

 Route::get('/get-trending-products', [CategoryController::class, 'get_trending_products']);
 
 
  Route::get('/get-ads', [CategoryController::class, 'get_ads']);
  Route::get('/get-sliders', [CategoryController::class, 'get_sliders']);
  Route::get('/get-currency', [CategoryController::class, 'get_currency']);
  Route::get('/get-tags', [CategoryController::class, 'get_tags']);
  Route::get('/update-currency-default', [CategoryController::class, 'update_currency_default']);


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

    Route::post('/add-order', [CartController::class, 'add_order']);
    Route::get('/get-my-order', [CartController::class, 'get_my_order']);
    
    Route::post('/add-payment', [CartController::class, 'add_payment']);

    Route::get('/get-coupon', [CartController::class, 'coupon']);

    Route::get('/default-address', [AuthController::class, 'is_default_address']);


    Route::get('/get-tax', [CartController::class, 'get_tax']);
    Route::get('/get-shimpent-rules', [CartController::class, 'get_shipment_rules']);

    Route::get('/delete-account', [AuthController::class, 'delete_account']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/change-password', [AuthController::class, 'change_password']);
    Route::post('/create-payment-intent-live', [CartController::class, 'payment_intent']);
    Route::post('/create-payment-intent-test', [CartController::class, 'payment_intent_test']);

    
    Route::get('/profile', [AuthController::class, 'getProfile']);

});

