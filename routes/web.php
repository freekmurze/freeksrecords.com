<?php

use App\Http\Controllers\CollectionShareImageController;
use App\Http\Controllers\RecordDetailsController;
use App\Http\Controllers\RecordShareImageController;
use App\Http\Controllers\ShowCollectionController;
use App\Http\Controllers\ShowRecordController;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowCollectionController::class)->name('home');

Route::get('api/records/{instanceId}', RecordDetailsController::class)
    ->whereNumber('instanceId')
    ->name('recordDetails');

Route::middleware('throttle:shareImages')->group(function () {
    Route::get('share/image.png', CollectionShareImageController::class)
        ->name('collectionShareImage');

    Route::get('record/{instanceId}/image.png', RecordShareImageController::class)
        ->whereNumber('instanceId')
        ->name('recordShareImage');
});

Route::get('record/{instanceId}/{slug?}', ShowRecordController::class)
    ->whereNumber('instanceId')
    ->name('recordShare');
