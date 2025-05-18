<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSocialTokensValid
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

        $invalidAccounts = $query->get()->filter(function ($account) {
            return $account->token_expires_at && $account->token_expires_at->isPast();
        });

        if ($invalidAccounts->isNotEmpty()) {
            $platforms = $invalidAccounts->pluck('platform')->map(function ($platform) {
                return ucfirst($platform);
            })->join(', ', ' and ');

            return redirect()->route('social-accounts.index')
                ->with('error', "Your access tokens for {$platforms} have expired. Please reconnect these accounts.");
        }

        return $next($request);
    }
}
