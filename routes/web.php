<?php

use App\Http\Controllers\CollectionShareImageController;
use App\Http\Controllers\RecordDetailsController;
use App\Http\Controllers\RecordShareImageController;
use App\Http\Controllers\ShowCollectionController;
use App\Http\Controllers\ShowRecordController;
use App\Http\Middleware\CacheAtEdge;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:shareImages')->group(function () {
    Route::get('share/image.png', CollectionShareImageController::class)
        ->name('collectionShareImage');

    Route::get('record/{instanceId}/image.png', RecordShareImageController::class)
        ->whereNumber('instanceId')
        ->name('recordShareImage');
});

Route::middleware(CacheAtEdge::class)->group(function () {
    Route::get('/', ShowCollectionController::class)->name('home');

    Route::get('api/records/{instanceId}', RecordDetailsController::class)
        ->whereNumber('instanceId')
        ->name('recordDetails');

    Route::get('record/{instanceId}/{slug?}', ShowRecordController::class)
        ->whereNumber('instanceId')
        ->name('recordShare');
});
