@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/service.services') !!}
@stop
@section('styles')
@stop
{{-- Content --}}
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/vendors.vendor_service_edit') !!}</h1>


        <!-- <ol class="breadcrumb">
                <li><a href="/admin"><i class="fa fa-dashboard"></i> {!! trans('admin/common.home') !!}</a></li>
                <li class="active">{!! trans('admin/service.services') !!}</li>
            </ol> -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Main row -->
        <div class="row">
            <div class="col-md-12">
                <!-- Notifications -->
                @include('admin.includes.notifications')
                <!-- ./ notifications -->
            </div>
            <div class="col-xs-12">
                <div class="box">
                    {!! Form::model($venderService, array('url' =>'admin/updateVenderService/'.$venderService->id, 'method' => 'PATCH', 'id' => 'service-form',
                    'files' => true )) !!}
                    

                    <div class="box-body">
                        <div class="form-group has-feedback">
                            {!! Form::label('price', trans('admin/vendors.price')) !!}
                            {!! Form::text('price', old("price"),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <!-- <div class="form-group has-feedback">
                            {!! Form::label('lastname', trans('admin/user.lastname')) !!}
                            {!! Form::text('lastname', old("lastname"),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('Gender', trans('admin/user.Gender')) !!} <br>
                            Male {!! Form::radio('gender', '0', (old('gender') == '0'), array('id'=>'', 'class'=>'')) !!}  &nbsp;&nbsp;&nbsp;
                            Female {!! Form::radio('gender', '1', (old('gender') == '1'), array('id'=>'', 'class'=>'')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('phone_number', trans('admin/user.phone_number')) !!}
                            {!! Form::text('phone_number', old("image"),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div> -->
                    </div>
                </div>
                <div class="box-footer">
                    {!! Form::submit(trans('admin/common.submit'),array('class'=>'btn btn-primary', 'id'=>'submitform')) !!}
                    <a href="{{route('editVender', $venderService->vender_id)}}" class="btn btn-default">{!! trans('admin/common.cancel') !!}</a>
                </div>
                {!! Form::close()!!}
            </div> <!-- /.box -->
        </div> <!-- /.col-xs-12 -->
</div><!-- /.row (main row) -->

</section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop

{{-- Scripts --}}
