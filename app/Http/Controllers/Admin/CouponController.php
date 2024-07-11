<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Datatable\SSP;
use App\Coupon;
use App\Service;
use App\couponService;
use App\CouponHistory;
use Validator;

class CouponController extends Controller
{

    protected $pageLimit;

    public function _construct()
    {
        $this->pageLimit = config('settings.pageLimit');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin/couponsList');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $services = Service::all();

        return view('admin/couponForm', compact('services'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = array(
            'name' => 'required',
            'code' => 'required|unique:coupons',
            'type' => 'required',
            'discount' => 'required|numeric',
            'minAmount' => 'required|numeric',
            'maxDiscountAmount' => 'required|numeric',
            'startDateTime' => 'required|date',
            'endDateTime' => 'required|date',
        );

        $string = strlen($request['code']);
        $result = preg_replace("/[^a-zA-Z0-9]+/", "", $request['code']);
        if ($string != strlen($result)) {
            return redirect()->back()->with('error_message', trans('admin/coupons.coupon_code_not_valid', ['code' => $request['code']]));
        }
        if ($request->type == 'Percent' and $request->discount > 100) {
            return redirect()->back()->with('error_message', trans('admin/coupons.coupon_discount_not_valid', ['value' => 100]));
        }

        if (strlen($result) < 6) {
            return redirect()->back()->with('error_message', trans('admin/coupons.coupon_code_not_valid', ['code' => $request['code']]));
        }


        $data = $request->all();

        //Adding 1 day to end date
        $date = date_create($data['endDateTime']);
        date_add($date, date_interval_create_from_date_string("1 day"));
        $data['endDateTime'] = date_format($date, "Y-m-d");

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if (!isset($data['services'])) {
            $data['all_services'] = 1;
        }
        $coupon = Coupon::create($data);

        if (isset($data['services'])) {
            foreach ($data['services'] as $service_id) {
                $service_data = array(
                    'coupon_id' => $coupon->id,
                    'service_id' => $service_id
                );

                couponService::create($service_data);
            }
        }

        return redirect()->route('coupons.index')->with('success_message', trans('admin/coupons.coupon_add_message'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $coupon = Coupon::find($id);



        //Subtracting a day from end date
        //Removing Time from DateTime
        //date_sub($date, date_interval_create_from_date_string("1 day"));
        // date('Y-m-d', strtotime('-1 day', strtotime($date)));
        $coupon->endDateTime = date('Y-m-d', strtotime('-1 day', strtotime($coupon->endDateTime)));
        $date = date_create($coupon->startDateTime);
        $coupon->startDateTime = date_format($date, "Y-m-d");;

        $services = Service::all();
        $coupon_services = couponService::where('coupon_id', $id)->get();

        if ($coupon) {
            return view('admin/couponForm', compact('coupon', 'services', 'coupon_services'));
        } else {
            return redirect('admin/couponForm')->with('error_message', trans('admin/coupons.coupon_invalid_message'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = array(
            'name' => 'required',
            'code' => 'required|unique:coupons,code,' . $id,
            'type' => 'required',
            'discount' => 'required|numeric',
            'minAmount' => 'required|numeric',
            'maxDiscountAmount' => 'required|numeric',
            'startDateTime' => 'required|date',
            'endDateTime' => 'required|date',
        );


        $string = strlen($request['code']);
        $result = preg_replace("/[^a-zA-Z0-9]+/", "", $request['code']);
        if ($string != strlen($result)) {
            return redirect()->back()->with('error_message', trans('admin/coupons.coupon_code_not_valid', ['code' => $request['code']]));
        }

        if (strlen($result) < 6) {
            return redirect()->back()->with('error_message', trans('admin/coupons.coupon_must_have_6_digits', ['code' => $request['code']]));
        }

        if ($request->type == 'Percent' and $request->discount > 100) {
            return redirect()->back()->with('error_message', trans('admin/coupons.coupon_discount_not_valid', ['value' => 100]));
        }
        $coupon = Coupon::findOrFail($id);
        $data = $request->all();

        //Adding 1 day to end date
        $date = date_create($data['endDateTime']);
        date_add($date, date_interval_create_from_date_string("1 day"));
        $data['endDateTime'] = date_format($date, "Y-m-d");

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if (!isset($data['services'])) {
            $data['all_services'] = 1;
        } else {
            $data['all_services'] = 0;
        }
        $coupon->update($data);

        $coupon_services = couponService::where('coupon_id', $coupon->id)->get();

        //First Destroying all Services for Coupon
        foreach ($coupon_services as $service) {
            couponService::destroy($service->id);
        }

        //Recreating Updated Services for Coupon
        if (isset($data['services'])) {
            foreach ($data['services'] as $service_id) {
                $service_data = array(
                    'coupon_id' => $coupon->id,
                    'service_id' => $service_id
                );

                couponService::create($service_data);
            }
        }

        return redirect()->route('coupons.index')->with('success_message', trans('admin/coupons.coupon_update_message'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Coupon::destroy($id);

        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/coupons.coupon_delete_message');
        echo json_encode($array);
    }

    public function getCouponData()
    {
        // DB table to use
        $table = 'coupons';

        // Table's primary key
        $primaryKey = 'id';

        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            array('db' => 'name', 'dt' => 0, 'field' => 'name'),
            array('db' => 'code', 'dt' => 1, 'field' => 'code'),
            array('db' => 'maxTotalUse', 'dt' => 2, 'field' => 'maxTotalUse'),
            array('db' => 'id', 'dt' => 3, 'formatter' => function ($d, $row) {
                $count = 0;
                if ($d) {
                    $count = CouponHistory::where('coupon_id', $d)->get()->count();
                }
                return $count;
            }, 'field' => 'maxTotalUsed'),
            array('db' => 'maxUseCustomer', 'dt' => 4, 'field' => 'maxUseCustomer'),
            array('db' => 'type', 'dt' => 11, 'field' => 'type'),

            array('db' => 'discount', 'dt' => 5, 'formatter' => function ($d, $row) {

                if ($d) {
                    if ($row['type'] == 'Percent') {
                        return $d . ' %';
                    } else {
                        return \Config::get('constants.CURRENCY_SYMBOL') . ' ' . $d;
                    }
                }
            }, 'field' => 'discount'),
            array('db' => 'startDateTime', 'dt' => 6, 'formatter' => function ($d, $row) {
                $date = date_create($d);
                return date_format($date, "Y-m-d");
            }, 'field' => 'startDateTime'),
            array('db' => 'endDateTime', 'dt' => 7, 'formatter' => function ($d, $row) {
                $date = date_create($d);
                //date_sub($date, date_interval_create_from_date_string("1 day"));
                $end_date = date('Y-m-d', (strtotime('-1 day', strtotime(date_format($date, "Y-m-d")))));
                return $end_date;
            }, 'field' => 'endDateTime'),
            array('db' => 'status', 'dt' => 8, 'formatter' => function ($d, $row) {
                if ($row['status']) {
                    return '<a href="javascript:;" class="btn btn-success status-btn" id="' . $row['id'] . '" title="' . trans('admin/common.click_to_inactive') . '" data-toggle="tooltip">' . trans('admin/common.active') . '</a>';
                } else {
                    return '<a href="javascript:;" class="btn btn-danger status-btn" id="' . $row['id'] . '" title="' . trans('admin/common.click_to_active') . '" data-toggle="tooltip">' . trans('admin/common.inactive') . '</a>';
                }
            }, 'field' => 'status'),
            array('db' => 'id', 'dt' => 9, 'formatter' => function ($d, $row) {
                $operation = ' <a href="coupons/' . $d . '/edit" class="btn btn-primary d-inline-block" title="' . trans('admin/common.edit') . '" data-toggle="tooltip"><i class="fa fa-pencil"></i></a> <a href="javascript:;" id="' . $d . '" class="btn btn-danger delete-btn" title="' . trans('admin/common.delete') . '" data-toggle="tooltip"><i class="fa fa-times"></i></a>';
                return $operation;
            }, 'field' => 'action'),
            array('db' => 'created_at', 'dt' => 10, 'field' => 'created_at')
        );
        // SQL server connection information
        $sql_details = array(
            'user' => config('database.connections.mysql.username'),
            'pass' => config('database.connections.mysql.password'),
            'db' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host')
        );

        $joinQuery = "";
        $extraWhere = "";
        $groupBy = "";

        echo json_encode(
            SSP::simple($_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy)
        );
    }

    public function changeCouponStatus(Request $request)
    {
        $data = $request->all();
        $coupon = Coupon::find($data['id']);

        if ($coupon->status) {
            $coupon->status = '0';
        } else {
            $coupon->status = '1';
        }
        $coupon->save();

        $array = array();
        $array['status'] = $coupon->status;
        $array['success'] = true;
        $array['message'] = trans('admin/coupons.coupon_status_message');
        echo json_encode($array);
    }
}
