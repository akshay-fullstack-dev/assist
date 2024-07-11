<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Mail\RejectVendor;
use Validator;
use App\UserAddresses;
use App\Helpers\Datatable\SSP;
use App\User;
use App\VenderService;
use App\Service;
use App\venderSlot;
use File;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    protected $vendor_service_per_page;

    /**
     * User Model
     * @var User
     */
    protected $user;
    protected $pageLimit;

    /**
     * Inject the models.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->pageLimit = config('settings.pageLimit');
        $this->vendor_service_per_page = config('settings.vendor_page_service_list');
    }

    /**
     * Display a listing of user
     *
     * @return Response
     */
    public function index()
    {

        // Grab all the user 
        $users = User::paginate($this->pageLimit);

        // Show the page
        return view('admin/vendorList', compact('users'));
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return Response
     */
    public function show_vendor($id)
    {

        $user = User::with('venderServices')->find($id);
        $venderServices = User::find($id)->venderServices()->paginate(2);
        return view('admin/vendorView', compact('user', 'venderServices'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->all();

        $rules = array(
            'firstname' => 'required',
            'lastname' => 'required',
            'gender' => 'required',
            //'phone_number' => 'required|numeric|digits_between:10,15',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if ($request->hasFile('image')) { //check the file present or not
            $rules = array('image' => 'mimes:jpeg,png,jpg,gif,svg||max:4096');
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $image = $request->file('image'); //get the file
            $data['image'] = uniqid() . '.' . $image->getClientOriginalExtension(); //get the  file extention
            $destinationPath = public_path('/images'); //public path folder dir
            if (!$image->move($destinationPath, $data['image'])) {
                echo "image not uploaded correclty! Try Later";
                die;
            } //mve to destination you mentioned
        }
        $user->update($data);

        $rules = array(
            'firstname' => 'required',
            'lastname' => 'required',
            'gender' => 'required',
            'phone_number' => 'required|numeric|integer',
        );


        // update user data

        if (isset($data['add']['home'])) {
            $homeAdd = UserAddresses::findOrFail($data['add']['home']['id']);
            if ($homeAdd) {
                $addData = $data['add']['home'];
                $homeAdd->update($addData);
            }
        }
        if (isset($data['add']['office'])) {
            $homeAdd = UserAddresses::findOrFail($data['add']['office']['id']);
            if ($homeAdd) {
                $addData = $data['add']['office'];
                $homeAdd->update($addData);
            }
        }
        if (isset($data['add']['other'])) {
            $homeAdd = UserAddresses::findOrFail($data['add']['other']['id']);
            if ($homeAdd) {
                $addData = $data['add']['other'];
                $homeAdd->update($addData);
            }
        }
        return redirect('admin/vendors')->with('success_message', trans('admin/user.user_update_message'));
    }

    public function edit($id)
    {
        $user = User::with('userAddress', 'venderServices', 'venderSlots')->find($id);
        $venderServices = User::find($id)->venderServices()->paginate($this->vendor_service_per_page);

        $vendorSlots = $user->venderSlots()->orderBy('day')->orderBy('slot_from')->get();

        $allServices = Service::where('status', '1')->orderBy('title')->pluck('title', 'id');

        if ($user) {
            return view('admin/editvendor', compact('user', 'venderServices', 'vendorSlots', 'allServices'));
        }
    }

    public function show($id)
    {

        $user = User::findOrFail($id);
        return view('admin/VendorView', compact('user'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $oldFile = \Config::get('constants.USER_IMAGE_PATH') . $user->image;
        if (File::exists($oldFile)) {
            File::delete($oldFile);
        }
        User::destroy($id);

        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/user.user_delete_message');
        echo json_encode($array);
    }

    public function changeUserStatus(Request $request)
    {
        $data = $request->all();
        $user = User::find($data['id']);

        if ($user->status) {
            $user->status = '0';
        } else {
            $user->status = '1';
        }
        $user->save();

        $array = array();
        $array['status'] = $user->status;
        $array['success'] = true;
        $array['message'] = trans('admin/user.user_status_message');
        echo json_encode($array);
    }

    public function addVenderService(Request $request)
    {

        $data = $request->all();
        $user = User::find($data['user_id']);

        if (!$user) {

            $array['status'] = 0;
            $array['success'] = false;
            $array['message'] = trans('admin/user.user_not_found');
        }

        $checkServiceAssignedAlready = VenderService::where([['vender_id', '=', $data['user_id']], ['service_id', '=', $data['service_id']]])->first();
        if ($checkServiceAssignedAlready) {
            $array['status'] = 0;
            $array['success'] = false;
            $array['message'] = trans('admin/service.service_already_assigned');
            return $array;
        }

        $getService = Service::where('id', '=', $data['service_id'])->first();
        $data = array(
            'vender_id' => $data['user_id'],
            'service_id' => $data['service_id'],
            'status' => '1',
            'cat_id' => $getService->cat_id
        );

        if (VenderService::create($data)) {

            $array = array();
            $array['status'] = 1;
            $array['success'] = true;
            $array['message'] = trans('admin/service.service_assigned_successfully');
            return $array;
        }
    }

    /**
     * Change user credit of the specified user.
     *
     * @return \Illuminate\Http\Response
     */
    public function changeVenderServiceStatus(Request $request)
    {
        $data = $request->all();
        $venderService = VenderService::find($data['id']);

        if ($venderService->status) {
            $venderService->status = '0';
        } else {
            $venderService->status = '1';
        }
        $venderService->save();

        $array = array();
        $array['status'] = $venderService->status;
        $array['success'] = true;
        $array['message'] = trans('admin/user.user_status_message');
        echo json_encode($array);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function deleteVenderService($id)
    {
        Schedule::where('service_id', $id)->delete();
        Service::destroy($id);

        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/service.service_delete_message');
        echo json_encode($array);
    }

    public function deleteVendorSlot(Request $request)
    {
        $data = $request->all();

        venderSlot::where('vender_id', $data['vendorId'])->where('slot_id', $data['slotId'])->delete();

        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/vendors.slot_delete_message');
        echo json_encode($array);
    }

    /**
     * Change user credit of the specified user.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateCredit(Request $request)
    {

        $data = $request->all();
        $data['credit'] = $data['value'];
        $user = User::find($data['userId']);

        $user->update($data);
        $array = array();
        $array['success'] = true;
        session()->flash('success_message', trans('admin/user.credit_update_message'));
        echo json_encode($array);
    }

    public function getVendorData()
    {
        // DB table to use
        $table = 'users';

        // Table's primary key
        $primaryKey = 'id';

        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            array('db' => 'users.firstname', 'dt' => 0, 'field' => 'firstname'),
            array('db' => 'users.lastname', 'dt' => 1, 'field' => 'lastname'),
            array('db' => 'users.email', 'dt' => 2, 'field' => 'email'),
            array('db' => 'users.phone_number', 'dt' => 3, 'field' => 'phone_number'),
            array('db' => 'users.reffer_code', 'dt' => 5, 'field' => 'reffer_code'),
            array('db' => 'users.refferal', 'dt' => 6, 'field' => 'refferal'),
            array('db' => 'users.agency_id', 'dt' => 10, 'formatter' => function ($d, $row) {
                $html = '';
                if ($d) {
                    $html .= trans('admin/vendors.agency_vendor');
                } else {
                    $html .= trans('admin/vendors.freelancer');
                }
                return $html;
            }, 'field' => 'agency_id'),
            // array('db' => 'u_p.expiry_date', 'dt' => 8, 'formatter' => function ($d, $row) {
            //         $html = '';
            //         if ($d >= date('Y-m-d')) {
            //             $html .= 'Vip User';
            //         } else {
            //             $html .= 'Not Vip User';
            //         }

            //         return $html;
            //     }, 'field' => 'expiry_date'),
            array('db' => 'users.payment_status', 'dt' => 8, 'field' => 'payment_status', 'formatter' => function ($d, $row) {
                $html = '';
                if ($d == "1") {
                    $html .= 'Vip User';
                } else {
                    $html .= 'Not Vip User';
                }
                return $html;
            }),
            array('db' => 'users.user_type', 'dt' => 13, 'field' => 'user_type'),
            array('db' => 'users.vender_doc', 'dt' => 7, 'formatter' => function ($d, $row) {
                $html = '';

                if ($row['vender_doc']) {

                    $external_link = url('images/doc/' . $row['vender_doc']);
                    if (@getimagesize($external_link)) {
                        $html .= '<a target="blank" href="' . url('images/doc/' . $row['vender_doc']) . '">Document</a><br>';
                        return $html;
                    }
                }
            }, 'field' => 'vender_doc'),
            array('db' => 'COALESCE(bk.total_bookings,0)', 'dt' => 4, 'formatter' => function ($d, $row) {
                $booking_link = 'javascript:void(0)';
                if ($row['total_bookings'] > 0) {
                    $booking_link = 'users/' . $d . '/booking';
                }
                return '<a href="' . $booking_link . '" class="btn btn-primary" id="' . $row['id'] . '" title="' . trans('admin/user.view_bookings') . '" data-toggle="tooltip">' . $row['total_bookings'] . '</a>';
            }, 'field' => 'id', 'as' => 'total_bookings'),
            array('db' => 'users.status', 'dt' => 9, 'formatter' => function ($d, $row) {
                if ($row['status'] == User::active) {
                    return '<a href="javascript:;" class="btn btn-success status-btn" id="' . $row['id'] . '" title="' . trans('admin/common.click_to_inactive') . '" data-toggle="tooltip">' . trans('admin/common.active') . '</a>';
                } else if ($row['status'] == User::inActive) {
                    return '<a href="javascript:;" class="btn btn-danger status-btn" id="' . $row['id'] . '" title="' . trans('admin/common.click_to_active') . '" data-toggle="tooltip">' . trans('admin/common.inactive') . '</a>';
                } else if ($row['status'] == User::pending) {
                    return '<a href="javascript:;" class="btn btn-warning status-btn" id="' . $row['id'] . '" title="' . trans('admin/common.pending') . '" data-toggle="tooltip">' . trans('admin/common.pending') . '</a>';
                } else {
                    return '<a href="javascript:;" class="btn btn-danger status-btn" id="' . $row['id'] . '" title="' . trans('admin/common.rejected') . '" data-toggle="tooltip">' . trans('admin/common.rejected') . '</a>';
                }
            }, 'field' => 'status'),
            array('db' => 'users.online', 'dt' => 11, 'formatter' => function ($d, $row) {
                if ($row['online']) {
                    return '<span class="text-success" title="' . trans('admin/vendors.online') . '" data-toggle="tooltip"><i class="fa fa-circle"></i></span>';
                } else {
                    return '<span class="text-danger" title="' . trans('admin/vendors.offline') . '" data-toggle="tooltip"><i class="fa fa-circle"></i></span>';
                }
            }, 'field' => 'online'),
            array('db' => 'users.id', 'dt' => 12, 'formatter' => function ($d, $row) {
                if ($row['status'] == User::pending) {
                    $operation = ' <a href="vendors/' . $d . '/edit" class="btn btn-primary d-inline-block" title="' . trans('admin/common.edit') . '" data-toggle="tooltip"><i class="fa fa-pencil"></i></a> <a href="javascript:;" id="' . $d . '" class="btn btn-danger delete-btn" title="' . trans('admin/common.delete') . '" data-toggle="tooltip"><i class="fa fa-times"></i></a> <a href="javascript:;" class="btn btn-danger" title="' . trans('admin/common.reject') . '" data-toggle="modal" data-target="#' . $row['id'] . $row['firstname'] . '"><i class="fa fa-minus-circle"></i></a>';
                    $operation .= '<div class="modal fade" id="' . $row['id'] . $row['firstname'] . '" role="dialog">';
                    $operation .= '<div class="modal-dialog">';
                    $operation .= '<div class="modal-content">';
                    $operation .= '<div class="modal-header">';
                    $operation .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    $operation .= '<h4 class="modal-title">Rejection Reason</h4>';
                    $operation .= '</div>';
                    $operation .= '<div class="modal-body">';
                    $operation .= '<textarea class="form-control rejection-reason" id="rejection_reason' . $d . '" rows="5"></textarea>';
                    $operation .= '</div>';
                    $operation .= '<div class="modal-footer">';
                    $operation .= '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
                    $operation .= '<button type="submit" class="btn btn-primary reject" id="reject' . $d . '">' . trans('admin/common.reject') . '</button>';
                    $operation .= '</div>';
                    $operation .= '</div>';
                    $operation .= '</div>';
                    $operation .= '</div>';
                } else {
                    $operation = ' <a href="vendors/' . $d . '/edit" class="btn btn-primary d-inline-block" title="' . trans('admin/common.edit') . '" data-toggle="tooltip"><i class="fa fa-pencil"></i></a> <a href="javascript:;" id="' . $d . '" class="btn btn-danger delete-btn" title="' . trans('admin/common.delete') . '" data-toggle="tooltip"><i class="fa fa-times"></i></a>';
                }
                return $operation;
            }, 'field' => 'id')
            // array('db' => 'users.id', 'dt' => 13, 'field' => 'id')
        );
        // SQL server connection information
        $sql_details = array(
            'user' => config('database.connections.mysql.username'),
            'pass' => config('database.connections.mysql.password'),
            'db' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host')
        );

        $joinQuery = "LEFT JOIN (SELECT COUNT(*) AS total_bookings, vender_id FROM bookings GROUP BY vender_id ) as bk ON bk.vender_id = users.id";
        $joinQuery .= " LEFT JOIN (SELECT COUNT(*) AS total_transactions, user_id FROM transactions GROUP BY user_id ) as trans ON trans.user_id = users.id";
        $joinQuery .= " LEFT JOIN role_user ru ON ru.user_id = users.id";
        $joinQuery .= " LEFT JOIN roles r ON r.id = ru.role_id";
        $joinQuery .= " LEFT JOIN user_packages u_p ON u_p.user_id = users.id";
        $extraWhere = "r.name='vendor'";
        $groupBy = "users.id";

        echo json_encode(
            SSP::simple($_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy)
        );
    }

    public function rejectVendor(Request $request)
    {
        $data = $request->all();
        $user = User::find($data['id']);
        $user->update(['rejection_reason' => $data['reason'], 'status' => (string) User::rejected]);
        Mail::to($user->email)->send(new RejectVendor($user));

        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/vendors.reject_success_message');
        echo json_encode($array);
    }
}
