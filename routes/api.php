<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes with rate limiting
Route::prefix('auth')->middleware(['throttle:10,1'])->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::post('verify-email', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('resend-verification', [AuthController::class, 'resendVerification'])->name('verification.resend');
});

// Public sports routes (read-only)
Route::prefix('sports')->group(function () {
    Route::get('/', [SportController::class, 'index'])->name('sports.index');
    Route::get('/active', [SportController::class, 'active'])->name('sports.active');
    Route::get('/{sport}', [SportController::class, 'show'])->name('sports.show');
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('logout-all', [AuthController::class, 'logoutFromAllDevices'])->name('logout.all');
        Route::get('me', [AuthController::class, 'me'])->name('user.profile');
        Route::put('update-profile', [AuthController::class, 'updateProfile'])->name('user.update');
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('password.change');
        Route::post('deactivate-account', [AuthController::class, 'deactivateAccount'])->name('user.deactivate');
    });
    
    // Sports management routes (admin/trainer only)
    Route::prefix('admin/sports')->group(function () {
        Route::post('/', [SportController::class, 'store'])->name('admin.sports.store');
        Route::put('/{sport}', [SportController::class, 'update'])->name('admin.sports.update');
        Route::delete('/{sport}', [SportController::class, 'destroy'])->name('admin.sports.destroy');
        Route::post('/{sport}/toggle-status', [SportController::class, 'toggleStatus'])->name('admin.sports.toggle');
    });
    
    // Get authenticated user
    Route::get('/user', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $request->user()
            ]
        ]);
    })->name('user.current');
});
