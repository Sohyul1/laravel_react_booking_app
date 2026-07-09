<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function __construct(protected JwtService $jwt)
    {
    }

    /**
     * Validate the bearer token on the request and authenticate the user
     * it belongs to before letting the request continue.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Token not provided.'], 401);
        }

        try {
            $payload = $this->jwt->validate($token);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        $user = User::find($payload['sub']);

        if (! $user) {
            return response()->json(['message' => 'User no longer exists.'], 401);
        }

        auth()->setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
