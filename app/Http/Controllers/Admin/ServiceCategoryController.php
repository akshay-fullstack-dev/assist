<?php

namespace App\Http\Controllers\Admin;

use App\ServiceCategory;
use App\Service;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Validation\Rule;
use DB;
use File;
class ServiceCategoryController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {


        $categories = ServiceCategory::orderBy('created_at', 'desc')->get();
        return view('admin/servicesCategoryList', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return view('admin/serviceCategory');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    public function saveCategory(Request $request) {
        $data = $request->all();
        $rules = array(
            'cat_name' => 'required|unique:service_categories',
            'image' => 'required||mimes:jpeg,png,jpg,gif,svg||max:4096',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if (isset($request->image)) {
            if ($request->file('image')->isValid()) {
                $oldFile = \Config::get('constants.CATEGORY_IMAGES') . $request->image;
                if (File::exists($oldFile)) {
                    File::delete($oldFile);
                }
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $image->move('assets/category/', $filename);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $data['image'] = $filename;
        }
        $data['status'] = '1';
        $cat_service = ServiceCategory::create($data);
        return redirect()->route('listCategory')->with('success_message', trans('admin/servicecategory.category_added_successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ServiceCategory  $serviceCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceCategory $serviceCategory) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ServiceCategory  $serviceCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceCategory $serviceCategory) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ServiceCategory  $serviceCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ServiceCategory $serviceCategory) {
        //
    }

    public function editCategory($id) {
        //$all_category = Service::all()->pluck('title', 'id');
        $getCategory = ServiceCategory::where(['id' => $id])->first();
        if ($getCategory) {
            return view('admin/serviceCategory')->withcategory($getCategory);
        } else {
            return redirect('admin/addCategory')->with('error_message', trans('admin/service.service_invalid_message'));
        }
    }

    public function updateCategory(Request $request) {
        $data = $request->all();

        $rules = array(
            'cat_name' => 'required|unique:service_categories,cat_name',
        );
        $validator = Validator::make($data, [
                    'cat_name' => [
                        'required',
                        Rule::unique('service_categories')->ignore($data['id']),
                    ],
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if (isset($request->image)) {
            if ($request->file('image')->isValid()) {
                $oldFile = \Config::get('constants.CATEGORY_IMAGES') . $request->image;
                if (File::exists($oldFile)) {
                    File::delete($oldFile);
                }
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $image->move('assets/category/', $filename);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $data['image'] = $filename;
        }

        $cat_service = ServiceCategory::where('id', $data['id'])->first();
        $cat_service->cat_name = $data['cat_name'];
        if ($request->hasFile('image')) {
            $cat_service->image = $data['image'];
        }
        $cat_service->save();
        return redirect()->route('listCategory')->with('success_message', trans('admin/servicecategory.service_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ServiceCategory  $serviceCategory
     * @return \Illuminate\Http\Response
     */
    public function deleteCategory($id) {
        
        $specification = ServiceCategory::findOrFail($id)->delete();
        DB::table('services')->where('cat_id', $id)->update(['cat_id' => 0]);
        if($specification) {
            session()->flash('flash_message', 'Category deleted successfully!');
            return redirect()->back();
        }
    }
    
    public function changeServiceStatus(Request $request)
    {
        $data = $request->all();

        $category = ServiceCategory::find($data['id']);
        $services = Service::where(['cat_id' => $data['id']])->get();

        if ($category->status) {
            $category->status = '0';
            foreach($services as $service) {
                $service->status = '0';
                $service->save();
            }
        } else {
            $category->status = '1';
            foreach($services as $service) {
                $service->status = '1';
                $service->save();
            }
        }
        
        $category->save();

        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/service.service_status_message');
        echo json_encode($array);
    }

    public function destroy(ServiceCategory $serviceCategory) {
        //
    }

}
