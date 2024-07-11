<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Datatable\SSP;
use App\Http\Controllers\Controller;
use App\ServiceFrequency;
use Illuminate\Http\Request;
use Log;

class ServiceFrequencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.frequency.listFrequency');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.frequency.serviceFrequencyDetails');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'frequency_name' => 'required|max:255|unique:service_frequencies,frequency_name',
            'frequency_day' => 'required|numeric',
        ]);
        ServiceFrequency::create($request->only('frequency_name', 'frequency_day'));
        return redirect('admin/service/frequency')->with('success_message', trans('admin/service.successfully_added_new_frequency'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ServiceFrequency  $serviceFrequency
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceFrequency $serviceFrequency)
    {
        return view('admin.frequency.serviceFrequencyDetails');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ServiceFrequency  $serviceFrequency
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $serviceFrequency = ServiceFrequency::findOrFail($request->id);
        return view('admin.frequency.serviceFrequencyDetails')->with(compact('serviceFrequency'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ServiceFrequency  $serviceFrequency
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $request->validate([
            'id' => 'required',
            'frequency_name' => 'required|max:255|unique:service_frequencies,frequency_name',
            'frequency_day' => 'required|numeric',
        ]);
        $serviceFrequency = ServiceFrequency::findOrFail($request->id);
        $serviceFrequency->update($request->only('frequency_name', 'frequency_day'));
        return redirect('admin/service/frequency')->with('success_message', trans('admin/fequency.service_frequency_updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ServiceFrequency  $serviceFrequency
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:service_frequencies,id']);
        ServiceFrequency::destroy($request->id);
        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/fequency.service_frequency_deleted');
        echo json_encode($array);
    }

    public function frequencyData()
    {
        // DB table to use
        $table = 'service_frequencies';

        // Table's primary key
        $primaryKey = 'id';

        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            array('db' => 'frequency_name', 'dt' => 0, 'field' => 'frequency_name'),
            array('db' => 'frequency_day', 'dt' => 1, 'field' => 'frequency_day'),
            array('db' => 'created_at', 'dt' => 2, 'field' => 'created_at'),
            array('db' => 'id', 'dt' => 3, 'formatter' => function ($d, $row) {
                $operation = ' <a href="frequency/' . $d . '/edit/?id=' . $d . '" class="btn btn-primary d-inline-block" title="' . trans('admin/common.edit') . '" data-toggle="tooltip"><i class="fa fa-pencil"></i></a> 

                    <a href="javascript:;" id="' . $d . '" class="btn btn-danger delete-btn" title="' . trans('admin/common.delete') . '" data-toggle="tooltip"><i class="fa fa-times"></i></a>';
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

        $joinQuery = '';
        $extraWhere = "";
        $groupBy = "";
        echo json_encode(
            SSP::simple($_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy)
        );
    }
}
