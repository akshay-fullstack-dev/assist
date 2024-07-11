@extends('frontend.layouts.main')
{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('user/dashboard.dashboard') !!}
@stop

@section('content')
<!-- Dashboard Wrapper Start -->
<div style="min-height:400px">
    <?php
    if ($user->status == '3') {
        echo '<h2 class="btn btn-danger"> Your is rejected by admin. Reason :- ' . $user->rejection_reason . '</h2>';
    }
    if ($user->status == '2') {
        echo '<h2 class="btn btn-info"> Your account is not reviewd by admin yet</h2>';
    }
    if ($user->status == '0') {
        echo '<h2 class="btn btn-info"> Your account is inactive please contact to admin</h2>';
    }
    ?>
</div>
<!-- Dashboard Wrapper End -->
@stop