<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthorizedAccessMiddleware
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
        $user = Auth::user();
        if ($user->is_authorized != 1) {
            Auth::logout();
            $request->session()->flash('msg', 'Unfortunately you are not authorized to use this app!');
            return redirect("/login");
        }
        return $next($request);
    }
}
