<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\SportServiceController;
use App\Http\Controllers\TierController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\TrainerProfileController;
use App\Http\Controllers\TrainerCertificationController;
use App\Http\Controllers\TrainerSpecialtyController;
use App\Http\Controllers\TrainerAvailabilityController;
use App\Http\Controllers\TrainerLocationController;
use App\Http\Controllers\TrainerSessionController;

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

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
})->name('health');

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

// Public trainer profile routes (read-only)
Route::prefix('trainers')->group(function () {
    Route::get('/', [TrainerProfileController::class, 'index'])->name('trainers.index');
    Route::get('/sport/{sport}', [TrainerProfileController::class, 'getBySport'])->name('trainers.by-sport');
    Route::get('/{trainerProfile}', [TrainerProfileController::class, 'show'])->name('trainers.show');
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
        
        // Trainer's own profile
        Route::get('/profile', [TrainerProfileController::class, 'myProfile'])->name('trainer.profile');
        Route::put('/profile/toggle-availability', [TrainerProfileController::class, 'toggleAvailability'])->name('trainer.toggle-availability');
    });
    
    // Trainer Profile management routes
    Route::prefix('trainer-profiles')->group(function () {
        Route::get('/', [TrainerProfileController::class, 'index'])->name('trainer-profiles.index');
        Route::post('/', [TrainerProfileController::class, 'store'])->name('trainer-profiles.store');
        Route::get('/statistics', [TrainerProfileController::class, 'statistics'])->name('trainer-profiles.statistics');
        Route::get('/{trainerProfile}', [TrainerProfileController::class, 'show'])->name('trainer-profiles.show');
        Route::put('/{trainerProfile}', [TrainerProfileController::class, 'update'])->name('trainer-profiles.update');
        Route::delete('/{trainerProfile}', [TrainerProfileController::class, 'destroy'])->name('trainer-profiles.destroy');
        
        // Trainer profile actions
        Route::post('/{trainerProfile}/verify', [TrainerProfileController::class, 'verify'])->name('trainer-profiles.verify');
        Route::post('/{trainerProfile}/unverify', [TrainerProfileController::class, 'unverify'])->name('trainer-profiles.unverify');
        Route::post('/{trainerProfile}/toggle-availability', [TrainerProfileController::class, 'toggleAvailability'])->name('trainer-profiles.toggle-availability');
        Route::post('/{trainerProfile}/update-statistics', [TrainerProfileController::class, 'updateStatistics'])->name('trainer-profiles.update-statistics');
    });

    // Trainer Certification management routes
    Route::prefix('trainer-certifications')->group(function () {
        Route::get('/', [TrainerCertificationController::class, 'index'])->name('trainer-certifications.index');
        Route::post('/', [TrainerCertificationController::class, 'store'])->name('trainer-certifications.store');
        Route::get('/{trainerCertification}', [TrainerCertificationController::class, 'show'])->name('trainer-certifications.show');
        Route::put('/{trainerCertification}', [TrainerCertificationController::class, 'update'])->name('trainer-certifications.update');
        Route::delete('/{trainerCertification}', [TrainerCertificationController::class, 'destroy'])->name('trainer-certifications.destroy');
        
        // Certification actions
        Route::post('/{trainerCertification}/verify', [TrainerCertificationController::class, 'verify'])->name('trainer-certifications.verify');
        Route::post('/{trainerCertification}/unverify', [TrainerCertificationController::class, 'unverify'])->name('trainer-certifications.unverify');
        
        // Get certifications by trainer
        Route::get('/trainer/{trainerProfile}', [TrainerCertificationController::class, 'getByTrainer'])->name('trainer-certifications.by-trainer');
    });

    // Trainer Specialty management routes
    Route::prefix('trainer-specialties')->group(function () {
        Route::get('/', [TrainerSpecialtyController::class, 'index'])->name('trainer-specialties.index');
        Route::post('/', [TrainerSpecialtyController::class, 'store'])->name('trainer-specialties.store');
        Route::post('/bulk', [TrainerSpecialtyController::class, 'bulkStore'])->name('trainer-specialties.bulk-store');
        Route::get('/popular', [TrainerSpecialtyController::class, 'getPopular'])->name('trainer-specialties.popular');
        Route::get('/{trainerSpecialty}', [TrainerSpecialtyController::class, 'show'])->name('trainer-specialties.show');
        Route::put('/{trainerSpecialty}', [TrainerSpecialtyController::class, 'update'])->name('trainer-specialties.update');
        Route::delete('/{trainerSpecialty}', [TrainerSpecialtyController::class, 'destroy'])->name('trainer-specialties.destroy');
        
        // Get specialties by trainer
        Route::get('/trainer/{trainerProfile}', [TrainerSpecialtyController::class, 'getByTrainer'])->name('trainer-specialties.by-trainer');
    });

    // Trainer Availability management routes
    Route::prefix('trainer-availability')->group(function () {
        Route::get('/', [TrainerAvailabilityController::class, 'index'])->name('trainer-availability.index');
        Route::post('/', [TrainerAvailabilityController::class, 'store'])->name('trainer-availability.store');
        Route::post('/bulk-update-status', [TrainerAvailabilityController::class, 'bulkUpdateStatus'])->name('trainer-availability.bulk-update-status');
        Route::get('/{trainerAvailability}', [TrainerAvailabilityController::class, 'show'])->name('trainer-availability.show');
        Route::put('/{trainerAvailability}', [TrainerAvailabilityController::class, 'update'])->name('trainer-availability.update');
        Route::delete('/{trainerAvailability}', [TrainerAvailabilityController::class, 'destroy'])->name('trainer-availability.destroy');
        
        // Get availability by trainer
        Route::get('/trainer/{trainerProfile}', [TrainerAvailabilityController::class, 'getByTrainer'])->name('trainer-availability.by-trainer');
        Route::get('/trainer/{trainerProfile}/weekly-schedule', [TrainerAvailabilityController::class, 'getWeeklySchedule'])->name('trainer-availability.weekly-schedule');
    });

    // Trainer Location management routes
    Route::prefix('trainer-locations')->group(function () {
        Route::get('/', [TrainerLocationController::class, 'index'])->name('trainer-locations.index');
        Route::post('/', [TrainerLocationController::class, 'store'])->name('trainer-locations.store');
        Route::get('/nearby', [TrainerLocationController::class, 'findNearby'])->name('trainer-locations.nearby');
        Route::get('/statistics', [TrainerLocationController::class, 'getStats'])->name('trainer-locations.statistics');
        Route::get('/{trainerLocation}', [TrainerLocationController::class, 'show'])->name('trainer-locations.show');
        Route::put('/{trainerLocation}', [TrainerLocationController::class, 'update'])->name('trainer-locations.update');
        Route::delete('/{trainerLocation}', [TrainerLocationController::class, 'destroy'])->name('trainer-locations.destroy');
        
        // Location actions
        Route::post('/{trainerLocation}/set-primary', [TrainerLocationController::class, 'setPrimary'])->name('trainer-locations.set-primary');
        
        // Get locations by trainer
        Route::get('/trainer/{trainerProfile}', [TrainerLocationController::class, 'getByTrainer'])->name('trainer-locations.by-trainer');
    });

    // Trainer Session management routes
    Route::prefix('trainer-sessions')->group(function () {
        Route::get('/', [TrainerSessionController::class, 'index'])->name('trainer-sessions.index');
        Route::post('/', [TrainerSessionController::class, 'store'])->name('trainer-sessions.store');
        Route::get('/statistics', [TrainerSessionController::class, 'getStats'])->name('trainer-sessions.statistics');
        Route::get('/my-sessions', [TrainerSessionController::class, 'getByClient'])->name('trainer-sessions.my-sessions');
        Route::get('/{trainerSession}', [TrainerSessionController::class, 'show'])->name('trainer-sessions.show');
        Route::put('/{trainerSession}', [TrainerSessionController::class, 'update'])->name('trainer-sessions.update');
        Route::delete('/{trainerSession}', [TrainerSessionController::class, 'destroy'])->name('trainer-sessions.destroy');
        
        // Session actions
        Route::post('/{trainerSession}/cancel', [TrainerSessionController::class, 'cancel'])->name('trainer-sessions.cancel');
        Route::post('/{trainerSession}/complete', [TrainerSessionController::class, 'complete'])->name('trainer-sessions.complete');
        
        // Get sessions by trainer
        Route::get('/trainer/{trainerProfile}', [TrainerSessionController::class, 'getByTrainer'])->name('trainer-sessions.by-trainer');
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
        
        // Member's own memberships
        Route::get('/memberships', [MembershipController::class, 'myMemberships'])->name('member.memberships');
    });

    // Membership management routes
    Route::prefix('memberships')->group(function () {
        Route::get('/', [MembershipController::class, 'index'])->name('memberships.index');
        Route::post('/', [MembershipController::class, 'store'])->name('memberships.store');
        Route::get('/statistics', [MembershipController::class, 'statistics'])->name('memberships.statistics');
        Route::get('/{membership}', [MembershipController::class, 'show'])->name('memberships.show');
        Route::put('/{membership}', [MembershipController::class, 'update'])->name('memberships.update');
        Route::delete('/{membership}', [MembershipController::class, 'destroy'])->name('memberships.destroy');
        
        // Membership actions
        Route::post('/{membership}/renew', [MembershipController::class, 'renew'])->name('memberships.renew');
        Route::post('/{membership}/pause', [MembershipController::class, 'pause'])->name('memberships.pause');
        Route::post('/{membership}/resume', [MembershipController::class, 'resume'])->name('memberships.resume');
        Route::post('/{membership}/cancel', [MembershipController::class, 'cancel'])->name('memberships.cancel');
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
