@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/booking.bookings_list') !!}
@stop

{{-- Content --}}
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/booking.bookings_list') !!}</h1>
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
                        <div class="row no-gutters mrgn_10b">
                            <div class="col-md-8">
                                {!! Form::open(array('route' => 'admin.booking.search', 'id' => 'booking-search-form',
                                'class' => 'form-inline','method' => 'POST')) !!}
                                <div class="form-group">
                                    {!! Form::label('search', trans('admin/common.search')) !!}
                                    {!! Form::select('search_by',array(''=>trans('admin/common.search_by'), 'user' =>
                                    trans('admin/booking.user_name'), 'vendor' => trans('admin/booking.vendor'),
                                    'service' => trans('admin/booking.service'), 'id' =>
                                    trans('admin/booking.order_id')), session('SEARCH.SEARCH_BY') ,
                                    array('class'=>'form-control', 'id' => 'search_by')) !!}
                                </div>
                                <div class="form-group">
                                    <?php //if (session('SEARCH.SEARCH_BY') == 'service' || session('SEARCH.SEARCH_BY') == 'user' || session('SEARCH.SEARCH_BY') == 'booking_date'): ?>
                                    <!--{!! Form::text('search_txt', session('SEARCH.SEARCH_TXT') ,array('id' => 'search_txt', 'class' => 'form-control', 'style' => 'display:none;')) !!}-->
                                    <?php //else: ?>
                                    {!! Form::text('search_txt', session('SEARCH.SEARCH_TXT') ,array('id' =>
                                    'search_txt', 'class' =>
                                    'form-control','placeholder'=>trans('admin/common.search'))) !!}
                                    <?php //endif; ?>
                                </div>
                                <!--                                <div class="form-group">
                                <?php if (session('SEARCH.SEARCH_BY') == 'service'): ?>
                                                                                    {!! Form::select('service_id',$serviceList, session('SEARCH.SERVICE_ID') , array('class'=>'form-control', 'id' => 'service_id', 'style' => 'display:inline-block;')) !!}
                                <?php else: ?>
                                                                                    {!! Form::select('service_id',$serviceList, session('SEARCH.SERVICE_ID') , array('class'=>'form-control', 'id' => 'service_id', 'style' => 'display:none;')) !!}
                                <?php endif; ?>
                                                                </div>-->
                                <!--                                <div class="form-group">
                                <?php if (session('SEARCH.SEARCH_BY') == 'user'): ?>
                                                                                    {!! Form::select('user_id',$userList, session('SEARCH.USER_ID') , array('class'=>'form-control', 'id' => 'user_id', 'style' => 'display:inline-block;')) !!}
                                <?php else: ?>
                                                                                    {!! Form::select('user_id',$userList, session('SEARCH.USER_ID') , array('class'=>'form-control', 'id' => 'user_id', 'style' => 'display:none;')) !!}
                                <?php endif; ?>
                                                                </div>-->
                                <!--                                <div class="form-group">
                                <?php if (session('SEARCH.SEARCH_BY') == 'booking_date'): ?>
                                                                                <div class="input-group search_date" style="display:inline-table;">
                                                                                    {!! Form::text('search_date',session('SEARCH.SEARCH_DATE'),['id' => 'search_date','class' => 'datepicker form-control',]) !!}
                                                                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                                                </div>
                                <?php else: ?>
                                                                                <div class="input-group search_date" style="display:none;">
                                                                                    {!! Form::text('search_date',session('SEARCH.SEARCH_DATE'),['id' => 'search_date','class' => 'datepicker form-control',]) !!}
                                                                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                                                </div>
                                <?php endif; ?>
                                                                </div>-->

                                <div class="form-group">
                                    {!! Form::submit(trans('admin/common.search'), array('id' => 'search', 'name' => '',
                                    'class' => 'btn btn-info')) !!}
                                    {!! Form::button(trans('admin/common.reset'),array('type'=>'submit','id' => 'reset',
                                    'name' => 'reset', 'value' => '1', 'class' => 'btn btn-defult')) !!}
                                </div>
                                {!! Form::close() !!}
                            </div>
                            <div class="col-sm-2  text-right">
                                @php
                                $total_bookings =isset($bookingCount)?$bookingCount:'';
                                @endphp
                                <h6><b>{!! trans('admin/booking.total_bookings') !!}</b>:-{{$total_bookings}} </h6>
                            </div>
                            <div class="col-md-2 text-right">
                                <a href="{!! url('/admin/booking/export') !!}"
                                    class="btn btn-sm btn-info pull-right">{!! trans('admin/booking.export_csv') !!}</a>
                            </div>
                        </div>
                        <table id="booking_list" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="15%">{!! trans('admin/booking.order_id') !!}</th>
                                    <th width="15%">{!! trans('admin/booking.user_name') !!}</th>
                                    <th width="15%">{!! trans('admin/booking.vendor') !!}</th>
                                    <th width="15%">{!! trans('admin/booking.service') !!}</th>
                                    <th width="15%">{!! trans('admin/booking.amount') !!}</th>
                                    <th width="15%">{!! trans('admin/booking.booking_date') !!}</th>
                                    <th width="15%">{!! trans('admin/booking.booking_status') !!}</th>
                                    <th width="10%">{!! trans('admin/common.action') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($bookings))
                                @foreach ($bookings as $data)
                                <tr>
                                    <td>{!! $data->id !!}</td>
                                    <td>{!! $data->user->firstname; !!} {!! $data->user->lastname !!}</td>
                                    <td>{!! $data->vender_name !!}</td>
                                    <td>{!! $data->service_name !!}</td>
                                    <td>{!! Config::get('constants.CURRENCY_SYMBOL') !!} {!!
                                        number_format($data->total_price, 2) !!} </td>
                                    <td>{!! date('Y-m-d', strtotime($data->booking_date)) !!}</td>
                                    <td><a title="{!! trans('admin/booking.onhold') !!}" data-toggle="tooltip">
                                            <select class="form-control status-btn" id="{!! $data->id !!}">

                                                <option value="0" @if( $data->is_onhold == '0') {!!
                                                    'selected="selected"' !!} @endif >Not On hold</option>
                                                <option value="1" @if( $data->is_onhold == '1') {!!
                                                    'selected="selected"' !!} @endif >On hold</option>

                                            </select></a></td>
                                    <!--                                        <td>
                                            <a href="/booking/{!! $data->id !!}/edit" class="spnToggle" id="bookingDetail_<?php echo $data->id; ?>">{!! trans('admin/common.view') !!}</a>
                                            <span id="bookingDetail_<?php echo $data->id; ?>" style="display:none;" class="">
                                                <table class="table table-bordered table-condensed">
                                                    <tbody>
                                    <?php $spots = $data->bookingDetail ?>
                                    <?php foreach ($spots as $key => $spot): ?>
                                                                        <tr>
                                                                            <td width='115'>{!! date('d-m-Y h:i A', strtotime($spot->start_time)) !!} to {!! date('d-m-Y h:i A', strtotime($spot->end_time)) !!}</td>
                                                                        </tr>
                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </span>
                                        </td>-->
                                    <!--                                        <td>
                                            @if($data->status == 'pending')
                                            <select class="form-control status-btn" id="{!! $data->id !!}">
                                                <option value="pending" selected="selected">{!! trans('admin/booking.pending') !!}</option>
                                                <option value="confirm">{!! trans('admin/booking.confirm') !!}</option>
                                                <option value="cancel">{!! trans('admin/booking.cancel') !!}</option>
                                            </select>
                                            @elseif($data->status == 'cancel')
                                            <a class="btn btn-danger" title="{!! trans('admin/booking.cancelled_booking') !!}" data-toggle="tooltip">{!! trans('admin/booking.'.$data->status) !!}</a>
                                            @elseif($data->status == 'confirm')
                                            <a class="btn btn-success" title="{!! trans('admin/booking.confirmed_booking') !!}" data-toggle="tooltip">{!! trans('admin/booking.'.$data->status) !!}</a>
                                            @endif
                                        </td>-->

                                    <td>
                                        <a href="{!! url('admin/booking/'.$data->id) !!}" id="{!! $data->id !!}"
                                            class="btn btn-primary view-btn" title="{!! trans('admin/common.view') !!}"
                                            data-toggle="tooltip"><i class="fa fa-eye"></i></a>&nbsp;

                                        <a href="{!! url('admin/get-booking-chat/'.$data->id) !!}"
                                            id="{!! $data->id !!}" class="btn btn-primary view-btn"
                                            title="{!! trans('admin/common.view_chat') !!}" data-toggle="tooltip"><i
                                                class="fa fa-comments-o"></i></a>

                                        <!--<a href="javascript:;" id="{!! $data->id !!}" class="btn btn-danger delete-btn" title="{!! trans('admin/common.delete') !!}" data-toggle="tooltip"><i class="fa fa-times"></i></a>&nbsp;-->
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="8" class="text-center">
                                        {!! trans('admin/booking.no_bookings_found') !!}
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div> <!-- /. box body -->
                    <div class="box-footer clearfix">
                        <div class="col-md-12 text-center pagination pagination-sm no-margin">
                            @if($bookings)
                            {!! $bookings->render() !!}
                            @endif
                        </div>
                        <div class="col-md-12 text-center">
                            <a class="btn">{!! trans('admin/common.total') !!} {!! $bookings->total() !!} </a>
                        </div>
                    </div><!-- /. box-footer -->
                </div> <!-- /.box -->
            </div> <!-- /.col-xs-12 -->
        </div><!-- /.row (main row) -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop
{{-- Scripts --}}
@section('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $("#booking_list").on('click', '.delete-btn', function () {
            var id = $(this).attr('id');
            var r = confirm("{!! trans('admin/common.delete_confirmation') !!}");
            if (!r) {
                return false
            }
            $.ajax({
                type: "POST",
                url: "booking/" + id,
                data: {
                    _method: 'DELETE',
                    _token: "{!! csrf_token() !!}"
                },
                dataType: 'json',
                beforeSend: function () {
                    $(this).attr('disabled', true);
                    $('.alert .msg-content').html('');
                    $('.alert').hide();
                },
                success: function (resp) {
                    $('.alert:not(".session-box")').show();
                    if (resp.success) {
                        $('.alert-success .msg-content').html(resp.message);
                        $('.alert-success').removeClass('hide');
                    } else {
                        $('.alert-danger .msg-content').html(resp.message);
                        $('.alert-danger').removeClass('hide');
                    }
                    $(this).attr('disabled', false);
                },
                error: function (e) {
                    alert('Error: ' + e);
                }
            });
        });

        $("#booking_list").on('change', '.status-btn', function () {
            var id = $(this).attr('id');
            var val = $(this).val();
            var r = confirm("{!! trans('admin/common.status_confirmation') !!}");
            if (!r) {
                return false
            }
            $.ajax({
                type: "POST",
                url: "{!! URL::to('admin/booking/changeStatus') !!}",
                data: {
                    id: id,
                    value: val,
                    _token: "{!! csrf_token() !!}"
                },
                dataType: 'json',
                beforeSend: function () {

                },
                success: function (resp) {
                    window.location.href = window.location.href
                },
                error: function (e) {
                    alert('Error: ' + e);
                }
            });
        });

        $(".spnToggle").click(function () {
            $('span#' + $(this).attr('id')).toggle();
        });

//        $('#search_by').change(function () {
//            if ($('#search_by').val() == 'service') {
//                $("#service_id").show();
//                $("#user_id").hide();
//                $("#search_txt").hide();
//                $(".search_date").hide();
//            }else if ($('#search_by').val() == 'user') {
//                $("#service_id").hide();
//                $("#user_id").show();
//                $("#search_txt").hide();
//                $(".search_date").hide();
//            }else if ($('#search_by').val() == 'booking_date') {
//                $("#service_id").hide();
//                $("#user_id").hide();
//                $("#search_txt").hide();
//                $(".search_date").show();
//            } else {
//                $("#service_id").hide();
//                $("#user_id").hide();
//                $(".search_date").hide();
//                $("#search_txt").show();
//            }
//        });

        $(window).on('load', function () {
            $('#search_by').trigger('change');
        });

        $('.datepicker').datepicker({
            format: "dd-mm-yyyy",
            //startDate: "od",
            todayHighlight: true,
            todayBtn: true,
            autoclose: true
        }).inputmask('dd-mm-yyyy', {"placeholder": "dd-mm-yyyy", alias: "date", "clearIncomplete": true});
    });
</script>
@stop