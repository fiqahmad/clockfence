<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Speak each logged-in User's chosen language (English or Bahasa Melayu).
 */
class SetUserLocale
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && in_array($user->locale, ['en', 'ms'], true)) {
            App::setLocale($user->locale);
        }

        return $next($request);
    }
}
