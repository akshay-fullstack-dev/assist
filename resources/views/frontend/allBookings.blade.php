

@extends('frontend.layouts.main')
{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('user/booking.my_bookings_list') !!}
@stop

{{-- Content --}}
@section('content')
{{-- Dashboard Wrapper Start --}}
<div class="dashboard-wrapper">
    {{-- Row Start --}}
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="widget">
                 <div class="widget-header">
                    <div class="title">
                        {!! trans('user/booking.my_bookings_list') !!}
                    </div>
                </div> 
                <div class="width_full mrgn_15t">
                    <div class="col-md-12">
                         @include('frontend.includes.notifications')
                    </div>
                    <div class="col-md-8">
                        {!! Form::open(array('url' => 'agency/bookings-list', 'id' => 'booking-search-form', 'class' => 'form-inline','method' => 'POST')) !!}
                        <div class="form-group">
                            {!! Form::label('search', trans('user/common.search')) !!}
                            {!! Form::select('search_by',array(''=>trans('user/common.search_by'), 'service' => trans('user/booking.service'), 'name' => trans('user/booking.booking_name'), 'email' => trans('user/booking.booking_email'),  'phone' => trans('user/booking.booking_mobile'), 'booking_date' => trans('user/booking.booking_date'), 'vendor_name' => trans('user/booking.vendor_name')), session('SEARCH.SEARCH_BY') , array('class'=>'form-control', 'id' => 'search_by')) !!}
                        </div>
                        <div class="form-group">
                        <?php if (session('SEARCH.SEARCH_BY') == 'service' || session('SEARCH.SEARCH_BY') == 'booking_date'): ?>
                            {!! Form::text('search_txt', session('SEARCH.SEARCH_TXT') ,array('id' => 'search_txt', 'class' => 'form-control', 'style' => 'display:none;')) !!}
                        <?php else: ?>
                            {!! Form::text('search_txt', session('SEARCH.SEARCH_TXT') ,array('id' => 'search_txt', 'class' => 'form-control','placeholder'=>trans('user/common.search'))) !!}
                        <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <?php if (session('SEARCH.SEARCH_BY') == 'service'): ?>
                                {!! Form::select('service_id',$serviceList, session('SEARCH.SERVICE_ID') , array('class'=>'form-control', 'id' => 'service_id', 'style' => 'display:inline-block;')) !!}
                            <?php else: ?>
                                {!! Form::select('service_id',$serviceList, session('SEARCH.SERVICE_ID') , array('class'=>'form-control', 'id' => 'service_id', 'style' => 'display:none;')) !!}
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <?php if(session('SEARCH.SEARCH_BY')=='booking_date'):?>
                            <div class="input-group search_date" style="display:inline-table;">
                                {!! Form::text('search_date',session('SEARCH.SEARCH_DATE'),['id' => 'search_date','class' => 'datepicker form-control',]) !!}
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                            <?php else:?>
                            <div class="input-group search_date" style="display:none;">
                                {!! Form::text('search_date',session('SEARCH.SEARCH_DATE'),['id' => 'search_date','class' => 'datepicker form-control',]) !!}
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                            <?php endif;?>
                        </div>

                        <div class="form-group">
                            {!! Form::submit(trans('user/common.search'), array('id' => 'search', 'name' => '', 'class' => 'btn btn-lbs')) !!}
                            <a href="{{url("agency/bookings-list")}}" class="btn btn-defult">Reset</a>  
                        </div>
                        {!! Form::close() !!}
                    </div>
                    <div class="col-md-4">
                        {{-- <a href="{!! url('/agency/booking/export/'.$user_id) !!}" class="btn btn-sm btn-lbs mrgn_5t pull-right">{!! trans('user/booking.export_csv') !!}</a> --}}
                    </div>
                </div>
                <div class="widget-body"> 
                    <div class="table-responsive">  
                        <table class="table table-condensed table-striped table-bordered no-margin table-custome">
                            <thead>
                                <tr class="table-head">
                                    <th>{!! trans('user/booking.serial_number') !!}</th>
                                    <th>{!! trans('user/booking.id') !!}</th>
                                    <th>{!! trans('user/booking.service') !!}</th>
                                    <th>{!! trans('user/booking.booking_name') !!}</th>
                                    <th>{!! trans('user/booking.booking_email') !!}</th>
                                    <th>{!! trans('user/booking.vendor_name') !!}</th>
                                    <th>{!! trans('user/booking.booking_mobile') !!}</th>
                                    <th>{!! trans('user/booking.booking_date') !!}</th>
                                    <th>{!! trans('user/common.status') !!}</th>
                                    <th>{!! trans('user/common.action') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($bookings))
                                @foreach ($bookings as $data)
                                    <tr>
                                        <td>{!! $loop->iteration !!}</td>
                                        <td>{!! $data->service->id !!}</td>
                                        <td>{!! $data->service->title !!}</td>
                                        <td>{!! $data->full_name !!}</td>
                                        <td>{!! $data->email !!}</td>
                                        <td>{!! $data->vender_name !!}</td>
                                        <td>{!! $data->phone !!}</td>
                                        <td>{!! date('d-m-Y', strtotime($data->booking_date))  !!} {!! $data->slot_start_from !!}</td>
                                        <td width='100'>{!! $data->status->label !!}</td>
                                        <td width='100'><a href="{!! url('agency/empBooking/'.$data->id) !!}" id="{!! $data->id !!}" class="btn btn-primary view-btn" title="{!! trans('admin/common.view') !!}" data-toggle="tooltip"><i class="fa fa-eye"></i></a></td>
                                    </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="8" class="text-center">
                                        {!! trans('user/booking.no_bookings_found') !!}
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-12 text-center pagination no-margin">
            @if($bookings)
               {{$bookings->links()}}
            @endif
        </div>
        <div class="col-md-12 text-center">
           
            <a class="btn">{!! trans('user/common.total') !!} {!! $bookings->total() !!} </a>
        </div>
    </div>
    {{-- Row End --}}
</div>
{{-- Dashboard Wrapper End --}}
@stop
{{-- Scripts --}}
@section('scripts')
<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip({
            //placement: "bottom"
        });
        
        $(".spnToggle").click(function(){
           $('span#'+$(this).attr('id')).toggle();
        });
        
        $('#search_by').change(function () {
            if ($('#search_by').val() == 'service') {
                $("#service_id").show();
                $("#search_txt").hide();
                $(".search_date").hide();
            }else if ($('#search_by').val() == 'booking_date') {
                $("#service_id").hide();
                $("#search_txt").hide();
                $(".search_date").show();
            } else {
                $("#service_id").hide();
                $(".search_date").hide();
                $("#search_txt").show();
            }
        });
        
        $(window).on('load',function(){
            $('#search_by').trigger('change');
        });
        
        $('.datepicker').datepicker({
            format: "dd-mm-yyyy",
            //startDate: "od",
            todayHighlight: true,
            todayBtn : true,
            autoclose: true
        }).inputmask('dd-mm-yyyy', {"placeholder": "dd-mm-yyyy", alias: "date", "clearIncomplete": true});
    });
</script>
@stop