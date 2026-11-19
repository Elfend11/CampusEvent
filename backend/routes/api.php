<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'service' => 'CampusEvent API',
        'version' => 'v1',
        'time' => now()->toIso8601String(),
    ]));
});
