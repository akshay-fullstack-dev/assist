@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/equipment.equipments') !!}
@stop
@section('styles')
@stop
{{-- Content --}}
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/equipment.add_equipments') !!}</h1>
        <!-- <ol class="breadcrumb">
            <li><a href="/admin"><i class="fa fa-dashboard"></i> {!! trans('admin/common.home') !!}</a></li>
            <li class="active">{!! trans('admin/equipment.equipments') !!}</li>
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
                    @if(isset($equipment))
                    {!! Form::model($equipment, array('route' => array('equipments.update', $equipment->id), 'method' => 'PATCH', 'id' => 'equipment-form', 'files' => true )) !!}
                    @else
                    {!! Form::open(array('route' => 'equipments.store', 'id' => 'equipment-form', 'files' => true)) !!}
                    @endif
                    <div class="box-body">
                        <div class="form-group has-feedback">
                            {!! Form::label('name', trans('admin/equipment.title')) !!}
                            {!! Form::text('name', old('name'),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('price', trans('Price')) !!} <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{!! trans('admin/service.price_info') !!}"></i><br />
                            {!! Form::text('price', old('price'), array('class'=>'form-control numberInput')) !!}
                        </div>

                        <div class="form-group has-feedback">
                            {!! Form::label('service_id', trans('admin/equipment.service_name')) !!} <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{!! trans('admin/equipment.parent_category_info') !!}"></i>
                            {!! Form::select('service_id', $services, old('service_id'), ['placeholder' => 'Please select ...', 'class' => 'form-control']); !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>

                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('Add Image', trans('Add Image')) !!}

                            <div class=row>

                                <div class="col-md-9">
                                    {!! Form::file('image', array('class'=>'form-control','style'=>'height:auto;')) !!}
                                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                </div>
                                <div class="col-md-3">
                                    @if (isset($equipment) && $equipment->image)
                                    <img src="{!! URL::asset('assets/equipments/'.$equipment->image) !!}"  height="50" width="100">
                                    @endif
                                </div>
                            </div>
                        </div>


                    </div>
                     
                    <div class="box-footer">
                        {!! Form::submit(trans('admin/common.submit'),array('class'=>'btn btn-primary', 'id'=>'submitform')) !!}
                        <a href="{!! URL::route('equipments.index') !!}" class="btn btn-default">{!! trans('admin/common.cancel') !!}</a>
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
        $(".equipment_type").change(function () {
            var equipment_type = $(this).val();
            if (equipment_type == 'weekly') {
                $('.daily-time').slideUp();
                $('.weekly').slideDown();

                $("#start_time").rules("remove", "required");
                $("#end_time").rules("remove", "required");

            } else {
                $('.daily-time').slideDown();
                $('.weekly').slideUp();

                $("#start_time").rules("add", "required");
                $("#end_time").rules("add", "required");

                if (equipment_type == 'monthly' || equipment_type == 'yearly') {
                    $('.daily-date').slideDown();

                    $("#start_date").rules("add", "required");
                    $("#end_date").rules("add", "required");

                } else {
                    $('.daily-date').slideUp();

                    $("#start_date").rules("remove", "required");
                    $("#end_date").rules("remove", "required");
                }
            }
        });

        var equipment_type = $(".equipment_type:checked").val();
        if (equipment_type == 'weekly') {
            $("#start_time").rules("remove", "required");
            $("#end_time").rules("remove", "required");

        }

        $(".numberInput").forceNumeric(); // for number input force enter numeric

        $(".datepicker").inputmask('dd-mm-yyyy', {"placeholder": "dd-mm-yyyy", alias: "date", "clearIncomplete": true});

        $("#start_date").datepicker({
            format: "dd-mm-yyyy",
            //startDate: "od",
            todayHighlight: true,
            todayBtn: true,
            autoclose: true
        }).on('changeDate', function (e) {
            var minDate = new Date(e.date.valueOf());
            $('#end_date').datepicker('setStartDate', minDate);
        });


        $('#end_date').datepicker({
            format: "dd-mm-yyyy",
            startDate: "od",
            todayHighlight: true,
            todayBtn: true,
            autoclose: true
        }).on('changeDate', function (e) {
            var maxDate = new Date(e.date.valueOf());
            $('#start_date').datepicker('setEndDate', maxDate);
        });

        //var minDate = moment().add(-1, 'seconds').toDate();
        $('input[id^=start_time]').datetimepicker({
            format: 'LT',
        }).inputmask('hh:mm t', {"placeholder": "hh:mm t", alias: "date", "clearIncomplete": true});
        $('input[id^=end_time]').datetimepicker({
            format: 'LT',
            useCurrent: false //Important! See issue #1075
        }).inputmask('hh:mm t', {"placeholder": "hh:mm t", alias: "date", "clearIncomplete": true});

        $("input[id^=start_time]").on("dp.change", function (e) {
            $(this).closest('.row').find('input[id^=end_time]').data("DateTimePicker").minDate(e.date.add(30, 'minutes').toDate());
        });
        $("input[id^=end_time]").on("dp.change", function (e) {
            $(this).closest('.row').find('input[id^=start_time]').data("DateTimePicker").maxDate(e.date);
        });
    });
</script>
@stop