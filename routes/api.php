<?php

use App\Domains\Contact\ManageContact\Api\Controllers\ContactController;
use App\Domains\Settings\ManageUsers\Api\Controllers\UserController;
use App\Domains\Vault\ManageVault\Api\Controllers\VaultController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the bootstrap/app.php file and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    // users
    Route::get('user', [UserController::class, 'user']);
    Route::apiResource('users', UserController::class)->only(['index', 'show']);
    // vaults
    Route::apiResource('vaults', VaultController::class);
    // contacts
    Route::get('contacts/favorites', [ContactController::class, 'favorites']);
    Route::get('contacts/stats', [ContactController::class, 'stats']);
    Route::apiResource('contacts', ContactController::class)->only(['index', 'show']);
    Route::post('contacts/{contact}/favorite', [ContactController::class, 'markFavorite']);
    Route::delete('contacts/{contact}/favorite', [ContactController::class, 'removeFavorite']);
    Route::patch('contacts/{contact}/favorite', [ContactController::class, 'toggleFavorite']);
    Route::put('contacts/{contact}/note', [ContactController::class, 'updateNote']);
});
