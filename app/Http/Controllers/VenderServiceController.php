<?php

namespace App\Http\Controllers;

use Validator;
use App\VenderService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VenderServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\VenderService  $venderService
     * @return \Illuminate\Http\Response
     */
    public function show(VenderService $venderService)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\VenderService  $venderService
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $venderService = VenderService::find($id);

        if ($venderService) {
            return view('admin/editVendorService', compact('venderService'));
        } else {
            return redirect('admin/services')->with('error_message', trans('admin/service.service_invalid_message'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\VenderService  $venderService
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $venderService = VenderService::findOrFail($id);
        $data = $request->all();

        $rules = array(
            'price' => 'required|integer'
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $venderService->update($data);

        return redirect(route("editVender", $venderService->vender_id))->with('success_message', trans('admin/user.user_update_message'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\VenderService  $venderService
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $data = $request->all();
        
        VenderService::destroy($data['id']);

        $array = array();
        $array['success'] = true;
        $array['message'] = trans('admin/user.user_delete_message');
        echo json_encode($array);
    }
}
