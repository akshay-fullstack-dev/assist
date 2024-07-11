<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Validator;
use File;
use App\Http\Controllers\Controller;
use App\Helpers\Datatable\SSP;
use App\Service;
use App\Equipment;

class EquipmentController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('admin/equipment/listEquipment');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $services = Service::pluck('title', 'id');
        return view('admin/equipment/equipment', compact('services'));
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
            'name' => 'required',
            'price' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if (isset($request->image)) {
            if ($request->file('image')->isValid()) {
                $oldFile = \Config::get('constants.EQUIPMENT_IMAGES') . $request->image;
                if (File::exists($oldFile)) {
                    File::delete($oldFile);
                }
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $image->move('assets/equipments/', $filename);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $data['image'] = $filename;
        }
        $user = Equipment::create($data);
        return redirect('admin/equipments')->with('success_message', trans('admin/equipment.equipment_add_successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Equipment  $equipment
     * @return \Illuminate\Http\Response
     */
    public function show(Equipment $equipment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Equipment  $equipment
     * @return \Illuminate\Http\Response
     */
    public function edit(Equipment $equipment)
    {
        $services = Service::pluck('title', 'id');
        return view('admin/equipment/equipment', compact('equipment', 'services'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Equipment  $equipment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Equipment $equipment)
    {



        if (isset($request->image)) {

            if ($request->file('image')->isValid()) {
                $oldFile = \Config::get('constants.EQUIPMENT_IMAGES') . $equipment->image;
                if (File::exists($oldFile)) {
                    File::delete($oldFile);
                }
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $image->move('assets/equipments/', $filename);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $data['image'] = $filename;
        }


        $equipment = Equipment::findOrFail($equipment->id);
        $equipment->name = $request->name;
        $equipment->price = $request->price;
        $equipment->service_id = $request->service_id;
        if ($request->image) {
            $equipment->image = $data['image'];
        }
        $equipment->update();

        return redirect('admin/equipments')->with('success_message', trans('admin/equipment.equipment_updated_successfully'));;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Equipment  $equipment
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $equipment = Equipment::findOrFail($id);
        $oldFile = \Config::get('constants.EQUIPMENT_IMAGES') . $equipment->image;
        if (File::exists($oldFile)) {
            File::delete($oldFile);
        }
        Equipment::destroy($id);

        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/equipment.equipment_deleted_successfully');
        echo json_encode($array);
    }

    public function EquipmentData()
    {

        // DB table to use
        $table = 'equipments';

        // Table's primary key
        $primaryKey = 'id';

        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            array('db' => 'equipments.name', 'dt' => 0, 'field' => 'name'),
            array('db' => 'equipments.image', 'dt' => 3, 'formatter' => function ($d, $row) {
                if ($row['image']) {
                    $html = '<img src="' . URL::asset('/assets/equipments/' . $d) . '" data-toggle="modal" data-target="#' . $row['id'] . $row['name'] . '" height="40" width="60">';
                    $html .= '<div class="modal fade" id="' . $row['id'] . $row['name'] . '" role="dialog">';
                    $html .= '<div class="modal-dialog">';
                    $html .= '<div class="modal-content">';
                    $html .= '<div class="modal-header">';
                    $html .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    $html .= '<h4 class="modal-title">Equipment Image</h4>';
                    $html .= '</div>';
                    $html .= '<div class="modal-body">';
                    $html .= '<img src="' . URL::asset('/assets/equipments/' . $d) . '" height="100%" width="100%">';
                    $html .= '</div>';
                    $html .= '<div class="modal-footer">';
                    $html .= '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                    return $html;
                }
            }, 'field' => 'image'),
            array('db' => 'equipments.price', 'dt' => 1, 'formatter' => function ($d, $row) {
                return \Config::get('constants.CURRENCY_SYMBOL') . $d;
            }, 'field' => 'price'),
            array('db' => 'ser.title', 'dt' => 2, 'field' => 'title'),
            array('db' => 'equipments.created_at', 'dt' => 4, 'field' => 'created_at'),
            array('db' => 'equipments.id', 'dt' => 5, 'formatter' => function ($d, $row) {
                $operation = ' <a href="equipments/' . $d . '/edit" class="btn btn-primary d-inline-block" title="' . trans('admin/common.edit') . '" data-toggle="tooltip"><i class="fa fa-pencil"></i></a> <a href="javascript:;" id="' . $d . '" class="btn btn-danger delete-btn" title="' . trans('admin/common.delete') . '" data-toggle="tooltip"><i class="fa fa-times"></i></a>';
                return $operation;
            }, 'field' => 'id'),
        );
        // SQL server connection information
        $sql_details = array(
            'user' => config('database.connections.mysql.username'),
            'pass' => config('database.connections.mysql.password'),
            'db' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host')
        );

        $joinQuery = ' LEFT JOIN services as ser ON ser.id = equipments.service_id ';
        $extraWhere = "";
        $groupBy = "";
        echo json_encode(
            SSP::simple($_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy)
        );
    }
}
