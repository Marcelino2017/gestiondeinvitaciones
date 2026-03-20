<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::get('/invitations/{token}', [InvitationController::class, 'showByToken']);
Route::post('/invitations/{token}/accept', [InvitationController::class, 'acceptByToken']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index'])
        ->middleware(['role:admin', 'permission:users.show']);

    Route::post('/users', [UserController::class, 'store'])
        ->middleware(['role:admin', 'permission:users.create']);


    Route::group(['prefix' => 'organizations'], function () {
        Route::get('/', [OrganizationController::class, 'index'])->middleware('permission:organizations.show');
        Route::post('/', [OrganizationController::class, 'store'])->middleware('permission:organizations.create');
        Route::get('/{organization}', [OrganizationController::class, 'show'])->middleware('permission:organizations.show');
        Route::put('/{organization}', [OrganizationController::class, 'update'])->middleware('permission:organizations.update');
        Route::delete('/{organization}', [OrganizationController::class, 'destroy'])->middleware('permission:organizations.delete');
        Route::get('/{organization}/users', [OrganizationController::class, 'users'])->middleware('permission:users.show');
        Route::post('/{organization}/users', [OrganizationController::class, 'createUser'])->middleware('permission:users.create');
        Route::patch('/{organization}/users/{user}', [OrganizationController::class, 'updateUserRole'])->middleware('permission:users.update');
        Route::delete('/{organization}/users/{user}', [OrganizationController::class, 'removeUser'])->middleware('permission:users.delete');
    });

    Route::post('/organizations/{organization}/invitations', [InvitationController::class, 'store'])
        ->middleware('permission:invitations.create');
});
