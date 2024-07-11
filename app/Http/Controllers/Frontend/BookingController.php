<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Service;
use App\Schedule;
use App\Booking;
use App\BookingDetail;
use App\User;
use Validator;
use DateTime;
use DatePeriod;
use DateInterval;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\Frontend\BookingMail;
use App\Mail\Admin\BookingStatusMail;
use App\CouponHistory;

class BookingController extends Controller
{

    protected $serviceList;
    protected $auth;
    private $pageLimit = 20;
    protected $userList;


    public function __construct()
    {
        $this->auth = auth()->guard('user');
        $this->pageLimit = config('settings.pageLimitFront');
        $this->serviceList = ['' => 'Select Service'] + Service::pluck('title', 'id')->all();
        $this->userList = ['' => 'Select User'] + User::select(DB::raw("CONCAT(firstname, ' ',lastname) as name"), 'id')->pluck('name', 'id')->toArray();
    }

    public function index(Request $request)
    {
        //remove search array
        session()->forget('SEARCH');
        $request->session()->put('agency_vendor_id', $request->id);
        $value = session('agency_vendor_id', 'default');
        //end code
        $serviceList = $this->serviceList;
        $user_id = $request->id;
        // ------------------------------------------------search functionality-----------------------------------------------------------
        if ($request->has('reset')) {
            $request->session()->forget('SEARCH');
            return redirect('agency/listBooking/' . $user_id);
        }
        //end code
        if ($request->get('search_by') != '') {
            session(['SEARCH.SEARCH_BY' => trim($request->get('search_by'))]);
        }

        if ($request->get('search_txt') != '') {
            session(['SEARCH.SEARCH_TXT' => trim($request->get('search_txt'))]);
        }

        if ($request->get('service_id') != '') {
            session(['SEARCH.SERVICE_ID' => trim($request->get('service_id'))]);
        }

        if ($request->get('search_date') != '') {
            session(['SEARCH.SEARCH_DATE' => trim($request->get('search_date'))]);
        }

        if ($request->session()->get('SEARCH.SEARCH_BY') != '') {
            $query = Booking::where('user_id', $user_id);

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'service') {
                $query->where('service_id', $request->session()->get('SEARCH.SERVICE_ID'));
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'name') {
                $query->where('full_name', 'LIKE', '"%' . $request->session()->get('SEARCH.SEARCH_TXT') . '%"');
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'email') {
                $query->where('email', 'LIKE', '"%' . $request->session()->get('SEARCH.SEARCH_TXT') . '%"');
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'phone') {
                $query->where('phone', 'LIKE', '"%' . $request->session()->get('SEARCH.SEARCH_TXT') . '%"');
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'booking_date') {
                $date = date('Y-m-d', strtotime($request->session()->get('SEARCH.SEARCH_DATE')));

                $queryDate = BookingDetail::select('booking_id');
                $queryDate->where(DB::raw("date(start_time)"), '=', $date);
                $bookingIdArray = $queryDate->orderBy('start_time', 'DESC')->get()->toArray();
                $query->whereIn('id', $bookingIdArray);
            }
            $bookings = $query->orderBy('created_at', 'desc')->paginate($this->pageLimit);
            $serviceList = $this->serviceList;
            return view('frontend.bookingList', compact('bookings', 'serviceList', 'user_id'));
        } else {

            // ------------------------------------------------end of search functionality----------------------------------------------------
            $bookings = Booking::where('vender_id', $user_id)->orderBy('created_at', 'DESC')->paginate($this->pageLimit);
            return view('frontend.bookingList', compact('bookings', 'serviceList', 'user_id'));
        }
    }
    public function show()
    { }
    /**
     * Store a newly created booking in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $rules = array(
            'full_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required'
        );
        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $service = Service::find($data['service_id']);
        $amount = $service->standard_price * count($request->get('spots'));

        //check available credit before booking
        $user_id = $this->auth->user()->id;
        $credit = $this->auth->user()->credit;
        if ($credit < $amount) {
            return redirect()->back()->with('error_message', trans('user/booking.booking_error_message'))->withInput();
        }
        //end code

        $data['user_id'] = $this->auth->user()->id;
        $data['amount'] = $amount;
        $data['status'] = 'pending';

        $booking = Booking::create($data);
        $lastInsertId = $booking->id;
        //$lastInsertId = 2;
        //update user credit
        $newCredit = $credit - $amount;
        User::where('id', $user_id)->update(array('credit' => $newCredit));
        //end code
        //store booking spots
        if ($request->get('spots')) {
            $resevationDate = date('Y-m-d', $request->get('reservation_date'));
            //echo $resevationDate;
            for ($i = 0; $i < count($request->get('spots')); $i++) {
                $timeArray = explode('-', $data['spots'][$i]);
                $start_time = $resevationDate . ' ' . $timeArray[0];
                $end_time = $resevationDate . ' ' . $timeArray[1];
                //echo $start_time."==".$end_time."<br>";
                $c = new BookingDetail;
                $c->booking_id = $lastInsertId;
                $c->start_time = $start_time;
                $c->end_time = $end_time;
                $c->save();
            }
        }
        //end code
        //send booking mail to user and bcc to admin
        Mail::to($this->auth->user()->email)
            ->bcc(config('settings.admin.email'))
            ->send(new BookingMail($booking));

        return redirect()->route('booking.index')->with('success_message', trans('user/booking.booking_success_message'));
    }

    /**
     * search booking from database.
     * 
     * @author Dhaval
     * @param  Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $user_id = $this->auth->user()->id;
        //reset search
        if ($request->has('reset')) {
            $request->session()->forget('SEARCH');
            return redirect('booking');
        }
        //end code

        if ($request->get('search_by') != '') {
            session(['SEARCH.SEARCH_BY' => trim($request->get('search_by'))]);
        }

        if ($request->get('search_txt') != '') {
            session(['SEARCH.SEARCH_TXT' => trim($request->get('search_txt'))]);
        }

        if ($request->get('service_id') != '') {
            session(['SEARCH.SERVICE_ID' => trim($request->get('service_id'))]);
        }

        if ($request->get('search_date') != '') {
            session(['SEARCH.SEARCH_DATE' => trim($request->get('search_date'))]);
        }

        if ($request->session()->get('SEARCH.SEARCH_BY') != '') {
            $query = Booking::where('user_id', $user_id);

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'service') {
                $query->where('service_id', $request->session()->get('SEARCH.SERVICE_ID'));
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'name') {
                $query->where('full_name', 'LIKE', '"%' . $request->session()->get('SEARCH.SEARCH_TXT') . '%"');
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'email') {
                $query->where('email', 'LIKE', '"%' . $request->session()->get('SEARCH.SEARCH_TXT') . '%"');
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'phone') {
                $query->where('phone', 'LIKE', '"%' . $request->session()->get('SEARCH.SEARCH_TXT') . '%"');
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'booking_date') {
                $date = date('Y-m-d', strtotime($request->session()->get('SEARCH.SEARCH_DATE')));

                $queryDate = BookingDetail::select('booking_id');
                $queryDate->where(DB::raw("date(start_time)"), '=', $date);
                $bookingIdArray = $queryDate->orderBy('start_time', 'DESC')->get()->toArray();
                $query->whereIn('id', $bookingIdArray);
            }
            $bookings = $query->orderBy('created_at', 'desc')->paginate($this->pageLimit);
            $serviceList = $this->serviceList;
            return view('frontend.bookingList', compact('bookings', 'serviceList', 'user_id'));
        } else {
            return redirect('agency/booking');
        }
    }

    /**
     *  export transactions-list.csv
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=bookings-list.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Service', 'Name', 'Email', 'Mobile', 'Credits', 'Date', 'Booking Status'));
        $user_id = $request['id'];
        $bookings = Booking::where('vender_id', $user_id)->orderBy('created_at', 'DESC')->get();
        foreach ($bookings as $data) {
            $date = '';
            $spots = $data->bookingDetail;
            foreach ($spots as $key => $spot) {
                $date .= ' (' . ($key + 1) . ') ' . date('d-m-Y h:i A', strtotime($spot->start_time)) . 'to' . date('d-m-Y h:i A', strtotime($spot->end_time));
            }
            fputcsv(
                $output,
                array(
                    $data->service->title,
                    $data->full_name,
                    $data->email,
                    $data->phone,
                    $data->amount,
                    $date,
                    $data->status->label
                )
            );
        }
        fclose($output);
        exit;
    }

    /**
     * check booking status and refund credit to user if their booking is still pending and date was passed.
     * 
     * @author Dhaval
     * @param  Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function cronBookingStatus()
    {
        //get pending bookings whose date has been passed
        $bookings = Booking::select('bookings.*', 'bd.start_time', 'bd.end_time')
            ->join('bookings_details as bd', 'bookings.id', '=', 'bd.booking_id')
            ->where('status', 'pending')
            ->where(DB::raw("date(start_time)"), '<=', date("Y-m-d"))->groupBy('bookings.id')->get();
        foreach ($bookings as $key => $booking) {
            $booking->status = 'cancel';
            $booking->save();
            $user = User::find($booking->user_id);
            $user->credit = $user->credit + $booking->amount;
            $user->save();

            $userEmail = $user > email;
            //send booking mail to user and bcc to admin
            Mail::to($userEmail)->send(new BookingStatusMail($booking));
        }
    }

    public function getEmployeeBooking($id)
    {
        $booking = Booking::findOrFail($id);
        $couponDetails = CouponHistory::where('booking_id', $booking['id'])->first();

        return view('frontend/booking', compact('booking', 'couponDetails'));
    }
    public function bookingList(Request $request)
    {
        // all services
        $serviceList = $this->serviceList;
        $agency_id = auth()->user()->id;
        // get all the vendor whcih ara assigned to this agency
        $vendors = User::select('id')->where('agency_id', $agency_id)->get();

        $userList = $vendors;

        $vendor_ids = array_column($vendors->toArray(), 'id');

        // if reqest comes from the form then implement search funtionality 
        if ($request->isMethod('post')) {

            $bookings_search = Booking::whereIn('vender_id', $vendor_ids);

            //  if search by booking date
            if ($request->search_by == 'booking_date') {
                $bookings_search->whereDate('booking_date', '=', $request->search_date);
            }
            // search based on service
            if ($request->search_by == 'service') {
                $bookings_search->where('service_id', $request->service_id);
            }
            // search based on name
            if ($request->search_by == 'name') {
                $bookings_search->where('full_name', 'like', '%' . $request->search_txt  . '%');
            }
            // search based on booking mail
            if ($request->search_by == 'email') {
                $bookings_search->where('email', 'like', '%' . $request->search_txt . '%' );
            }
            // search based on vendor
            if ($request->search_by == 'vendor_name') {
                $bookings_search->where('vender_name', 'like', '%' . $request->search_txt . '%' );
            }

            // searching based on the phone number
            if ($request->search_by == 'phone') {
                $bookings_search->where('phone', 'like', '%' . $request->search_txt .'%');
            }

            $bookings = $bookings_search->paginate($this->pageLimit);
            $total_bookings = $bookings->count();
        } else {
            // get all the agency bookings vendors
            $bookings = Booking::whereIn('vender_id', $vendor_ids)->latest()->paginate($this->pageLimit);
            // get the total number of booking  
            $total_bookings =  $booking = Booking::whereIn('vender_id', $vendor_ids)->get()->count();
        }
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

        // Grab user the bookings
        //$bookings = Booking::userBy($userId)->orderBy('created_at', 'DESC')->paginate($this->pageLimit);
        $totalBookings = Booking::select('id')->get();
        $bookingCount = $totalBookings->count();
        return view('frontend/allBookings', compact('bookings', 'serviceList', 'userList', 'status', 'total_bookings'));
    }
}
