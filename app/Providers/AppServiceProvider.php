<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        date_default_timezone_set(Config::get('app.timezone', 'Asia/Riyadh'));

        Response::macro('validation', function ($data, $message, $code = 422) {
            $response = array();
            $response["success"] = false;
            $response["data"] = (object)[];
            $response["error"] = $data;
            $response["message"] = $message;
            return response()->json($response, $code, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        });
        
             Response::macro('success', function ($data, $message, $code = 200) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data,  // احتفظ بها كمصفوفة مباشرة
            ], $code, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        });


        Response::macro('error', function ($errors, $message, $code = 200) {
            $response = array();
            $response["success"] = false;
            $response["data"] = (object)[];
            $response["error"] = empty($errors) ? array("missing_data" => [__("No Data Found")]) : $errors;
            $response["message"] = $message;
            return response()->json($response, $code, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        });




    }
}
