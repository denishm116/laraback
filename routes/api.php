<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => '/auth', ['middleware' => ['throttle:20,5']]], function (){
    Route::post('/register', 'api\auth\RegisterController@register');
    Route::post('/login', 'api\auth\LoginController@login');
    Route::post('/logout', 'api\auth\LoginController@logout');
    Route::get('/login/{service}', 'api\auth\SocialLoginController@redirect');
    Route::get('/login/{service}/callback', 'api\auth\SocialLoginController@callback');

});

Route::group(['middleware' => ['jwt.auth']], function (){
    Route::get('/home', 'HomeController@testapi');
});
Route::get('/test', 'HomeController@testapi2');
