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

class BookingController extends Controller {

    /**
     * Booking Model
     * @var Booking
     */
    protected $booking;
    protected $pageLimit;
    protected $serviceList;

    /**
     * Inject the models.
     * @param Booking $booking
     */
    public function __construct(Booking $booking) {
        $this->booking = $booking;
        $this->pageLimit = config('settings.pageLimit');
        $this->serviceList = ['' => 'Select Service'] + Service::pluck('title', 'id')->all();
        $this->userList = ['' => 'Select User'] + User::select(DB::raw("CONCAT(firstname, ' ',lastname) as name"), 'id')->pluck('name', 'id')->toArray();
    }

    /**
     * Display a listing of bookings
     *
     * @return Response
     */
    public function index() {
        //remove search array

        session()->forget('SEARCH');
        //end code

        $serviceList = $this->serviceList;
        $userList = $this->userList;
        $status = array(
            "orderPlaced" => Booking::orderPlaced,
            "venderAssigned" => Booking::venderAssigned,
            "venderOnTheWay" => Booking::venderOnTheWay,
            "orderInProgres" => Booking::orderInProgres,
            "orderCompleted" => Booking::orderCompleted,
            "orderCanceled" => Booking::orderCanceled,
            "orderRefund" => Booking::orderRefund,
            "extentionPending" => Booking::extentionPending,
            "extentionCompleted" => Booking::extentionCompleted,
            "extentionRejected" => Booking::extentionRejected,
            "venderArived" => Booking::venderArived,
            "rescheduled" => Booking::rescheduled,
            "onHold" => Booking::onHold,
        );

        $userId = request()->segment(3);

        if ($userId) {
            $usr = User::find($userId);
            if ($usr->hasRole('vendor')) {

                $bookings = Booking::venderBy($userId)->orderBy('created_at', 'DESC')->paginate($this->pageLimit);
            } else {

                $bookings = Booking::userBy($userId)->orderBy('created_at', 'DESC')->paginate($this->pageLimit);
            }
            // Grab user the bookings
            //$bookings = Booking::userBy($userId)->orderBy('created_at', 'DESC')->paginate($this->pageLimit);
        } else {
            // Grab all the bookings

            $bookings = Booking::orderBy('created_at', 'DESC')->paginate($this->pageLimit);
        }
        $totalBookings = Booking::select('id')->get();
        $bookingCount = $totalBookings->count();
        return view('admin/bookingList', compact('bookings', 'serviceList', 'userList', 'status', 'bookingCount'));
    }

    /**
     * Display the specified booking.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        $booking = Booking::findOrFail($id);
        $couponDetails = CouponHistory::where('booking_id', $id)->first();
        return view('admin/bookingDetails', compact('booking', 'couponDetails'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        Booking::destroy($id);

        session()->flash('success_message', trans('admin/booking.booking_delete_message'));
        $array = array();
        $array['success'] = true;
        //$array['message'] = 'Booking deleted successfully!';
        echo json_encode($array);
    }

    public function changeBookingStatus(Request $request) {

        $data = $request->all();
        $booking = Booking::find($data['id']);
        $booking->is_onhold = $data['value'];
        $booking->save();

        $bookingHistory = new BookingStatusHistory();
        $bookingHistory->booking_id = $booking->id;

        $bookingHistory->status_id = $data['value'] ? 14 : $booking->status_id;
        $bookingHistory->user_type = 'admin';
        $bookingHistory->save();
        $userEmail = isset($booking->user->email) ? $booking->user->email : '';
        $venderEmail = isset($booking->vender->email) ? $booking->vender->email : '';
        //send booking mail to user and bcc to admin
        if ($venderEmail) {
            Mail::to($venderEmail)->send(new BookingStatusMail($booking));
        }
        if ($userEmail) {
            Mail::to($userEmail)->send(new BookingStatusMail($booking));
        }

        if ($booking->user_id) {

            $message = 'Assist has changed status of your booking to hold.';
            if ($data['value'] == '0') {
                $message = 'Assist has changed status of your booking to previous status.';
            }

            Notification::createNotification($booking->id, Notification::bookingOnHoldStatus, Notification::bookinOnHold, $message, $booking->user_id);
        }
        if (isset($booking->vender_id) && $booking->vender_id != '') {
            $message = 'Assist has changed status of your booking to hold. Please contact to admin if you have any query';
            if ($data['value'] == '0') {
                $message = 'Assist has changed status of your booking to previous status.';
            }
            Notification::createNotification($booking->id, Notification::bookingOnHoldStatus, Notification::bookinOnHold, $message, $booking->vender_id);
        }
        session()->flash('success_message', trans('admin/booking.booking_status_message'));
        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/booking.booking_status_message');
        echo json_encode($array);
    }

    /**
     * search booking from database.
     * 
     * @author Dhaval
     * @param  Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request) {

        //reset search
        if ($request->has('reset')) {
            $request->session()->forget('SEARCH');
            return redirect('admin/booking');
        }
        //end code

        if ($request->get('search_by') != '') {
            session(['SEARCH.SEARCH_BY' => trim($request->get('search_by'))]);
        }

        if ($request->get('search_txt') != '') {
            session(['SEARCH.SEARCH_TXT' => trim($request->get('search_txt'))]);
        }

        if ($request->session()->get('SEARCH.SEARCH_BY') != '') {


            $query = Booking::select('*');

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'service') {
//                $query->where('service_id', $request->session()->get('SEARCH.SERVICE_ID'));
                $query->where('service_name', 'LIKE', '%' . $request->session()->get('SEARCH.SEARCH_TXT') . '%');
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'id') {
//                $query->where('service_id', $request->session()->get('SEARCH.SERVICE_ID'));
                $query->where('id', '=', $request->session()->get('SEARCH.SEARCH_TXT'));
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'user') {
//                $query->where('user_id', $request->session()->get('SEARCH.USER_ID'));

                $getUser_id = User::select('id')->where('firstname', 'LIKE', '%' . $request->session()->get('SEARCH.SEARCH_TXT') . '%')->get()->toArray();
                $user_type = '';
                $query = '';

                $query = Booking::select('*');
                if ($getUser_id) {
                    //echo $getUser_id->count();
                    $ids = array_column($getUser_id, 'id');
                    if ($ids) {
                        $query->whereIn('user_id', $ids);
                    }
                } else {
                    $query->where('phone', 'LIKE', '%' . $request->session()->get('SEARCH.SEARCH_TXT') . '%');
                }
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'vendor') {
                $query->where('vender_name', 'LIKE', '%' . $request->session()->get('SEARCH.SEARCH_TXT') . '%');
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'online') {
                $query->where('payment_type', '=', 0);
            }
            if ($request->session()->get('SEARCH.SEARCH_BY') == 'offline') {
                $query->where('payment_type', '=', 1);
            }

//            if ($request->session()->get('SEARCH.SEARCH_BY') == 'booking_date') {
//                $date = date('Y-m-d',strtotime($request->session()->get('SEARCH.SEARCH_DATE')));
//                
//                $queryDate = BookingDetail::select('booking_id');
//                $queryDate->where(DB::raw("date(start_time)"),'=',$date);
//                $bookingIdArray = $queryDate->orderBy('start_time', 'DESC')->get()->toArray();
//                $query->whereIn('id', $bookingIdArray);
//            }

            $bookings = $query->orderBy('created_at', 'desc')->paginate($this->pageLimit);


//            $bookings = $query->orderBy('created_at', 'DESC')->toSql();
//            echo $bookings;
//            $bindings = $query->getBindings();
//            dd($bindings);

            $serviceList = $this->serviceList;
            $userList = $this->userList;
            return view('admin/bookingList', compact('bookings', 'serviceList', 'userList'));
        } else {
            return redirect('admin/booking');
        }
    }

    /**
     *  export transactions-list.csv
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=bookings-list.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Service', 'Name', 'Email', 'Mobile', 'Credits', 'Date', 'Booking Status'));
        $booking_status = array('0'=>'Booking not in hold', '1' => 'Order placed', '2' => 'Vendor assigned', '3' => 'Vendor on the way', '4' => 'Order in progress', '5' => 'Order completed', '6' => 'Order cancelled','7'=>'Order refund', '8' => 'Extension pending', '9' => 'Extension completed', '10' => 'Extension rejected', '11' => 'Vendor arrived','13'=>'booking reschedules','14'=>'Booking on hold');

        $bookings = Booking::orderBy('created_at', 'DESC')->get();

        foreach ($bookings as $data) {
            $date = '';
            $spots = $data->bookingDetail;
            foreach ($spots as $key => $spot) {
                $date .= ' (' . ($key + 1) . ') ' . date('d-m-Y h:i A', strtotime($spot->start_time)) . 'to' . date('d-m-Y h:i A', strtotime($spot->end_time));
            }
            fputcsv($output, array(
                $data->service->title,
                $data->full_name,
                $data->email,
                $data->phone,
                $data->amount,
                $date,
                $booking_status[$data->status_id]
                    )
            );
        }
        fclose($output);
        exit;
    }

    /**
     * 
     * @return type
     * @description getnerate map of total earning by all vendors and admin
     */
    public function getBookigReport() {

        $chart = new BookingReport;
        $vendors = $this->getVendorsForFilter();
        $chart->labels(['Jan', 'Feb', 'march', 'April', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec']);
        $chart->dataset('Vendor Earnings', 'line', [$this->getMonthBooking(1), $this->getMonthBooking(2), $this->getMonthBooking(3), $this->getMonthBooking(4), $this->getMonthBooking(5), $this->getMonthBooking(6), $this->getMonthBooking(4), $this->getMonthBooking(8), $this->getMonthBooking(9), $this->getMonthBooking(10), $this->getMonthBooking(11), $this->getMonthBooking(12)]);

        $chart->dataset('Admin Earnings', 'line', [$this->getAdminMonthBooking(1), $this->getAdminMonthBooking(2), $this->getAdminMonthBooking(3), $this->getAdminMonthBooking(4), $this->getAdminMonthBooking(5), $this->getAdminMonthBooking(6), $this->getAdminMonthBooking(4), $this->getAdminMonthBooking(8), $this->getAdminMonthBooking(9), $this->getAdminMonthBooking(10), $this->getAdminMonthBooking(11), $this->getAdminMonthBooking(12)]);



        return view('admin/bookingReport', compact('vendors', 'chart'));
    }

    private function getMonthBooking($month, $Year = '') {

        if (!$Year) {
            $Year = date('Y');
        }
        return $booking = Booking::whereMonth('created_at', $month)->whereYear('created_at', $Year)->sum('total_price');
    }

    private function getAdminMonthBooking($month, $Year = '') {
        if (!$Year) {
            $Year = date('Y');
        }
        $booking = Booking::select('id')->whereMonth('created_at', $month)->whereYear('created_at', $Year)->get()->toArray();


        if (count($booking) > 0) {
            $ids = array_column($booking, 'id');
            return $amount = Transaction::whereIn('booking_id', $ids)->sum('admin_amount');
        } else {
            return 0;
        }
    }

    public function getFilteredBookigReport(Request $request) {


        if ($request->has('reset')) {
            $request->session()->forget('SEARCH');
            return redirect('admin/booking-report');
        }
        $vendors = $this->getVendorsForFilter();
        $month = $request['month'] ? $request['month'] : '';
        $year = $request['year'] ? $request['year'] : '';
        $vender_id = $request['vender_id'] ? $request['vender_id'] : '';
        
        session(['SEARCH.MONTH' => trim($month)]);
        session(['SEARCH.YEAR' => trim($year)]);
        session(['SEARCH.VENDOR' => trim($vender_id)]);
         
        $days = array();
        if ($month) {
            $d = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
            for ($i = 1; $i <= $d; $i++) {
                $days[] = $i;
            }
        }
        $chart = '';
        $chart = new BookingReport;
        if ($month) {


            $chart->labels($days);
        }
        if ($year) {


            $chart->labels(['Jan', 'Feb', 'march', 'April', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec']);
        }
        // Total earning in a month
        if ($month != '' && $year == '' && $vender_id == '') {
            
            $chart = new BookingReport;
            $year = Date('Y');
            $monthBooking = Booking::whereMonth('created_at', $month)->whereYear('created_at', $year)->groupBy('booking_date')->get()->toArray();
            
            $booking_dates =array();
            foreach($monthBooking as $monthbook) {
                $booking_dates[] = date('Y-m-d', strtotime($monthbook['booking_date']));
            }
            
            $chart->labels($booking_dates);
            $bookings = $this->getMonthEarning($month);
            
            if ($bookings) {
                $chart->dataset('Vendor Earnings', 'line', $bookings['vendorEarnings']);
                $chart->dataset('Admin Earnings', 'line', $bookings['adminEarnings']);
            }
            $chart->dataset('Vendor Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
            $chart->dataset('Admin Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
        }

        // Total earning in a Year
        if ($month == '' && $year != '' && $vender_id == '') {
            
            $chart->dataset('Earnings', 'line', [$this->getMonthBooking(1, $year), $this->getMonthBooking(2, $year), $this->getMonthBooking(3, $year), $this->getMonthBooking(4, $year), $this->getMonthBooking(5, $year), $this->getMonthBooking(6, $year), $this->getMonthBooking(4, $year), $this->getMonthBooking(8, $year), $this->getMonthBooking(9, $year), $this->getMonthBooking(10, $year), $this->getMonthBooking(11, $year), $this->getMonthBooking(12, $year)]);

            $chart->dataset('Admin Earnings', 'line', [$this->getAdminMonthBooking(1, $year), $this->getAdminMonthBooking(2, $year), $this->getAdminMonthBooking(3, $year), $this->getAdminMonthBooking(4, $year), $this->getAdminMonthBooking(5, $year), $this->getAdminMonthBooking(6, $year), $this->getAdminMonthBooking(7, $year), $this->getAdminMonthBooking(8, $year), $this->getAdminMonthBooking(9, $year), $this->getAdminMonthBooking(10, $year), $this->getAdminMonthBooking(11, $year), $this->getAdminMonthBooking(12, $year)]);
            $chart->labels(['Jan', 'Feb', 'march', 'April', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec']);
        }

        // Total earning By Vendor 
        if ($month == '' && $year == '' && $vender_id  != '') {

            $bookings = Booking::where('vender_id', $vender_id)->groupBy('booking_date')->get()->toArray();
            $book = array_column($bookings, 'booking_date');
            // create chart labels
            $chart->labels($book);
            $bookings = $this->getVendorEarning($bookings);
            $chart->dataset('Vendor Earnings', 'line', $bookings['vendorEarnings']);
            $chart->dataset('Admin Earnings', 'line', $bookings['adminEarnings']);
        }

        if ($month != '' && $year != '' && $vender_id == '') {

            $bookings = $this->getMonthYearEarning($month, $year);
            if ($bookings) {
                $chart->dataset('Vendor Earnings', 'line', $bookings['vendorEarnings']);
                $chart->dataset('Admin Earnings', 'line', $bookings['adminEarnings']);
            } else {
                $chart->dataset('Vendor Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
                $chart->dataset('Admin Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
            }
        }

        if ($month != '' && $vender_id != '' && $year == '') {

            $bookings = $this->getVendorMonthEarning($month, $vender_id);
            if ($bookings) {
                $chart->dataset('Vendor Earnings', 'line', $bookings['vendorEarnings']);
                $chart->dataset('Admin Earnings', 'line', $bookings['adminEarnings']);
            }
            $chart->dataset('Vendor Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
            $chart->dataset('Admin Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
        }

        if ($year != '' && $vender_id != '' && $month == '') {

            $bookings = $this->getVendorYearEarning($year, $vender_id);
            if ($bookings) {
                $chart->dataset('Vendor Earnings', 'line', $bookings['vendorEarnings']);
                $chart->dataset('Admin Earnings', 'line', $bookings['adminEarnings']);
            } else {
                $chart->dataset('Vendor Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
                $chart->dataset('Admin Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
            }
        }

        if ($month != '' && $year != '' && $vender_id != '') {

            $bookings = $this->getVendorMonthYearEarning($month, $year, $vender_id);
            if ($bookings) {
                $chart->dataset('Vendor Earnings', 'line', $bookings['vendorEarnings']);
                $chart->dataset('Admin Earnings', 'line', $bookings['adminEarnings']);
            } else {
                $chart->dataset('Vendor Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
                $chart->dataset('Admin Earning', 'line', ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']);
            }
        }
        //$this->getFilteredRecords($month, $year, $vender_id);

        return view('admin/bookingReport', compact('vendors', 'chart'));
    }

    private function getVendorEarning($bookings) {
        $bookings = array_column($bookings, 'booking_date');
        $vendorEarnings = array();
        $adminEarnings = array();
        $bookingData = array();
        foreach ($bookings as $bookingDate) {

            $booking = Booking::select('id')->where('booking_date', $bookingDate)->get()->toArray();
            $amount = '';
            if (count($booking) > 0) {
                $ids = array_column($booking, 'id');
                $bookingData['vendorEarnings'][] = Transaction::whereIn('booking_id', $ids)->sum('vender_amount');
                $bookingData['adminEarnings'][] = Transaction::whereIn('booking_id', $ids)->sum('admin_amount');
            }
        }

        return $bookingData;
    }

    private function getMonthEarning($parameter) {

        $bookings = array();
        $year = Date('Y');
        $booking = Booking::whereMonth('created_at', $parameter)->whereYear('created_at', $year)->groupBy('booking_date')->get()->toArray();
        
        $bookDates = array_column($booking, 'booking_date');
        $bookings = array_column($booking, 'id');
        $vendorEarnings = array();
        $adminEarnings = array();
        $bookingData = array();
        foreach ($bookDates as $bookingDate) {
            $booking = Booking::select('id')->where('booking_date', $bookingDate)->get()->toArray();
            $amount = '';
            if (count($booking) > 0) {
                $ids = array_column($booking, 'id');
                $bookingData['vendorEarnings'][] = Transaction::whereIn('booking_id', $ids)->sum('vender_amount');
                $bookingData['adminEarnings'][] = Transaction::whereIn('booking_id', $ids)->sum('admin_amount');
            }
        }
        return $bookingData;
    }

    private function getVendorMonthEarning($month, $vender_id) {

        $bookings = array();
        $year = Date('Y');
        $booking = Booking::where('vender_id', $vender_id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get()->toArray();
        $bookDates = array_column($booking, 'booking_date');
        $bookings = array_column($booking, 'id');
        $vendorEarnings = array();
        $adminEarnings = array();
        $bookingData = array();
        foreach ($bookDates as $bookingDate) {
            $booking = Booking::select('id')->where('booking_date', $bookingDate)->get()->toArray();
            $amount = '';
            if (count($booking) > 0) {
                $ids = array_column($booking, 'id');
                $bookingData['vendorEarnings'][] = Transaction::whereIn('booking_id', $ids)->sum('vender_amount');
                $bookingData['adminEarnings'][] = Transaction::whereIn('booking_id', $ids)->sum('admin_amount');
            }
        }
        return $bookingData;
    }

    private function getMonthYearEarning($month, $year) {

        $bookings = array();
        $booking = Booking::whereMonth('created_at', $month)->whereYear('created_at', $year)->get()->toArray();
        $bookDates = array_column($booking, 'booking_date');
        $bookings = array_column($booking, 'id');
        $vendorEarnings = array();
        $adminEarnings = array();
        $bookingData = array();
        foreach ($bookDates as $bookingDate) {
            $booking = Booking::select('id')->where('booking_date', $bookingDate)->get()->toArray();
            $amount = '';
            if (count($booking) > 0) {
                $ids = array_column($booking, 'id');
                $bookingData['vendorEarnings'][] = Transaction::whereIn('booking_id', $ids)->sum('vender_amount');
                $bookingData['adminEarnings'][] = Transaction::whereIn('booking_id', $ids)->sum('admin_amount');
            }
        }
        return $bookingData;
    }

    private function getVendorMonthYearEarning($month, $year, $vender_id) {

        $bookings = array();
        $booking = Booking::where('vender_id', $vender_id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get()->toArray();
        $bookDates = array_column($booking, 'booking_date');
        $bookings = array_column($booking, 'id');
        $vendorEarnings = array();
        $adminEarnings = array();
        $bookingData = array();
        foreach ($bookDates as $bookingDate) {
            $booking = Booking::select('id')->where('booking_date', $bookingDate)->get()->toArray();
            $amount = '';
            if (count($booking) > 0) {
                $ids = array_column($booking, 'id');
                $bookingData['vendorEarnings'][] = Transaction::whereIn('booking_id', $ids)->sum('vender_amount');
                $bookingData['adminEarnings'][] = Transaction::whereIn('booking_id', $ids)->sum('admin_amount');
            }
        }
        return $bookingData;
    }

    private function getVendorYearEarning($year, $vender_id) {

        $bookings = array();
        $booking = Booking::where('vender_id', $vender_id)->whereYear('created_at', $year)->get()->toArray();
        $bookDates = array_column($booking, 'booking_date');
        $bookings = array_column($booking, 'id');
        $vendorEarnings = array();
        $adminEarnings = array();
        $bookingData = array();
        foreach ($bookDates as $bookingDate) {
            $booking = Booking::select('id')->where('booking_date', $bookingDate)->get()->toArray();
            $amount = '';
            if (count($booking) > 0) {
                $ids = array_column($booking, 'id');
                $bookingData['vendorEarnings'][] = Transaction::whereIn('booking_id', $ids)->sum('vender_amount');
                $bookingData['adminEarnings'][] = Transaction::whereIn('booking_id', $ids)->sum('admin_amount');
            }
        }
        return $bookingData;
    }

    private function getVendorsForFilter() {
        $users = User::with(['roles' => function($q) {
                        $q->where('slug', 'vendor');
                    }])->get()->pluck('id', 'firstname');
                    
        foreach($users as $key => $user) {
            $checUser = Booking::where('vender_id', $user)->first();
            if(!isset($checUser->id)) {
                unset($users[$key]);
            }
        }
        return $users;
    }

}
