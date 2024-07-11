@extends('frontend.layouts.main')
{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('user/profile.profile') !!}
@stop
@section('styles')
@stop
{{-- Content --}}
@section('content')
{{-- Dashboard Wrapper Start --}}
<div class="dashboard-wrapper">
    {{-- Row Start --}}
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="widget">
                <div class="widget-header">
                    <div class="title">{!! trans('user/profile.profile') !!}</div>
                    <a href="{!! url('/agency/password/change') !!}" class="btn btn-sm btn-lbs mrgn_5t pull-right">{!! trans('user/changePassword.change_password') !!}</a>
                </div>
                <div class="clearfix"></div> 
                <div class="widget-body">
                    @include('admin.includes.notifications')
                    {!! Form::model($profile, ['route' => array('users.update', $profile->id),'method' => 'PATCH', 'id' => 'profile-form','class' => 'form-horizontal no-margin', 'files' => true]) !!}
                    <div class="form-group has-feedback">
                        {!! Form::label('email', trans('user/profile.email'), array('class' => 'col-sm-2 control-label required-sign')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('email', old('email'),array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>

                    <div class="form-group has-feedback">
                        {!! Form::label('firstname', trans('user/profile.owner_firstname'), array('class' => 'col-sm-2 control-label required-sign')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('firstname', old('firstname'), array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>

                    </div>

                    <div class="form-group has-feedback">
                        {!! Form::label('lastname', trans('user/profile.owner_lastname'), array('class' => 'col-sm-2 control-label required-sign')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('lastname', old('lastname'), array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>

                    <div class="form-group has-feedback">
                        {!! Form::label('phone_number', trans('user/profile.phone'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('phone_number', old('phone_number'), array('class'=>'form-control')) !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>

                    <div class="form-group has-feedback">
                        {!! Form::label('document1', trans('user/profile.photo'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            <input type="file" class="form-control" name="document1[]" multiple />
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>

                    @if(isset($profile))
                    <div class="form-group has-feedback">
                        @if($userDocuments)


                        @foreach($userDocuments as $userDocument)

                        {!! Form::label('document1', trans('user/profile.document'), array('class' => 'col-sm-2 control-label')) !!}


                        <div class=" col-sm-10 document-div">
                            <?php
                            $pdf = false;
                            $str = explode('.', $userDocument->document1);
                            if ($str[1] == 'pdf') {
                                $pdf = true;
                            }
                            ?>
                            @if($pdf) 
                            <a target="blank" href="{{ url('assets/images/doc/' . $userDocument->document1) }}">Pdf Document</a>
                            @else
                            <img src="{{ url('assets/images/doc/' . $userDocument->document1) }}" width="50">
                            @endif
                            <input type="button" value="delete" class="btn btn-danger" onclick="delete_this_doc(<?php echo $userDocument->id; ?>)">
                        </div>

                        @endforeach
                        @endif
                    </div>
                    @endif

                    <div class="form-group has-feedback mt-10">
                        <div class="col-sm-2 control-label">
                            {!! Form::label('phone_number', trans('user/agency.services')) !!}
                        </div>   
                        <div class="col-sm-10">
							<div class="row">
                            @foreach ( $allServices as $i => $service )
							<div class="col-sm-2 mt7">
                            <input type="checkbox" value="{!! $service->id !!}" name="services[]" {{ (in_array($service->id, $services)) ? 'checked="checked" ' : '' }}>
                            {!! Form::label($service->title,  $service->title) !!}
							</div>
                            @endforeach
							</div>
                        </div>

                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            {!! Form::submit(trans('user/common.save'), array('class'=>'btn btn-lbs btn-lg')) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div> {{-- widget-body End --}}
            </div>
        </div>
    </div>
    {{-- Row End --}}
</div>
{{-- Dashboard Wrapper End --}}
@stop
@section('scripts')
<script type="text/javascript">
    function delete_this_doc(id) {

        var r = confirm("{!! trans('admin/common.delete_confirmation') !!}");
        if (!r) {
            return false
        }
        $.ajax({
            type: "POST",
            url: "document/" + id,
            data: {
                _method: 'DELETE',
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            beforeSend: function () {
                $(this).attr('disabled', true);
                $('.alert .msg-content').html('');
                $('.alert').hide();
            },
            success: function (resp) {

                $('.alert:not(".session-box")').show();
                if (resp.success) {
                    $('.alert-success .msg-content').html(resp.message);
                    $('.alert-success').removeClass('hide');
                } else {
                    $('.alert-danger .msg-content').html(resp.message);
                    $('.alert-danger').removeClass('hide');
                }
                $(this).attr('disabled', false);
                location.reload();
            },
            error: function (e) {
                alert('Error: ' + e);
            }
        });
    }
</script>
@stop