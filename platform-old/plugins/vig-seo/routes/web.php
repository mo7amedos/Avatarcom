<?php

Route::group(['namespace' => 'VigStudio\VigSeo\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'vig-seos', 'as' => 'vig-seo.'], function () {
            Route::resource('', 'VigSeoController')->parameters(['' => 'vig-seo']);
            Route::delete('items/destroy', [
                'as' => 'deletes',
                'uses' => 'VigSeoController@deletes',
                'permission' => 'vig-seo.destroy',
            ]);
        });
    });
});
