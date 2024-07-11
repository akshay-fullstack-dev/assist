@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/service.services') !!}
@stop
@section('styles')
@stop
{{-- Content --}}
@section('content')
<style>
    .span-tab { display:inline-block; border:1px solid #ccc; padding:10px; border-radius:10px; background-color:#01bbcb; margin-left:10px; color:#fff;}
</style>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/agency.agency_edit') !!}</h1>


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
                @include('admin.includes.notifications')
                <!-- ./ notifications -->
            </div>
            <div class="col-xs-12">
                <div class="box">
                    @if(isset($user))
                    {!! Form::model($user, array('url' =>'admin/agencies/'.$user->id, 'method' => 'PATCH', 'id' => 'service-form',
                    'files' => true )) !!}
                    @else
                    {!! Form::open(array('route' => 'users.store', 'id' => 'service-form', 'files' => true)) !!}
                    @endif

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
                            {!! Form::label('phone_number', trans('admin/user.phone_number'), array('style'=>'display:block;')) !!}
                            <div class="row">
                                <div class="col-sm-2">{!! Form::text('phone_country_code', old("image"),array('class'=>'form-control', 'readonly')) !!}</div>
                                <div class="col-sm-10">
                                    {!! Form::text('phone_number', old("image"),array('class'=>'form-control', 'readonly')) !!}
                                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="box-footer">
                    {!! Form::submit(trans('admin/common.submit'),array('class'=>'btn btn-primary', 'id'=>'submitform')) !!}
                    <a href="{!! url('admin/agencies') !!}" class="btn btn-default">{!! trans('admin/common.cancel') !!}</a>
                </div>
                {!! Form::close()!!}



            </div> <!-- /.box -->
        </div> <!-- /.col-xs-12 -->


    </section><!-- /.content -->
    @if(count($employees) > 0)
    <section class="content-header">
        <h1>{!! trans('admin/agency.agency_employees') !!}</h1>
    </section>
    <section class="content">
        <!-- Main row -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{!! trans('admin/agency.firstname') !!}</th>
                                <th>{!! trans('admin/agency.lastname') !!}</th>
                                <th>{!! trans('admin/agency.email') !!}</th>
                                <th>{!! trans('admin/agency.phone_number') !!}</th>
                                <th>{!! trans('admin/agency.action') !!}</th>
                            </tr>
                        </thead>
                        <?php $i = 1; ?>
                        @foreach($employees as $employee)
                        <tr>
                            <td>{!! $employee->firstname !!}</td>
                            <td>{!! $employee->lastname !!}</td>
                            <td>{!! $employee->email !!}</td>
                            <td>{!! $employee->phone_number !!}</td>
                            <td><a href="<?php echo url('admin/users/' . $employee->id . '/edit') ?>" class="btn btn-primary d-inline-block" title="' . trans('admin/common.edit') . '" data-toggle="tooltip"><i class="fa fa-pencil"></i></a> <a href="javascript:;" id="{!! $employee->id !!}" class="btn btn-danger d-inline-block delete-btn" title="' . trans('admin/common.delete') . '" data-toggle="tooltip"><i class="fa fa-times"></i></a></td>

                        </tr>
                        <?php $i++; ?>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </section>
    @endif

    @if(isset($user->generalHistory) && count($user->generalHistory) > 0)
    <section class="content-header">
        <h1>{!! trans('admin/agency.rejection_history') !!}</h1>
    </section>

    <section class="content">
        <!-- Main row -->
        <div class="row">

            <div class="col-xs-12">
                <div class="box">



                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{!! trans('admin/agency.sno') !!}</th>
                                <th>{!! trans('admin/agency.message') !!}</th>
                                <th>{!! trans('admin/agency.rejectionDate') !!}</th>
                            </tr>
                        </thead>
                        <?php $i = 1; ?>
                        @foreach($user->generalHistory as $history)
                        <tr>
                            <td>{!! $i !!}</td>
                            <td>{!! $history->message !!}</td>
                            <td>{!! $history->created_at !!}</td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                    </table>
                </div>
            </div>

        </div>
    </section>
    @endif

</div><!-- /.content-wrapper -->
@stop

{{-- Scripts --}}
@section('scripts')
<script type="text/javascript">
    $("#add_service").on('click', function () {

        var service_id = $('[name="service"]').val();
        var user_id = "{!! $user->id !!}";
        console.log(service_id);
        console.log(user_id);
        $.ajax({
            type: "POST",
            url: "{!! URL::to('admin/venderService/addVenderService') !!}",
            data: {
                user_id: user_id,
                service_id: service_id,
                _token: "{!! csrf_token() !!}"
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
                    alert(resp.message);
                    $('.alert-success').removeClass('hide');
                    location.reload();
                } else {
                    alert(resp.message);

                }
                $(this).attr('disabled', false);
            },
            error: function (e) {
                alert('Error: ' + e);
            }
        });
    });
    $(".status-btn").on('click', function () {
        var id = $(this).attr('id');
        var r = confirm("{!! trans('admin/common.status_confirmation') !!}");

        if (!r) {
            return false
        }

        $.ajax({
            type: "POST",
            url: "{!! URL::to('admin/agency/changeStatus') !!}",
            data: {
                id: id,
                _token: "{!! csrf_token() !!}"
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
                    location.reload();
                } else {
                    $('.alert-danger .msg-content').html(resp.message);
                    $('.alert-danger').removeClass('hide');
                }
                $(this).attr('disabled', false);
            },
            error: function (e) {
                alert('Error: ' + e);
            }
        });
    });

    $(".delete-btn").on('click', function () {
        var id = $(this).attr('id');
        var r = confirm("{!! trans('admin/common.delete_confirmation') !!}");

        if (!r) {
            return false
        }

        $.ajax({
            type: "POST",
            url: "{!! URL::to('admin/users/') !!}/"+id,
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
    });

    $('.delete_this_slote').on('click', function (e) {
        e.stopPropagation();
        var delete_icon = $(this);
        var id = delete_icon.data('delid');
        console.log(id);
        console.log("{!! $user->id !!}");

        var r = confirm("{!! trans('admin/common.delete_confirmation') !!}");

        if (r) {
            $.ajax({
                type: "POST",
                url: "{!! URL::to('admin/vendorSlots/delete') !!}",
                data: {
                    vendorId: "{!! $user->id !!}",
                    slotId: id,
                    _token: "{!! csrf_token() !!}"
                },
                dataType: 'json',
                beforeSend: function () {
                    $(this).attr('disabled', true);
                    $('.alert .msg-content').html('');
                    $('.alert').hide();
                },
                success: function (resp) {
                    console.log(resp);
                    $('.alert:not(".session-box")').show();
                    if (resp.success) {
                        $('.alert-success .msg-content').html(resp.message);
                        $('.alert-success').removeClass('hide');
                        delete_icon.parent('.span-tab').remove();
                    } else {
                        $('.alert-danger .msg-content').html(resp.message);
                        $('.alert-danger').removeClass('hide');
                    }
                },
                error: function (e) {
                    alert('Error: ' + e);
                }
            });
        }
    });
</script>
@stop