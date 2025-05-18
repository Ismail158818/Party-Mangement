<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle($request, Closure $next)
    {
        // تحقق من وجود مستخدم موثق
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
    
        $key = 'api:' . $request->ip();
    
        if ($this->limiter->tooManyAttempts($key, 60)) {
            return response()->json([
                'message' => 'Too many requests',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }
    
        $this->limiter->hit($key, 60);
    
        return $next($request);
    }
}    
