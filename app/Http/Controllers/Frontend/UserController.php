<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User as User;
use Auth;
use Hash;
use Validator;
use App\AgencyDocument;
use App\Service;
use File;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Mail\Frontend\RegisterMail;
use App\Mail\Frontend\RegisterEmployeeMail;
use App\Mail\Frontend\RegisterMailAdmin;
use App\VenderService;
use App\UserPackage;
use App\UserAddresses;

class UserController extends Controller {

    protected $auth;

    const totalRow = 20;

    public function __construct() {
        $this->middleware('auth.user', ['except' => ['create', 'store']]);
        //$this->middleware('guest.user', ['except' => ['index', 'update', 'destroy']]);
        $this->auth = auth()->guard('user');
    }

    /**
     * Display a listing of users
     *
     * @return Response
     */
    public function index() {

        $profile = User::where('id', $this->auth->user()->id)->with('agencyDocument')->first();
        $userDocuments = array();

        if (isset($profile->agencyDocument)) {
            $userDocuments = $profile->agencyDocument;
        }
        $allServices = Service::where(['status' => '1'])->get();
        $saved_services = VenderService::where(['vender_id' => $this->auth->user()->id])->get()->toArray();

        $services = array_column($saved_services, 'service_id');

        if ($profile) {
            return view('frontend.profile', compact('profile', 'userDocuments', 'services', 'allServices'));
        } else {
            return response()->view('errors.404', array(), 404);
        }
    }

    /**
     * Agency profile
     */
    public function agencyProfile() {
        $profile = User::where('id', $this->auth->user()->id)->with('agencyDocument')->first();
        $document = array();


        $userDocuments = $profile->agencyDocument;


        /* if (isset($profile->agencyDocument[0])) {
          $document = $profile->agencyDocument[0];
          } */

        $allServices = Service::where(['status' => '1'])->get();
        $saved_services = VenderService::where(['vender_id' => $this->auth->user()->id])->get()->toArray();

        $services = array_column($saved_services, 'service_id');


        if ($profile) {
            return view('frontend.profile', compact('profile', 'userDocuments', 'services', 'allServices'));
        } else {
            return response()->view('errors.404', array(), 404);
        }
    }

    /**
     * List agency employess
     */
    public function agencyEmployess(Request $request) {

        if ($request->has('reset')) {
            $request->session()->forget('SEARCH');
            return redirect('agency/allUsers');
        }

        if ($request->get('search_by') != '') {
            session(['SEARCH.SEARCH_BY' => trim($request->get('search_by'))]);
        }

        if ($request->get('search_txt') != '') {
            session(['SEARCH.SEARCH_TXT' => trim($request->get('search_txt'))]);
        }

        if ($request->session()->get('SEARCH.SEARCH_BY') != '') {
            $query = User::select('*')->where('agency_id', $this->auth->user()->id);

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'firstname') {

                $query->where('firstname', 'LIKE', '%' . $request->session()->get('SEARCH.SEARCH_TXT') . '%');
            }
            if ($request->session()->get('SEARCH.SEARCH_BY') == 'lastname') {

                $query->where('lastname', '=', $request->session()->get('SEARCH.SEARCH_TXT'));
            }
            if ($request->session()->get('SEARCH.SEARCH_BY') == 'email') {
                $query->where('email', '=', $request->session()->get('SEARCH.SEARCH_TXT'));
            }
            if ($request->session()->get('SEARCH.SEARCH_BY') == 'phone') {

                $query->where('phone_number', '=', $request->session()->get('SEARCH.SEARCH_TXT'));
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'prouser' || $request->session()->get('SEARCH.SEARCH_BY') == 'notprouser') {
                $comparison = '';
                $getUser_id = UserPackage::select('user_id')->where('expiry_date', '>', date("Y-m-d H:i:s"))->get()->toArray();
                $getUser_id = array_column($getUser_id, 'user_id');
                if ($request->session()->get('SEARCH.SEARCH_BY') == 'prouser') {
                    $query = User::select('*')->where('agency_id', $this->auth->user()->id)->whereIn('id', $getUser_id);
                }
                if ($request->session()->get('SEARCH.SEARCH_BY') == 'notprouser') {
                    $query = User::select('*')->where('agency_id', $this->auth->user()->id)->whereNotIn('id', $getUser_id);
                }
            }

            $employees = $query->orderBy('created_at', 'desc')->paginate(self::totalRow);
            return view('frontend.employee.agencyEmployees', compact('employees'));
        }

        $employees = User::where('agency_id', $this->auth->user()->id)->with('package')->orderBy('id', 'desc')->get();

        if ($employees) {

            return view('frontend.employee.agencyEmployees', compact('employees'));
        } else {
            return response()->view('errors.404', array(), 404);
        }
    }

    /**
     * Show the form for creating a new user
     *
     * @return Response
     */
    public function create() {

        $services = Service::where(['status' => '1'])->get();
        return view('frontend.register', compact('services'));
    }

    /**
     * Store a newly created user in storage.
     *
     * @return Response
     */
    public function store(Request $request) {
        $rules = array(
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|unique:users',
            'services' => 'required|array',
            'phone_number' => 'required|numeric|unique:users',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6'
        );
        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $doc = array();
        if ($request->hasFile('document1')) {
            $allowedfileExtension = ['pdf', 'jpg', 'png', 'docx'];
            $documents = $request->file('document1');

            foreach ($documents as $document) {

                $filename = $document->getClientOriginalName();
                $extension = $document->getClientOriginalExtension();
                $check = in_array($extension, $allowedfileExtension);
                if ($check) {
                    $filename = time() . '.' . $extension;
                    $document->move('assets/images/doc/', $filename);
                    $doc[] = $filename;
                }
            }
        }


        $data['status'] = User::pending;
        $user = User::create($data);
        $user->password = \Hash::make($data['password']);
        $user->save();

        if (isset($request['services']) && !empty($request['services'])) {

            foreach ($request['services'] as $service_id) {

                $cat_id = Service::select('cat_id')->where(['id' => $service_id])->first()->toArray();
                VenderService::create([
                    'vender_id' => $user->id,
                    'service_id' => $service_id,
                    'cat_id' => $cat_id['cat_id'],
                ]);
            }
        }

        $role = Role::find('3');
        $user->attachRole($role);
        if ($doc) {
            foreach ($doc as $do) {
                $document = new AgencyDocument();
                $document->user_id = $user->id;
                $document->document1 = $do;
                $document->save();
            }
        }

        $user_adress = UserAddresses::create([
                    'user_id' => $user->id,
                    'latitude' => $request['address']['latitude'] ? $request['address']['latitude'] : '',
                    'longitude' => $request['address']['longitude'] ? $request['address']['longitude'] : '',
                    'city' => $request['address']['city'] ? $request['address']['city'] : '',
                    'country' => $request['address']['country'] ? $request['address']['country'] : '',
                    'pincode' => $request['address']['pincode'] ? $request['address']['pincode'] : '',
                    'full_address' => $request['address']['full_address'] ? $request['address']['full_address'] : '',
                    'address_type' => 'home',
                    'name' => $request['firstname'] . ' ' . $request['lastname'],
                    'phone' => $request['phone_number'],
        ]);
        $user->selected_address = $user_adress->id;
        $user->save();
        //return redirect()->route('frontend.login')->with('success_message', "Your account created successfully!");
        //send regstraion mail to user
        Mail::to($request->get('email'))->send(new RegisterMail($user));

        //send regstraion mail to admin
        Mail::to(config('settings.admin.email'))->send(new RegisterMailAdmin($user));
        return redirect()->back()->with('success_message', trans('user/register.account_create_message'));
    }

    /**
     * 
     * @param Request $request
     * 
     * @return type
     * @description Save user created by agency
     */
    public function storeUser(Request $request) {

        $rules = array(
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|unique:users',
            'phone_number' => 'required|numeric|unique:users',
            'services' => 'required|array',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6'
        );
        
        $user = $this->auth->user();
//        echo "<pre>";
//        print_r();
//        echo "</pre>";
        if($user->status != '1') {
            return redirect()->back()->with('error_message', trans('user/register.agecny_not_active'));
            
        }
        $data = $request->all();

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {

            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (isset($request->image)) {

            if ($request->file('image')->isValid()) {

                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $image->move('images/avatars/', $filename);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $data['image'] = $filename;
        }

        $data['status'] = User::active;
        $data['is_verified'] = User::verified;
        $data['agency_id'] = $this->auth->user()->id;
        $data['agency_name'] = $this->auth->user()->firstname . " " . $this->auth->user()->lastname;
        $user = User::create($data);
        $user->password = \Hash::make($data['password']);
        $user->save();

        if ($this->auth->user()->selected_address) {
            $agency_address = UserAddresses::find($this->auth->user()->selected_address);
            $user_adress = UserAddresses::create([
                        'user_id' => $user->id,
                        'latitude' => $agency_address->latitude ? $agency_address->latitude : '',
                        'longitude' => $agency_address->longitude ? $agency_address->longitude : '',
                        'city' => $agency_address->city ? $agency_address->city : '',
                        'country' => $agency_address->country ? $agency_address->country : '',
                        'pincode' => $agency_address->pincode ? $agency_address->pincode : '',
                        'full_address' => $agency_address->full_address ? $agency_address->full_address : '',
                        'address_type' => 'home',
                        'name' => $request['firstname'] . ' ' . $request['lastname'],
                        'phone' => $request['phone_number'],
            ]);
            $user->selected_address = $user_adress->id;
            $user->save();
        }


        if (isset($request['services']) && !empty($request['services'])) {

            foreach ($request['services'] as $service_id) {

                $cat_id = Service::select('cat_id')->where(['id' => $service_id])->first()->toArray();
                VenderService::create([
                    'vender_id' => $user->id,
                    'service_id' => $service_id,
                    'cat_id' => $cat_id['cat_id'],
                ]);
            }
        }

        $role = Role::find('2');
        $user->attachRole($role);

        //return redirect()->route('frontend.login')->with('success_message', "Your account created successfully!");
        //send regstraion mail to user
        $user['agencyName'] = $this->auth->user()->firstname;
        $user['username'] = $data['email'];
        $user['password'] = $data['password'];
        Mail::to($request->get('email'))->send(new RegisterEmployeeMail($user));

        //send regstraion mail to admin
        Mail::to(config('settings.admin.email'))->send(new RegisterMailAdmin($user));
        return redirect()->back()->with('success_message', trans('user/register.account_create_message'));
    }

    public function addUser() {

        $agencyServices = VenderService::where(['vender_id' => $this->auth->user()->id])->get()->toArray();
        $agencyServices = array_column($agencyServices, 'service_id');
        $services = Service::where(['status' => '1'])->whereIn('id', $agencyServices)->get();
        return view('frontend.employee.addEmployee', compact('services'));
    }

    public function editUser(Request $request, $id) {

        $user = User::findOrFail($id);

        $agencyServices = VenderService::where(['vender_id' => $this->auth->user()->id])->get()->toArray();
        $agencyServices = array_column($agencyServices, 'service_id');
        $allServices = Service::where(['status' => '1'])->whereIn('id', $agencyServices)->get();
        $saved_services = VenderService::where(['vender_id' => $user->id])->get()->toArray();
        $services = array_column($saved_services, 'service_id');
        return view('frontend.employee.editEmployee', compact('user', 'services', 'allServices'));
    }

    /**
     * Update agency User
     */
    public function updateUser(Request $request, $id) {

        $rules = array(
            'firstname' => 'required',
            'lastname' => 'required',
            'phone_number' => 'required|numeric',
            'email' => 'required|email|unique:users,email,' . $id,
        );
        $user = User::findOrFail($id);
        $data = $request->all();
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $this->assignServices($request['services'], $id);

        $user->update($data);
        return redirect()->back()->with('success_message', trans('user/profile.profile_update_message'));
    }

    private function assignServices($services, $user_id) {
        if ($services) {
            $assigned_array = array();
            $already_assigned_services = array_column(VenderService::where('vender_id', '=', $user_id)->get()->toArray(), 'service_id');
            foreach ($already_assigned_services as $already_assigned_service) {
                $assigned_array[] = $already_assigned_service;
            }
            $array1 = array_diff($assigned_array, $services);
            $array2 = array_diff($services, $assigned_array);
            $array3 = array_merge($array1, $array2);

            if ($array3) {

                foreach ($array3 as $service_id) {

                    $if_alreay_assigned_then_remove = venderService::where(['service_id' => $service_id, 'vender_id' => $user_id])->first();
                    if (isset($if_alreay_assigned_then_remove->id)) {
                        $if_alreay_assigned_then_remove->delete();
                    } else {
                        $cat_id = Service::select('cat_id')->where(['id' => $service_id])->first()->toArray();
                        venderService::create(['vender_id' => $user_id, 'service_id' => $service_id, 'cat_id' => $cat_id['cat_id']]);
                    }
                }
            }
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id) {

        $rules = array(
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
        );

        $user = User::findOrFail($id);

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $doc = array();

        if ($request->hasFile('document1')) {
            $allowedfileExtension = ['pdf', 'jpg', 'png', 'docx'];
            $documents = $request->file('document1');

            foreach ($documents as $document) {

                $filename = $document->getClientOriginalName();
                $extension = $document->getClientOriginalExtension();
                $check = in_array($extension, $allowedfileExtension);
                if ($check) {
                    $filename = time() . '.' . $extension;
                    $document->move('assets/images/doc/', $filename);
                    $doc[] = $filename;
                }
            }
        }

        if ($doc) {
            foreach ($doc as $do) {
                $document = new AgencyDocument();
                $document->user_id = $user->id;
                $document->document1 = $do;
                $document->save();
            }
        }

        $this->assignServices($request['services'], $id);

        $user->update($data);
        return redirect()->back()->with('success_message', trans('user/profile.profile_update_message'));
    }

    /* Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */

    public function destroy($id) {
        $user = User::findOrFail($id);
        AgencyDocument::where(['user_id' => $user->id])->delete();
        User::destroy($id);
        return redirect('agency/allUsers')->with('success_message', 'Account deleted successfully!');
    }

    public function changePassword() {
        
        return view('frontend.changePassword');
    }

    public function updatePassword(Request $request) {
        $data = $request->all();
        $user = User::findOrFail($this->auth->user()->id);

        if (!Hash::check($data['old_password'], $user->password)) {
            return redirect()->back()->with('error_message', trans('user/changePassword.invalid_password_message'));
        } else {
            $rules = array(
                'password' => 'required|confirmed|min:6',
            );
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $user->password = \Hash::make($data['password']);
            $user->save();
            return redirect()->back()->with('success_message', trans('user/changePassword.password_change_message'));
        }
    }

    public function export(Request $request) {



        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Agency-Vendors-list.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('FirstName ', 'LastName', 'Email', 'Phone Number', 'Status', 'Booking Count', 'User Type'));

        $user_id = $this->auth->user()->id;
        $bookings = User::where('agency_id', $user_id)->orderBy('created_at', 'DESC')->get();
        foreach ($bookings as $data) {
            $user = 'Not Pro';
            if(isset($data->package[0]) && $data->package[0]->expiry_date >= date('Y-m-d H:i:s')) { 
                $user = 'Pro User';
            }
             
            fputcsv($output, array(
                $data->firstname,
                $data->lastname,
                $data->email,
                $data->phone_number,
                $data->status,
                $data->vendorBooking->count(),
                $user
                
                    )
            );
        }
        fclose($output);
        exit;
    }

}
