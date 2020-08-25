<?php

namespace App\Http\Middleware;

use Closure;

class SocialMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $services = ['google', 'yandex', 'vkontakte', 'instagram'];
        $enabledServices = [];
        foreach ($services as $service) {
            if (config('services' . $service)) {
                $enabledServices[] = $service;
            }
        }

        if (!in_array(strtolower($request->service), $enabledServices)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid social service'
                ], 403);
            } else {
                return redirect()->back();
            }
        }
        return $next($request);
    }
}
