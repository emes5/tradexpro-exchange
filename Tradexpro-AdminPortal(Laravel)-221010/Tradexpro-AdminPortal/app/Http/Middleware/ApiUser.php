<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiUser
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
        $user = Auth::user();
        if (!empty($user)) {
            if( !empty($user->is_verified)) {
                if($user->status == STATUS_ACTIVE) {
                    if($user->role == USER_ROLE_USER) {
                        return $next($request);
                    } else {
                        return response()->json(['success' => false, 'message' => __('You are not eligible for login in this panel')]);
                    }
                } else {
                    return response()->json(['success' => false, 'message' => __('Your account is currently deactivate, Please contact to admin')]);
                }
            } else {
                return response()->json(['success' => false, 'message' => __('Please verify your email')]);
            }
        } else {
            return response()->json(['success' => false, 'message' => __('User not found')]);
        }
    }
}
