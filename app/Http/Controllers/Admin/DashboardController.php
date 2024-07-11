<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;


class DashboardController extends Controller {
    
    public function __construct() {
        
    }

    /**
     * Admin dashboard
     *
     */
    public function index() {
        $users = \App\RoleUser::where('role_id', '1')->count();
        //$onlineUsers = \App\User::active()->online()->count();
        $totalVendors = \App\RoleUser::where('role_id', '2')->count();
        $services = \App\Service::count();
        $bookings = \App\Booking::count();
        $transactions = \App\Transaction::count();
        $enquiries = \App\Enquiry::count();
        
        return view('admin/dashboard',  compact('users','bookings','transactions','services','enquiries', 'totalVendors'));
    }
}