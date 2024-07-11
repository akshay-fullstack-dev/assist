@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/notification.list') !!}
@stop
@section('styles')
<link href="{!! asset('assets/admin/plugins/bootstrap3-editable/css/bootstrap-editable.css') !!}" rel="stylesheet"
      type="text/css" />
@stop
{{-- Content --}}
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/notification.list') !!}</h1>
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

                    {!! Form::open(array('url' => 'admin/sendnotification', 'id' => 'sendnotification-form')) !!}

                    <div class="box-body">

                        <div class="form-group">
                            <div class="row">


                                <div class="col-sm-12">
                                    {!! Form::label('mode', trans('admin/user.select_vendor')) !!}
                                </div>
                                <div class="col-sm-12">
                                    {!! Form::select('user_id', $users) !!}
                                </div>

                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-12">
                                    {!! Form::label('mode', trans('admin/user.message')) !!}
                                </div>
                                <div class="col-sm-12">

                                    {!! Form::textarea('message', null, ['class'=>'form-control']) !!}

                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="box-footer">
                        {!! Form::submit(trans('admin/common.submit'),array('class'=>'btn btn-primary', 'id'=>'submitform')) !!}
                        <a href="{!! URL::route('paypalsettings.index') !!}" class="btn btn-default">{!! trans('admin/common.cancel') !!}</a>
                    </div>
                    {!! Form::close()!!}
                </div> <!-- /.box -->
            </div> <!-- /.col-xs-12 -->
        </div><!-- /.row (main row) -->

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop
