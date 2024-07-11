<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Datatable\SSP;
use Illuminate\Support\Facades\URL;
use App\Slider;
use Validator;

class SliderController extends Controller
{
    public function __construct()
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
        return view('admin/sliderList');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin/slider');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $rules = array(
            'name' => 'required|unique:banners',
            'image' => 'required||mimes:jpeg,png,jpg,gif,svg||max:4096',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if ($request->hasFile('image')) { //check the file present or not
            $image = $request->file('image'); //get the file
            $data['image'] = uniqid() . '.' . $image->getClientOriginalExtension(); //get the  file extention
            $destinationPath = public_path('/banners'); //public path folder dir
            if (!$image->move($destinationPath, $data['image'])) {
                return redirect()->back()->withErrors(trans('admin/banners.error_upload_message'))->withInput();
            } //mve to destination you mentioned
        }

        Slider::create($data);
        return redirect()->route('slider.index')->with('success_message', trans('admin/banners.banner_added_successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    { }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $banner = Slider::find($id);

        if ($banner) {
            return view('admin/slider', compact('banner'));
        } else {
            return redirect('admin/slider')->with('error_message', trans('admin/banners.banner_invalid_message'));
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
        $banner = Slider::findOrFail($id);

        $data = $request->all();
        $rules = array(
            'name' => 'required|unique:banners,name,' . $id,
            'image' => 'mimes:jpeg,png,jpg,gif,svg||max:4096',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if ($request->hasFile('image')) { //check the file present or not
            if (file_exists(public_path('/banners/' . $banner->image))) {
                unlink(public_path('/banners/' . $banner->image)); //deleting previous banner
            }
            $image = $request->file('image'); //get the file
            $data['image'] = uniqid() . '.' . $image->getClientOriginalExtension(); //get the  file extention
            $destinationPath = public_path('/banners'); //public path folder dir
            if (!$image->move($destinationPath, $data['image'])) {
                return redirect()->back()->withErrors(trans('admin/banners.error_upload_message'))->withInput();
            } //mve to destination you mentioned
        }
        $banner->update($data);
        return redirect()->route('slider.index')->with('success_message', trans('admin/banners.banner_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $banner = Slider::findOrFail($id);
        if (file_exists(public_path('/banners/' . $banner->image))) {
            unlink(public_path('/banners/' . $banner->image)); //deleting previous banner image
        }

        Slider::destroy($id);

        //        return redirect()->route('banners.index')->with('success_message', trans('admin/banners.banner_delete_message'));

        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/banners.banner_delete_message');
        echo json_encode($array);
    }

    public function getSliderData()
    {
        // DB table to use
        $table = 'banners';

        // Table's primary key
        $primaryKey = 'id';

        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            array('db' => 'name', 'dt' => 0, 'field' => 'name'),
            array('db' => 'image', 'dt' => 1, 'formatter' => function ($d, $row) {
                return '<img src="' . URL::asset('/public/banners/' . $d) . '"  height="40" width="60">';
            }, 'field' => 'image'),
            array('db' => 'status', 'dt' => 2, 'formatter' => function ($d, $row) {
                if ($row['status']) {
                    return '<a href="javascript:;" class="btn btn-success status-btn" id="' . $row['id'] . '" title="' . trans('admin/common.click_to_inactive') . '" data-toggle="tooltip">' . trans('admin/common.active') . '</a>';
                } else {
                    return '<a href="javascript:;" class="btn btn-danger status-btn" id="' . $row['id'] . '" title="' . trans('admin/common.click_to_active') . '" data-toggle="tooltip">' . trans('admin/common.inactive') . '</a>';
                }
            }, 'field' => 'status'),
            array('db' => 'id', 'dt' => 3, 'formatter' => function ($d, $row) {
                $operation = ' <a href="slider/' . $d . '/edit" class="btn btn-primary d-inline-block" title="' . trans('admin/common.edit') . '" data-toggle="tooltip"><i class="fa fa-pencil"></i></a> <a href="javascript:;" id="' . $d . '" class="btn btn-danger delete-btn" title="' . trans('admin/common.delete') . '" data-toggle="tooltip"><i class="fa fa-times"></i></a>';
                return $operation;
            }, 'field' => 'action'),
            array('db' => 'created_at', 'dt' => 4, 'field' => 'created_at')
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

    public function changeBannerStatus(Request $request)
    {
        $data = $request->all();
        $banner = Slider::find($data['id']);

        if ($banner->status) {
            $banner->status = '0';
        } else {
            $banner->status = '1';
        }
        $banner->save();

        $array = array();
        $array['status'] = $banner->status;
        $array['success'] = true;
        $array['message'] = trans('admin/banners.banner_status_message');
        echo json_encode($array);
    }
}
