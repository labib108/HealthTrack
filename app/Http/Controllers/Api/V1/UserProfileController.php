<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->profile;

        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved.',
            'data'    => [
                'profile' => $profile ? new UserProfileResource($profile) : null,
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')
                            ->store('profile-photos', 'public');

            $data['profile_photo'] = $path;
        }

        $profile = $request->user()->profile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => [
                'profile' => new UserProfileResource($profile),
            ],
        ]);
    }
}
