<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Services\UserService;
use Illuminate\Http\JsonResponse;

class SecurityController extends Controller
{
    public function __construct(protected UserService $service)
    {
        //
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): JsonResponse
    {
        $this->service->updatePassword($request->user(), $request->validated('password'));

        return response()->json(['message' => 'Password Updated']);
    }
}
