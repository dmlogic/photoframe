<?php

use App\Iptc;
// https://github.com/google/php-photoslibrary
Route::get('/', function () {
    return view('welcome');
});

Route::prefix('oauth')->group(function () {
    Route::get('start',    'Oauth\Google@start');
    Route::get('callback', 'Oauth\Google@receiveCallback');
});