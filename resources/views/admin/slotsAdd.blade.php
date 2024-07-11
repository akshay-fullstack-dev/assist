@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/service.services') !!}
@stop
@section('css')
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css" rel="stylesheet">

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script> 
@stop
@section('styles')
@stop
{{-- Content --}}
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/sidebar.add_slots') !!}</h1>
        <!-- <ol class="breadcrumb">
          <li><a href="/admin"><i class="fa fa-dashboard"></i> {!! trans('admin/common.home') !!}</a></li>
          <li class="active">{!! trans('admin/sidebar.add_slots') !!}</li>
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
                    @if(isset($slot))
                    {!! Form::model($slot, array('route' => array('slots.update', $slot->id), 'method' => 'PATCH', 'id' => 'slot-form', 'files' => true )) !!}
                    @else
                    {!! Form::open(array('route' => 'slots.store', 'id' => 'slot-form', 'files' => true)) !!}
                    @endif
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                {!! Form::label('title', trans('admin/sidebar.day')) !!}
                                <select name='day' class="form-control">
                                    <option value='1'>{{ trans('admin/sidebar.mon') }}</option>
                                    <option value='2'>{{ trans('admin/sidebar.tue') }}</option>
                                    <option value='3'>{{ trans('admin/sidebar.wed') }}</option>
                                    <option value='4'>{{ trans('admin/sidebar.thru') }}</option>
                                    <option value='5'>{{ trans('admin/sidebar.fri') }}</option>
                                    <option value='6'>{{ trans('admin/sidebar.sat') }}</option>
                                    <option value='7'>{{ trans('admin/sidebar.sun') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">

                                <div class="form-group has-feedback">
                                    {!! Form::label('title', trans('admin/sidebar.from')) !!}
                                    {!! Form::text('slot_from', old('slot_from'),array('class'=>'form-control', 'id' => 'from_time')) !!}
                                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                {!! Form::label('title', trans('admin/sidebar.to')) !!}
                                {!! Form::text('slot_to', old('slot_to'),array('class'=>'form-control', 'id' => 'to_time')) !!}
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        {!! Form::submit(trans('admin/common.submit'),array('class'=>'btn btn-primary', 'id'=>'submitform')) !!}
                        <a href="{!! URL::route('slots.index') !!}" class="btn btn-default">{!! trans('admin/common.cancel')
                            !!}</a>
                    </div>
                    {!! Form::close()!!}
                </div> <!-- /.box -->
            </div> <!-- /.col-xs-12 -->
        </div><!-- /.row (main row) -->

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop
@section('scripts')
<script type="text/javascript">
$(document).ready(function () {
    $('#from_time').datetimepicker({
        format: 'H:s',
    });

    $('#to_time').datetimepicker({
        format: 'H:s',
    });
});
</script>
@stop
<!-- @section('scripts')
@stop -->