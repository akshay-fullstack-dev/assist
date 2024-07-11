<ul>

    <li class="hidden-sm {!! Request::is('agency/dashboard') ? ' active' : '' !!}">
        <a href="{!! url('agency/dashboard') !!}">
            <i class="fa fa-dashboard"></i>
            {!! trans('user/menu.dashboard')!!}
        </a>
    </li>
    @php
    $user = Auth::User();
    @endphp

    @if($user->status == '1')
    <li class="hidden-sm {!! Request::is('agency/allUsers') ? ' active' : '' !!}">
        <a href="{!! url('/agency/allUsers') !!}">
            <i class="fa fa-user"></i>
            {!! trans('user/agency.all_employees')!!}
        </a>
    </li>
    <li class="hidden-sm {!! Request::is('agency/addUsers') ? ' active' : '' !!}">
        <a href="{!! url('/agency/addUsers') !!}">
            <i class="fa fa-user-plus"></i>
            {!! trans('user/agency.add_employee')!!}
        </a>
    </li>
    <li class="hidden-sm {!! Request::is('agency/bookings-list') ? ' active' : '' !!}">
        <a href="{!! url('/agency/bookings-list') !!}">
            <i class="fa fa-shopping-cart"></i>
            {!! trans('user/agency.bookings')!!}
        </a>
    </li>
    @endif

</ul>