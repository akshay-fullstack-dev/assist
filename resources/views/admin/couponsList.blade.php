@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/coupons.coupons') !!}
@stop
@section('styles')
<link href="{!! asset('assets/admin/plugins/bootstrap3-editable/css/bootstrap-editable.css') !!}" rel="stylesheet" type="text/css" />
@stop
{{-- Content --}}
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/coupons.coupons') !!}</h1>
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
                        <table id="coupons" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="10%">{!! trans('admin/coupons.name') !!}</th>
                                    <th width="10%">{!! trans('admin/coupons.code') !!}</th>
                                    <th width="5%">{!! trans('admin/coupons.max_total_use') !!}</th>
                                    <th width="5%">{!! trans('admin/coupons.max_total_used') !!}</th>
                                    <th width="5%">{!! trans('admin/coupons.max_use_customer') !!}</th>
                                    <th width="10%">{!! trans('admin/coupons.discount') !!}</th>
                                    <th width="10%">{!! trans('admin/coupons.start_date') !!}</th>
                                    <th width="10%">{!! trans('admin/coupons.end_date') !!}</th>
                                    <th width="15%">{!! trans('admin/common.status') !!}</th>
                                    <th width="25%">{!! trans('admin/common.action') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop
{{-- Scripts --}}
@section('scripts')
<script src="{{asset('assets/admin/plugins/bootstrap3-editable/js/bootstrap-editable.min.js')}}" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function () {
    var oTable;

    oTable = $('#coupons').DataTable({
        "dom": "<'row no-gutters'<'col-xs-12 col-sm-4 col-md-4 col-lg-4 no-padding'l><'col-xs-12 col-sm-4 col-md-4 col-lg-4'r><'col-xs-12 col-sm-4 col-md-4 col-lg-4 no-padding'f>>t<'row no-gutters'<'col-xs-12 col-sm-4 col-md-4 col-lg-4 no-padding'i><'col-xs-12 col-sm-4 col-md-4 col-lg-4'><'col-xs-12 col-sm-4 col-md-4 col-lg-4 no-padding'p>>",
        "processing": true,
        "serverSide": true,
        "ajax": "{!! url('admin/coupons/couponData') !!}",
        "columnDefs": [
            {"orderable": false, "targets": [2]},
            {"targets": [10, 11], "visible": false}
        ],
        "order": [[10, "desc"]],
        "language": {
            "emptyTable": "{!! trans('admin/common.datatable.empty_table') !!}",
            "info": "{!! trans('admin/common.datatable.info') !!}",
            "infoEmpty": "{!! trans('admin/common.datatable.info_empty') !!}",
            "infoFiltered": "({!! trans('admin/common.datatable.info_filtered') !!})",
            "lengthMenu": "{!! trans('admin/common.datatable.length_menu') !!}",
            "loadingRecords": "{!! trans('admin/common.datatable.loading') !!}",
            "processing": "{!! trans('admin/common.datatable.processing') !!}",
            "search": "{!! trans('admin/common.datatable.search') !!}:",
            "zeroRecords": "{!! trans('admin/common.datatable.zero_records') !!}",
            "paginate": {
                "first": "{!! trans('admin/common.datatable.first') !!}",
                "last": "{!! trans('admin/common.datatable.last') !!}",
                "next": "{!! trans('admin/common.datatable.next') !!}",
                "previous": "{!! trans('admin/common.datatable.previous') !!}"
            },
        }
    });
    
    $("#coupons").on('click', '.delete-btn', function () {
        var id = $(this).attr('id');
        var r = confirm("{!! trans('admin/common.delete_confirmation') !!}");
        if (!r) {
            return false
        }
        $.ajax({
            type: "POST",
            url: "coupons/" + id,
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
                oTable.draw();
            },
            error: function (e) {
                alert('Error: ' + e);
            }
        });
    });
    
    $("#coupons").on('click', '.status-btn', function () {
        var id = $(this).attr('id');
        var r = confirm("{!! trans('admin/common.status_confirmation') !!}");
        if (!r) {
            return false
        }
        $.ajax({
            type: "POST",
            url: "{{ url('admin/coupons/changeStatus') }}",
            data: {
                id: id,
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
                oTable.draw();
            },
            error: function (e) {
                alert('Error: ' + e);
            }
        });
    });
});
</script>
@stop