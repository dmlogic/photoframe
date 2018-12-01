<?php

use App\Iptc;
// https://github.com/google/php-photoslibrary
Route::get('/', function () {
    return view('welcome');
});

Route::get('/scratch', function () {
    $file = storage_path('shrimp.jpg');
    dump($file);
    $cmd = sprintf('exiftool %s -Description="%s"',$file,'PHP generated description');
    dump(exec($cmd));
});
Route::get('/test', 'Oauth\Google@test');

Route::prefix('oauth')->group(function () {
    Route::get('start',    'Oauth\Google@start');
    Route::get('callback', 'Oauth\Google@receiveCallback');
});