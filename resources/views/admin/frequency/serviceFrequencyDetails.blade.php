@extends('admin.layouts.default')
{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/service.service_frequency') !!}
@stop
@section('styles')
<style>
  .detailBox>.row:nth-of-type(2n+1) {
    background-color: #f9f9f9;
  }

  .detailBox>.row {
    margin: 0px 0px 5px 0px !important;
  }

  .detailBox>.row {
    padding: 10px !important;
  }

</style>
@stop
{{-- Content --}}
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <a href="{!! url('/admin/service/frequency') !!}" class="btn btn-primary pull-right">{!! trans('admin/common.back') !!}</a>
    <h1>{!! trans('admin/service.add_service_frequency') !!}</h1>
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
          @if(isset($serviceFrequency))
          {!! Form::model($serviceFrequency, array('url' => array("admin/service/frequency/update/?id=$serviceFrequency->id"), 'method' => 'put', 'id' => 'service-form', 'files' => true )) !!}
          @else
          {!! Form::open(array('url' => 'admin/service/frequency','method'=>'post', 'id' => 'service-category-form', 'files' => true)) !!}
          @endif
          <div class="box-body">
            <div class="form-group has-feedback">
              {!! Form::label('frequency_name', trans('admin/service.frequency_name')) !!}
              {!! Form::text('frequency_name', old('frequency_name'), array('class'=>'form-control')) !!}

              <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
            </div>

            {{-- frequency days --}}
            <div class="form-group has-feedback">
              {!! Form::label('frequency_day',trans('admin/service.frequency_days')) !!}
              {!! Form::text('frequency_day', old('frequency_day'), array('class'=>'form-control')) !!}
            </div>
          </div>
          <div class="box-footer">
            {!! Form::submit(trans('admin/common.submit'),array('class'=>'btn btn-primary', 'id'=>'submitform')) !!}
            <a href="{!! url('admin/service/frequency') !!}" class="btn btn-default">{!! trans('admin/common.cancel') !!}</a>
          </div>
          {!! Form::close()!!}
        </div> <!-- /.box -->
      </div> <!-- /.col-xs-12 -->
    </div><!-- /.row (main row) -->
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop
