@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/service.services') !!}
@stop
@section('styles')
@stop
{{-- Content --}}
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>{!! trans('admin/service.add_services') !!}</h1>
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
          @if(isset($service))
          {!! Form::model($service, array('route' => array('services.update', $service->id), 'method' => 'PATCH', 'id'=>
          'service-form', 'files' => true )) !!}
          @else
          {!! Form::open(array('route' => 'services.store', 'id' => 'service-form', 'files' => true)) !!}
          @endif
          <div class="box-body">
            <div class="form-group has-feedback">
              {!! Form::label('title', trans('admin/service.title')) !!}
              {!! Form::text('title', old('title'),array('class'=>'form-control')) !!}
              <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
            </div>
            <div class="form-group has-feedback">
              {!! Form::label('description', trans('admin/service.description')) !!}
              {!! Form::textarea('description', old('description'),array('class'=>'form-control')) !!}
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
                  @if (isset($service) && $service->image)
                  <img src="{!!  url('assets/services/'.$service->image) !!}" height="50" width="100">
                  @endif
                </div>
              </div>
            </div>
            <div class="form-group has-feedback">
              {!! Form::label('cat_id', trans('admin/service.parent_category')) !!} <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{!! trans('admin/service.parent_category_info') !!}"></i>

              {!! Form::select('cat_id', $categories, old('cat_id'), ['placeholder' => 'Please select ...', 'class' =>
              'form-control']); !!}
              <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-8">
                  {!! Form::label('service_frequency', trans('admin/service.service_frequency_message')) !!} <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{!! trans('admin/service.service_frequency_tooltip') !!}"></i>
                </div>
                <div class="col-md-4">
                  {!! Form::label('service_frequency_price', trans('admin/service.service_frequency_price')) !!} <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{!! trans('admin/service.set_price_for_service_frequency') !!}"></i>
                </div>
              </div>
              @if ($services_frequency->count()>0)
              <div class="form-group">
                <div class="row">
                  @foreach ($services_frequency as $frequency)
                  <div class="col-md-6">
                    {{-- @if (!in_array($frequency->id,$selected_frequency_ids)) --}}
                    {{$frequency->frequency_name}} (Will Repeat after {{$frequency->frequency_day}} days)
                    {{-- <option data-id="{{$frequency->id}}" value="{{$frequency->id}}">{{$frequency->frequency_name}}
                    :-{{$frequency->frequency_day}}</option> --}}
                    {{-- @endif --}}
                  </div>
                  <div class="col-md-6">
                    @php $service_frequency =
                    isset($service)?$service->selected_service_frequencies()->where('frequency_id',$frequency->id)->first():[]
                    @endphp
                    <input type="hidden" name="service_frequency[{{ $loop->index }}][frequency_id]" value="{{$frequency->id}}">
                    <input class="form-control numberInput" name="service_frequency[{{ $loop->index }}][price]" name="frequency_price" type="text" id="service_frequency_price" value="@php echo $service_frequency->service_price??0; @endphp" required>
                  </div>
                  @endforeach
                </div>
              </div>
              @endif
              {{-- service questions --}}
              <div class="form-group">
                <div class="row">
                  <div class="col-md-4">
                    @php
                    if(isset($service) and $service->service_additional_questions()->get()->count()>0){
                    $service_option= $service->service_additional_questions()->get();
                    $service_option= $service_option->toarray();
                    $option1=$service_option[0]['option']??"";
                    $option1Id=$service_option[0]['id']??"";
                    $price1=$service_option[0]['price']??"";
                    $option2=$service_option[1]['option']??"";
                    $option2Id=$service_option[1]['id']??"";
                    $price2=$service_option[1]['price']??"";
                    }else {
                    $option1=null;
                    $price1=null;
                    $option2=null;
                    $price2=null;
                    $option1Id=null;
                    $option2Id=null;
                    }
                    @endphp
                    <input type="hidden" name="option[1][id]" value="{{$option1Id}}">
                    <input type="hidden" name="option[2][id]" value="{{$option2Id}}">
                    {!! Form::label('additional_question', trans('admin/service.additional_question')) !!} <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{!! trans('admin/service.service_frequency_tooltip') !!}"></i>
                    <textarea name="service_question" class="form-control" id="additional_question" rows="2">@php echo $service->service_question??null;@endphp</textarea>
                  </div>
                  <div class="col-md-2">
                    {!! Form::label('service_option', trans('admin/service.service_option')) !!} <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{!! trans('admin/service.service_frequency_tooltip') !!}"></i>
                    <textarea name="option[1][option]" class="form-control" id="service_option" rows="2">{{$option1}}</textarea>
                  </div>
                  <div class="col-md-2">
                    {!! Form::label('service_second_option', trans('admin/service.service_second_option')) !!} <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{!! trans('admin/service.service_frequency_tooltip') !!}"></i>
                    <textarea name="option[2][option]" class="form-control" id="service_second_option" rows="2">{{$option2}}</textarea>
                  </div>
                  <div class="col-md-2">
                    {!! Form::label('option_1_price', trans('admin/service.option_1_price')) !!} <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{!! trans('admin/service.service_frequency_tooltip') !!}"></i>
                    <textarea name="option[1][price]" class="form-control" id="option_1_price" rows="2">{{$price1}}</textarea>
                  </div>
                  <div class="col-md-2">
                    {!! Form::label('option_2_price', trans('admin/service.option_2_price')) !!} <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{!! trans('admin/service.service_frequency_tooltip') !!}"></i>
                    <textarea name="option[2][price]" class="form-control" id="option_2_price" rows="2">{{$price2}}</textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php  if (isset($service)) {
                        $start_date = $service->start_date != "" ? date('d-m-Y', strtotime($service->start_date)) : "";
                        $end_date = $service->end_date != "" ? date('d-m-Y', strtotime($service->end_date)) : "";

                        if ($service->service_type == 'weekly') {
                            $timeDisplay = 'none';
                            $weekDisplay = 'block';
                        } else {
                            $timeDisplay = 'block';
                            $weekDisplay = 'none';
                        }
                    } else {

                        $start_date = old('start_date') ? old('start_date') : '';
                        $end_date = old('end_date') ? old('old_date') : '';

                        if (old('service_type') == 'weekly') {
                            $timeDisplay = 'none';
                            $weekDisplay = 'block';
                        } else {
                            $timeDisplay = 'block';
                            $weekDisplay = 'none';
                        }
                    }
                    ?>
      <div class="box-footer">
        {!! Form::submit(trans('admin/common.submit'),array('class'=>'btn btn-primary', 'id'=>'submitform')) !!}
        <a href="{!! URL::route('services.index') !!}" class="btn btn-default">{!! trans('admin/common.cancel')
          !!}</a>
      </div>
      {!! Form::close()!!}
    </div> <!-- /.box -->
</div>
</div><!-- /.row (main row) -->

</section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop
{{-- Scripts --}}
@section('scripts')
<script type="text/javascript">
  CKEDITOR.replace('description', {
    toolbar: 'BlogToolbar'
  , });
  $(document).ready(function() {
    $(".service_type").change(function() {
      var service_type = $(this).val();
      if (service_type == 'weekly') {
        $('.daily-time').slideUp();
        $('.weekly').slideDown();

        $("#start_time").rules("remove", "required");
        $("#end_time").rules("remove", "required");

      } else {
        $('.daily-time').slideDown();
        $('.weekly').slideUp();

        $("#start_time").rules("add", "required");
        $("#end_time").rules("add", "required");

        if (service_type == 'monthly' || service_type == 'yearly') {
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

    var service_type = $(".service_type:checked").val();
    if (service_type == 'weekly') {
      $("#start_time").rules("remove", "required");
      $("#end_time").rules("remove", "required");

    }

    $(".numberInput").forceNumeric(); // for number input force enter numeric

    $(".datepicker").inputmask('dd-mm-yyyy', {
      "placeholder": "dd-mm-yyyy"
      , alias: "date"
      , "clearIncomplete": true
    });

    $("#start_date").datepicker({
      format: "dd-mm-yyyy",
      //startDate: "od",
      todayHighlight: true
      , todayBtn: true
      , autoclose: true
    }).on('changeDate', function(e) {
      var minDate = new Date(e.date.valueOf());
      $('#end_date').datepicker('setStartDate', minDate);
    });


    $('#end_date').datepicker({
      format: "dd-mm-yyyy"
      , startDate: "od"
      , todayHighlight: true
      , todayBtn: true
      , autoclose: true
    }).on('changeDate', function(e) {
      var maxDate = new Date(e.date.valueOf());
      $('#start_date').datepicker('setEndDate', maxDate);
    });

    //var minDate = moment().add(-1, 'seconds').toDate();
    $('input[id^=start_time]').datetimepicker({
      format: 'LT'
    , }).inputmask('hh:mm t', {
      "placeholder": "hh:mm t"
      , alias: "date"
      , "clearIncomplete": true
    });
    $('input[id^=end_time]').datetimepicker({
      format: 'LT'
      , useCurrent: false //Important! See issue #1075
    }).inputmask('hh:mm t', {
      "placeholder": "hh:mm t"
      , alias: "date"
      , "clearIncomplete": true
    });

    $("input[id^=start_time]").on("dp.change", function(e) {
      $(this).closest('.row').find('input[id^=end_time]').data("DateTimePicker").minDate(e.date.add(30, 'minutes').toDate());
    });
    $("input[id^=end_time]").on("dp.change", function(e) {
      $(this).closest('.row').find('input[id^=start_time]').data("DateTimePicker").maxDate(e.date);
    });
  });

</script>
@stop
