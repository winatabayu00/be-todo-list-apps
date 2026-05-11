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
     * @param Request $request
     * @return Response
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
     * @param Request $request
     * @return Response
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
     * @param Request $request
     * @return Response
     */
    #[Attributes\Post('logout')]
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();
        return $this->response(['message' => 'Logged out']);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Attributes\Get('profile')]
    public function profile(Request $request): Response
    {
        return $this->response([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
        ]);
    }
}
