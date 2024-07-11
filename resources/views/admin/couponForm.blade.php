@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/coupons.coupons') !!}
@stop
@section('styles')
@stop
{{-- Content --}}
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/coupons.coupon') !!}</h1>
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
                    @if(isset($coupon))
                        {!! Form::model($coupon, array('route' => array('coupons.update', $coupon->id),  'method' => 'PATCH', 'id' => 'coupon-form', 'files' => true )) !!}
                    @else
                        {!! Form::open(array('route' => 'coupons.store', 'id' => 'coupon-form', 'files' => true)) !!}
                    @endif
                    <div class="box-body">
                        <div class="form-group has-feedback">
                            {!! Form::label('name', trans('admin/coupons.coupon_name')) !!}
                            {!! Form::text('name', old('name'),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('code', trans('admin/coupons.code')) !!}
                            {!! Form::text('code', old('code'),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('type', trans('admin/coupons.type')) !!}
                            @if(isset($coupon->type))
                                {!! Form::select('type', array(''=> '--Select--', 'Fixed' => trans('admin/coupons.fixed'), 'Percent' => trans('admin/coupons.percent')), $coupon->type, array('class'=>'form-control discount_type')) !!}
                            @else
                                {!! Form::select('type', array(''=> '--Select--', 'Fixed' => trans('admin/coupons.fixed'), 'Percent' => trans('admin/coupons.percent')), '--Select--', array('class'=>'form-control')) !!}
                            @endif
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('discount', trans('admin/coupons.discount')) !!}
                            {!! Form::text('discount', old('discount'),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('minAmount', trans('admin/coupons.min_amount')) !!}
                            {!! Form::text('minAmount', old('minAmount'),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('maxDiscountAmount', trans('admin/coupons.max_discount_amt')) !!}
                            {!! Form::text('maxDiscountAmount', old('maxDiscountAmount'),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('services', trans('admin/coupons.services')) !!} <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{!! trans('admin/coupons.services_specific_info') !!}"></i>
                           <select class="js-example-basic-multiple form-control" name="services[]" multiple="multiple">
                                 @foreach($services as $service)
                                    <option value="{!! $service->id !!}" @if(isset($coupon_services)) @foreach($coupon_services as $coupon_service) @if($coupon_service->service_id == $service->id) selected @endif @endforeach @endif>{!! $service->title !!}</option>
                                 @endforeach
                            </select>
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('startDateTime', trans('admin/coupons.start_date')) !!}
                            <div class="input-group date" id="dateTimePicker1">
                                <span class="input-group-addon" id="dateTimePickerIcon1">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                                {!! Form::text('startDateTime', old('startDateTime'),array('class'=>'form-control')) !!}
                            </div>
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('endDateTime', trans('admin/coupons.end_date')) !!}
                            <div class="input-group date" id="dateTimePicker2">
                                <span class="input-group-addon" id="dateTimePickerIcon2">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                                {!! Form::text('endDateTime', old('endDateTime'),array('class'=>'form-control')) !!}
                            </div>
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('maxTotalUse', trans('admin/coupons.max_total_use')) !!}
                            {!! Form::text('maxTotalUse', old('maxTotalUse'),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('maxUseCustomer', trans('admin/coupons.max_use_customer')) !!}
                            {!! Form::text('maxUseCustomer', old('maxUseCustomer'),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="box-footer">
                        {!! Form::button(trans('admin/common.submit'),array('class'=>'btn btn-primary', 'id'=>'submitform')) !!}
                        <a href="{!! URL::route('coupons.index') !!}" class="btn btn-default">{!! trans('admin/common.cancel') !!}</a>
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
<script type="text/javascript">
    $(document).ready(function () {
        $('.discount_type').on('change', function(){
            if($(this).val() == 'Percent') {
                if($('#discount').val() > 100){
                   $('#discount').val('') ;
                }
            }
        });
        $('#submitform').click(function(e){
            if($('.discount_type').val() == 'Percent') {
                if($('#discount').val() > 100){
                    alert('Percentage can not exceed 100%');
                    $('#discount').focus() ;
                    return false;
                }
            }
            $('#coupon-form').submit();
        });
        
        //To set the time in new date as 00:00:00
        //This would allow today's date to be selected again if another date was selected before
        var today_date = new Date();
        var curr_year = today_date.getFullYear();
        var curr_month = today_date.getMonth();
        var curr_day = today_date.getDate();
        today_date = new Date(curr_year, curr_month, curr_day);
        
        $('.js-example-basic-multiple').select2();
        
        //Set DateTimePicker
        $("#dateTimePicker1").datetimepicker();
        $("#dateTimePicker2").datetimepicker();

        //DateTimePicker Format
        $("#dateTimePicker1").data("DateTimePicker").format("YYYY-MM-DD");
        $("#dateTimePicker2").data("DateTimePicker").format("YYYY-MM-DD");
        
        @if(isset($coupon->startDateTime))
            $("#dateTimePicker1").data("DateTimePicker").date("{!! $coupon->startDateTime !!}");
            $("#dateTimePickerIcon1").click(function() {
                $("#dateTimePicker1").data("DateTimePicker").minDate(today_date);
            });
            @if(isset($coupon->endDateTime))
                var startDateTime = $("#startDateTime").val();
                $('#dateTimePicker2').data("DateTimePicker").minDate(startDateTime);
                $("#dateTimePicker2").data("DateTimePicker").date("{!! $coupon->endDateTime !!}");
            @endif
        @else
            $("#dateTimePickerIcon1").click(function() {
                $("#dateTimePicker1").data("DateTimePicker").minDate(today_date);
            });
        @endif
        
        $("#dateTimePickerIcon2").click(function() {
            var startDateTime = $("#startDateTime").val();
            if (startDateTime)
            {
                $("#dateTimePicker2").data("DateTimePicker").minDate(startDateTime);
            }
            else
            {
                $("#dateTimePicker2").data("DateTimePicker").minDate(today_date);
            }
        });
        
        //Limit the dates selection ranges
        $("#dateTimePicker1").on("dp.change", function (e) {
            var endDateTime = $("#endDateTime").val();
            if (endDateTime)
            {
                $('#dateTimePicker2').data("DateTimePicker").minDate(e.date);
            }
        });
    });
</script>
@stop