<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Auth;

class LoginController extends Controller {

    public function __construct() {
        $this->middleware('guest.user', ['except' => 'doLogout']);
    }

    /**
     * Display a login page
     * @return Response
     */
    public function index() {
        return view('frontend.login');
    }

    /**
     * Check login with email and password.
     * @return redirect to member area or error_message if login fails
     */
    public function doLogin(Request $request) {

        $input = $request->all();
        $credentials = array(
            'email' => $input['email'],
            'password' => $input['password']
        );

        if (auth()->guard('user')->attempt($credentials)) {
            $credentials = array(
                'email' => $input['email'],
                'password' => $input['password'],
                 
            );

            if (auth()->guard('user')->attempt($credentials)) {
                $user = $request->user(); //getting the current logged in user
                if (!$user->hasRole('agency')) {
                    auth()->guard('user')->logout();
                    session()->flush();
                    session()->regenerate();
                    return redirect('agency/login')->with('error_message', trans('user/agency.not_allowed_to_view_this_page'));
                }

                User::where('id', auth()->guard('user')->user()->id)->update(['online' => '1']);
                $array = array();
                $array['success'] = true;
                $array['warning'] = false;
                $array['message'] = trans('user/login.login_success_message');
                //return response()->json($array);
                return redirect('agency/dashboard')->with('success_message', trans('user/login.login_success_message'));
            } else {
                ///echo 'in else'. auth()->user()->role; exit;
                auth()->guard('user')->logout();
                session()->flush();
                session()->regenerate();
                // authentication failure! lets go back to the login page
                $array['message'] = trans('user/login.login_block_message');
                //return response()->json($array);
                return redirect('agency/login')->with('error_message', trans('user/login.login_block_message'));
            }
        } else {
            // authentication failure! lets go back to the login page
            $array = array();
            $array['success'] = false;
            //$array['warning'] = false;
            //$array['message'] = trans('user/login.login_invalid_message');
            //return response()->json($array);
            return redirect()->back()->with('error_message', 'Invalid email or password!');
        }
    }

    public function doLogout() {
        User::where('id', auth()->guard('user')->user()->id)->update(['online' => '0']);
        auth()->guard('user')->logout();
        session()->flush();
        session()->regenerate();
        return redirect('agency/login');
    }

}
