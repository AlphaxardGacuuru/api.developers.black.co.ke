<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileAvatarUpdateRequest;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(protected UserService $service)
    {
        //
    }

    /**
     * Update the user's name and email.
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $this->service->updateProfile($request->user(), $request->validated());

        return response()->json([
            'message' => 'Profile Updated',
            'data' => $request->user(),
        ]);
    }

    /**
     * Replace the user's profile picture.
     */
    public function updateAvatar(ProfileAvatarUpdateRequest $request): JsonResponse
    {
        $this->service->updateAvatar($request->user(), $request->file('avatar'));

        return response()->json([
            'message' => 'Profile picture updated.',
            'avatar' => $request->user()->avatar,
        ]);
    }

    /**
     * Delete the user's account and end their session.
     */
    public function destroy(ProfileDeleteRequest $request): JsonResponse
    {
        $user = $request->user();

        // Only the SPA's cookie requests carry a session to end
        if ($request->hasSession()) {
            Auth::guard('web')
                ->logout();

            $request->session()
                ->invalidate();

            $request->session()
                ->regenerateToken();
        }

        $this->service->deleteAccount($user);

        return response()->json(['message' => 'Account Deleted']);
    }
}
