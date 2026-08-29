<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceFinancialDiscipline
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_penalized) {
            // Lock access to analytics & portfolio views
            if ($request->routeIs(['analytics', 'portfolio'])) {
                return response()->view('discipline-locked', [
                    'user' => $user,
                    'reason' => 'Financial discipline streak broken! Your streak has dropped to 0 due to inactivity.',
                ], 403);
            }
        }

        return $next($request);
    }
}
