<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);
        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }

        abort_unless($user->role === 'delivery_person', 403, 'Only delivery-person accounts can use the delivery API.');

        $token = $user->createToken($credentials['device_name'], [
            'deliveries:read',
            'deliveries:update',
            'payments:create',
        ]);

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => now()->addMinutes(config('sanctum.expiration'))->toIso8601String(),
            'user' => $user,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Token revoked.']);
    }
}
