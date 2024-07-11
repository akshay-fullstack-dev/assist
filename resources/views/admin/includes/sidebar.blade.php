<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu">
      <li class="{!! (Request::is('admin/dashboard') ? 'active' : '') !!}">
        <a href="{!!url('admin')!!}">
          <i class="fa fa-dashboard"></i> <span>{!! trans('admin/sidebar.dashboard') !!}</span>
        </a>
      </li>

      <li class="treeview {!! (Request::is('admin/settings*') || Request::is('admin/paymentsettings*') || Request::is('admin/paypalsettings*') || Request::is('admin/password/change') ? ' active' : '') !!}">
        <a href="javascript:;">
          <i class="fa fa-wrench"></i>
          <span>{!! trans('admin/sidebar.settings') !!}</span>
          <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
          <li class="{!! (Request::is('admin/settings*') ? 'active' : '') !!}"><a href="{!!url('admin/settings')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.general_setting') !!}</a></li>
          <li class="{!! (Request::is('admin/paymentsettings*') ? 'active' : '') !!}"><a href="{!!url('admin/paymentsettings')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.payment_setting') !!}</a></li>
          <li class="{!! (Request::is('admin/paypalsettings*') ? 'active' : '') !!}"><a href="{!!url('admin/paypalsettings')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.paypal_setting') !!}</a></li>
          <li class="{!! (Request::is('admin/password/change') ? 'active' : '') !!}"><a href="{!!url('admin/password/change')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.change_password') !!}</a></li>
        </ul>
      </li>
      <li class="treeview {!! (Request::is('admin/currency*') ? ' active' : '') !!}">
        <a href="javascript:;">
          <i class="fa fa-money"></i>
          <span>{!! trans('admin/sidebar.currency_management') !!}</span>
          <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
          <li class="{!! (Request::is('admin/currency/create') ? 'active' : '') !!}"><a href="{!!url('admin/currency/create')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.add_currency') !!}</a></li>
          <li class="{!! (Request::is('admin/currency') ? 'active' : '') !!}"><a href="{!!url('admin/currency')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.currency_list') !!}</a></li>
        </ul>
      </li>
      <li class="treeview {!! (Request::is('admin/services*') ? ' active' : '') !!} {!! (Request::is('admin/equipments*') ? ' active' : '') !!} {!! (Request::is('admin/addCategory') ? ' active' : '') !!} {!! (Request::is('admin/listCategory') ? ' active' : '') !!} {!! (Request::is('admin/service/frequency*') ? ' active' : '') !!}">
        <a href="javascript:;">
          <i class="fa fa-cogs"></i>
          <span>{!! trans('admin/sidebar.services_management') !!}</span>
          <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
          <li class="{!! (Request::is('admin/addCategory') ? 'active' : '') !!}"><a href="{!!url('admin/addCategory')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/servicecategory.add_category') !!}</a></li>
          <li class="{!! (Request::is('admin/listCategory') ? 'active' : '') !!}"><a href="{!!url('admin/listCategory')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/servicecategory.categories_list') !!}</a></li>
          <li class="{!! (Request::is('admin/services/create') ? 'active' : '') !!}"><a href="{!!url('admin/services/create')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.add_service') !!}</a></li>
          <li class="{!! (Request::is('admin/services') ? 'active' : '') !!}"><a href="{!!url('admin/services')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.services_list') !!}</a></li>
          <li class="{!! (Request::is('admin/equipments/create') ? 'active' : '') !!}"><a href="{!!url('admin/equipments/create')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.add_equipment') !!}</a></li>
          <li class="{!! (Request::is('admin/equipments') ? 'active' : '') !!}"><a href="{!!url('admin/equipments')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.equipment_list') !!}</a></li>

          {{-- service frequecy listing  --}}
          <li class="{!! (Request::is('admin/service/frequency/create') ? 'active' : '') !!}"><a href="{!!url('admin/service/frequency/create')!!}"><i class="fa fa-angle-double-right"></i>{!!trans('admin/sidebar.add_frequency') !!}</a>
          </li>

          <li class="{!! (Request::is('admin/service/frequency') ? 'active' : '') !!}"><a href="{!!url('admin/service/frequency')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.service_frequency_list') !!}</a></li>
        </ul>
      </li>
      <li class="{!! (Request::is('admin/slots/') ? 'active' : '') !!}">
        <a href="{!!url('admin/slots/')!!}">
          <i class="fa fa-calendar"></i><span>{!! trans('admin/sidebar.slotes_list') !!}</span>
        </a>
      </li>
      <li class="{!! (Request::is('admin/users') ? 'active' : '') !!}">
        <a href="{!!url('admin/users')!!}">
          <i class="fa fa-user"></i><span>{!! trans('admin/sidebar.users_list') !!}</span>
        </a>
      </li>
      <li class="{!! (Request::is('admin/agencies') ? 'active' : '') !!}">
        <a href="{!!url('admin/agencies')!!}">
          <i class="fa fa-user"></i><span>{!! trans('admin/sidebar.agencies_list') !!}</span>
        </a>
      </li>
      <li class="{!! (Request::is('admin/vendors') ? 'active' : '') !!}">
        <a href="{!!url('admin/vendors')!!}">
          <i class="fa fa-user"></i><span>{!! trans('admin/sidebar.vendors_list') !!}</span>
        </a>
      </li>
      <li class="{!! (Request::is('admin/booking') ? 'active' : '') !!}">
        <a href="{!!url('admin/booking')!!}">
          <i class="fa fa-dollar"></i><span>{!! trans('admin/sidebar.booking_list') !!}</span>
        </a>
      </li>
      <li class="{!! (Request::is('admin/booking-report') ? 'active' : '') !!}">
        <a href="{!!url('admin/booking-report')!!}">
          <i class="fa fa-dollar"></i><span>{!! trans('admin/booking.booking_Report') !!}</span>
        </a>
      </li>
      <li class="treeview {!! (Request::is('admin/coupons/create') ? ' active' : '') !!} {!! (Request::is('admin/coupons') ? ' active' : '') !!}">
        <a href="javascript:;">
          <i class="fa fa-tags"></i>
          <span>{!! trans('admin/sidebar.coupons') !!}</span>
          <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
          <li class="{!! (Request::is('admin/coupons/create') ? 'active' : '') !!}"><a href="{!!url('admin/coupons/create')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/coupons.add_coupon') !!}</a></li>
          <li class="{!! (Request::is('admin/coupons') ? 'active' : '') !!}"><a href="{!!url('admin/coupons')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/coupons.list_coupon') !!}</a></li>
        </ul>
      </li>
      <li class="treeview {!! (Request::is('admin/slider/create') ? ' active' : '') !!} {!! (Request::is('admin/slider') ? ' active' : '') !!}">
        <a href="javascript:;">
          <i class="fa fa-cogs"></i>
          <span>{!! trans('admin/sidebar.banners') !!}</span>
          <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
          <li class="{!! (Request::is('admin/slider/create') ? 'active' : '') !!}"><a href="{!!url('admin/slider/create')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/banners.add_banner') !!}</a></li>
          <li class="{!! (Request::is('admin/Slider') ? 'active' : '') !!}"><a href="{!!url('admin/slider')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/banners.list_banner') !!}</a></li>
        </ul>
      </li>
      <li class="{!! (Request::is('admin/transaction') ? 'active' : '') !!}">
        <a href="{!!url('admin/transaction')!!}">
          <i class="fa fa-dollar"></i><span>{!! trans('admin/sidebar.transaction_list') !!}</span>
        </a>
      </li>
      <!--li class="{!! (Request::is('admin/chatboard/*') ? 'active' : '') !!}">
                            <a href="{!!url('admin/chatboard')!!}">
                                <i class="fa fa-comment"></i> <span>{!! trans('admin/sidebar.chat_dashboard') !!}</span>
                            </a>
                        </li-->
      <li class="treeview {!! (Request::is('admin/enquiry*') ? ' active' : '') !!}">
        <a href="javascript:;">
          <i class="fa fa-question-circle"></i>
          <span>{!! trans('admin/sidebar.enquiry_management') !!}</span>
          <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
          <li class="{!! (Request::is('admin/enquiry') ? 'active' : '') !!}"><a href="{!!url('admin/enquiry')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.enquiry_list') !!}</a></li>
        </ul>
      </li>


      <li class="treeview {!! (Request::is('admin/notifications*')  ? ' active' : '') !!}">
        <a href="{!!url('admin/notifications/all')!!}">
          <i class="fa fa-bell"></i>
          <span>{!! trans('admin/sidebar.notifications') !!}</span>
        </a>
        <ul class="treeview-menu">
          <li class="{!! (Request::is('admin/notifications') ? 'active' : '') !!}"><a href="{!!url('admin/notifications/all')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.allnotifications') !!}</a></li>
          <li class="{!! (Request::is('admin/createnotification*') ? 'active' : '') !!}"><a href="{!!url('admin/createnotification')!!}"><i class="fa fa-angle-double-right"></i>{!!
              trans('admin/sidebar.sendnotification') !!}</a></li>
        </ul>
      </li>

    </ul>
  </section>
  <!-- /.sidebar -->
</aside>
