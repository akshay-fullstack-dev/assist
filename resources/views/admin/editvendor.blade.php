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
    .span-tab {
        display: inline-block;
        border: 1px solid #ccc;
        padding: 10px;
        border-radius: 10px;
        background-color: #01bbcb;
        margin-left: 10px;
        color: #fff;
    }
</style>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/vendors.vendor_edit') !!}</h1>


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
                    {!! Form::model($user, array('url' =>'admin/vendors/'.$user->id, 'method' => 'PATCH', 'id' =>
                    'service-form',
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
                            {!! Form::label('Gender', trans('admin/user.Gender')) !!} <br>
                            Male {!! Form::radio('gender', '0', (old('gender') == '0'), array('id'=>'', 'class'=>''))
                            !!} &nbsp;&nbsp;&nbsp;
                            Female {!! Form::radio('gender', '1', (old('gender') == '1'), array('id'=>'', 'class'=>''))
                            !!}
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            {!! Form::label('phone_number', trans('admin/user.phone_number'),
                            array('style'=>'display:block;')) !!}
                            <div class="row">
                                <div class="col-sm-2">{!! Form::text('phone_country_code',
                                    old("image"),array('class'=>'form-control', 'readonly')) !!}</div>
                                <div class="col-sm-10">
                                    {!! Form::text('phone_number', old("image"),array('class'=>'form-control',
                                    'readonly')) !!}
                                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group has-feedback">
                            {!! Form::label('Profile Image', trans('Profile Image')) !!}
                            <div class=row>
                                <div class="col-md-3">
                                    @if (isset($user) && $user->image)
                                    <img src="{!! url('images/avatars/' . $user->image) !!}" height="50" width="100"
                                        alt="Profile Image">
                                    @else
                                    <img src="{!! URL::asset('/public/images/dummy.png') !!}" height="50" width="100"
                                        alt="Profile Image">
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($user->userAddress->count())
                        <div class="form-group has-feedback">

                            {{ Form::label('address',trans('admin/user.address')) }}
                            <!-- Check address exxist in database or not -->
                            @foreach ($user->userAddress as $key)
                            <div class="well">
                                @if( strtolower($key->address_type) == "home")
                                <div class="row">
                                    <div class="form-group has-feedback">
                                        {{ Form::label('home_address',trans('admin/user.home_address')) }}
                                        <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="hidden" name="add[home][address_type]"
                                            value="{{$key->address_type}}">
                                        <input type="hidden" name="add[home][id]" value="{{$key->id}}">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('city',trans('admin/user.city')) }}
                                            <input class="form-control" type="text" name="add[home][city]"
                                                value="{{$key->city}}" readonly>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('country',trans('admin/user.country')) }}
                                            <input class="form-control" type="text" name="add[home][country]"
                                                value="{{$key->country}}" readonly>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('pincode',trans('admin/user.pincode')) }}
                                            <input class="form-control" type="text" name="add[home][pincode]"
                                                value="{{$key->pincode}}" readonly>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('full_address',trans('admin/user.full_address'), array('style'=>'display: block;')) }}
                                            <textarea id="" cols="30" name="add[home][full_address]" rows="5"
                                                readonly>{{$key->full_address}}</textarea>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if(strtolower($key->address_type) == "work")
                                <div class="row">
                                    <div class="form-group has-feedback">
                                        {{ Form::label('office_address',trans('admin/user.office_address')) }}
                                        <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            <input type="hidden" name="add[office][address_type]"
                                                value="{{$key->address_type}}">
                                            <input type="hidden" name="add[office][id]" value="{{$key->id}}">
                                            {{ Form::label('city',trans('admin/user.city')) }}
                                            <input class="form-control" type="text" name="add[office][city]"
                                                value="{{$key->city}}" readonly>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('country',trans('admin/user.country')) }}
                                            <input class="form-control" type="text" name="add[office][country]"
                                                value="{{$key->country}}" readonly>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('pincode',trans('admin/user.pincode')) }}
                                            <input class="form-control" type="text" name="add[office][pincode]"
                                                value="{{$key->pincode}}" readonly>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('full_address',trans('admin/user.full_address'), array('style'=>'display: block;')) }}
                                            <textarea name="add[office][full_address]" id="" cols="30" rows="5"
                                                readonly>{{$key->full_address}}</textarea>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <!-- if office addess -->
                                @if(strtolower($key->address_type) == "office")
                                <div class="row">
                                    <div class="form-group has-feedback">
                                        {{ Form::label('office_address',trans('admin/user.office_address')) }}
                                        <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            <input type="hidden" name="add[office][address_type]"
                                                value="{{$key->address_type}}">
                                            <input type="hidden" name="add[office][id]" value="{{$key->id}}">
                                            {{ Form::label('city',trans('admin/user.city')) }}
                                            <input class="form-control" type="text" name="add[office][city]"
                                                value="{{$key->city}}" readonly>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('country',trans('admin/user.country')) }}
                                            <input class="form-control" type="text" name="add[office][country]"
                                                value="{{$key->country}}" readonly>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('pincode',trans('admin/user.pincode')) }}
                                            <input class="form-control" type="text" name="add[office][pincode]"
                                                value="{{$key->pincode}}" readonly>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('full_address',trans('admin/user.full_address'), array('style'=>'display: block;')) }}
                                            <textarea name="add[office][full_address]" id="" cols="30" rows="5"
                                                readonly>{{$key->full_address}}</textarea>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                </div>
                                @endif


                                <!-- if office addess -->
                                @if( strtolower($key->address_type) == "other")
                                <div class="row">
                                    <div class="form-group has-feedback">
                                        {{ Form::label('home_address',trans('admin/user.other_address')) }}
                                        <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="hidden" name="add[other][address_type]"
                                            value="{{$key->address_type}}">
                                        <input type="hidden" name="add[other][id]" value="{{$key->id}}">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('city',trans('admin/user.city')) }}
                                            <input class="form-control" type="text" name="add[other][city]"
                                                value="{{$key->city}}" readonly>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('country',trans('admin/user.country')) }}
                                            <input class="form-control" type="text" name="add[other][country]"
                                                value="{{$key->country}}" readonly>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('pincode',trans('admin/user.pincode')) }}
                                            <input class="form-control" type="text" name="add[other][pincode]"
                                                value="{{$key->pincode}}" readonly>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group has-feedback">
                                            {{ Form::label('full_address',trans('admin/user.full_address'), array('style'=>'display: block;')) }}<br>
                                            <textarea name="add[other][full_address]" id="" cols="30" rows="5"
                                                readonly>{{$key->full_address}}</textarea>
                                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                </div> <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                @endif
                            </div>
                        </div>
                        @endforeach

                        @endif

                        {{ Form::label('full_address',trans('admin/vendors.vendor_services')) }}
                        <button type="button" title="Add new Slots" class="btn btn-primary modal-window" data-idval="1"
                            data-toggle="modal" data-target="#exampleModalCenter"><i class="fa fa-plus"></i></button>
                        <div class="well">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-striped  table-hover bg-secondary">
                                        <thead>
                                            <th width="10%">{!! Form::label('title', trans('admin/vendors.title')) !!}
                                            </th>
                                            <th width="50%">{!! Form::label('title', trans('admin/vendors.description'))
                                                !!}</th>
                                            <th width="10%">{!! Form::label('title', trans('admin/vendors.price')) !!}
                                            </th>
                                            <th width="10%">{!! Form::label('title', trans('admin/vendors.price_type'))
                                                !!}</th>
                                            <th width="10%">{!! Form::label('title', trans('admin/vendors.status')) !!}
                                            </th>
                                            <th width="10%">{!! Form::label('title', trans('admin/vendors.action')) !!}
                                            </th>
                                        </thead>
                                        <tbody>
                                            @foreach($venderServices as $venderService)
                                            <tr>
                                                <td>{{$venderService->service->title}}</td>
                                                <td style="word-break: break-word;">
                                                    {{ strip_tags($venderService->service->description) }}</td>
                                                <td>{{$venderService->price}}</td>
                                                <td>{{$venderService->service->price_type}}</td>
                                                <td>
                                                    @if( $venderService->status && $venderService->service->status =='1')
                                                    <a href="javascript:void(0);" class="btn btn-success status-btn"
                                                        id="{{$venderService->id}}"
                                                        title="{{trans('admin/common.click_to_inactive')}}">{{trans('admin/common.active')}}</a>
                                                    @else
                                                    <a href="javascript:void(0);" class="btn btn-danger status-btn"
                                                        id="{{$venderService->id}}"
                                                        title="{{trans('admin/common.click_to_active')}}">{{trans('admin/common.inactive')}}</a>
                                                    @endif
                                                </td>
                                                <td><a href="{{route('editVenderService', $venderService->id)}}"
                                                        class="btn btn-primary d-inline-block"
                                                        title="{{trans('admin/common.view')}}"><i
                                                            class="fa fa-pencil"></i> </a>&nbsp;<a
                                                        href="javascript:void(0);" id="{{$venderService->id}}"
                                                        class="btn btn-danger delete-btn"
                                                        title="{{trans('admin/common.delete')}}"
                                                        data-toggle="tooltip"><i class="fa fa-times"></i></a>
                                                </td>
                                            </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                    <div class="box-footer clearfix">
                                        <div class="col-md-12 text-center pagination pagination-sm no-margin">
                                            @if($venderServices)
                                            {!! $venderServices->render() !!}
                                            @endif
                                        </div>
                                        <div class="col-md-12 text-center">
                                            <a class="btn">{!! trans('admin/common.total') !!} {!!
                                                $venderServices->total() !!} </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{ Form::label('vendor_slots',trans('admin/vendors.vendor_slots')) }}
                        <div class="well">
                            <div class="box">
                                <div class="box-body table-responsive tab-slot">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <?php
                                            $monday = '';
                                            $tuesday = '';
                                            $wednesday = '';
                                            $thrusday = '';
                                            $friday = '';
                                            $saturday = '';
                                            $sunday = '';
                                            foreach ($vendorSlots as $slot) {

                                                $slot_from = date('H:i', strtotime($slot->slot_from));
                                                $slot_to = date('H:i', strtotime($slot->slot_to));
                                                $slot_id = $slot->id;

                                                if ($slot->day == 1) {
                                                    $monday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter">' . $slot_from . '-' . $slot_to . '<a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot_id . '"><i class=" fa fa-close"></i></a></span>';
                                                }
                                                if ($slot->day == 2) {
                                                    $tuesday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter">' . $slot_from . '-' . $slot_to . '<a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot_id . '"><i class=" fa fa-close"></i></a></span>';
                                                }
                                                if ($slot->day == 3) {
                                                    $wednesday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter">' . $slot_from . '-' . $slot_to . '<a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot_id . '"><i class=" fa fa-close"></i></a></span>';
                                                }
                                                if ($slot->day == 4) {
                                                    $thrusday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter">' . $slot_from . '-' . $slot_to . '<a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot_id . '"><i class=" fa fa-close"></i></a></span>';
                                                }
                                                if ($slot->day == 5) {
                                                    $friday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter">' . $slot_from . '-' . $slot_to . '<a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot_id . '"><i class=" fa fa-close"></i></a></span>';
                                                }
                                                if ($slot->day == 6) {
                                                    $saturday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter">' . $slot_from . '-' . $slot_to . '<a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot_id . '"><i class=" fa fa-close"></i></a></span>';
                                                }
                                                if ($slot->day == 7) {
                                                    $sunday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter">' . $slot_from . '-' . $slot_to . '<a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot_id . '"><i class=" fa fa-close"></i></a></span>';
                                                }
                                            }
                                            ?>

                                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                                <li class="nav-item" style="min-height:45px">
                                                    <a class="nav-link active" id="home-tab" data-toggle="tab"
                                                        href="#monday" role="tab" aria-controls="monday"
                                                        aria-selected="true">Monday</a>
                                                    <?php echo $monday; ?>
                                                </li>
                                                <li class="nav-item" style="min-height:45px">
                                                    <a class="nav-link" id="profile-tab" data-toggle="tab"
                                                        href="#tuesday" role="tab" aria-controls="tuesday"
                                                        aria-selected="false">Tuesday</a>
                                                    <?php echo $tuesday; ?>
                                                </li>
                                                <li class="nav-item" style="min-height:45px">
                                                    <a class="nav-link" id="contact-tab" data-toggle="tab"
                                                        href="#wednesday" role="tab" aria-controls="wednesday"
                                                        aria-selected="false">Wednesday</a><?php echo $wednesday; ?>
                                                </li>
                                                <li class="nav-item" style="min-height:45px">
                                                    <a class="nav-link" id="contact-tab" data-toggle="tab"
                                                        href="#thrusday" role="tab" aria-controls="thrusday"
                                                        aria-selected="false">Thrusday</a>
                                                    <?php echo $thrusday; ?>
                                                </li>
                                                <li class="nav-item" style="min-height:45px">
                                                    <a class="nav-link" id="contact-tab" data-toggle="tab"
                                                        href="#friday" role="tab" aria-controls="friday"
                                                        aria-selected="false">Friday</a>
                                                    <?php echo $friday; ?>
                                                </li>
                                                <li class="nav-item" style="min-height:45px">
                                                    <a class="nav-link" id="contact-tab" data-toggle="tab"
                                                        href="#saturday" role="tab" aria-controls="saturday"
                                                        aria-selected="false">Saturday</a>
                                                    <?php echo $saturday; ?>
                                                </li>
                                                <li class="nav-item" style="min-height:45px">
                                                    <a class="nav-link" id="contact-tab" data-toggle="tab"
                                                        href="#sunday" role="tab" aria-controls="sunday"
                                                        aria-selected="false">Sunday</a>
                                                    <?php echo $sunday; ?>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalCenterTitle">Assign service</h5>
                                <button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group has-feedback">
                                            {!! trans('admin/sidebar.from') !!}
                                            {!! Form::select('service', $allServices, null) !!}
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary close-modal"
                                    data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="add_service">Save changes</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    {!! Form::submit(trans('admin/common.submit'),array('class'=>'btn btn-primary', 'id'=>'submitform'))
                    !!}
                    <a href="{!! url('admin/vendors') !!}" class="btn btn-default">{!! trans('admin/common.cancel')
                        !!}</a>
                </div>
                {!! Form::close()!!}
            </div> <!-- /.box -->
        </div> <!-- /.col-xs-12 -->
</div><!-- /.row (main row) -->

</section><!-- /.content -->
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
            url: "{!! URL::to('admin/venderService/changeStatus') !!}",
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
            url: "{!! URL::to('admin/venderService/delete') !!}",
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