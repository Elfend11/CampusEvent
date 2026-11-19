<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'service' => 'CampusEvent API',
        'version' => 'v1',
        'time' => now()->toIso8601String(),
    ]));

    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/user', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);

        Route::middleware('role:SUPER_ADMIN')->group(function () {
            Route::put('/users/{user}/roles', [UserController::class, 'updateRoles']);
            Route::apiResource('users', UserController::class);
        });

        Route::middleware('role:SUPER_ADMIN,ORGANIZATION_ADMIN')->group(function () {
            Route::get('/organizations', [OrganizationController::class, 'index']);
            Route::post('/organizations', [OrganizationController::class, 'store']);
            Route::get('/organizations/{organization}', [OrganizationController::class, 'show']);
            Route::put('/organizations/{organization}', [OrganizationController::class, 'update']);
            Route::get('/organizations/{organization}/members', [OrganizationController::class, 'members']);
            Route::post('/organizations/{organization}/members', [OrganizationController::class, 'addMember']);
            Route::put('/organizations/{organization}/members/{user}', [OrganizationController::class, 'updateMember']);
            Route::delete('/organizations/{organization}/members/{user}', [OrganizationController::class, 'removeMember']);
        });

        Route::middleware('role:SUPER_ADMIN,ORGANIZATION_ADMIN')->group(function () {
            Route::post('/events', [EventController::class, 'store']);
            Route::put('/events/{event}', [EventController::class, 'update']);
            Route::patch('/events/{event}/status', [EventController::class, 'updateStatus']);
            Route::get('/events/{event}/officers', [EventController::class, 'officers']);
            Route::post('/events/{event}/officers', [EventController::class, 'assignOfficer']);
            Route::delete('/events/{event}/officers/{user}', [EventController::class, 'removeOfficer']);
        });
    });
});
