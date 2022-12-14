<?php

namespace App\Http\Middleware;

use Closure;

class CoinApiConfig
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(allsetting('coin_api_settings') == 1 || allsetting('coin_api_settings') == 2) {
            return $next($request);
        } else {
            return redirect()->route('adminApiSettings')->with('dismiss', __('Please save any option for coin api'));
        }
    }
}
