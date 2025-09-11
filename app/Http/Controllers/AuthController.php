<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TrainerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'user_role' => 'nullable|in:member,owner,admin',
            'is_trainer' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password_hash' => Hash::make($request->password),
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'user_role' => $request->input('user_role', 'member'),
                'is_trainer' => $request->boolean('is_trainer', false),
                'is_verified' => false,
                'is_active' => true,
                'join_date' => now()->toDateString(),
            ]);

            // Fire the registered event for email verification
            event(new Registered($user));

            // Create token for immediate authentication
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $this->formatUserResponse($user),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account is deactivated'
            ], 403);
        }

        // Update last login
        $user->updateLastLogin();

        // Create token
        $deviceName = $request->device_name ?? 'Unknown Device';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => $this->formatUserResponse($user),
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Logout user (revoke current token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Logout from all devices (revoke all tokens)
     */
    public function logoutFromAllDevices(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out from all devices'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $this->formatUserResponse($request->user())
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            'date_of_birth' => 'sometimes|nullable|date|before:today',
            'gender' => 'sometimes|nullable|in:male,female,other',
            'profile_image_url' => 'sometimes|nullable|url|max:500',
            'user_role' => 'sometimes|in:member,owner,admin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user is trying to change their role
        if ($request->has('user_role') && $request->user_role !== $user->user_role) {
            // Only owners can change user roles
            if ($user->user_role !== 'owner') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only owners can change user roles'
                ], 403);
            }
        }

        try {
            $updateData = $request->only([
                'name', 'phone', 'date_of_birth', 'gender', 'profile_image_url'
            ]);
            
            // Only allow role change if user is owner
            if ($request->has('user_role') && $user->user_role === 'owner') {
                $updateData['user_role'] = $request->user_role;
            }
            
            $user->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => $this->formatUserResponse($user->fresh())
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password_hash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 400);
        }

        try {
            $user->update([
                'password_hash' => Hash::make($request->new_password)
            ]);

            // Revoke all tokens except current one
            $currentToken = $request->user()->currentAccessToken();
            $user->tokens()->where('id', '!=', $currentToken->id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password change failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Password reset link sent to your email'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send reset link'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send reset link',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password_hash' => Hash::make($password)
                    ])->save();

                    // Revoke all tokens
                    $user->tokens()->delete();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Password reset successful'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reset password'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password reset failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'hash' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($request->id);

        if ($user->is_verified) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email already verified'
            ]);
        }

        if (hash('sha256', $user->email) !== $request->hash) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification link'
            ], 400);
        }

        $user->update(['is_verified' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully'
        ]);
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->is_verified) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email already verified'
            ]);
        }

        try {
            event(new Registered($user));

            return response()->json([
                'status' => 'success',
                'message' => 'Verification email sent'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send verification email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate account
     */
    public function deactivateAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password is incorrect'
            ], 400);
        }

        try {
            $user->update(['is_active' => false]);
            $user->tokens()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Account deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account deactivation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format user response
     */
    private function formatUserResponse(User $user): array
    {
        // Get trainer availability status if user has a trainer profile
        $trainerAvailabilityStatus = null;
        if ($user->is_trainer) {
            $trainerProfile = TrainerProfile::where('user_id', $user->id)->first();
            if ($trainerProfile) {
                $trainerAvailabilityStatus = $trainerProfile->is_available;
            }
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'date_of_birth' => $user->date_of_birth,
            'gender' => $user->gender,
            'profile_image_url' => $user->profile_image_url,
            'user_role' => $user->user_role,
            'is_trainer' => $user->is_trainer,
            'is_verified' => $user->is_verified,
            'is_active' => $user->is_active,
            'join_date' => $user->join_date,
            'last_login' => $user->last_login,
            'trainer_availability_status' => $trainerAvailabilityStatus,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}
