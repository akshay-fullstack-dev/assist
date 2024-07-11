@extends('admin.layouts.default')
{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/service.service_frequency') !!}
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
    <a href="{!! url('/admin/service/frequency') !!}" class="btn btn-primary pull-right">{!! trans('admin/common.back') !!}</a>
    <h1>{!! trans('admin/service.add_service_frequency') !!}</h1>
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
        @if($serviceFrequency)
        @foreach($serviceFrequency as $frequency)
        {{$frequency}}
        @endforeach
        @else
        <h2>Service not found</h2>
        @endif
      </div> <!-- /.box -->
    </div> <!-- /.col-xs-12 -->
</div><!-- /.row (main row) -->
</section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop
