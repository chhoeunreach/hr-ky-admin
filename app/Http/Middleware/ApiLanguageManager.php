<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ApiLanguageManager
{
    public function handle(Request $request, Closure $next)
    {
        $locale = config('app.api_locale', 'km');
        $supportedLocales = array_keys(config('app.supported_locales', []));

        App::setLocale(in_array($locale, $supportedLocales, true) ? $locale : 'km');

        return $next($request);
    }
}
