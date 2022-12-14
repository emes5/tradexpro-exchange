<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApi
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
        $lang = $request->header('lang') ?? 'en';
        app()->setLocale($lang);
        $key = 'h0vWu6MkInNlWHJVfIXmHbIbC66cQvlbSUQI09Whbp';
        if ($request->header('userapisecret') && $request->header('userapisecret') == $key) {
            return $next($request);
        } else {
            return response()->json(['success' => false, 'message' => __('Invalid key')]);
        }

    }
}
