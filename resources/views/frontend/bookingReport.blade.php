`@extends('frontend.layouts.main')
{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('user/booking.my_bookings_list') !!}
@stop
{{-- Content --}}
@section('content')
{{-- Dashboard Wrapper Start --}}
<div class="dashboard-wrapper">
    @if(isset($user))
        @if ($user->status == 0)
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>	
                You are markd inActive by admin. Please contact with admin for further details.
            </div>
        @elseif  ($user->status == 3)
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>	
                You are rejected by admin. Please contact with admin for further details.
            </div>
        @elseif  ($user->status == 2)
        <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>	
                You are not accepted by admin yet. Please contact with admin for further details.
            </div>
            {{-- user end condition --}}
        @endif
    @endif
    {{-- Row Start --}}
    <div class="row">
        <div class="col-lg-12">

            <form method="post" action="{{ url('agency/filtered-booking-report') }}">

                {!! Form::token() !!}
                 

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

                <div id="vendor_div" class="col-lg-2">
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
                        <?php $date = date('Y');
                            for ($i = 2018; $i <= $date+1; $i++) { ?>
                            <option value="<?php echo $i; ?>" @if(session('SEARCH.YEAR') == $i) selected="selected"  @endif ><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div id="vendor_div" class="col-lg-6" style="margin-top:25px;">
                    <input type="submit" value="Get Records" class="btn btn-primary" id="submit_search">
                    {!! Form::button(trans('admin/common.reset'),array('type'=>'submit','id' => 'reset', 'name' => 'reset', 'value' => '1', 'class' => 'btn btn-defult')) !!}
                </div>
            </form>
        </div>
        <div class="col-lg-12" style="margin-top:20px; padding-left:30px">
                <span><b>Admin Earning</b> : - {{ $totalAdminEarning }}</span>
                <span style="margin-left:20px"><b>Vendor Earning</b> : - {{ $totalVendorEarning }}</span>
            </div>
        <div class="col-xs-12">
            <div style="width:100%">
                {!! $chart->container() !!}
            </div>
        </div> <!-- /.col-xs-12 -->
    </div>
    {{-- Row End --}}
</div>
{{-- Dashboard Wrapper End --}}
@stop
{{-- Scripts --}}
@section('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        showAgencyOrVendor();
        $('.search_type').on('change', function () {
            showAgencyOrVendor();
        });
    });
    function showAgencyOrVendor() {
        if ($('.search_type').val() == 1) {
            $('#agency_div').show();
            $('#vendor_div').hide();
        } else {
            $('#agency_div').hide();
            $('#vendor_div').show();
        }
    }
</script>
@stop