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
                    <div class="box-body table-responsive">
                        <table id="user_list" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{!! trans('admin/notification.name') !!}</th>
                                    <!--<th>{!! trans('admin/notification.type') !!}</th>-->
                                    <th>{!! trans('admin/notification.title') !!}</th>
                                    <th>{!! trans('admin/notification.message') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                                 
                                @if($notifications->count() > 0)
                                @foreach($notifications as $notification)
                                
                                
                                <tr>
                                    <td>@if(isset($notification->getUser->firstname)) {{ $notification->getUser->firstname .' '.$notification->getUser->lastname  }} @endif</td>
                                    {{-- <!--<td>{{ $notification->status->label}}</td>--> --}}
                                    <td>{{ $notification->title}}</td>
                                    <td>{{ $notification->message}}</td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td>No Record found.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                        @if($type=='all')
                        <div class="box-footer clearfix">
                            <div class="col-md-12 text-center pagination pagination-sm no-margin">
                                @if($notifications)
                                {!! $notifications->render() !!}
                                @endif
                            </div>
                            <div class="col-md-12 text-center">
                                <a class="btn">{!! trans('admin/common.total') !!} {!! $notifications->total() !!} </a>
                            </div>
                        </div><!-- /. box-footer -->
                        @endif
                    </div> <!-- /. box body -->
                </div> <!-- /.box -->
            </div> <!-- /.col-xs-12 -->
        </div><!-- /.row (main row) -->

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop
