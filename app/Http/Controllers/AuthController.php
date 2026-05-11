<?php

namespace App\Http\Controllers;

use App\Models\User;
use Dentro\Yalr\Attributes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('auth')]
class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @bodyParam name string required The full name of the user. Example: John Doe
     * @bodyParam email string required The email address. Must be unique. Example: john@example.com
     * @bodyParam password string required The password. Minimum 8 characters. Example: secret123
     * @bodyParam password_confirmation string required Confirmation of the password.
     *
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "user": {
     *       "id": "uuid",
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     },
     *     "token": "1|sanctum-token"
     *   }
     * }
     * @response 422 {"success": false, "message": "The email has already been taken.", "errors": {...}}
     */
    #[Attributes\Post('register')]
    public function register(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->response([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Login user and get access token.
     *
     * @bodyParam email string required The email address. Example: john@example.com
     * @bodyParam password string required The password. Example: secret123
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "user": {
     *       "id": "uuid",
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     },
     *     "token": "1|sanctum-token"
     *   }
     * }
     * @response 401 {"success": false, "message": "The provided credentials are incorrect."}
     * @response 422 {"success": false, "message": "The email field is required."}
     */
    #[Attributes\Post('login')]
    public function login(Request $request): Response
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($validated)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $validated['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->response([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Logout user (revoke current token).
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {"message": "Logged out"}
     * }
     * @response 401 {"message": "Unauthenticated."}
     */
    #[Attributes\Post('logout', middleware: ['auth:sanctum'])]
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();
        return $this->response(['message' => 'Logged out']);
    }

    /**
     * Get authenticated user profile.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": "uuid",
     *     "name": "John Doe",
     *     "email": "john@example.com"
     *   }
     * }
     * @response 401 {"message": "Unauthenticated."}
     */
    #[Attributes\Get('profile', middleware: ['auth:sanctum'])]
    public function profile(Request $request): Response
    {
        return $this->response([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
        ]);
    }
}
