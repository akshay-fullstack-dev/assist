@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/service.services') !!}
@stop
@section('styles')
@stop
{{-- Content --}}
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>{!! trans('admin/vendors.vendor_details') !!}</h1>

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
          {!! Form::model($user) !!}
          @endif
          <div class="box-body">
            <div class="form-group has-feedback">
              {!! Form::label('firstname', trans('admin/user.firstname')) !!}
              {!! Form::text('firstname', old("firstname"),array('class'=>'form-control','readonly'=>'readonly'))!!}
              <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
            </div>
            <div class="form-group has-feedback">
              {!! Form::label('lastname', trans('admin/user.lastname')) !!}
              {!! Form::text('lastname', old("lastname"),array('class'=>'form-control','readonly'=>'readonly')) !!}
              <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
            </div>
            <div class="form-group has-feedback">
              {!! Form::label('Gender', trans('admin/user.Gender')) !!}
              Male {{ Form::radio('gender', 'male' ),'disabled' }} &nbsp;&nbsp;&nbsp;

              Female {{ Form::radio('gender', 'female' ), 'disabled' }}
              <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
            </div>
            <div class="form-group has-feedback">
              {!! Form::label('phone_number', trans('admin/user.phone_number')) !!}
              {!! Form::text('phone_number', old("image"),array('class'=>'form-control','readonly'=>'readonly')) !!}
              <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
            </div>
              <div class="form-group has-feedback">
              {!! Form::label('Add_Image', trans('admin/vendors.image')) !!}

              <div class=row>
                <div class="col-md-4">
                  @if (isset($user) && $user->image)
                  
                  <img src="{!! url('images/avatars/' . $user->image) !!}" height="50" width="100"
                    alt="User Image">
                  @else
                  <img src="{!! URL::asset('/public/images/dummy.png') !!}" height="50" width="100" alt="User Image">
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
                @if( $key->address_type == "home")
                <div class="row">
                  <div class="form-group has-feedback">
                    {{ Form::label('home_address',trans('admin/user.home_address')) }}
                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                  </div>
                  <div class="col-md-3">
                    <input type="hidden" name="add[home][address_type]" value="{{$key->address_type}}">
                    <input type="hidden" name="add[home][id]" value="{{$key->id}}">
                    <div class="form-group has-feedback">
                      {{ Form::label('city',trans('admin/user.city')) }}
                      <input class="form-control" type="text" name="add[home][city]" value="{{$key->city}}" readonly>
                      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group has-feedback">
                      {{ Form::label('country',trans('admin/user.country')) }}
                      <input class="form-control" type="text" name="add[home][country]" value="{{$key->country}}"
                        readonly>
                      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group has-feedback">
                      {{ Form::label('pincode',trans('admin/user.pincode')) }}
                      <input class="form-control" type="text" name="add[home][pincode]" value="{{$key->pincode}}"
                        readonly>
                      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group has-feedback">
                      {{ Form::label('full_address',trans('admin/user.full_address')) }}<br>
                      <textarea id="" cols="30" name="add[home][full_address]" rows="5"
                        readonly>{{$key->full_address}}</textarea>
                      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div>
                @endif

                <!-- if office addess -->
                @if($key->address_type == "office")
                <div class="row">
                  <div class="form-group has-feedback">
                    {{ Form::label('office_address',trans('admin/user.office_address')) }}
                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group has-feedback">
                      <input type="hidden" name="add[office][address_type]" value="{{$key->address_type}}" readonly>
                      <input type="hidden" name="add[office][id]" value="{{$key->id}}">
                      {{ Form::label('city',trans('admin/user.city')) }}
                      <input class="form-control" type="text" name="add[office][city]" value="{{$key->city}}" readonly>
                      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group has-feedback">
                      {{ Form::label('country',trans('admin/user.country')) }}
                      <input class="form-control" type="text" name="add[office][country]" value="{{$key->country}}"
                        readonly>
                      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group has-feedback">
                      {{ Form::label('pincode',trans('admin/user.pincode')) }}
                      <input class="form-control" type="text" name="add[office][pincode]" value="{{$key->pincode}}"
                        readonly>
                      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group has-feedback">
                      {{ Form::label('full_address',trans('admin/user.full_address')) }}
                      <textarea name="add[office][full_address]" id="" cols="30" rows="5"
                        readonly>{{$key->full_address}}</textarea>
                      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div>
                @endif
                <!-- if office addess -->
                @if( $key->address_type == "other")
                <div class="row">
                  <div class="form-group has-feedback">
                    {{ Form::label('home_address',trans('admin/user.other_address')) }}
                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                  </div>
                  <div class="col-md-3">
                    <input type="hidden" name="add[other][address_type]" value="{{$key->address_type}}" readonly>
                    <input type="hidden" name="add[other][id]" value="{{$key->id}}">
                    <div class="form-group has-feedback">
                      {{ Form::label('city',trans('admin/user.city')) }}
                      <input class="form-control" type="text" name="add[other][city]" value="{{$key->city}}" readonly>
                      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group has-feedback">
                      {{ Form::label('country',trans('admin/user.country')) }}
                      <input class="form-control" type="text" name="add[other][country]" value="{{$key->country}}"
                        readonly>
                      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group has-feedback">
                      {{ Form::label('pincode',trans('admin/user.pincode')) }}
                      <input class="form-control" type="text" name="add[other][pincode]" value="{{$key->pincode}}"
                        readonly>
                      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group has-feedback">
                      {{ Form::label('full_address',trans('admin/user.full_address')) }}
                      <textarea name="add[other][full_address]" id="" cols="30" rows="5"
                        readonly>{{$key->full_address}}</textarea>
                      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                </div> <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
              </div>
              @endif
            @endforeach
            @endif
            </div>

           {{ Form::label('full_address',trans('admin/vendors.vendor_services')) }}
            <div class="well">       
              <div class="row">
                <div class="col-md-12">
                <table class="table table-striped  table-hover bg-secondary">
                <thead>
                    <th>{!! Form::label('title', trans('admin/vendors.title')) !!}</th>
                    <th>{!! Form::label('title', trans('admin/vendors.description')) !!}</th>
                    <th>{!! Form::label('title', trans('admin/vendors.price')) !!}</th>
                    <th>{!! Form::label('title', trans('admin/vendors.price_type')) !!}</th>
                </thead>
                <tbody>
                @foreach($venderServices as $verderService)
                <tr>
                  <td>{{$verderService->service->title}}</td>
                  <td>{{$verderService->service->description}}</td>
                  <td>{{$verderService->service->price}}</td>
                  <td>{{$verderService->service->price_type}}</td>
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
                            <a class="btn">{!! trans('admin/common.total') !!} {!! $venderServices->total() !!} </a>
                        </div>
                  </div>
                </div>
              </div>



              </div> 
                </div>
              </div>


            
          </div>
          {!! Form::close()!!}
        </div> <!-- /.box -->
      </div> <!-- /.col-xs-12 -->
    </div><!-- /.row (main row) -->

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop