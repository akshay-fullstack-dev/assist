@extends('frontend.layouts.main')

@section('content')
<section class="process-section section-padding">
    <div class="container">
        <div class="row text-center">

            <p style="margin-top:10px">
                @if (!Auth::check())
                {!! trans('user/register.already_have_account') !!} &nbsp; <a  href="javascript:;" data-dismiss="modal" data-target="#sign-in" data-toggle="modal" class="xs-block">{!! trans('user/register.sign_in') !!}</a>
                @else 
            <h2>{!! trans('user/register.add_new_employee') !!}</h2>
            @endif

            </p>

            @include('frontend.includes.notifications')
            <div style="max-width: 600px; margin:0 auto">

                {!! Form::open(['url' => 'agency/storeUser', 'id' => 'register-form', 'class' => 'form form-horizontal', 'files' => true]) !!}


                <div class="form-group has-feedback">
                    {!! Form::text('firstname', old('firstname'),array('class'=>'form-control', 'placeholder'=>trans('user/register.firstname'))) !!}
                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                </div>

                <div class="form-group has-feedback">
                    {!! Form::text('lastname', old('lastname'),array('class'=>'form-control', 'placeholder'=>trans('user/register.lastname'))) !!}
                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                </div>

                <div class="form-group has-feedback">
                    {!! Form::text('email', old('email'),array('class'=>'form-control', 'placeholder'=>trans('user/register.email'))) !!}
                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    <span id="loader" class="help-block"></span>
                </div>

                <div class="form-group has-feedback">
                    {!! Form::text('phone_number', old('phone_number'),array('class'=>'form-control', 'placeholder'=>trans('user/register.phone'))) !!}
                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    <span id="loader" class="help-block"></span>
                </div>

                <div class="form-group has-feedback">
                    <div class="row">
                        <div class="col-lg-11">
                            {!! Form::password('password', array('class'=>'form-control', 'id'=>'password', 'placeholder'=>trans('user/register.password'))) !!}</div>
                        <div class="col-lg-1">
                            <i class="fa fa-info-circle" aria-hidden="true"  data-toggle="tooltip" title="Example password: Singh@123#"></i>
                        </div>

                    </div>
                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                </div>

                <div class="form-group has-feedback">
                    {!! Form::password('password_confirmation', array('class'=>'form-control', 'placeholder'=>trans('user/register.confirm_password'))) !!}
                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                </div>
                <style>
                    .radio-control{}
                    .radio-control label{font-weight:400;font-size: 14px;}
                    .radio-control label input{top: 5px;}
                    .radio-control input[type="checkbox"]{margin-right:5px !important;}
                    .form-control input[type="file"]{width:100%;}
                    .form-control.col-lg-12{padding-right:0}
                </style>
                <div class="form-group has-feedback ">
                    <div class="text-left custom-control custom-radio">

                        {!! Form::label('Gender', trans('admin/user.Gender')) !!}       

                        <div class="radio-control"><label class="radio-inline">
                                {{ Form::radio('gender', '0' ),'disabled' }} Male
                            </label>
                            <label class="radio-inline">
                                {{ Form::radio('gender', '1' ),'disabled' }} Female
                            </label>
                        </div>
                    </div> 
                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                </div>

                <div class="form-group has-feedback">
                    <div class="text-left">
                        <div class="text-left">
                            {!! Form::label('phone_number', trans('user/agency.services')) !!}
                        </div>
                        <div class="text-left row radio-control">
                            @foreach ( $services as $service )
                            <div class="col-lg-4 ">
                                {!! Form::checkbox( 'services[]', $service->id, NULL, ['class' => '',] ) !!}
                                {!! Form::label($service->title,  $service->title) !!}
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
                <div class="form-group has-feedback">
                    <div class="row text-left">
                        <div class="col-lg-12">
                            <label>{!! trans('user/register.profile_image') !!} <i class="fa fa-info-circle" aria-hidden="true"  data-toggle="tooltip" title="You can upload multiple images while choose file"></i></label>
                        </div>
                        <div class="col-lg-12">
                            <span class="form-control col-lg-12">{!! Form::file('image'); !!}
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span></span>
                        </div>
                    </div>
                </div>

                <div class="form-group has-feedback">
                    <div class="col-lg-12 form-group text-left">
                        <button type="submit" class="btn btn-primary">{!! trans('user/register.register') !!}</button>
                    </div>
                </div>
                {!! Form::close()!!}
            </div>
        </div>
    </div>
</section>

@stop
@section('script')
<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@stop