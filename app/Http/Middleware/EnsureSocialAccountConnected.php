<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSocialAccountConnected
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $platform = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $query = $user->socialAccounts();

        if ($platform) {
            $query->where('platform', $platform);
        }

        if (!$query->exists()) {
            return redirect()->route('social-accounts.index')
                ->with('error', $platform 
                    ? "Please connect your {$platform} account first."
                    : 'Please connect at least one social media account.');
        }

        return $next($request);
    }
}
