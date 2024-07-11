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
                        {!! trans('user/agency.my_employee_list') !!}
                    </div>
                </div>
                <div class="width_full mrgn_15t">
                    <div class="col-md-12">
                        @include('frontend.includes.notifications')
                    </div>
                    <div class="col-md-8">
                        {!! Form::open(array('url' => 'agency/allUsers', 'id' => 'booking-search-form', 'class' => 'form-inline','method' => 'GET')) !!}
                        <div class="form-group">
                            {!! Form::label('search', trans('user/common.search')) !!}
                            {!! Form::select('search_by', array(''=>trans('user/common.search_by'), 'firstname' => trans('user/agency.firstname'), 'lastname' => trans('user/agency.lastname'), 'email' => trans('user/agency.email'), 'phone' => trans('user/agency.phone_number'), 'prouser' => trans('user/agency.prouser'), 'notprouser' => trans('user/agency.notprouser')), session('SEARCH.SEARCH_BY') , array('class'=>'form-control', 'id' => 'search_by')) !!}
                        </div>
                        <div class="form-group">
                            <?php if (session('SEARCH.SEARCH_BY') == 'service' || session('SEARCH.SEARCH_BY') == 'booking_date') : ?>
                                {!! Form::text('search_txt', session('SEARCH.SEARCH_TXT') ,array('id' => 'search_txt', 'class' => 'form-control', 'style' => 'display:none;')) !!}
                            <?php else : ?>
                                {!! Form::text('search_txt', session('SEARCH.SEARCH_TXT') ,array('id' => 'search_txt', 'class' => 'form-control','placeholder'=>trans('user/common.search'))) !!}
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <?php if (session('SEARCH.SEARCH_BY') == 'booking_date') : ?>
                                <div class="input-group search_date" style="display:inline-table;">
                                    {!! Form::text('search_date',session('SEARCH.SEARCH_DATE'),['id' => 'search_date','class' => 'datepicker form-control',]) !!}
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            <?php else : ?>
                                <div class="input-group search_date" style="display:none;">
                                    {!! Form::text('search_date',session('SEARCH.SEARCH_DATE'),['id' => 'search_date','class' => 'datepicker form-control',]) !!}
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            {!! Form::submit(trans('user/agency.search'), array('id' => 'search', 'name' => '', 'class' => 'btn btn-lbs')) !!}
                            {!! Form::button(trans('user/agency.reset'),array('type'=>'submit','id' => 'reset', 'name' => 'reset', 'value' => '1', 'class' => 'btn btn-defult')) !!}
                        </div>
                        {!! Form::close() !!}
                    </div>
                    <div class="col-md-4">
                        <a href="{!! url('/agency/users/export') !!}" class="btn btn-sm btn-lbs mrgn_5t pull-right">{!! trans('user/booking.export_csv') !!}</a>
                    </div>
                </div>
                <div class="widget-body">
                    <div class="table-responsive">
                        <table class="table table-condensed table-striped table-bordered no-margin table-custome">
                            <thead>
                                <tr class="table-head">
                                    <th>{!! trans('user/agency.firstname') !!}</th>
                                    <th>{!! trans('user/agency.lastname') !!}</th>
                                    <th>{!! trans('user/agency.email') !!}</th>
                                    <th>{!! trans('user/agency.phone_number') !!}</th>
                                    <th>{!! trans('user/agency.user_type') !!}</th>
                                    <th>{!! trans('user/agency.booking') !!}</th>
                                    <th>{!! trans('user/agency.status') !!}</th>
                                    <th>{!! trans('user/agency.action') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($employees))
                                @foreach ($employees as $employee)
                                <tr>
                                    <td>{!! $employee->firstname !!}</td>
                                    <td>{!! $employee->lastname !!}</td>
                                    <td>{!! $employee->email !!}</td>
                                    <td>{!! $employee->phone_number !!}</td>
                                    <td>
                                        @if(isset($employee->package[0]) && $employee->package[0]->expiry_date > date('Y-m-d H:i:s'))
                                        {!! trans('user/agency.prouser') !!}
                                        @else
                                        {!! trans('user/agency.notprouser') !!}
                                        @endif
                                    </td>
                                    <td>@if($employee->vendorBooking->count()) <a class="btn btn-default" href="{!! url('agency/listBooking/'.$employee->id) !!}" > {!! $employee->vendorBooking->count() !!} </a> @else <a class="btn btn-default" href="#" >{!! '0' !!}</a> @endif </td>
                                    <td>
                                        @if($employee->status == '1')
                                        {{ "Active" }}
                                        @else
                                        {{ "In Active" }}
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{!! url('agency/editUser/'.$employee->id) !!}" class="btn btn-primary d-inline-block" title="{{trans('user/agency.view')}}"><i class="fa fa-pencil"></i> </a>&nbsp;<a href="{!! url('agency/deleteUser/'.$employee->id) !!}" id="" onclick="return confirm('Are you sure you want to delete this?')" class="btn btn-danger delete-btn" title="{{trans('user/agency.delete')}}" data-toggle="tooltip"><i class="fa fa-times"></i></a>
                                    </td>
                                </tr>


                                @endforeach
                                @else
                                <tr>
                                    <td colspan="8" class="text-center">
                                        {!! trans('user/agency.no_employee_found') !!}
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

        </div>
        <div class="col-md-12 text-center">

        </div>
    </div>
    {{-- Row End --}}
</div>
{{-- Dashboard Wrapper End --}}
@stop
{{-- Scripts --}}
@section('scripts')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip({
            //placement: "bottom"
        });

        $(".spnToggle").click(function() {
            $('span#' + $(this).attr('id')).toggle();
        });

        $('#search_by').change(function() {
            if ($('#search_by').val() == 'service') {
                $("#service_id").show();
                $("#search_txt").hide();
                $(".search_date").hide();
            } else if ($('#search_by').val() == 'booking_date') {
                $("#service_id").hide();
                $("#search_txt").hide();
                $(".search_date").show();
            } else {
                $("#service_id").hide();
                $(".search_date").hide();
                $("#search_txt").show();
            }
        });

        $(window).on('load', function() {
            $('#search_by').trigger('change');
        });



        $('.datepicker').datepicker({
            format: "dd-mm-yyyy",
            //startDate: "od",
            todayHighlight: true,
            todayBtn: true,
            autoclose: true
        }).inputmask('dd-mm-yyyy', {
            "placeholder": "dd-mm-yyyy",
            alias: "date",
            "clearIncomplete": true
        });
    });
</script>
@stop