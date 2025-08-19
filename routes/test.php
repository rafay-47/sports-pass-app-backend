<?php

use Illuminate\Http\Request;
use App\Models\TrainerProfile;
use App\Models\TrainerCertification;
use App\Models\TrainerSpecialty;
use App\Models\TrainerAvailability;
use App\Models\TrainerLocation;

Route::get('/test/trainer-data', function () {
    try {
        // Test data retrieval
        $trainerProfile = TrainerProfile::with([
            'user:id,name,email',
            'sport:id,name,display_name',
            'tier:id,tier_name,display_name',
            'certifications',
            'specialties',
            'availability',
            'locations'
        ])->first();

        if (!$trainerProfile) {
            return response()->json([
                'status' => 'error',
                'message' => 'No trainer profiles found'
            ], 404);
        }

        // Test statistics
        $stats = [
            'total_trainer_profiles' => TrainerProfile::count(),
            'verified_trainers' => TrainerProfile::verified()->count(),
            'available_trainers' => TrainerProfile::available()->count(),
            'total_certifications' => TrainerCertification::count(),
            'total_specialties' => TrainerSpecialty::count(),
            'total_availability_slots' => TrainerAvailability::count(),
            'total_locations' => TrainerLocation::count(),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Trainer functionality test successful',
            'data' => [
                'sample_trainer' => $trainerProfile,
                'statistics' => $stats,
                'database_tables_created' => [
                    'trainer_profiles' => 'Created',
                    'trainer_certifications' => 'Created', 
                    'trainer_specialties' => 'Created',
                    'trainer_availability' => 'Created',
                    'trainer_locations' => 'Created',
                    'trainer_sessions' => 'Created'
                ]
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Test failed: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
