<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Datatable\SSP;
use App\Http\Controllers\Controller;
use App\Schedule as Schedule;
use App\Service as Service;
use File;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\URL;
use App\ServiceCategory;
use App\ServiceFrequency;
use Illuminate\Support\Facades\Config;

class ServicesController extends Controller
{

    /**
     * Service Model
     * @var Service
     */
    protected $service;
    protected $pageLimit;

    /**
     * Inject the models.
     * @param Service $service
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
        $this->pageLimit = config('settings.pageLimit');
    }

    /**
     * Display a listing of services
     *
     * @return Response
     */
    public function index()
    {
        // Grab all the services
        $services = Service::paginate($this->pageLimit);
        // Show the service\
        return view('admin/servicesList', compact('services'));
    }

    /**
     * Show the form for creating a new service
     *
     * @return Response
     */
    public function create()
    {
        $categories = ServiceCategory::where('status', '1')->pluck('cat_name', 'id');
        $selected_frequency_ids = [];
        $services_frequency = ServiceFrequency::get();
        return view('admin.services', compact('selected_frequency_ids', 'services_frequency'))->withcategories($categories);
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

        $startTimeRequired = $endTimeRequired = '';
        $rules = array(
            'title' => 'required|unique:services',
            'image' => 'required|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'description' => 'required',
            'cat_id' => 'required',
            'service_question' => 'nullable|string',
            'option_1' => 'nullable|string',
            'option_2' => 'nullable|string',
            'option_1_price' => 'nullable|numeric',
            'option_2_price' => 'nullable|numeric',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if (isset($request->image)) {
            if ($request->file('image')->isValid()) {
                $oldFile = \Config::get('constants.SERVICE_IMAGES') . $request->image;
                if (File::exists($oldFile)) {
                    File::delete($oldFile);
                }
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $image->move('assets/services/', $filename);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $data['image'] = $filename;
        }

        $service = Service::create($data);
        if (isset($request->service_frequency) and !empty($request->service_frequency)) {
            foreach ($request->service_frequency as $service_frequency) {
                $service->selected_service_frequencies()->updateOrCreate(
                    ['frequency_id' => $service_frequency['frequency_id']],
                    ['service_price' => $service_frequency['price']]
                );
            }
        }
        if ($request->option and !empty($request->option)) {
            foreach ($request->option as $option) {
                $service->service_additional_questions()->create(
                    [
                        'option' => $option['option'],
                        'price' => $option['price']
                    ]
                );
            }
        }
        $lastInsertId = $service->id;
        return redirect()->route('services.index')->with('success_message', trans('admin/service.service_add_message'));
    }

    /**
     * Display the specified service.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $service = Service::with('selected_service_frequencies.service_frequencies')->findOrFail($id);
        $services_frequency = ServiceFrequency::get();
        return view('admin.services.serviceDetail', compact('service', 'services_frequency'));
    }

    /**
     * Show the form for editing the specified service.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $all_categories = ServiceCategory::all()->pluck('cat_name', 'id');
        $service = Service::with(['selected_service_frequencies.service_frequencies', 'service_additional_questions'])->find($id);
        $services_frequency = ServiceFrequency::get();
        if ($service) {
            return view('admin/services', compact('service', 'services_frequency'))->withcategories($all_categories);
        } else {
            return redirect('admin/services')->with('error_message', trans('admin/service.service_invalid_message'));
        }
    }
    // ServiceAdditionalOptions
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $data = $request->all();
        $rules = array(
            'title' => 'required',
            'image' => 'mimes:jpeg,png,jpg,gif,svg|max:4096',
            'description' => 'required',
            'cat_id' => 'required',
            'service_frequency' => 'nullable',
            'service_frequency_price' => 'nullable|required_if:service_frequency,!=,null|numeric',
            'service_question' => 'nullable|string',

            'option.*.price' => 'nullable|string',
            'option.*.price' => 'nullable|numeric',

        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if (isset($request->image)) {
            if ($request->file('image')->isValid()) {
                $oldFile = \Config::get('constants.SERVICE_IMAGES') . $request->image;
                if (File::exists($oldFile)) {
                    File::delete($oldFile);
                }
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $image->move('assets/services/', $filename);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $data['image'] = $filename;
        }
        if (isset($request->service_frequency) and !empty($request->service_frequency)) {
            foreach ($request->service_frequency as $service_frequency) {
                $service->selected_service_frequencies()->updateOrCreate(
                    ['frequency_id' => $service_frequency['frequency_id']],
                    ['service_price' => $service_frequency['price']]
                );
            }
        }

        if ($request->option and !empty($request->option)) {
            foreach ($request->option as $option) {
                $service->service_additional_questions()->updateOrCreate(
                    ['id' => $option['id']],
                    [
                        'option' => $option['option'],
                        'price' => $option['price']
                    ]
                );
            }
        }
        $service->update($request->only(['title', 'description', 'status', 'service_question', 'image', 'parent_id', 'cat_id']));

        return redirect()->route('services.index')->with('success_message', trans('admin/service.service_update_message'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        Schedule::where('service_id', $id)->delete();
        Service::destroy($id);

        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/service.service_delete_message');
        echo json_encode($array);
    }

    public function changeServiceStatus(Request $request)
    {
        $array = array();
        $data = $request->all();

        $service = Service::find($data['id']);
        $catID = $service->cat_id;

        $category = ServiceCategory::where(['id' => $catID])->get();

        if (!$category['0']->status) {
            $array['message'] = 'Category of the selected service is inactive.';
            return json_encode($array);
        }

        if ($service->status) {
            $service->status = '0';
        } else {
            $service->status = '1';
        }
        $service->save();

        $array['success'] = true;
        $array['message'] = trans('admin/service.service_status_message');
        echo json_encode($array);
    }

    public function getServicesData()
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
        $table = 'services';

        // Table's primary key
        $primaryKey = 'id';

        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        //{!! Config::get('constants.CURRENCY_SYMBOL') !!}
        $columns = array(
            array('db' => 'services.title', 'dt' => 0, 'field' => 'title'),
            array('db' => 's_c.cat_name', 'dt' => 1, 'field' => 'cat_name'),
            array('db' => 'services.image', 'dt' => 2, 'field' => 'image', 'formatter' => function ($d, $row) {
                $html = '';
                if ($row['image']) {
                    //return '<img src="' . URL::asset('/assets/services/' . $d) . '"  height="40" width="60">';

                    $html .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#' . $row['id'] . '"><img src="' . URL::asset('/assets/services/' . $d) . '"  height="40" width="60"></a>';
                    $html .= '<div class="modal fade" id="' . $row['id'] . '" role="dialog">';
                    $html .= '<div class="modal-dialog">';
                    $html .= '<div class="modal-content">';
                    $html .= '<div class="modal-body">';
                    $html .= '<img src="' . URL::asset('/assets/services/' . $d) . '" height="100%" width="100%">';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                }
                return $html;
            }),
            array('db' => 'services.status', 'dt' => 3, 'formatter' => function ($d, $row) {
                if ($row['status']) {
                    return '<a href="javascript:void(0);" class="btn btn-success status-btn" id="' . $row['id'] . '" title="' . trans('admin/common.click_to_inactive') . '" data-toggle="tooltip">' . trans('admin/common.active') . '</a>';
                } else {
                    return '<a href="javascript:void(0);" class="btn btn-danger status-btn" id="' . $row['id'] . '" title="' . trans('admin/common.click_to_active') . '" data-toggle="tooltip">' . trans('admin/common.inactive') . '</a>';
                }
            }, 'field' => 'status'),
            array('db' => 'services.id', 'dt' => 4, 'formatter' => function ($d, $row) {

                $operation = '<a href="services/' . $d . '/edit/" class="btn btn-primary" title="' . trans('admin/common.edit') . '" data-toggle="tooltip"><i class="fa fa-pencil"></i></a>&nbsp;';

                $operation .= '<a href="javascript:;" id="' . $d . '" class="btn btn-danger delete-btn" title="' . trans('admin/common.delete') . '" data-toggle="tooltip"><i class="fa fa-times"></i></a>&nbsp;';

                return $operation;
            }, 'field' => 'id')
            // array('db' => 'services.created_at', 'dt' => 5, 'field' => 'created_at')
        );

        // SQL server connection information
        $sql_details = array(
            'user' => config('database.connections.mysql.username'),
            'pass' => config('database.connections.mysql.password'),
            'db' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host'),
        );

        $joinQuery = ' LEFT JOIN service_categories s_c ON s_c.id = services.cat_id ';
        $extraWhere = "";
        $groupBy = "";
        echo json_encode(
            SSP::simple($_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy)
        );
    }
}
