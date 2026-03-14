<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            // Check if password is 'password'. In production, user would use a seeded password.
            if (\Illuminate\Support\Facades\Hash::check('password', $user->password)) {
                // If they are not already on the password change page or updating it
                if (! $request->is('password/change') && ! $request->is('logout')) {
                    return redirect()->route('password.change.show')->with('warning', 'Please change your default password to continue.');
                }
            }
        }

        return $next($request);
    }
}
