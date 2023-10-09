<?php
use Illuminate\Support\Facades\Route;

Route::get('/test1', function () {
    return 'ok';
});
Route::middleware('check.auth:web')->group(function () {
    Route::middleware('permission:web')->group(function () {
        Route::get('/test', function () {
            return 'ok';
        });
    });

});