<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\SportServiceController;
use App\Http\Controllers\TierController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\TrainerProfileController;
use App\Http\Controllers\TrainerRequestController;
use App\Http\Controllers\TrainerCertificationController;
use App\Http\Controllers\TrainerSpecialtyController;
use App\Http\Controllers\TrainerAvailabilityController;
use App\Http\Controllers\TrainerLocationController;
use App\Http\Controllers\TrainerSessionController;
use App\Http\Controllers\TrainerClubController;
use App\Http\Controllers\ServicePurchaseController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\ClubImageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;

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

// Public amenities (master list)
Route::prefix('amenities')->group(function () {
    Route::get('/', [AmenityController::class, 'index'])->name('amenities.index');
    Route::get('/{amenity}', [AmenityController::class, 'show'])->name('amenities.show');
});

// Public facilities (master list)
Route::prefix('facilities')->group(function () {
    Route::get('/', [FacilityController::class, 'index'])->name('facilities.index');
    Route::get('/{facility}', [FacilityController::class, 'show'])->name('facilities.show');
});

// Public clubs routes (read-only)
Route::prefix('clubs')->group(function () {
    Route::get('/', [ClubController::class, 'index'])->name('clubs.index');
    Route::get('/search', [ClubController::class, 'search'])->name('clubs.search');
    Route::get('/nearby', [ClubController::class, 'nearby'])->name('clubs.nearby');
    Route::get('/filter', [ClubController::class, 'filter'])->name('clubs.filter');
    Route::middleware('auth:sanctum')->get('/my-clubs', [ClubController::class, 'myClubs'])->name('clubs.my-clubs');
    Route::get('/{club}', [ClubController::class, 'show'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.show');
    Route::get('/{club}/amenities', [ClubController::class, 'getAmenities'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.amenities');
    Route::get('/{club}/facilities', [ClubController::class, 'getFacilities'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.facilities');
    Route::get('/{club}/images', [ClubController::class, 'getImages'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.images');
    Route::get('/{club}/check-ins', [ClubController::class, 'getCheckIns'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.check-ins');
    Route::get('/{club}/events', [ClubController::class, 'getEvents'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.events');
    Route::get('/{club}/statistics', [ClubController::class, 'statistics'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.statistics');
    Route::get('/{club}/qr-code', [ClubController::class, 'generateQrCode'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.qr-code');
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

    // Amenities management (admin only)
    Route::prefix('admin/amenities')->middleware('role:admin')->group(function () {
        Route::get('/', [AmenityController::class, 'index'])->name('admin.amenities.index');
        Route::post('/', [AmenityController::class, 'store'])->name('admin.amenities.store');
        Route::get('/{amenity}', [AmenityController::class, 'show'])->name('admin.amenities.show');
        Route::put('/{amenity}', [AmenityController::class, 'update'])->name('admin.amenities.update');
        Route::delete('/{amenity}', [AmenityController::class, 'destroy'])->name('admin.amenities.destroy');
    });

    // Facilities management (admin only)
    Route::prefix('admin/facilities')->middleware('role:admin')->group(function () {
        Route::get('/', [FacilityController::class, 'index'])->name('admin.facilities.index');
        Route::post('/', [FacilityController::class, 'store'])->name('admin.facilities.store');
        Route::get('/{facility}', [FacilityController::class, 'show'])->name('admin.facilities.show');
        Route::put('/{facility}', [FacilityController::class, 'update'])->name('admin.facilities.update');
        Route::delete('/{facility}', [FacilityController::class, 'destroy'])->name('admin.facilities.destroy');
    });
    
    // Club management routes
    Route::prefix('clubs')->group(function () {
        // Admin only routes - must come before parameter routes
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('/', [ClubController::class, 'adminIndex'])->name('admin.clubs.index');
            Route::post('/{club}/verify', [ClubController::class, 'verify'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('admin.clubs.verify');
            Route::post('/{club}/unverify', [ClubController::class, 'unverify'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('admin.clubs.unverify');
            Route::get('/statistics', [ClubController::class, 'adminStatistics'])->name('admin.clubs.statistics');
        });

        // Club owner and admin routes - parameter routes come last
        Route::middleware('role:owner,admin')->group(function () {
            Route::post('/', [ClubController::class, 'store'])->name('clubs.store');
            Route::put('/{club}', [ClubController::class, 'update'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.update');
            Route::delete('/{club}', [ClubController::class, 'destroy'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.destroy');
            Route::post('/{club}/toggle-status', [ClubController::class, 'toggleStatus'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.toggle-status');
            Route::post('/{club}/amenities', [ClubController::class, 'addAmenities'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.add-amenities');
            Route::delete('/{club}/amenities/{amenity}', [ClubController::class, 'removeAmenity'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.remove-amenity');
            Route::post('/{club}/facilities', [ClubController::class, 'addFacilities'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.add-facilities');
            Route::delete('/{club}/facilities/{facility}', [ClubController::class, 'removeFacility'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.remove-facility');
            Route::post('/{club}/images', [ClubController::class, 'addImage'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.add-image');
            Route::delete('/{club}/images/{image}', [ClubController::class, 'removeImage'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.remove-image');
            Route::post('/{club}/check-in', [ClubController::class, 'checkIn'])->where('club', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}')->name('clubs.check-in');
        });
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
    
    // Event management routes
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('events.index');
        Route::post('/', [EventController::class, 'store'])->name('events.store');
        Route::get('/statistics', [EventController::class, 'statistics'])->name('events.statistics');
        Route::get('/sport/{sport}', [EventController::class, 'getBySport'])->name('events.by-sport');
        Route::get('/organizer/{user?}', [EventController::class, 'getByOrganizer'])->name('events.by-organizer');
        Route::get('/my-events', [EventController::class, 'getMyEvents'])->name('events.my-events');
        Route::post('/{event}/register', [EventController::class, 'register'])->name('events.register');
        Route::get('/my-registrations', [EventController::class, 'myRegistrations'])->name('events.my-registrations');
        Route::get('/{event}', [EventController::class, 'show'])->name('events.show');
        Route::put('/{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    });
    
    // Event Registration management routes
    Route::prefix('event-registrations')->group(function () {
        Route::get('/', [EventRegistrationController::class, 'index'])->name('event-registrations.index');
        Route::post('/', [EventRegistrationController::class, 'store'])->name('event-registrations.store');
        Route::get('/statistics', [EventRegistrationController::class, 'statistics'])->name('event-registrations.statistics');
        Route::get('/event/{event}', [EventRegistrationController::class, 'getByEvent'])->name('event-registrations.by-event');
        Route::get('/user/{user}', [EventRegistrationController::class, 'getByUser'])->name('event-registrations.by-user');
        Route::post('/{eventRegistration}/cancel', [EventRegistrationController::class, 'cancel'])->name('event-registrations.cancel');
        Route::post('/{eventRegistration}/confirm', [EventRegistrationController::class, 'confirm'])->name('event-registrations.confirm');
        Route::post('/{eventRegistration}/process-payment', [EventRegistrationController::class, 'processPayment'])->name('event-registrations.process-payment');
        Route::get('/{eventRegistration}', [EventRegistrationController::class, 'show'])->name('event-registrations.show');
        Route::put('/{eventRegistration}', [EventRegistrationController::class, 'update'])->name('event-registrations.update');
        Route::delete('/{eventRegistration}', [EventRegistrationController::class, 'destroy'])->name('event-registrations.destroy');
    });
    
    // Check-in management routes
    Route::prefix('check-ins')->group(function () {
        Route::get('/', [CheckInController::class, 'index'])->name('check-ins.index');
        Route::post('/', [CheckInController::class, 'store'])->name('check-ins.store');
        Route::get('/statistics', [CheckInController::class, 'statistics'])->name('check-ins.statistics');
        Route::get('/current', [CheckInController::class, 'currentCheckIns'])->name('check-ins.current');
        Route::post('/qr-check-in', [CheckInController::class, 'qrCheckIn'])->name('check-ins.qr-check-in');
        Route::get('/club/{club}', [CheckInController::class, 'getByClub'])->name('check-ins.by-club');
        Route::get('/user/{user}', [CheckInController::class, 'getByUser'])->name('check-ins.by-user');
        Route::post('/{checkIn}/check-out', [CheckInController::class, 'checkOut'])->name('check-ins.check-out');
        Route::get('/{checkIn}', [CheckInController::class, 'show'])->name('check-ins.show');
        Route::put('/{checkIn}', [CheckInController::class, 'update'])->name('check-ins.update');
        Route::delete('/{checkIn}', [CheckInController::class, 'destroy'])->name('check-ins.destroy');
    });
    
    // Club Image management routes
    Route::prefix('club-images')->group(function () {
        Route::get('/', [ClubImageController::class, 'index'])->name('club-images.index');
        Route::post('/', [ClubImageController::class, 'store'])->name('club-images.store');
        Route::post('/bulk-upload', [ClubImageController::class, 'bulkUpload'])->name('club-images.bulk-upload');
        Route::get('/statistics', [ClubImageController::class, 'statistics'])->name('club-images.statistics');
        Route::get('/club/{club}', [ClubImageController::class, 'getByClub'])->name('club-images.by-club');
        Route::post('/{clubImage}/set-primary', [ClubImageController::class, 'setPrimary'])->name('club-images.set-primary');
        Route::post('/update-sort-order', [ClubImageController::class, 'updateSortOrder'])->name('club-images.update-sort-order');
        Route::get('/{clubImage}', [ClubImageController::class, 'show'])->name('club-images.show');
        Route::put('/{clubImage}', [ClubImageController::class, 'update'])->name('club-images.update');
        Route::delete('/{clubImage}', [ClubImageController::class, 'destroy'])->name('club-images.destroy');
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
        
        Route::put('/profile/toggle-availability', [TrainerProfileController::class, 'toggleAvailability'])->name('trainer.toggle-availability');
    });
    
    // Trainer profile routes (any authenticated user can access their own profile)
    Route::prefix('trainer')->group(function () {
        Route::get('/profile', [TrainerProfileController::class, 'myProfile'])->name('trainer.profile');
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
        
        // Club management for trainers
        Route::post('/{trainerProfile}/clubs', [TrainerProfileController::class, 'addClub'])->name('trainer-profiles.add-club');
        Route::delete('/{trainerProfile}/clubs', [TrainerProfileController::class, 'removeClub'])->name('trainer-profiles.remove-club');
    });

    // Trainer Clubs management routes
    Route::prefix('trainer-clubs')->group(function () {
        Route::get('/', [TrainerClubController::class, 'statistics'])->name('trainer-clubs.statistics');
        Route::get('/trainer/{trainerProfile}', [TrainerClubController::class, 'getByTrainer'])->name('trainer-clubs.by-trainer');
        Route::get('/club/{club}', [TrainerClubController::class, 'getByClub'])->name('trainer-clubs.by-club');
        Route::get('/trainer/{trainerProfile}/clubs', [TrainerClubController::class, 'index'])->name('trainer-clubs.index');
        Route::post('/trainer/{trainerProfile}/clubs', [TrainerClubController::class, 'store'])->name('trainer-clubs.store');
        Route::put('/trainer/{trainerProfile}/clubs', [TrainerClubController::class, 'bulkUpdate'])->name('trainer-clubs.bulk-update');
        Route::put('/trainer/{trainerProfile}/clubs/replace', [TrainerClubController::class, 'replaceAll'])->name('trainer-clubs.replace-all');
        Route::put('/trainer/{trainerProfile}/primary-club', [TrainerClubController::class, 'updatePrimaryClub'])->name('trainer-clubs.update-primary');
        Route::put('/trainer/{trainerProfile}/clubs/bulk-primary', [TrainerClubController::class, 'bulkUpdatePrimary'])->name('trainer-clubs.bulk-update-primary');
        Route::get('/trainer/{trainerProfile}/clubs/{club}', [TrainerClubController::class, 'show'])->name('trainer-clubs.show');
        Route::put('/trainer/{trainerProfile}/clubs/{club}', [TrainerClubController::class, 'update'])->name('trainer-clubs.update');
        Route::delete('/trainer/{trainerProfile}/clubs/{club}', [TrainerClubController::class, 'destroy'])->name('trainer-clubs.destroy');
        Route::post('/trainer/{trainerProfile}/clubs/{club}/set-primary', [TrainerClubController::class, 'setPrimary'])->name('trainer-clubs.set-primary');
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
        Route::get('/memberships/{membership}/services', [MembershipController::class, 'getServices'])->name('member.memberships.services');
        Route::get('/memberships/{membership}/trainers', [MembershipController::class, 'getTrainers'])->name('member.memberships.trainers');
        
        // Member's own service purchases
        Route::get('/service-purchases', [ServicePurchaseController::class, 'myPurchases'])->name('member.service-purchases');
        
        // Member's notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('member.notifications');
        Route::get('/notifications/statistics', [NotificationController::class, 'statistics'])->name('member.notifications.statistics');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('member.notifications.mark-all-read');
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('member.notifications.mark-read');
        Route::patch('/notifications/{notification}/unread', [NotificationController::class, 'markAsUnread'])->name('member.notifications.mark-unread');
        
        // Member's trainer requests
        Route::get('/trainer-requests', [TrainerRequestController::class, 'index'])->name('member.trainer-requests');
        Route::post('/trainer-requests', [TrainerRequestController::class, 'store'])->name('member.trainer-requests.store');
        Route::get('/trainer-requests/{trainerRequest}', [TrainerRequestController::class, 'show'])->name('member.trainer-requests.show');
        Route::patch('/trainer-requests/{trainerRequest}/cancel', [TrainerRequestController::class, 'cancel'])->name('member.trainer-requests.cancel');
    });

    // Trainer routes
    Route::prefix('trainer')->middleware('trainer')->group(function () {
        Route::get('/requests', [TrainerRequestController::class, 'incoming'])->name('trainer.requests');
        Route::patch('/requests/{trainerRequest}/accept', [TrainerRequestController::class, 'accept'])->name('trainer.requests.accept');
        Route::patch('/requests/{trainerRequest}/decline', [TrainerRequestController::class, 'decline'])->name('trainer.requests.decline');
    });

    // Payment management routes
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/statistics', [PaymentController::class, 'statistics'])->name('payments.statistics');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::put('/{payment}', [PaymentController::class, 'update'])->name('payments.update');
        Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });

    // Notification management routes (admin only)
    Route::prefix('admin/notifications')->middleware('role:admin')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('admin.notifications.index');
        Route::post('/', [NotificationController::class, 'store'])->name('admin.notifications.store');
        Route::get('/{notification}', [NotificationController::class, 'show'])->name('admin.notifications.show');
        Route::put('/{notification}', [NotificationController::class, 'update'])->name('admin.notifications.update');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('admin.notifications.destroy');
    });

    // Membership management routes
    Route::prefix('memberships')->group(function () {
        Route::get('/', [MembershipController::class, 'index'])->name('memberships.index');
        Route::post('/', [MembershipController::class, 'store'])->name('memberships.store');
        Route::get('/statistics', [MembershipController::class, 'statistics'])->name('memberships.statistics');
        Route::get('/{membership}', [MembershipController::class, 'show'])->name('memberships.show');
        Route::get('/{membership}/services', [MembershipController::class, 'getServices'])->name('memberships.services');
        Route::get('/{membership}/trainers', [MembershipController::class, 'getTrainers'])->name('memberships.trainers');
        Route::put('/{membership}', [MembershipController::class, 'update'])->name('memberships.update');
        Route::delete('/{membership}', [MembershipController::class, 'destroy'])->name('memberships.destroy');
        
        // Membership actions
        Route::post('/{membership}/renew', [MembershipController::class, 'renew'])->name('memberships.renew');
        Route::post('/{membership}/pause', [MembershipController::class, 'pause'])->name('memberships.pause');
        Route::post('/{membership}/resume', [MembershipController::class, 'resume'])->name('memberships.resume');
        Route::post('/{membership}/cancel', [MembershipController::class, 'cancel'])->name('memberships.cancel');
        
        // Get service purchases by membership
        Route::get('/{membership}/service-purchases', [ServicePurchaseController::class, 'getByMembership'])->name('memberships.service-purchases');
    });

    // Service Purchase management routes
    Route::prefix('service-purchases')->group(function () {
        Route::get('/', [ServicePurchaseController::class, 'index'])->name('service-purchases.index');
        Route::post('/', [ServicePurchaseController::class, 'store'])->name('service-purchases.store');
        Route::get('/statistics', [ServicePurchaseController::class, 'statistics'])->name('service-purchases.statistics');
        Route::get('/{servicePurchase}', [ServicePurchaseController::class, 'show'])->name('service-purchases.show');
        Route::put('/{servicePurchase}', [ServicePurchaseController::class, 'update'])->name('service-purchases.update');
        Route::delete('/{servicePurchase}', [ServicePurchaseController::class, 'destroy'])->name('service-purchases.destroy');
        
        // Service purchase actions
        Route::post('/{servicePurchase}/complete', [ServicePurchaseController::class, 'markCompleted'])->name('service-purchases.complete');
        Route::post('/{servicePurchase}/cancel', [ServicePurchaseController::class, 'cancel'])->name('service-purchases.cancel');
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
                'user_role' => 'required|in:member,admin,owner'
            ]);
            
            $user->update([
                'user_role' => $request->user_role
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
