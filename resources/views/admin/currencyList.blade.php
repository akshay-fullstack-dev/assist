@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/currency.currency_list') !!}
@stop
@section('styles')

@stop
{{-- Content --}}
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/currency.currency_list') !!}</h1>
        <!-- <ol class="breadcrumb">
            <li><a href="{!! URL::to('admin/currency/create') !!}"><i class="fa fa-plus-square"></i> {!! trans('admin/currency.add_currency') !!}</a></li>
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
                    <div class="box-body table-responsive">
                        <table id="currency_list" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="22%">{!! trans('admin/currency.currency_name') !!}</th>
                                    <th width="13%">{!! trans('admin/currency.currency_code') !!}</th>
                                    <th width="13%">{!! trans('admin/currency.language_code') !!}</th>
                                    <th width="13%">{!! trans('admin/currency.country_code') !!}</th>
                                    <th width="13%">{!! trans('admin/currency.country_code') !!}</th>
                                    <th width="13%">{!! trans('admin/common.status') !!}</th>
                                    <th width="13%">{!! trans('admin/common.action') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div> <!-- /. box body -->
                </div> <!-- /.box -->
            </div> <!-- /.col-xs-12 -->
        </div><!-- /.row (main row) -->

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@stop
{{-- Scripts --}}
@section('scripts')
<script type="text/javascript">
    var oTable;
    $(document).ready(function () {
        oTable = $('#currency_list').dataTable({
            "dom": "<'row no-gutters'<'col-md-4 no-padding'l><'col-md-4'r><'col-md-4 no-padding'f>>t<'row no-gutters'<'col-md-4 no-padding'i><'col-md-4'><'col-md-4 no-padding'p>>",
            "processing": true,
            "serverSide": true,
            "ajax": "{!! URL::to('admin/currency/CurrencyData') !!}",
            "columnDefs": [
                {"orderable": false, "targets": [4, 5, 6]},
            ],
            "order": [[0, "asc"]],
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
        $("#currency_list").on('click', '.delete-btn', function () {
            var id = $(this).attr('id');
            var r = confirm("{!! trans('admin/common.delete_confirmation') !!}");
            if (!r) {
                return false
            }
            $.ajax({
                type: "POST",
                url: "currency/" + id,
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
                    oTable.fnDraw();
                },
                error: function (e) {
                    alert('Error: ' + e);
                }
            });
        });
        $("#currency_list").on('click', '.status-btn', function () {
            var id = $(this).attr('id');
            var r = confirm("{!! trans('admin/common.status_confirmation') !!}");
            if (!r) {
                return false
            }
            $.ajax({
                type: "POST",
                url: "{!! URL::to('admin/currency/changeStatus') !!}",
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
                    } else {
                        $('.alert-danger .msg-content').html(resp.message);
                        $('.alert-danger').removeClass('hide');
                    }
                    $(this).attr('disabled', false);
                    oTable.fnDraw();
                },
                error: function (e) {
                    alert('Error: ' + e);
                }
            });
        });
    });
</script>
@stop