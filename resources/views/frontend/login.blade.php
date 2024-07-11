@extends('frontend.layouts.default')

@section('content')

<section class="process-section section-padding" style="margin-top:100px;">
    <div class="container">
        <div class="row text-center" style="width:500px; margin:0 auto">
             
            @include('frontend.includes.notifications')
            {!! Form::open(['url' => 'agency/doLogin', 'id' => 'login-form', 'class' => 'form']) !!}
            <div class="form-group">
                <div class="form-icon has-feedback">
                    {!! Form::text('email', old('email'),array('class'=>'form-control', 'placeholder'=>trans('user/login.email'))) !!}
                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                </div>
            </div>
            <div class="form-group">
                <div class="form-icon has-feedback">
                    {!! Form::password('password', array('class'=>'form-control', 'placeholder'=>trans('user/login.password'))) !!}
                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                </div>
            </div>

            <div class="form-group has-feedback">
                <button type="submit" class="btn btn-primary">{!! trans('user/login.sign_in') !!}</button>&nbsp;
<!--                <a href="javascript:;" data-dismiss="modal" data-target="#forgot-password" data-toggle="modal" class="xs-block">{!! trans('user/login.i_forgot_my_password') !!}</a>-->
            </div>

            {!! Form::close()!!}
        </div>
    </div>
</section>
@stop