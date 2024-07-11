<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Mail\RejectAgency;
use Validator;
use App\UserAddresses;
use App\GeneralHistory;
use App\Helpers\Datatable\SSP;
use App\User;
use App\VenderService;
use App\Service;
use App\venderSlot;
use File;
use Illuminate\Support\Facades\Auth;

class AgencyController extends Controller
{

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
    }

    /**
     * Display a listing of user
     *
     * @return Response
     */
    public function index()
    {

        // Grab all the user
        //$users = User::orderBy('id', 'DESC')->get();
        // Show the page
        return view('admin/agency/agencyList');
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
        return redirect('admin/agencies')->with('success_message', trans('admin/user.user_update_message'));
    }

    public function edit($id)
    {

        $user = User::with('userAddress', 'venderServices', 'venderSlots', 'generalHistory')->find($id);

        $employees = User::where(['agency_id' => $id])->paginate($this->pageLimit);
        //        $venderServices = User::find($id)->venderServices()->paginate(2);
        //        $vendorSlots = $user->venderSlots()->orderBy('day')->orderBy('slot_from')->get();
        //
        //        $allServices = Service::where('status', '1')->orderBy('title')->pluck('title', 'id');

        if ($user) {
            return view('admin/agency/editagency', compact('user', 'employees'));
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

    public function changeAgencyStatus(Request $request)
    {
        $data = $request->all();
        $user = User::find($data['id']);

        if ($user->status == User::active) {
            $user->status = '0';
        } else {
            $user->status = '1';
        }
        if ($user->save()) {
            $history = new GeneralHistory();
            $history->user_id = $data['id'];
            if ($user->status == '0') {
                $type = User::inactiveAgency;
                $message = 'You are marked inactive By admin';
            } else {
                $type = User::activeAgency;
                $message = 'You are marked active By admin';
            }
            $history->type = $type;
            $history->message = $message;
            $history->save();
        }

        $array = array();
        $array['status'] = $user->status;
        $array['success'] = true;
        $array['message'] = trans('admin/agency.agency_status_message');
        echo json_encode($array);
    }

    public function getAgencyData()
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
            array('db' => 'users.id', 'dt' => 01, 'field' => 'id'),
            array('db' => 'users.firstname', 'dt' => 0, 'field' => 'firstname'),
            array('db' => 'users.lastname', 'dt' => 1, 'field' => 'lastname'),
            array('db' => 'users.email', 'dt' => 2, 'field' => 'email'),
            array('db' => 'users.phone_number', 'dt' => 3, 'field' => 'phone_number'),
            //            array('db' => 'users.reffer_code', 'dt' => 5, 'field' => 'reffer_code'),
            //            array('db' => 'users.refferal', 'dt' => 6, 'field' => 'refferal'),
            array('db' => 'group_concat(a_d.document1) as document1', 'dt' => 4, 'formatter' => function ($d, $row) {
                $i = 1;
                $html = '';
                $docs = explode(',', $row['document1']);
                foreach ($docs as $doc) {
                    if ($doc) {
                        $html .= '<a target="blank" href="' . url('/assets/images/doc/' . $doc) . '"> Document ' . $i . '</a><br>';
                        $i++;
                    }
                }
                return $html;
            }, 'field' => 'document1'),
            array('db' => 'users.status', 'dt' => 5, 'formatter' => function ($d, $row) {
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
            array('db' => 'users.online', 'dt' => 6, 'formatter' => function ($d, $row) {
                if ($row['online']) {
                    return '<span class="text-success" title="' . trans('admin/vendors.online') . '" data-toggle="tooltip"><i class="fa fa-circle"></i></span>';
                } else {
                    return '<span class="text-danger" title="' . trans('admin/vendors.offline') . '" data-toggle="tooltip"><i class="fa fa-circle"></i></span>';
                }
            }, 'field' => 'online'),
            array('db' => 'users.id', 'dt' => 7, 'formatter' => function ($d, $row) {
                if ($row['status'] == User::pending) {
                    $operation = ' <a href="agencies/' . $d . '/edit" class="btn btn-primary d-inline-block" title="' . trans('admin/common.edit') . '" data-toggle="tooltip"><i class="fa fa-pencil"></i></a> <a href="javascript:;" id="' . $d . '" class="btn btn-danger delete-btn" title="' . trans('admin/common.delete') . '" data-toggle="tooltip"><i class="fa fa-times"></i></a> <a href="javascript:;" class="btn btn-danger" title="' . trans('admin/common.reject') . '" data-toggle="modal" data-target="#' . $row['id'] . $row['lastname'] . '"><i class="fa fa-minus-circle"></i></a>';
                    $operation .= '<div class="modal fade" id="' . $row['id'] . $row['lastname'] . '" role="dialog">';
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
                    $operation = ' <a href="agencies/' . $d . '/edit" class="btn btn-primary d-inline-block" title="' . trans('admin/common.edit') . '" data-toggle="tooltip"><i class="fa fa-pencil"></i></a> <a href="javascript:;" id="' . $d . '" class="btn btn-danger delete-btn" title="' . trans('admin/common.delete') . '" data-toggle="tooltip"><i class="fa fa-times"></i></a>';
                }
                return $operation;
            }, 'field' => 'id')
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
        $joinQuery .= " LEFT JOIN roles r ON r.id = ru.role_id ";
        $joinQuery .= " LEFT JOIN agency_document a_d ON a_d.user_id = users.id ";
        $extraWhere = " r.name='agency' ";
        $groupBy = " users.id ";

        echo json_encode(
            SSP::simple($_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy)
        );
    }

    public function rejectAgency(Request $request)
    {
        $data = $request->all();
        $user = User::find($data['id']);
        $user->update(['rejection_reason' => $data['reason'], 'status' => (string) User::rejected]);
        Mail::to($user->email)->send(new RejectAgency($user));

        $history = new GeneralHistory();
        $history->user_id = $data['id'];
        $history->type = User::rejectAgency;
        $history->message = $data['reason'];
        $history->save();
        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/agency.reject_success_message');
        echo json_encode($array);
    }
}
