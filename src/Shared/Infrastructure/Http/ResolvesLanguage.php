<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Http;

use Illuminate\Http\Request;

trait ResolvesLanguage
{
    /**
     * Resolve the active language from, in order:
     *  1. explicit ?lang= query param
     *  2. Accept-Language header (primary tag, e.g. "es-CO" → "es")
     *  3. config('app.locale') fallback
     */
    protected function resolveLang(Request $request, array $supported = ['es', 'en']): string
    {
        $fallback = config('app.locale', 'es');

        $queryLang = $request->query('lang');
        if (is_string($queryLang) && $queryLang !== '') {
            $lang = strtolower(substr($queryLang, 0, 2));
            return in_array($lang, $supported, true) ? $lang : $fallback;
        }

        $header = $request->header('Accept-Language');
        if (is_string($header) && $header !== '') {
            $lang = strtolower(substr(explode(',', $header)[0], 0, 2));
            return in_array($lang, $supported, true) ? $lang : $fallback;
        }

        return $fallback;
    }
}
