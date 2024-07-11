@extends('frontend.layouts.main')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('user/booking.my_bookings_list') !!}
@stop

{{-- Content --}}
@section('content')
{{-- Dashboard Wrapper Start --}}
<div class="dashboard-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('user/agency.user_edit') !!}</h1>


        <!-- <ol class="breadcrumb">
                <li><a href="/admin"><i class="fa fa-dashboard"></i> {!! trans('admin/common.home') !!}</a></li>
                <li class="active">{!! trans('admin/service.services') !!}</li>
            </ol> -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Main row -->
        <div class="row">
            <div class="col-md-12">
                <!-- Notifications -->
                @include('frontend.includes.notifications')
                <!-- ./ notifications -->
            </div>
            <div class="col-xs-12">
                <div style="max-width: 600px; margin:0 auto">

                    {!! Form::model($user, array('url' =>'agency/updateUser/'.$user->id.'/', 'method' => 'post', 'id' => 'update-user-form',
                    'files' => true )) !!}



                    <div class="box-body">
                        <div class="form-group has-feedback">
                            {!! Form::label('firstname', trans('admin/user.firstname')) !!}
                            {!! Form::text('firstname', old("firstname"),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('lastname', trans('admin/user.lastname')) !!}
                            {!! Form::text('lastname', old("lastname"),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('email', trans('admin/user.email')) !!}
                            {!! Form::text('email', old("email"),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('phone_number', trans('admin/user.phone_number')) !!}
                            {!! Form::text('phone_number', old("phone_number"),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">

                            {!! Form::label('Gender', trans('admin/user.Gender')) !!} <br>
                            Male {!! Form::radio('gender', '0', (old('gender') == '0'), array('id'=>'', 'class'=>'')) !!} 
                            Female {!! Form::radio('gender', '1', (old('gender') == '1'), array('id'=>'', 'class'=>'')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>

                        <div class="form-group has-feedback">

                            <div class="">
                                {!! Form::label('phone_number', trans('user/agency.services')) !!}
                            </div>
                            <div class="row">
                                @foreach ( $allServices as $i => $service )
                                <div class="col-lg-4">
                                    <input type="checkbox" value="{!! $service->id !!}" name="services[]" {{ (in_array($service->id, $services)) ? 'checked="checked" ' : '' }}>
                                    {!! Form::label($service->title,  $service->title) !!}
                                </div>
                                @endforeach
                            </div>

                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('Profile Image', trans('Profile Image')) !!}

                            <div class=row>
                                <!--<div class="col-md-9">
                                    {!! Form::file('image', array('class'=>'form-control','style'=>'height:auto;')) !!}
                                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                </div>-->
                                <div class="col-md-3">
                                    @if (isset($user) && $user->image)

                                    @if (file_exists(base_path() . '/images/avatars/' . $user->image)) 
                                    <img src="{!! URL('images/avatars/'.$user->image) !!}" height="50" width="100"
                                         alt="Profile Image">
                                    @else  
                                    <img src="{!! URL('images/avatars/dummy.png') !!}" height="50" width="100" alt="Profile Image">
                                    @endif
                                    @else
                                    <img src="{!! URL('images/avatars/dummy.png') !!}" height="50" width="100" alt="Profile Image">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        {!! Form::submit(trans('admin/common.submit'),array('class'=>'btn btn-primary', 'id'=>'submitform')) !!}
                        <a href="{!! url('agency/allUsers') !!}" class="btn btn-default">{!! trans('admin/common.cancel') !!}</a>
                    </div>
                    {!! Form::close()!!}
                </div> <!-- /.box -->
            </div> <!-- /.col-xs-12 -->
        </div><!-- /.row (main row) -->

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop
