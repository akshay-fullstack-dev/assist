<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUsersActivation {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $user = Auth::User();
        if ($user) {
            if ($user->hasRole('vendor')) {
                if ($user->status == 0) {
                    $this->response['message'] = trans('api/user.vender_is_inactive');
                    $this->response['status'] = 0;
                    $this->response['data'] = array();
                    return response()->json($this->response, 403);
                }
            }
        }

        return $next($request);
    }

}
