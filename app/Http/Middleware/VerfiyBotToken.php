<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerfiyBotToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Bot-Token');
        if ($token !== config('services.botpress.token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
