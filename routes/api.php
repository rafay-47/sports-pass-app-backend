<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\SportServiceController;
use App\Http\Controllers\TierController;

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
    Route::get('/with-available-tiers', [SportController::class, 'withAvailableTiers'])->name('sports.with-available-tiers');
    Route::get('/{sport}', [SportController::class, 'show'])->name('sports.show');
    
    // Public sport services routes (read-only)
    Route::get('/{sport}/services', [SportServiceController::class, 'getBySport'])->name('sports.services');
    Route::get('/{sport}/services/prices', [SportServiceController::class, 'getServicePricesBySport'])->name('sports.services.prices');
    
    // Public tier routes (read-only)
    Route::get('/{sport}/tiers', [TierController::class, 'getBySport'])->name('sports.tiers');
    Route::get('/{sport}/tiers/available', [TierController::class, 'getAvailableBySport'])->name('sports.tiers.available');
});

// Public sport services routes (read-only)
Route::prefix('sport-services')->group(function () {
    Route::get('/', [SportServiceController::class, 'index'])->name('sport-services.index');
    Route::get('/{sportService}', [SportServiceController::class, 'show'])->name('sport-services.show');
});

// Public tier routes (read-only)
Route::prefix('tiers')->group(function () {
    Route::get('/', [TierController::class, 'index'])->name('tiers.index');
    Route::get('/{tier}', [TierController::class, 'show'])->name('tiers.show');
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
    
    // Sports management routes (admin only)
    Route::prefix('admin/sports')->middleware('role:admin')->group(function () {
        Route::post('/', [SportController::class, 'store'])->name('admin.sports.store');
        Route::put('/{sport}', [SportController::class, 'update'])->name('admin.sports.update');
        Route::delete('/{sport}', [SportController::class, 'destroy'])->name('admin.sports.destroy');
        Route::post('/{sport}/toggle-status', [SportController::class, 'toggleStatus'])->name('admin.sports.toggle');
    });
    
    // Sport Services management routes (admin only)
    Route::prefix('admin/sport-services')->middleware('role:admin')->group(function () {
        Route::get('/', [SportServiceController::class, 'index'])->name('admin.sport-services.index');
        Route::post('/', [SportServiceController::class, 'store'])->name('admin.sport-services.store');
        Route::get('/{sportService}', [SportServiceController::class, 'show'])->name('admin.sport-services.show');
        Route::put('/{sportService}', [SportServiceController::class, 'update'])->name('admin.sport-services.update');
        Route::delete('/{sportService}', [SportServiceController::class, 'destroy'])->name('admin.sport-services.destroy');
        Route::post('/{sportService}/toggle-status', [SportServiceController::class, 'toggleStatus'])->name('admin.sport-services.toggle');
    });
    
    // Tier management routes (admin only)
    Route::prefix('admin/tiers')->middleware('role:admin')->group(function () {
        Route::get('/', [TierController::class, 'index'])->name('admin.tiers.index');
        Route::post('/', [TierController::class, 'store'])->name('admin.tiers.store');
        Route::get('/{tier}', [TierController::class, 'show'])->name('admin.tiers.show');
        Route::put('/{tier}', [TierController::class, 'update'])->name('admin.tiers.update');
        Route::delete('/{tier}', [TierController::class, 'destroy'])->name('admin.tiers.destroy');
        Route::post('/{tier}/toggle-status', [TierController::class, 'toggleStatus'])->name('admin.tiers.toggle');
    });
    
    // Trainer-specific routes (trainers, admins, and owners can access)
    Route::prefix('trainer')->middleware('trainer')->group(function () {
        // Add trainer-specific endpoints here
        Route::get('/dashboard', function (Request $request) {
            return response()->json([
                'status' => 'success',
                'message' => 'Trainer dashboard access granted',
                'data' => [
                    'user' => $request->user()->only(['id', 'name', 'user_role', 'is_trainer'])
                ]
            ]);
        })->name('trainer.dashboard');
    });
    
    // Member-only routes (all authenticated users can access)
    Route::prefix('member')->group(function () {
        // Member-specific endpoints
        Route::get('/dashboard', function (Request $request) {
            return response()->json([
                'status' => 'success',
                'message' => 'Member dashboard access granted',
                'data' => [
                    'user' => $request->user()->only(['id', 'name', 'user_role', 'is_trainer'])
                ]
            ]);
        })->name('member.dashboard');
    });
    
    // Owner-only routes (highest privilege level)
    Route::prefix('owner')->middleware('role:owner')->group(function () {
        // User management
        Route::get('/users', function (Request $request) {
            $users = \App\Models\User::select(['id', 'name', 'email', 'user_role', 'is_trainer', 'is_active', 'created_at'])
                ->paginate(15);
            
            return response()->json([
                'status' => 'success',
                'data' => $users
            ]);
        })->name('owner.users.index');
        
        Route::put('/users/{user}/role', function (Request $request, \App\Models\User $user) {
            $request->validate([
                'user_role' => 'required|in:member,admin,owner',
                'is_trainer' => 'boolean'
            ]);
            
            $user->update([
                'user_role' => $request->user_role,
                'is_trainer' => $request->boolean('is_trainer', false)
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'User role updated successfully',
                'data' => [
                    'user' => $user->only(['id', 'name', 'user_role', 'is_trainer'])
                ]
            ]);
        })->name('owner.users.update_role');
        
        Route::put('/users/{user}/toggle-status', function (Request $request, \App\Models\User $user) {
            $user->update([
                'is_active' => !$user->is_active
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'User status updated successfully',
                'data' => [
                    'user' => $user->only(['id', 'name', 'is_active'])
                ]
            ]);
        })->name('owner.users.toggle_status');
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
