<?php

namespace App\Http\Controllers\Admin;

use App\AvatarImage;
use App\Helpers\Datatable\SSP;
use App\Http\Controllers\Controller;
use App\User;
use App\phoneOtp;
use App\RoleUser;
use App\UserAddresses;
use File;
use Validator;
use Illuminate\Http\Request;

class UserController extends Controller
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
        // Show the page
        return view('admin/userList');
    }

    public function agencies()
    {
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', 'agency');
        })->get();

        return view('admin/agenciesList', compact('users'));
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('admin/userDetails', compact('user'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {

        //$user = User::with('userAddress')->find($id);
        $user = User::find($id);
        // get the user role . Whether it is vendor or user
        $user_role = RoleUser::select('role_id')->where('user_id', $user->id)->first(); 
        $user_role = $user_role ? $user_role->role_id :"";

        $avatar_image = AvatarImage::find($user->avtaar_image);

        $home_addresses = $user->userAddress()->where('address_type', 'LIKE', '%home%')->get();
        $office_addresses = $user->userAddress()->where('address_type', 'LIKE', '%office%')->get();
        $other_addresses = $user->userAddress()->where('address_type', 'LIKE', '%other%')->get();
        $work_addresses = $user->userAddress()->where('address_type', 'LIKE', '%work%')->get();
        if ($user) {
            return view('admin/edituser', compact('user', 'home_addresses', 'office_addresses', 'other_addresses', 'work_addresses', 'avatar_image', 'user_role'));
        }
    }

    //save edit data
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->all();

        $rules = array(
            'firstname' => 'required',
            'lastname' => 'required',
            'gender' => 'required',
            //'phone_number' => 'required|numeric|digits_between:10,15'
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
            $destinationPath = base_path('images/avatars/'); //public path folder dir
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
            'phone_number' => 'required|numeric',
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
        return redirect('admin/users')->with('success_message', trans('admin/user.user_update_message'));
    }

    public function destroy($id)
    {

        $user = User::findOrFail($id);
        if ($user->phone_number) {
            $existInOtpTable = phoneOtp::where(['phone_no' => $user->phone_number])->first();
            if ($existInOtpTable) {
                phoneOtp::destroy($existInOtpTable->id);
            }
        }


        $oldFile = \Config::get('constants.USER_IMAGE_PATH') . $user->image;
        if (File::exists($oldFile)) {
            File::delete($oldFile);
        }
        User::destroy($id);

        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/user.delete_message');
        echo json_encode($array);
    }

    public function changeUserStatus(Request $request)
    {
        $data = $request->all();
        $user = User::find($data['id']);

        if ($user->status == User::pending || $user->status == User::inActive || $user->status == User::rejected) {
            $user->status = '1';
        } else {
            $user->status = '0';
        }
        $user->save();

        $array = array();
        $array['status'] = $user->status;
        $array['success'] = true;
        $array['message'] = trans('admin/user.user_status_message');
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

    public function getUserData()
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
            array('db' => 'users.user_type', 'dt' => 3, 'formatter' => function ($d, $row) {
                if ($d) {
                    return 'facebook User';
                } else {
                    return 'Web User';
                }
            }, 'field' => 'user_type'),
            array('db' => 'users.phone_number', 'dt' => 4, 'field' => 'phone_number'),
            array('db' => 'users.refferal', 'dt' => 6, 'field' => 'refferal'),
            array('db' => 'users.user_type', 'dt' => 9,  'field' => 'user_type'),
            array('db' => 'users.reffer_code', 'dt' => 7,  'field' => 'reffer_code'),

            // array('db' => 'users.credit', 'dt' => 3, 'formatter' => function( $d, $row ) {
            //     return '<span class="" data-toggle="tooltip" title="'.trans('admin/user.credit_info').'"><a class="credit_'.$row['id'].' credit-txt" data-userid="'.$row['id'].'">' .$d. '</a></span>';
            // }, 'field' => 'credit'),
            array('db' => 'COALESCE(bk.total_bookings,0)', 'dt' => 5, 'formatter' => function ($d, $row) {
                return '<a href="users/' . $d . '/booking" class="btn btn-primary" id="' . $row['id'] . '" title="' . trans('admin/user.view_bookings') . '" data-toggle="tooltip">' . $row['total_bookings'] . '</a>';
            }, 'field' => 'id', 'as' => 'total_bookings'),
            // array('db' => 'COALESCE(trans.total_transactions,0)', 'dt' => 5, 'formatter' => function( $d, $row ) {
            //         return '<a href="users/' . $d.'/transaction" class="btn btn-primary" id="' . $row['id'] . '" title="'.trans('admin/user.view_transactions').'" data-toggle="tooltip">'.$row['total_transactions'].'</a>';
            // }, 'field' => 'id', 'as' => 'total_transactions'),
            // array('db' => 'users.id', 'dt' => 6, 'formatter' => function( $d, $row ) {
            //     return '<a href="chatboard/history/' . $d . '" class="btn btn-primary" title="'.trans('admin/user.view_chat').'" data-toggle="tooltip"><i class="fa fa-eye"></i></a>';
            // }, 'field' => 'id'),
            // array('db' => 'users.status', 'dt' => 7, 'formatter' => function( $d, $row ) {
            //         if ($row['status']) {
            //             return '<a href="javascript:;" class="btn btn-success status-btn" id="' . $row['id'] . '" title="'.trans('admin/common.click_to_inactive').'" data-toggle="tooltip">'.trans('admin/common.active').'</a>';
            //         } else {
            //             return '<a href="javascript:;" class="btn btn-danger status-btn" id="' . $row['id'] . '" title="'.trans('admin/common.click_to_active').'" data-toggle="tooltip">'.trans('admin/common.inactive').'</a>';
            //         }
            // }, 'field' => 'status'),
            // array('db' => 'users.id', 'dt' => 8, 'formatter' => function ($d, $row) {
            //     $operation = '<a href="{!! url("admin/users/show{$d}") !!}" class="btn btn-success"><i class="fa fa-eye"></i></a> || <a href="users/show/' . $d . '" class="btn btn-primary" title="' . trans('admin/common.edit') . '" data-toggle="tooltip"><i class="fa fa-pencil"></i></a>&nbsp;';
            //     return $operation;
            // }, 'field' => 'id'),
            array('db' => 'users.id', 'dt' => 8, 'formatter' => function ($d, $row) {
                $operation = ' <a href="users/' . $d . '/edit" class="btn btn-primary d-inline-block" title="' . trans('admin/common.edit') . '" data-toggle="tooltip"><i class="fa fa-pencil"></i></a> <a href="javascript:;" id="' . $d . '" class="btn btn-danger d-inline-block delete-btn" title="' . trans('admin/common.delete') . '" data-toggle="tooltip"><i class="fa fa-times"></i></a>';
                return $operation;
            }, 'field' => 'id'),
        );
        //   dd($columns);
        // SQL server connection information
        $sql_details = array(
            'user' => config('database.connections.mysql.username'),
            'pass' => config('database.connections.mysql.password'),
            'db' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host'),
        );

        $joinQuery = "LEFT JOIN (SELECT COUNT(*) AS total_bookings, user_id FROM bookings GROUP BY user_id ) as bk ON bk.user_id = users.id";
        $joinQuery .= " LEFT JOIN (SELECT COUNT(*) AS total_transactions, user_id FROM transactions GROUP BY user_id ) as trans ON trans.user_id = users.id";
        $joinQuery .= " LEFT JOIN role_user ru ON ru.user_id = users.id";
        $joinQuery .= " LEFT JOIN roles r ON r.id = ru.role_id";
        // $joinQuery .= " LEFT JOIN user_addresses ur ON ur.user_id = users.id";
        $extraWhere = " r.name='User'";
        $groupBy = "";
        echo json_encode(
            SSP::simple($_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy)
        );
    }
}
