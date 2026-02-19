<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported locales.
     */
    protected array $supportedLocales = ['en', 'zh-TW'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Priority: User DB > Session > Cookie > Browser > Default
        $locale = null;

        try {
            // Check user's saved preference if logged in
            if (auth()->check() && auth()->user()->locale) {
                $locale = auth()->user()->locale;
            }

            if (!$locale) {
                $locale = Session::get('locale');
            }

            if (!$locale) {
                $locale = $request->cookie('locale');
            }
        } catch (\Throwable) {
            // Fallback to browser/default locale when session or auth storage is not available.
            $locale = null;
        }

        if (!$locale) {
            // Detect from browser
            $locale = $this->detectBrowserLocale($request);
        }
        
        if (!$locale || !in_array($locale, $this->supportedLocales)) {
            $locale = config('app.locale', 'en');
        }
        
        App::setLocale($locale);
        
        return $next($request);
    }

    /**
     * Detect locale from browser Accept-Language header.
     */
    protected function detectBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header
        $languages = explode(',', $acceptLanguage);
        
        foreach ($languages as $lang) {
            $lang = trim(explode(';', $lang)[0]);
            
            // Check for exact match
            if (in_array($lang, $this->supportedLocales)) {
                return $lang;
            }
            
            // Check for Chinese variants
            if (str_starts_with($lang, 'zh')) {
                return 'zh-TW';
            }
            
            // Check for English variants
            if (str_starts_with($lang, 'en')) {
                return 'en';
            }
        }
        
        return null;
    }
}
