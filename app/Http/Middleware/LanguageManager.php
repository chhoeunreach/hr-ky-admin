<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $supportedLocales = array_keys(config('app.supported_locales', []));
        $requestedLocale = $request->query('lang', $request->query('locale'));

        if ($requestedLocale && in_array($requestedLocale, $supportedLocales, true)) {
            App::setLocale($requestedLocale);
            if (!$request->ajax() && !$request->has('auto_print')) {
                session(['locale' => $requestedLocale]);
            }
        } else {
            $locale = session('locale', config('app.locale', 'km'));
            if (in_array($locale, $supportedLocales, true)) {
                App::setLocale($locale);
                session(['locale' => $locale]);
            } else {
                App::setLocale(config('app.locale', 'km'));
            }
        }

        return $next($request);
    }
}
