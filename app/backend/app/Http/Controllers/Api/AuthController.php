<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginFormRequest;
use App\Http\Requests\RegisterFormRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     */
    public function register(RegisterFormRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json($result, 201);
    }

    /**
     * Authenticate a user.
     */
    public function login(LoginFormRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json($result);
    }

    /**
     * Log out the user.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }
}
