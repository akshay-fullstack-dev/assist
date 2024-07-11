@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/booking.booking_details') !!}
@stop
@section('styles')
<style>
    .detailBox > .row:nth-of-type(2n+1) {
        background-color: #f9f9f9;
    }
    .detailBox > .row{
        margin: 0px 0px 5px 0px !important;
    }
    .detailBox > .row{
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
        <h1>{!! trans('admin/booking.booking_Report') !!}</h1>

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
            <div class="col-lg-12">

                <form method="post" action="{{ url('admin/filtered-booking-report') }}">

                    {!! Form::token() !!}
                    <div class="col-lg-3">
                        <label>Select Type</label>

                        <select data-column="9"  name="search_type" class="search_type search-input-select form-control" style="display: inline-block;">
                            <option value="0" @if(session('SEARCH.SEARCHTYPE') == 0) selected="selected"  @endif >All</option>
                            <option value="1" @if(session('SEARCH.SEARCHTYPE') == 1) selected="selected"  @endif >Vendor</option>
                            <option value="2" @if(session('SEARCH.SEARCHTYPE') == 2) selected="selected"  @endif >Agency</option>
                        </select>
                    </div>


                    <div id="agency_div" style="display:none" class="col-lg-2">    
                        <label>Select Agency</label>
                        <select data-column="9"  name="agency_id" id="agency_dropdown" class="agency_search search-input-select form-control" style="display: inline-block;">
                            <option value="">Select</option>
                            @if(!empty($agencys))
                            @foreach($agencys as $key => $agency)
                            <option value="{{ $agency }}" @if(session('SEARCH.AGENCY') == $agency) selected="selected"  @endif >{{ $key }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>

                    <div id="vendor_div" style="display:none" class="col-lg-2">
                        <label>Select Vendor</label>
                        <select data-column="9"  name="vender_id" id="vender_dropdown" class="search-input-select form-control" style="display: inline-block;">
                            <option value="">Select</option>
                            @if(!empty($vendors))
                            @foreach($vendors as $key => $vendor)
                            <option value="{{ $vendor }}" @if(session('SEARCH.VENDER') == $vendor) selected="selected"  @endif >{{ $key }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>

                    <div id="vendor_div" class="col-lg-2">
                        <label>Select Month</label>
                        @php
                        $sessionValue = session('SEARCH.MONTH');
                        $selected = 'selected="selected"';
                        @endphp
                        <select data-column="13"  name="month" class="search-input-select form-control" style="display: inline-block;">
                            <option value="">All</option>
                            <option value="1" <?php
                            if ($sessionValue == 1) {
                                echo $selected;
                            }
                            ?>>Jan</option>
                            <option value="2" <?php
                            if ($sessionValue == 2) {
                                echo $selected;
                            }
                            ?>>Feb</option>
                            <option value="3" <?php
                            if ($sessionValue == 3) {
                                echo $selected;
                            }
                            ?>>Mar</option>
                            <option value="4" <?php
                            if ($sessionValue == 4) {
                                echo $selected;
                            }
                            ?>>Apr</option>
                            <option value="5" <?php
                            if ($sessionValue == 5) {
                                echo $selected;
                            }
                            ?>>May</option>
                            <option value="6" <?php
                            if ($sessionValue == 6) {
                                echo $selected;
                            }
                            ?>>Jun</option>
                            <option value="7" <?php
                            if ($sessionValue == 7) {
                                echo $selected;
                            }
                            ?>>Jul</option>
                            <option value="8" <?php
                            if ($sessionValue == 8) {
                                echo $selected;
                            }
                            ?>>Aug</option>
                            <option value="9" <?php
                            if ($sessionValue == 9) {
                                echo $selected;
                            }
                            ?>>Sept</option>
                            <option value="10" <?php
                            if ($sessionValue == 10) {
                                echo $selected;
                            }
                            ?>>Oct</option>
                            <option value="11" <?php
                            if ($sessionValue == 11) {
                                echo $selected;
                            }
                            ?>>Nov</option>
                            <option value="12" <?php
                            if ($sessionValue == 12) {
                                echo $selected;
                            }
                            ?>>Dec</option>
                        </select>
                    </div>
                    <div id="vendor_div" class="col-lg-2">
                        <label>Select Year</label>
                        <select data-column="13"  name="year" class="search-input-select form-control" style="display: inline-block;">
                            <option value="">All</option>
                            
                            <?php 
                            $date = date('Y');
                            for ($i = 2018; $i <= $date+1; $i++) { ?>
                                <option value="<?php echo $i; ?>" @if(session('SEARCH.YEAR') == $i) selected="selected"  @endif ><?php echo $i; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div id="vendor_div" class="col-lg-3" style="margin-top:25px;">
                        <input type="submit" value="Get Records" class="btn btn-primary" id="submit_search">
                        {!! Form::button(trans('admin/common.reset'),array('type'=>'submit','id' => 'reset', 'name' => 'reset', 'value' => '1', 'class' => 'btn btn-defult')) !!}
                    </div>
                </form>


            </div>
            <div class="col-lg-12" style="margin-top:20px; padding-left:30px">
                <span><b>Admin Earning</b> : - {{ $totalAdminEarning }}</span>
                <span style="margin-left:20px"><b>Vendor Earning</b> : - {{ $totalVendorEarning }}</span>
            </div>
            <div class="col-lg-12">
                <div style="width:100%">
                    {!! $chart->container() !!}
                </div>
            </div> <!-- /.col-xs-12 -->
        </div><!-- /.row (main row) -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop

@section('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        showAgencyOrVendor();
        $('.search_type').on('change', function () {
            showAgencyOrVendor();
        });
        /*$('#submit_search').click(function(){
         if ($('.search_type').val() == 1) {
         if($('#agency_dropdown').val() == ''){
         alert('Please select agency');
         return false;
         }
         }
         if ($('.search_type').val() == 0) {
         if($('#vender_dropdown').val() == ''){
         alert('Please select Vender');
         return false;
         }
         }
         })*/
    });
    function showAgencyOrVendor() {
        if ($('.search_type').val() == 0) {
            $('#agency_div').hide();
            $('#vendor_div').hide();
        }
        if ($('.search_type').val() == 1) {
            $('#agency_div').hide();
            $('#vendor_div').show();
        }
        if ($('.search_type').val() == 2) {
            $('#agency_div').show();
            $('#vendor_div').hide();
        }
    }
</script>
@stop