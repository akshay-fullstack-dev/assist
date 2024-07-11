<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Helpers\Datatable\SSP;
use App\Helpers\Common;
use App\Service;
use App\User;
use App\Booking;
use App\BookingDetail;
use App\Transaction;
use Validator;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\Admin\BookingStatusMail;
use App\BookingStatusHistory;
use App\Notification;
use App\CouponHistory;
use App\Charts\BookingReport;
use Illuminate\Support\Facades\Session;

class ReportingController extends Controller {

    private $status = [6, 13, 7, 12];

    public function getBookigReport() {

        $chart = new BookingReport;
        $vendors = $this->getVendorsForFilter();
        $agencys = $this->getAgencyForFilter();
        
        $totalVendorEarning = sprintf('%0.2f', Transaction::sum('vender_amount'));
        $totalAdminEarning = sprintf('%0.2f', Transaction::sum('admin_amount'));

        $chart->labels(['Jan', 'Feb', 'March', 'April', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec']);
        $chart->dataset('Vendor Earnings', 'line', [$this->getMonthBooking(1), $this->getMonthBooking(2), $this->getMonthBooking(3), $this->getMonthBooking(4), $this->getMonthBooking(5), $this->getMonthBooking(6), $this->getMonthBooking(4), $this->getMonthBooking(8), $this->getMonthBooking(9), $this->getMonthBooking(10), $this->getMonthBooking(11), $this->getMonthBooking(12)]);

        $chart->dataset('Admin Earnings', 'line', [$this->getAdminMonthBooking(1), $this->getAdminMonthBooking(2), $this->getAdminMonthBooking(3), $this->getAdminMonthBooking(4), $this->getAdminMonthBooking(5), $this->getAdminMonthBooking(6), $this->getAdminMonthBooking(4), $this->getAdminMonthBooking(8), $this->getAdminMonthBooking(9), $this->getAdminMonthBooking(10), $this->getAdminMonthBooking(11), $this->getAdminMonthBooking(12)]);



        return view('admin/bookingReport', compact('vendors', 'chart', 'agencys', 'totalVendorEarning', 'totalAdminEarning'));
    }

    private function getMonthBooking($month, $Year = '', $vendor_ids = array()) {

        if (!$Year) {
            $Year = date('Y');
        }
        if (empty($vendor_ids)) {
            $booking = Booking::whereMonth('created_at', $month)->whereYear('created_at', $Year)->whereNotIn('status_id', $this->status)->sum('total_price');
            return sprintf('%0.2f', $booking);
        } else {
            $booking = Booking::whereIn('vender_id', $vendor_ids)->whereMonth('created_at', $month)->whereYear('created_at', $Year)->whereNotIn('status_id', $this->status)->sum('total_price');
             
            return sprintf('%0.2f', $booking);
        }
    }

    private function getAdminMonthBooking($month, $Year = '', $vendor_ids = array()) {
        if (!$Year) {
            $Year = date('Y');
        }
        $booking = '';
        if (empty($vendor_ids)) {
            $booking = Booking::select('id')->whereMonth('created_at', $month)->whereYear('created_at', $Year)->get()->toArray();
        } else {
            $booking = Booking::select('id')->whereIn('vender_id', $vendor_ids)->whereMonth('created_at', $month)->whereYear('created_at', $Year)->get()->toArray();
        }


        if (count($booking) > 0) {
            $ids = array_column($booking, 'id');
            $amount = Transaction::whereIn('booking_id', $ids)->sum('admin_amount');
            return sprintf('%0.2f', $amount);
        } else {
            return 0;
        }
    }

    public function getFilteredBookigReport(Request $request) {

        if ($request->has('reset')) {
            $request->session()->forget('SEARCH');
            return redirect('admin/booking-report');
        }
        //$adminEarnings = Transaction::sum('admin_amount'); 
        $vendors = $this->getVendorsForFilter();
        $agencys = $this->getAgencyForFilter();

        $month = $request['month'] ? $request['month'] : '';
        $year = $request['year'] ? $request['year'] : '';

        $vender_id = $request['vender_id'] ? $request['vender_id'] : '';
        $agency_id = $request['agency_id'] ? $request['agency_id'] : '';
        $search_type = $request['search_type'];
        $this->makeSessions($request, $month, $year, $search_type, $vender_id, $agency_id);
        $vendor_ids = $this->getVendorsIds($search_type, $vender_id, $request['agency_id']);

        if ($month) {
            $d = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
            for ($i = 1; $i <= $d; $i++) {
                $days[] = $i;
            }
        }
        $chart = new BookingReport;

        if ($month) {
            $chart->labels($days);
        } else {
            $chart->labels(['Jan', 'Feb', 'March', 'April', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec']);
        }
        // Total earning in a month
        $totalVendorEarning = $this->totalEarningAmount($month, $year, $vendor_ids, 1);
        $totalAdminEarning = $this->totalEarningAmount($month, $year, $vendor_ids);
        
        if ($month == '' && $year == '' && empty($vendor_ids)) {

            return $this->getBookigReport();
        }
        if ($month != '' && $year == '' && empty($vendor_ids)) {

            unset($chart);
            $chart = new BookingReport;
            $year = Date('Y');
            $monthBookings = Booking::select(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as created_at"))
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year)->groupBy('created_at')->get();
            $bookDates = array_unique(array_column($monthBookings->toArray(), 'created_at'));
            $booking_dates = array();
            foreach ($bookDates as $monthbook) {
                $booking_dates[] = date('Y-m-d', strtotime($monthbook));
            }
            $chart->labels($booking_dates);
            $bookings = $this->getMonthEarning($month, $bookDates);
        }
        // Total earning in a Year
        if ($month == '' && $year != '' && empty($vendor_ids)) {

            $bookings = $this->getYearEarning($year);
        }
        // Total earning By Vendor 
        
        if ($month == '' && $year == '' && !empty($vendor_ids)) {
            
            $bookings = $this->getVendorEarning($vendor_ids);
        }

        if ($month != '' && $year != '' && empty($vendor_ids)) {

            $monthBooking = Booking::select('created_at')
                            ->whereNotIn('status_id', $this->status)
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year)
                            ->groupBy('created_at')->get();

            $booking_dates = array();
            foreach ($monthBooking as $monthbook) {
                $booking_dates[] = date('Y-m-d', strtotime($monthbook->created_at));
            }
            $booking_dates = array_unique($booking_dates);
            $chart->labels(array_values($booking_dates));
            $bookings = $this->getMonthYearEarning($booking_dates);
        }

        if ($month != '' && !empty($vendor_ids) && $year == '') {

            $year = Date('Y');
            $monthBooking = Booking::select('created_at')
                            ->whereIn('vender_id', $vendor_ids)
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year)
                            ->groupBy('created_at')->get();
            $booking_dates = array();
            foreach ($monthBooking as $monthbook) {
                $booking_dates[] = date('Y-m-d', strtotime($monthbook['created_at']));
            }
            $booking_dates = array_values(array_unique($booking_dates));
            $chart->labels(array_values($booking_dates));
            $bookings = $this->getVendorMonthEarning($month, $vendor_ids, $booking_dates);
        }

        if ($year != '' && !empty($vendor_ids) && $month == '') {
            $bookings = $this->getVendorYearEarning($year, $vendor_ids);
        }

        if ($month != '' && $year != '' && !empty($vendor_ids)) {

            unset($chart);
            $chart = new BookingReport;
            $monthBooking = Booking::select('created_at')
                            ->whereNotIn('status_id', $this->status)
                            ->whereIn('vender_id', $vendor_ids)
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year)
                            ->groupBy('created_at')
                            ->get()->toArray();

            $booking_dates = array();
            $label = array();

            foreach ($monthBooking as $monthbook) {
                $booking_dates[] = date('Y-m-d', strtotime($monthbook['created_at']));
            }
            $booking_dates = array_unique($booking_dates);

            $chart->labels(array_values($booking_dates));
            $bookings = $this->getVendorMonthYearEarning($month, $year, $vendor_ids, $booking_dates);
        }

        if ($bookings) {
            $chart->dataset('Vendor Earnings', 'line', $bookings['vendorEarnings']);
            $chart->dataset('Admin Earnings', 'line', $bookings['adminEarnings']);
        }

        if (empty($bookings) && $year == '') {
            $chart->dataset('Vendor Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
            $chart->dataset('Admin Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
        }
        if (empty($bookings) && $year != '') {
            $chart->dataset('Vendor Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
            $chart->dataset('Admin Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
        }
        return view('admin/bookingReport', compact('vendors', 'chart', 'agencys', 'totalVendorEarning', 'totalAdminEarning'));
    }

    private function makeSessions($request, $month, $year, $search_type, $vender_id, $agency_id) {
        session(['SEARCH.MONTH' => trim($month)]);
        session(['SEARCH.YEAR' => trim($year)]);
        session(['SEARCH.SEARCHTYPE' => trim($search_type)]);

        if ($search_type == 0) {

            session(['SEARCH.AGENCY' => trim($agency_id)]);
            $request->session()->forget('SEARCH.VENDER');
            $request->session()->forget('SEARCH.AGENCY');
        } else if ($search_type == 1) {

            session(['SEARCH.VENDER' => trim($vender_id)]);
            $request->session()->forget('SEARCH.AGENCY');
        } else if ($search_type == 2) {
            session(['SEARCH.AGENCY' => trim($agency_id)]);
            $request->session()->forget('SEARCH.VENDER');
        }
    }

    private function getVendorsIds($search_type, $vender_id, $agency_id) {

        if ($search_type == '0') {
            
            return $vendor_ids = $this->getVendor();
        }

        if ($search_type == '1') {

            if ($vender_id) {
                return $vendor_ids = $this->getVendor($vender_id);
            } else {
                return $vendor_ids = $this->getVendor();
            }
        }
        if ($search_type == '2') {

            $user_id = $agency_id ? $agency_id : '';
            if ($user_id) {
                return $vendor_ids = $this->getAgencyVendors($user_id);
            } else {
                return $vendor_ids = $this->getAgencyVendors();
            }
        }
    }

    /**
     * 
     * @param type $month
     * @param type $year
     * @param type $vendor_ids
     * @param type $type // on base of this we decide whose total will be returned '' = admin amount, 1 = vender amount
     * @return type
     */
    private function totalEarningAmount($month, $year, $vendor_ids, $type = '') {

        $amount = '';
//        $bookingIds = Booking::select('id')->whereNotIn('status_id', $this->status)->get();
//         
//        $bookings = array_column($bookingIds->toArray(), 'id');

        $amount = Transaction::select('admin_amount');
        if ($type) {
            $amount = Transaction::select('vender_amount');
        }
        if ($month) {
            $amount->whereMonth('created_at', $month);
        }
        if ($year) {
            $amount->whereYear('created_at', $year);
        }
        if (!empty($vendor_ids)) {
            $bookings = Booking::select('id')->whereIn('vender_id', $vendor_ids)->whereNotIn('status_id', $this->status)->get();
            $bookingIds = array();
            if ($bookings->count()) {
                $bookingIds = array_column($bookings->toArray(), 'id');
            }
            if ($bookingIds) {
                $amount->whereIn('booking_id', $bookingIds);
            }
        }
        $amount->whereIn('booking_id', $bookings);
        if ($type) {
            return sprintf('%0.2f', $amount->sum('vender_amount'));
        } else {
            return sprintf('%0.2f', $amount->sum('admin_amount'));
            
        }
    }

    private function getVendorEarning($vendor_ids) {

        $bookingData = array();
        for ($i = 1; $i <= 12; $i++) {

            $booking = Booking::select('id')->whereIn('vender_id', $vendor_ids)->whereMonth('created_at', $i)->get()->toArray();
            $amount = '';
            if (count($booking) > 0) {
                $ids = array_column($booking, 'id');
                $bookingData['vendorEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('vender_amount'));
                $bookingData['adminEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('admin_amount'));
            } else {
                $bookingData['vendorEarnings'][] = 0;
                $bookingData['adminEarnings'][] = 0;
            }
        }

        return $bookingData;
    }

    private function getMonthEarning($parameter, $bookDates) {

        $bookingData = array();
        foreach ($bookDates as $bookingDate) {
            $booking = Booking::select('id')->where('created_at', $bookingDate)->get()->toArray();
            $amount = '';
            if (count($booking) > 0) {
                $ids = array_column($booking, 'id');
                $bookingData['vendorEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('vender_amount'));
                $bookingData['adminEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('admin_amount'));
            }
        }
        return $bookingData;
    }

    private function getYearEarning($year) {

        $bookingData = array();
        for ($i = 1; $i <= 12; $i++) {
            $booking = Booking::select('id')->whereNotIn('status_id', $this->status)->whereMonth('created_at', $i)->whereYear('created_at', $year)->get()->toArray();
            $amount = '';

            $ids = array_column($booking, 'id');
            $bookingData['vendorEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('vender_amount'));
            $bookingData['adminEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('admin_amount'));
        }
        return $bookingData;
    }

    private function getVendorMonthEarning($month, $vendor_ids, $booking_dates) {

        $year = Date('Y');
        $bookingData = array();
        foreach ($booking_dates as $booking) {
            $booking = Booking::select('id')->whereNotIn('status_id', $this->status)
                            ->whereIn('vender_id', $vendor_ids)
                            ->where('created_at', 'Like', '%' . $booking . '%')->get()->toArray();

            $ids = array_column($booking, 'id');
            $bookingData['vendorEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('vender_amount'));
            $bookingData['adminEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('admin_amount'));
        }
        return $bookingData;
    }

    private function getMonthYearEarning($bookDates) {

        $bookingData = array();
        foreach ($bookDates as $bookingDate) {
            $booking = Booking::select('id')->whereNotIn('status_id', $this->status)->where('created_at', 'like', '%' . $bookingDate . '%')->get()->toArray();

            $amount = '';
            if (count($booking) > 0) {
                $ids = array_column($booking, 'id');
                $bookingData['vendorEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('vender_amount'));
                $bookingData['adminEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('admin_amount'));
            }
        }

        return $bookingData;
    }

    private function getVendorMonthYearEarning($month, $year, $vendor_ids, $booking_dates) {

        $bookingData = array();
        foreach ($booking_dates as $booking) {
            $book = Booking::select('id')->whereNotIn('status_id', $this->status)
                            ->whereIn('vender_id', $vendor_ids)
                            ->where('created_at', 'Like', '%' . $booking . '%')
                            ->get()->toArray();

            if (count($book) > 0) {
                $ids = array_column($book, 'id');
                $bookingData['vendorEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('vender_amount'));
                $bookingData['adminEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('admin_amount'));
            }
        }

        return $bookingData;
    }

    private function getVendorYearEarning($year, $vendor_ids) {

        $bookings = array();


        $bookingData = array();
        for ($i = 1; $i <= 12; $i++) {
            $booking = Booking::select('id')->whereIn('vender_id', $vendor_ids)->whereNotIn('status_id', $this->status)->whereMonth('created_at', $i)->whereYear('created_at', $year)->get()->toArray();
            $amount = '';
            $ids = array_column($booking, 'id');
            $bookingData['vendorEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('vender_amount'));
            $bookingData['adminEarnings'][] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('admin_amount'));
        }

        return $bookingData;
    }

    private function getVendorsForFilter() {
        $users = User::whereHas('roles', function ($q) {
                    $q->where('role_id', '=', '2');
                })->where('status', '1')->get()->pluck('id', 'firstname');

        /*foreach ($users as $key => $user) {
            $checUser = Booking::where('vender_id', $user)->first();
            if (!isset($checUser->id)) {
                unset($users[$key]);
            }
        }*/
        return $users;
    }

    private function getAgencyForFilter() {


        $users = User::whereHas('roles', function ($q) {
                    $q->where('role_id', '=', '3');
                })->where('status', '1')->get()->pluck('id', 'firstname');
        return $users;
    }

    private function getAgencyVendors($user_id = '') {
        $checkUser = array();
        if ($user_id) {
            $checkUser = User::whereHas('roles', function ($q) {
                        $q->where('role_id', '=', 2);
                    })->where('agency_id', $user_id)->where('status', '1')->get()->toArray();
        } else {
            $checkUser = User::whereHas('roles', function ($q) {
                        $q->where('role_id', '=', 2);
                    })->where('agency_id', '!=', '')->where('status', '1')->get()->toArray();
        }
         
        $users = array();
        if (!empty($checkUser)) {
            $users = array_column($checkUser, 'id');
        }
        return $users;
    }

    private function getVendor($getVendor = '') {
        $checkUser = array();
        if ($getVendor) {
            $checkUser = User::where('id', $getVendor)->where('status', '1')->get()->toArray();
        } else {
             
            $checkUser = User::whereHas('roles', function ($q) {
                        $q->where('role_id', '=', 2);
                    })->where('status', '1')->get()->toArray();
        }
         
        $users = array();
         
        if (!empty($checkUser)) {
            $users = array_column($checkUser, 'id');
        }
        return $users;
    }

}
