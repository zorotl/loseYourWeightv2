<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Check if the user is authenticated and if any of the required profile fields are null
        $profileIsIncomplete = $user && (
            is_null($user->height_cm) ||
            is_null($user->date_of_birth) ||
            is_null($user->gender) ||
            is_null($user->activity_level) ||
            is_null($user->target_weight_kg)
        );

        // If the profile is incomplete and the user is not already on the setup page, redirect them.
        if ($profileIsIncomplete && !$request->routeIs('pages.setup')) {
            return redirect()->route('pages.setup');
        }

        return $next($request);
    }
}