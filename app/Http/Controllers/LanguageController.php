<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class LanguageController extends Controller
{
    public function change(Request $request): RedirectResponse
    {
        $locale = (string) $request->query('lang', config('app.locale'));
        $supportedLocales = array_keys(config('app.supported_locales', []));

        if (!in_array($locale, $supportedLocales, true)) {

            Log::warning("Invalid locale attempted: $locale");
            return redirect()->back()->withErrors(['locale' => 'Invalid locale']);
        }


        session(['locale' => $locale]);

        App::setLocale($locale);

        return redirect()->back();
    }
}
