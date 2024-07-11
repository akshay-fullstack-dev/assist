<?php

namespace App\Http\Controllers\Admin;

use App\slot;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Datatable\SSP;
use Illuminate\Support\Facades\URL;
use File;
use Validator;

class SlotController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $pageLimit;

    public function __construct(Slot $slot)
    {
        $this->slot = $slot;
        $this->pageLimit = config('settings.pageLimit');
        $this->response['data'] = new \stdClass();
    }

    public function index()
    {
        $slots = Slot::orderBy('day')->orderBy('slot_from')->get();

        return view('admin.slotsList', compact('slots'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.slotsAdd');
    }

    public function getSlotData()
    {


        /*
         * DataTables example server-side processing script.
         *
         * Please note that this script is intentionally extremely simply to show how
         * server-side processing can be implemented, and probably shouldn't be used as
         * the basis for a large complex system. It is suitable for simple use cases as
         * for learning.
         *
         * See http://datatables.net/usage/server-side for full details on the server-
         * side processing requirements of DataTables.
         *
         * @license MIT - http://datatables.net/license_mit
         */

        /*         * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * Easy set variables
         */

        // DB table to use
        $table = 'slots';

        // Table's primary key
        $primaryKey = 'id';

        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            array('db' => 'day', 'dt' => 0, 'field' => 'day'),
            array('db' => 'slot_from', 'dt' => 1, 'field' => 'slot_from'),
            array('db' => 'slot_to', 'dt' => 2, 'field' => 'slot_to'),
            array('db' => 'id', 'dt' => 3, 'formatter' => function ($d, $row) {
                $operation = '<a href="slots/' . $d . '/edit" class="btn btn-primary" title="' . trans('admin/common.edit') . '" data-toggle="tooltip"><i class="fa fa-pencil"></i></a>&nbsp;';
                $operation .= '<a href="javascript:;" id="' . $d . '" class="btn btn-danger delete-btn" title="' . trans('admin/common.delete') . '" data-toggle="tooltip"><i class="fa fa-times"></i></a>&nbsp;';
                return $operation;
            }, 'field' => 'id'),
        );

        // SQL server connection information
        $sql_details = array(
            'user' => config('database.connections.mysql.username'),
            'pass' => config('database.connections.mysql.password'),
            'db' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host'),
        );

        $joinQuery = null;
        $extraWhere = "";
        $groupBy = "";




        echo json_encode(
            SSP::simple($_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy)
        );
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
            'day' => 'required',
            'slot_from' => 'required',
            'slot_to' => 'required',
        );

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $slot = slot::create($data);
        echo $lastInsertId = $slot->id;

        if ($lastInsertId) {
            return 1;
        }
        //return redirect()->route('slots.index')->with('success_message', trans('admin/slot.slot_add_message'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\slot  $slot
     * @return \Illuminate\Http\Response
     */
    public function show(slot $slot)
    { }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\slot  $slot
     * @return \Illuminate\Http\Response
     */
    public function edit(slot $slot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\slot  $slot
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $data = $request->all();

        $slot = Slot::find($data['slot_id']);

        $rules = array(
            'day' => 'required',
            'slot_from' => 'required',
            'slot_to' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $slot->update($data);

        return redirect()->route('slots.index')->with('success_message', trans('admin/slot.slot_update_message'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\slot  $slot
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        slot::destroy($id);

        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/slot.slot_delete_message');
        echo json_encode($array);
    }

    public function checkOverlappingSlots(Request $request)
    {
        $data = $request->all();

        if (isset($data['slot_id'])) {
            //case 1 Example: existing slot 5:00 - 6:00, new slot 4:30 - 5:30  //Overlapping from left side of existing slot
            $overlap_slots = slot::where('day', $data['day'])->where('slot_from', '>', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', '>', $data['slot_to'])->where('id', '<>', $data['slot_id'])
                //case 2 Example: existing slot 5:00 - 6:00, new slot 5:30 - 6:30  //Overlapping from right side of existing slot
                ->orWhere('day', $data['day'])->where('slot_from', '<', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', '<', $data['slot_to'])->where('id', '<>', $data['slot_id'])
                //case 3 Example: existing slot 5:00 - 6:00, new slot 4:30 - 6:30  //Existing slot within new slot
                ->orWhere('day', $data['day'])->where('slot_from', '>', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', '<', $data['slot_to'])->where('id', '<>', $data['slot_id'])
                //case 4 Example: existing slot 5:00 - 6:00, new slot 5:15 - 5:45  //New Slot within existing slot
                ->orWhere('day', $data['day'])->where('slot_from', '<', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', '>', $data['slot_to'])->where('id', '<>', $data['slot_id'])
                //case 5 Example: existing slot 5:00 - 6:00, new slot 5:00 - 7:00  //Start Time same, end time of new slot greater than existing slot
                ->orWhere('day', $data['day'])->where('slot_from', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', '<', $data['slot_to'])->where('id', '<>', $data['slot_id'])
                //case 6 Example: existing slot 5:00 - 6:00, new slot 5:00 - 5:30  //Start time same, end time of new slot smaller than existing slot
                ->orWhere('day', $data['day'])->where('slot_from', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', '>', $data['slot_to'])->where('id', '<>', $data['slot_id'])
                //case 7 Example: existing slot 5:00 - 6:00, new slot 4:30 - 6:00  //End time same, start time of new slot smaller than existing slot
                ->orWhere('day', $data['day'])->where('slot_from', '>', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', $data['slot_to'])->where('id', '<>', $data['slot_id'])
                //case 8 Example: existing slot 5:00 - 6:00, new slot 5:30 - 6:00  //End time same, start time of new slot greater than existing slot
                ->orWhere('day', $data['day'])->where('slot_from', '<', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', $data['slot_to'])->where('id', '<>', $data['slot_id'])
                ->count();
        } else {
            $overlap_slots = slot::where('day', $data['day'])->where('slot_from', '>', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', '>', $data['slot_to'])
                ->orWhere('day', $data['day'])->where('slot_from', '<', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', '<', $data['slot_to'])
                ->orWhere('day', $data['day'])->where('slot_from', '>', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', '<', $data['slot_to'])
                ->orWhere('day', $data['day'])->where('slot_from', '<', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', '>', $data['slot_to'])
                ->orWhere('day', $data['day'])->where('slot_from', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', '<', $data['slot_to'])
                ->orWhere('day', $data['day'])->where('slot_from', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', '>', $data['slot_to'])
                ->orWhere('day', $data['day'])->where('slot_from', '>', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', $data['slot_to'])
                ->orWhere('day', $data['day'])->where('slot_from', '<', $data['slot_from'])->where('slot_from', '<', $data['slot_to'])->where('slot_to', '>', $data['slot_from'])->where('slot_to', $data['slot_to'])
                ->count();
        }

        if ($overlap_slots) {
            $array['overlap_slots_exists'] = true;
        } else {
            $array['overlap_slots_exists'] = false;
        }

        $same_slot = slot::where('day', $data['day'])->where('slot_from', $data['slot_from'])->where('slot_to', $data['slot_to'])->count();
        if ($same_slot) {
            $array['same_slot_exists'] = true;
        } else {
            $array['same_slot_exists'] = false;
        }

        echo json_encode($array);
    }
}
