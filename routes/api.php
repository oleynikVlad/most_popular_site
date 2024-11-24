<?php

use App\Http\API\CategoryController;
use App\Http\API\TagController;
use App\Http\API\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'middleware'=>'api'], function () {
    Route::get('/test', function (){
       return 'test';
    })->name('test');
    //TODO refactor  controllers
    Route::apiResource('users', UserController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('tags', TagController::class);
    Route::apiResource('posts', PostController::class);

});
