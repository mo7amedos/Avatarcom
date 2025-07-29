<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;
use VigStudio\VigAutoTranslations\Http\Controllers\VigAutoTranslationsController;

Route::group([
    'namespace' => 'VigStudio\VigAutoTranslations\Http\Controllers',
    'middleware' => ['web', 'core']
], function () {
    Route::group([
        'prefix' => BaseHelper::getAdminPrefix() . '/vig-auto-translations',
        'middleware' => 'auth',
        'permission' => 'vig-auto-translations.index',
        'as' => 'vig-auto-translations.',
    ], function () {

        Route::group(['prefix' => 'settings'], function () {
            Route::get('', [
                'as' => 'settings',
                'uses' => 'SettingsController@settings',
            ]);

            Route::put('', [
                'as' => 'settings.update',
                'uses' => 'SettingsController@update',
            ]);

        });


        Route::group(['prefix' => 'theme'], function () {
            Route::get('/', [
                'as' => 'theme',
                'uses' => 'VigAutoTranslationsController@getThemeTranslations',
            ]);

            Route::post('/', [
                'as' => 'theme.post',
                'uses' => 'VigAutoTranslationsController@postThemeTranslations',
            ]);

            Route::post('all', [
                'as' => 'theme.post-all',
                'uses' => 'VigAutoTranslationsController@postThemeAllTranslations',
            ]);
        });

        Route::group(['prefix' => 'plugin'], function () {
            Route::get('', [
                'as' => 'plugin',
                'uses' => 'VigAutoTranslationsController@getPluginsTranslations',
            ]);

            Route::post('', [
                'as' => 'plugin.post',
                'uses' => 'VigAutoTranslationsController@postPluginsTranslations',
            ]);

            Route::post('all', [
                'as' => 'plugin.all',
                'uses' => 'VigAutoTranslationsController@postAllPluginsTranslations',
            ]);
        });

        Route::get('auto-translate', [
            'as' => 'auto-translate',
            'uses' => 'VigAutoTranslationsController@getAutoTranslate',
        ]);
    });
});
