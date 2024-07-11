@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/transaction.transactions_list') !!}
@stop

{{-- Content --}}
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/transaction.transactions_list') !!}</h1>
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
                    <div class="box-body table-responsive">
                        <div class="row no-gutters mrgn_10b">
                            <div class="col-md-8">
                                {!! Form::open(array('route' => 'admin.transaction.search', 'id' =>
                                'transaction-search-form', 'class' => 'form-inline','method' => 'POST')) !!}
                                <div class="form-group">
                                    {!! Form::label('search', trans('admin/common.search')) !!}
                                    {!! Form::select('search_by',array(''=>trans('admin/common.search_by'), 'user' =>
                                    trans('admin/transaction.user_name'), 'trans_id' =>
                                    trans('admin/transaction.transaction_id'), 'online' =>
                                    trans('admin/booking.paid_online'), 'offline' => trans('admin/booking.offline'),
                                    'transaction_date' => trans('admin/transaction.transaction_date')),
                                    session('SEARCH.SEARCH_BY') , array('class'=>'form-control', 'id' => 'search_by'))
                                    !!}
                                </div>
                                <div class="form-group">
                                    @if (session('SEARCH.SEARCH_BY') == 'user' || session('SEARCH.SEARCH_BY') ==
                                    'transaction_date')
                                    {!! Form::text('search_txt', session('SEARCH.SEARCH_TXT') ,array('id' =>
                                    'search_txt', 'class' => 'form-control', 'style' => 'display:none;')) !!}
                                    @else
                                    {!! Form::text('search_txt', session('SEARCH.SEARCH_TXT') ,array('id' =>
                                    'search_txt', 'class' =>
                                    'form-control','placeholder'=>trans('admin/common.search'),'style' =>
                                    'display:none;')) !!}
                                    @endif
                                </div>
                                <div class="form-group">
                                    @if (session('SEARCH.SEARCH_BY') == 'user')
                                    {!! Form::select('user_id',$userList, session('SEARCH.USER_ID') ,
                                    array('class'=>'form-control', 'id' => 'user_id', 'style' =>
                                    'display:inline-block;')) !!}
                                    @else
                                    {!! Form::select('user_id',$userList, session('SEARCH.USER_ID') ,
                                    array('class'=>'form-control', 'id' => 'user_id', 'style' => 'display:none;')) !!}
                                    @endif
                                </div>
                                <div class="form-group">
                                    @if (session('SEARCH.SEARCH_BY') == 'transaction_date')
                                    <div class="input-group search_date" style="display:inline-table;">
                                        {!! Form::text('search_date',session('SEARCH.SEARCH_DATE'),['id' =>
                                        'search_date_from','class' => 'datepicker cccc form-control',]) !!}
                                        <span class="input-group-addon transaction_start_date pointer"><i
                                                class="fa fa-calendar"></i></span>

                                        {!! Form::text('search_date_to',session('SEARCH.SEARCH_DATE_TO'),['id' =>
                                        'search_date_to','class' => 'datepicker cccc form-control',]) !!}
                                        <span class="input-group-addon transaction_end_date pointer" id="22"><i
                                                class="fa fa-calendar"></i></span>
                                    </div>
                                    @else
                                    <div class="input-group search_date" style="display:none;">
                                        {!! Form::text('search_date',session('SEARCH.SEARCH_DATE'),['id'
                                        =>'search_date_from','class' => 'datepicker bbbb form-control',]) !!}
                                        <span class="input-group-addon transaction_start_date pointer"><i
                                                class="fa fa-calendar"></i></span>
                                        {!! Form::text('search_date_to',session('SEARCH.SEARCH_DATE'),['id'
                                        =>'search_date_to','class' => 'datepicker bbbb form-control',]) !!}
                                        <span class="input-group-addon transaction_end_date pointer"><i
                                                class="fa fa-calendar"></i></span>
                                    </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    {!! Form::submit(trans('admin/common.search'), array('id' => 'search', 'name' => '',
                                    'class' => 'btn btn-info')) !!}
                                    {!! Form::button(trans('admin/common.reset'),array('type'=>'submit','id' => 'reset',
                                    'name' => 'reset', 'value' => '1', 'class' => 'btn btn-defult')) !!}
                                </div>
                                {!! Form::close() !!}
                            </div>
                            <div class="col-md-4">

                                <a href="{{ route('admin.transaction.export') }}"
                                    class="btn btn-sm btn-info pull-right">{!! trans('admin/transaction.export_csv')
                                    !!}</a>

                            </div>
                        </div>
                        <table id="transaction_list" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{!! trans('admin/transaction.transaction_id') !!}</th>
                                    <th>{!! trans('admin/transaction.user_name') !!}</th>
                                    <th>{!! trans('admin/transaction.payment_method') !!}</th>
                                    <th>{!! trans('admin/transaction.amount') !!}</th>
                                    <th>{!! trans('admin/transaction.admin_amount') !!}</th>
                                    <th>{!! trans('admin/transaction.vender_amount') !!}</th>
                                    <th>{!! trans('admin/transaction.booking_id') !!}</th>
                                    <th>{!! trans('admin/transaction.transaction_date') !!}(GMT)</th>
                                    <th width="12%">{!! trans('admin/common.status') !!}</th>
                                    <th width="5%">{!! trans('admin/common.action') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($transactions))
                                @foreach ($transactions as $data)
                                <tr>
                                    <td>{!! $data->trans_id !!}</td>
                                    <td>{!! $data->user->firstname.' '.$data->user->lastname !!}</td>
                                    <td>
                                        @if ($data->payment_method == 0) {
                                        {!! 'Paypal' !!}
                                        @elseif($data->payment_method == 2)
                                        {!! 'Online Payment' !!}
                                        @else
                                        {!! 'cash on delivery' !!}

                                        @endif
                                    </td>
                                    <td>
                                        @if($data->amount)
                                        {!! Config::get('constants.CURRENCY_SYMBOL') !!}
                                        {!! number_format($data->amount, 2) !!}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($data->admin_amount) {!! Config::get('constants.CURRENCY_SYMBOL') !!} {!!
                                        number_format($data->admin_amount, 2) !!} @endif</td>
                                    <td>
                                        @if ($data->vender_amount) {!! Config::get('constants.CURRENCY_SYMBOL') !!} {!!
                                        number_format($data->vender_amount, 2) !!} @endif</td>
                                    <td><a href="{!! url('/admin/booking/'.$data->booking_id) !!}">{!! $data->booking_id
                                            !!}</a></td>
                                    <td>{!! date('d-m-Y H:i:s',strtotime($data->created_at)) !!}</td>
                                    <td>
                                        @if($data->status == 'success')
                                        <a class="btn btn-success"
                                            title="{!! trans('admin/transaction.success_transaction') !!}"
                                            data-toggle="tooltip">{!! trans('admin/transaction.success') !!}</a>
                                        @else
                                        {!! $data->status !!}
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{!! url('/admin/transaction/'.$data->id) !!}" id="{!! $data->id !!}"
                                            class="btn btn-primary view-btn" title="{!! trans('admin/common.view') !!}"
                                            data-toggle="tooltip"><i class="fa fa-eye"></i></a>&nbsp;
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="8" class="text-center">
                                        {!! trans('admin/transaction.no_transactions_found') !!}
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div> <!-- /. box body -->
                    <div class="box-footer clearfix">
                        <div class="col-md-12 text-center pagination pagination-sm no-margin">
                            @if($transactions)
                            {!! $transactions->render() !!}
                            @endif
                        </div>
                        <div class="col-md-12 text-center">
                            <a class="btn">{!! trans('admin/common.total') !!} {!! $transactions->total() !!} </a>
                        </div>
                    </div><!-- /. box-footer -->
                </div> <!-- /.box -->
            </div> <!-- /.col-xs-12 -->
        </div><!-- /.row (main row) -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop
{{-- Scripts --}}
@section('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $("#transaction_list").on('click', '.delete-btn', function () {
            var id = $(this).attr('id');
            var r = confirm("{!! trans('admin/common.delete_confirmation') !!}");
            if (!r) {
                return false
            }
            $.ajax({
                type: "POST",
                url: "transaction/" + id,
                data: {
                    _method: 'DELETE',
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

        $('#search_by').change(function () {
            if ($('#search_by').val() == 'user') {
                $("#user_id").show();
                $("#search_txt").hide();
                $(".search_date").hide();
            } else if ($('#search_by').val() == 'transaction_date') {
                $("#user_id").hide();
                $("#search_txt").hide();
                $(".search_date").show();
            } else if ($('#search_by').val() == 'online') {
                $("#user_id").hide();
                $("#search_txt").hide();
                $(".search_date").hide();
            } else if ($('#search_by').val() == 'offline') {
                $("#user_id").hide();
                $("#search_txt").hide();
                $(".search_date").hide();
            } else {
                $("#user_id").hide();
                $("#search_txt").show();
                $(".search_date").hide();
            }
        });

        $(window).on('load', function () {
            $('#search_by').trigger('change');
        });

        $('.datepicker').datepicker({
            format: "dd-mm-yyyy",
            //startDate: "od",
            todayHighlight: true,
            todayBtn: true,
            autoclose: true
        }).inputmask('dd-mm-yyyy', {
            "placeholder": "dd-mm-yyyy",
            alias: "date",
            "clearIncomplete": true
        });
        $('.transaction_start_date').click(function () {
            //alert('hello');
            $('#search_date_from').trigger('focus');
        });
        $('.transaction_end_date').click(function () {
            //alert('hello');
            $('#search_date_to').trigger('focus');
        });
    });
</script>
@stop