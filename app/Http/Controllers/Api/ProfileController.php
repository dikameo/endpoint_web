<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Get authenticated user's profile
     */
    public function show(Request $request)
    {
        $profile = Profile::find($request->user()->id);

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
                'error' => [
                    'code' => 'NOT_FOUND',
                    'details' => 'Profile does not exist for this user'
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => $profile
        ]);
    }

    /**
     * Update authenticated user's profile
     */
    public function update(Request $request)
    {
        $profile = Profile::find($request->user()->id);

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
                'error' => [
                    'code' => 'NOT_FOUND',
                    'details' => 'Profile does not exist for this user'
                ]
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $profile->update($request->only(['name', 'phone']));

        // Also update user's name if provided
        if ($request->has('name')) {
            $user = $request->user();
            $user->name = $request->name;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $profile->fresh()
        ]);
    }
}
