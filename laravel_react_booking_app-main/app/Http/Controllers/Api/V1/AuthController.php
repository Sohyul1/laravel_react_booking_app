<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(protected JwtService $jwt)
    {
    }

    /**
     * Register a new user and return a JWT for them.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'User registered successfully.',
            'token' => $this->jwt->generateToken($user),
            'user' => $user,
        ], 201);
    }

    /**
     * Verify credentials and return a JWT for the user.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        return response()->json([
            'message' => 'Login successful.',
            'token' => $this->jwt->generateToken($user),
            'user' => $user,
        ]);
    }

    /**
     * Return the currently authenticated user (requires the jwt.auth middleware).
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * "Log out" the user. JWTs are stateless, so there is no server-side
     * session to destroy — the client is expected to discard the token.
     */
    public function logout(Request $request)
    {
        return response()->json(['message' => 'Logged out successfully.']);
    }
}
