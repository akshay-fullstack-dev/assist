@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/servicecategory.categories') !!}
@stop
@section('styles')
@stop
{{-- Content --}}
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/servicecategory.add_category') !!}</h1>
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
            </div>
            <div class="col-xs-12">
                <div class="box">
                    @if(isset($category))
                    {!! Form::model($category, array('route' => array('updateCategory'), 'method' => 'POST', 'id' => 'service-form', 'files' => true )) !!}
                    @else
                    {!! Form::open(array('route' => 'saveCategory', 'id' => 'service-category-form', 'files' => true)) !!}
                    @endif
                    <div class="box-body">
                        <div class="form-group has-feedback">
                            {!! Form::label('cat_name', trans('admin/servicecategory.name')) !!}
                            {!! Form::text('cat_name', old('cat_name'), array('class'=>'form-control')) !!}
                            @if(isset($category->id))
                            <input type="hidden" name="id" value="{{ $category->id }}">
                            @endif
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('image', trans('admin/servicecategory.add_image')) !!}
                            <div class=row>

                                <div class="col-md-9">
                                    {!! Form::file('image', array('class'=>'form-control','style'=>'height:auto;')) !!}
                                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                </div>
                                <div class="col-md-3">
                                    
                                    @if(isset($category->image))
                                    <img style="width:100px;" src="{{ url('assets/category/'.$category->image) }}">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        {!! Form::submit(trans('admin/common.submit'),array('class'=>'btn btn-primary', 'id'=>'submitform')) !!}
                        <a href="{!! URL::route('listCategory') !!}" class="btn btn-default">{!! trans('admin/common.cancel') !!}</a>
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