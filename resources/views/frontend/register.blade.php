@extends('frontend.layouts.default')

@section('content')
<section class="process-section section-padding" style="margin-top:100px;">
    <div class="container">
        <div class="row text-center" style="width:600px; margin:0 auto">
            <p>{!! trans('user/register.already_have_account') !!} &nbsp; <a  href="{!! url('agency/login') !!}"  class="xs-block">{!! trans('user/register.sign_in') !!}</a></p>
            @include('frontend.includes.notifications')
            {!! Form::open(['url' => 'agency/store', 'id' => 'register-form', 'class' => 'form', 'files' => true]) !!}

            <div class="form-group has-feedback">
                {!! Form::text('firstname', old('firstname'),array('class'=>'form-control', 'placeholder'=>trans('user/register.owner_first_name'))) !!}
                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
            </div>

            <div class="form-group has-feedback">
                {!! Form::text('lastname', old('lastname'),array('class'=>'form-control', 'placeholder'=>trans('user/register.owner_last_name'))) !!}
                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
            </div>
            <div class="form-group has-feedback">
                {!! Form::text('agency_name', old('agency_name'),array('class'=>'form-control', 'placeholder'=>trans('user/register.agency_name'))) !!}
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
                        {!! Form::password('password', array('class'=>'form-control', 'id'=>'password', 'placeholder'=>trans('user/register.password'))) !!}
                        <span class="glyphicon form-control-feedback" aria-hidden="true" style="width:64px"></span>
                    </div>
                    <div class="col-lg-1">
                        <i class="fa fa-info-circle" aria-hidden="true"  data-toggle="tooltip" title="Example password: Singh@123#"></i>

                    </div>

                </div>
            </div>
            <div class="form-group has-feedback">
                {!! Form::password('password_confirmation', array('class'=>'form-control', 'placeholder'=>trans('user/register.confirm_password'))) !!}
                {!! Form::hidden('role_id', '3') !!}
                <span class="glyphicon form-control-feedback" aria-hidden="true" ></span>
            </div>
            <div class="form-group has-feedback">
                <div style="padding:10px; background-color:#fff;">
                    <div class="text-left">
                        {!! Form::label('phone_number', trans('user/agency.services')) !!}
                    </div>
                    <div class="text-left row">
                        @foreach ( $services as $service )
                        <div class='col-lg-4'>
                            {!! Form::checkbox( 'services[]', $service->id, NULL, ['class' => '',] ) !!}
                            {!! Form::label($service->title,  $service->title) !!}
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <div class="form-group has-feedback">
                <div style="padding:10px; background-color:#fff;">
                    <div class="row">

                        <div class="col-lg-3">
                            <label>Documents</label>
                        </div>
                        <div class="col-lg-8">

                            <input id="upload_doc" type="file" class="form-control" name="document1[]" multiple />
                            <span class="glyphicon form-control-feedback" aria-hidden="true" style="width:64px"></span>
                        </div>
                        <div class="col-lg-1">
                            <i class="fa fa-info-circle" aria-hidden="true"  data-toggle="tooltip" title="Upload multiple by select multple files while choose file"></i>
                        </div>
                    </div>
                </div>
            </div>


            <div class="form-group has-feedback">
                <div class="row">
                    <div class="col-lg-12">
                        <div id="locationField" class='clearfix'>
                            <input id="autocomplete" class='form-control' id='autocomplete' name="address[full_address]" placeholder='Enter your address' onFocus="geolocate()" type="text">
                        </div>
                        <span class="glyphicon form-control-feedback" aria-hidden="true" style="width:64px"></span>
                    </div>
                </div>
            </div>
            <div class="form-group has-feedback">
                <div class="row" style="display:none">
                    <div class="col-lg-6"><input class="form-control" name="" id="street_number" placeholder="Street address" disabled="true"></div>
                    <div class="col-lg-6"><input class="form-control" id="route" disabled="true"></div>
                </div>
                <div class="row"><div class="col-lg-6">
                        <input class="form-control" id="locality" name="address[city]"  placeholder="City" required></div>
                    <div class="col-lg-6">
                        <input class="form-control" id="postal_code" placeholder="Zip code" name="address[pincode]" required>
                    </div>
                </div>
                <div class="row">
                    
                    <div class="col-lg-6"><input class="form-control"id="administrative_area_level_1"  style="display:none"  placeholder="State"></div>
                    
                </div>
                <div class="row">
                    <div class="col-lg-6"><input class="form-control" id="lat" placeholder="Lat" name="address[latitude]"  ></div>
                    <div class="col-lg-6"><input class="form-control" id="long" placeholder="Long" name="address[longitude]" ></div>
                </div>
                <div class="row">
                    <div class="col-lg-12"><input placeholder="Country" class="form-control" name="address[country]" id="country" ></div>
                </div>

            </div>





            <div class="form-group has-feedback">

                <div class="form-group has-feedback">
                    <button type="submit" class="btn btn-primary">{!! trans('user/register.register') !!}</button>
                </div>
            </div>

            {!! Form::close()!!}
        </div>
    </div>
</section>

@stop
@section('styles')

@end
@section('scripts')
<script>
    // This example displays an address form, using the autocomplete feature
    // of the Google Places API to help users fill in the information.

    // This example requires the Places library. Include the libraries=places
    // parameter when you first load the API. For example:
    // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

    var placeSearch, autocomplete;
    var componentForm = {
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'short_name',
        country: 'long_name',
        postal_code: 'short_name'
    };

    function initAutocomplete() {
        // Create the autocomplete object, restricting the search to geographical
        // location types.
        autocomplete = new google.maps.places.Autocomplete(
                /** @type {!HTMLInputElement} */(document.getElementById('autocomplete')),
                {types: ['geocode']});

        // When the user selects an address from the dropdown, populate the address
        // fields in the form.
        autocomplete.addListener('place_changed', fillInAddress);
    }

    function fillInAddress() {
        // Get the place details from the autocomplete object.
        var place = autocomplete.getPlace();
        console.log(place.geometry.location);
        console.log(place.geometry.location.lat());
        console.log(place.geometry.location.lng());
        document.getElementById('lat').value = place.geometry.location.lat();
        document.getElementById('long').value = place.geometry.location.lng();
        for (var component in componentForm) {

            document.getElementById(component).value = '';
            document.getElementById(component).disabled = false;
        }

        // Get each component of the address from the place details
        // and fill the corresponding field on the form.
        for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];
            if (componentForm[addressType]) {
                var val = place.address_components[i][componentForm[addressType]];
                document.getElementById(addressType).value = val;
            }
        }
    }

    // Bias the autocomplete object to the user's geographical location,
    // as supplied by the browser's 'navigator.geolocation' object.
    function geolocate() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                var geolocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                var circle = new google.maps.Circle({
                    center: geolocation,
                    radius: position.coords.accuracy
                });
                autocomplete.setBounds(circle.getBounds());
            });
        }
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCFqZK-UIXS_xpNYXis6ctW95zcaMybdsI&libraries=places&callback=initAutocomplete"
async defer></script>
@stop

