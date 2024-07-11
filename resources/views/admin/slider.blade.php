@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/banners.banners') !!}
@stop
@section('styles')
@stop
{{-- Content --}}
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/banners.banner') !!}</h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Main row -->
        <div class="row">
            <div class="col-md-12">
                <!-- Notifications -->
                @include('admin.includes.notifications')
            </div>
            <div class="col-xs-12">
                <div class="box">
                    @if(isset($banner))
                        {!! Form::model($banner, array('route' => array('slider.update', $banner->id), 'method' => 'PATCH', 'id' => 'banner-form', 'files' => true )) !!}
                    @else
                        {!! Form::open(array('route' => 'slider.store', 'id' => 'banner-form', 'files' => true)) !!}
                    @endif
                    <div class="box-body">
                        <div class="form-group has-feedback">
                            {!! Form::label('name', trans('admin/banners.banner_name')) !!}
                            {!! Form::text('name', old('name'), array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('image', trans('admin/banners.add_image')) !!}
                            <div class=row>
                                <div class="col-md-9">
                                    {!! Form::file('image', array('class'=>'form-control','style'=>'height:auto;')) !!}
                                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                </div>
                                <div class="col-md-3">
                                    @if (isset($banner->name))
                                        <img src="{!! URL::asset('/public/banners/'.$banner->image) !!}"  height="50" width="100">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        {!! Form::submit(trans('admin/common.submit'),array('class'=>'btn btn-primary', 'id'=>'submitform')) !!}
                        <a href="{!! URL::route('slider.index') !!}" class="btn btn-default">{!! trans('admin/common.cancel') !!}</a>
                    </div>
                    {!! Form::close()!!}
                </div> <!-- /.box -->
            </div> <!-- /.col-xs-12 -->
        </div><!-- /.row (main row) -->

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop
{{-- Scripts --}}
@section('scripts')

@stop