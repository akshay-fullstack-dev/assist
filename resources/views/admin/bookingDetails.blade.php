@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/booking.booking_details') !!}
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
        <a href="{!! url('/admin/booking') !!}" class="btn btn-primary pull-right">{!! trans('admin/common.back') !!}</a>
        <h1>{!! trans('admin/booking.booking_details') !!}</h1>
        <!-- <ol class="breadcrumb">
            <li><a href="/admin"><i class="fa fa-dashboard"></i> {!! trans('admin/common.home') !!}</a></li>
            <li><a href="/admin/booking"><i class="fa fa-bookmark"></i> {!! trans('admin/booking.bookings_list') !!}</a></li>
            <li class="active">{!! trans('admin/booking.booking_details') !!}</li>
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
        </div>
        <div class="row">
            <div class="col-xs-6">
                <div class="box">
                    <div class="box-header">
                        <h4>Order Details</h4>
                    </div>
                    <div class="box-body detailBox">
                        <div class="row">
                            <div class="col-md-4">{!! trans('admin/booking.booking_date') !!}</div>
                            <!--<div class="col-md-10">{!! $booking->user->firstname.' '.$booking->user->lastname !!}</div>-->
                            <!--<div class="col-md-8">@if(isset($booking->created_at)){{ \Carbon\Carbon::parse($booking->created_at)->format('Y-m-d H:i:s')}}@endif</div>-->
                            <div class="col-md-8">@if(isset($booking->booking_date)){{ \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d')}}@endif</div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">{!! trans('admin/booking.payment_mode') !!}</div>
                            <div class="col-md-8">@if($booking->payment_type == 1){!! 'COD' !!} @else {!! 'ONLINE' !!}@endif</div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">{!! trans('admin/booking.booking_status') !!}</div>
                            <div class="col-md-8">@if(isset($booking->status->label)){!! $booking->status->label !!}@endif</div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 sdfsdsdsdsd">{!! trans('admin/booking.slot') !!}</div>
                            <div class="col-md-8"> @if(isset($booking->slot_start_from)){!! date('H:i', strtotime($booking->slot_start_from)) !!}@endif - @if(isset($booking->slot_start_end)){!! date('H:i', strtotime($booking->slot_start_end)) !!} ( {!! $booking->selected_hours; !!} @if($booking->selected_hours > 1) hours @else hour @endif) @endif </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">{!! trans('admin/booking.price_type') !!}</div>
                            <div class="col-md-8">@if($booking->booking_type == 1){!! 'Fixed' !!} @else {!! 'Hourly' !!}@endif</div>
                        </div>
                        <!--                        <div class="row">
                                                    <div class="col-md-2">{!! trans('admin/booking.booking_date') !!}</div>
                                                    <div class="col-md-10">
                        <?php $spots = $booking->bookingDetail ?>
                        <?php foreach ($spots as $key => $spot) : ?>
                                                                                                <span>{!! date('Y-m-d h:i A', strtotime($spot->start_time)) !!} to {!! date('Y-m-d h:i A', strtotime($spot->end_time)) !!}</span><br>
                        <?php endforeach; ?>
                                                    </div>
                                                </div>-->
                        <!--                        <div class="row">
                                                    <div class="col-md-2">{!! trans('admin/booking.booking_status') !!}</div>
                                                    <div class="col-md-10">
                                                        @if($booking->status == 'pending')
                                                        <button class="btn btn-default">{!! trans('admin/booking.'.$booking->status) !!}</button>
                                                        @elseif($booking->status == 'cancel')
                                                        <button class="btn btn-danger">{!! trans('admin/booking.'.$booking->status) !!}</button>
                                                        @elseif($booking->status == 'confirm')
                                                        <button class="btn btn-success">{!! trans('admin/booking.'.$booking->status) !!}</button>
                                                        @endif
                                                    </div>
                                                </div>-->
                    </div>
                </div> <!-- /.box -->
            </div> <!-- /.col-xs-12 -->
            <div class="col-xs-6">
                <div class="box">
                    <div class="box-header">
                        <h4>Customer Detail</h4>
                    </div>
                    <div class="box-body detailBox">
                        <div class="row">
                            <div class="col-md-4">{!! trans('admin/booking.user_name') !!}</div>
                            <div class="col-md-8">@if(isset($booking->user->firstname)){!! $booking->user->firstname !!}@endif @if(isset($booking->user->lastname)){!! $booking->user->lastname !!}@endif</div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">{!! trans('admin/booking.booking_email') !!}</div>
                            <div class="col-md-8">@if(isset($booking->email)){!! $booking->email !!}@endif</div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">{!! trans('admin/booking.booking_mobile') !!}</div>
                            <div class="col-md-8">@if(isset($booking->phone)){!! $booking->phone !!}@endif</div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">{!! trans('admin/booking.booking_address') !!}</div>
                            <div class="col-md-8">@if(isset($booking->address)){!! $booking->address !!}@endif</div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.row (main row) -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h4>Booking Details</h4>
                    </div>
                    <div class="box-body detailBox">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="20%">{!! trans('admin/booking.vendor') !!}</th>
                                    <th width="20%">{!! trans('admin/booking.service') !!}</th>
                                    <th width="40%">{!! trans('admin/booking.description') !!}</th>
                                    <th width="20%">{!! trans('admin/booking.amount') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>@if(isset($booking->vender_name)){!! $booking->vender_name !!}@endif</td>
                                    <td>@if(isset($booking->service_name)){!! $booking->service_name !!}@endif</td>
                                    <td>@if(isset($booking->service->description)){!! $booking->service->description !!}@endif</td>
                                    <td>@if(isset($booking->price))
                                        {!! Config::get('constants.CURRENCY_SYMBOL') !!}
                                        @php
                                        $bookingPrice = $booking->price;
                                        $discountAmount = $booking->selected_hours ? $booking->selected_hours : 1;
                                        $bookServicePrice = $bookingPrice * $discountAmount;
                                        @endphp
                                        {!! number_format($bookServicePrice, 2) !!}
                                        @endif</td>
                                </tr>
                                @if($booking->bookedEquipment)
                                <tr>
                                    <td colspan='3'><span class="pull-right text-bold">Equipments Charges</span></td>
                                    </td>
                                    <td>
                                        @php
                                        $price = 0;

                                        foreach($booking->bookedEquipment as $equipment) {
                                        if(isset($equipment->price)) {
                                        if($equipment->price != '') {
                                        $price = $price + $equipment->price;
                                        }
                                        }
                                        }
                                        echo '+'. Config::get('constants.CURRENCY_SYMBOL');
                                        echo number_format($price, 2);
                                        @endphp
                                    </td>
                                </tr>

                                @endif

                                @if($booking->couponHistory)
                                @if(!empty($booking->couponHistory->coupon_id))
                                <tr>
                                    <td colspan='3'><span class="pull-right text-bold">Discount </span></td>
                                    </td>
                                    <td>
                                        @if(isset($booking->couponHistory->coupon_id))
                                        -@if(isset($booking->couponHistory->coupon_id)) {!! Config::get('constants.CURRENCY_SYMBOL') !!} {!! number_format($booking->couponHistory->discount, 2) !!}@endif
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @endif

                                <tr>
                                    <td colspan='3'><span class="pull-right">{!! trans('admin/common.total') !!}</span></td>
                                    <td>@if(isset($booking->total_price)) {!! Config::get('constants.CURRENCY_SYMBOL') !!} {!! number_format($booking->total_price, 2) !!}@endif</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6">
                <div class="box">
                    <div class="box-header">
                        <h4>Order History</h4>
                    </div>
                    <div class="box-body detailBox">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="50%">{!! trans('admin/common.status') !!}</th>
                                    <th width="50%">{!! trans('admin/booking.date_time') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($booking->bookingStatusHistory))
                                @foreach($booking->bookingStatusHistory as $status)
                                <tr>
                                    <td>{!! $status->status->label !!}</td>
                                    <td>@if(isset($status->created_at)){{ \Carbon\Carbon::parse($status->created_at)->format('Y-m-d H:i:s')}} GMT @endif</td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <div class="col-xs-6">
                <div class="box">
                    <div class="box-header">
                        <h4>Equipment Details</h4>
                    </div>
                    <div class="box-body detailBox">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="50%">{!! trans('admin/equipment.title') !!}</th>
                                    <th width="25%">{!! trans('admin/equipment.qty') !!}</th>
                                    <th width="25%">{!! trans('admin/equipment.price') !!}</th>

                                </tr>
                            </thead>
                            <tbody>
                                @if(count($booking->bookedEquipment))
                                @foreach($booking->bookedEquipment as $bookedEquipment)
                                <tr>
                                    <td>{!! $bookedEquipment->equipment_name !!}</td>
                                    <td>{!! $bookedEquipment->quantity !!}</td>
                                    <td>{!! Config::get('constants.CURRENCY_SYMBOL') !!} {!! $bookedEquipment->price !!}</td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6">
                <div class="box">
                    <div class="box-header">
                        <h4>Rating</h4>
                    </div>
                    <div class="box-body detailBox">
                        <table class="table table-bordered">
                            <thead>
                                <tr>

                                    <th width="25%">{!! trans('admin/booking.rated_by') !!}</th>
                                    <th width="25%">{!! trans('admin/booking.comment') !!}</th>
                                    <th width="25%">{!! trans('admin/booking.rating_point') !!}</th>
                                    <th width="25%">{!! trans('admin/booking.is_like') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($booking->Review))
                                @foreach($booking->Review as $history)
                                <tr>
                                    <td>
                                        @if($history->user_id == $history->review_submitted_by)
                                        <?php $user_name = ' ';
                                        $type = 'User';
                                        if (isset($history->user->firstname)) {
                                            $user_name = $history->user->firstname;
                                        }
                                        if (isset($history->user->lastname)) {
                                            $user_name .= " " . $history->user->lastname;
                                        }
                                        ?>
                                        @else
                                        <?php $user_name = '';
                                        $type = 'Vendor';
                                        if (isset($history->vender->firstname)) {
                                            $user_name = $history->vender->firstname;
                                        }
                                        if (isset($history->vender->lastname)) {
                                            $user_name .= " " . $history->vender->lastname;
                                        }
                                        ?>
                                        @endif
                                        {!!$user_name !!} ({!! $type !!})
                                    </td>
                                    <td>{!! $history->feedback_message !!}</td>
                                    <td>{!! $history->rating !!}</td>
                                    <td>@if($history->is_like == 1) Yes @else No @endif </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
                <div class="box">
                    <div class="box-header">
                        <h4>Coupons Detail</h4>
                    </div>
                    <div class="box-body detailBox">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="50%">{!! trans('admin/coupons.coupon_code') !!}</th>
                                    <th width="50%">{!! trans('admin/coupons.discount_amount') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($couponDetails)
                                <tr>
                                    <td>@if($couponDetails->coupon_code) {!! $couponDetails->coupon_code !!} @endif</td>
                                    <td>@if($couponDetails->discount) {!! Config::get('constants.CURRENCY_SYMBOL') !!} {!! $couponDetails->discount !!} @endif</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop