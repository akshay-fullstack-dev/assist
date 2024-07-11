@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/service.services_list') !!}
@stop
@section('styles')

@stop
{{-- Content --}}
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/servicecategory.categories_list') !!}</h1>
        <!-- <ol class="breadcrumb">
            <li><a href="{!! URL::to('admin/services/create') !!}"><i class="fa fa-plus-square"></i> {!! trans('admin/service.add_service') !!}</a></li>
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
                        <table id="services_list" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="10%">{!! trans('admin/servicecategory.name') !!}</th>
                                    <th width="10%">{!! trans('admin/common.image') !!}</th>
                                    <th width="10%">{!! trans('admin/common.status') !!}</th>
                                    <th width="10%">{!! trans('admin/common.action') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                <tr>
                                    <td>{{ $category->cat_name }}</td>
                                    <td>
                                        @if($category->image)
                                        <img style="width:40px;" src="{{ url('assets/category/'.$category->image) }}">
                                        @endif
                                    </td>
                                    <td>
                                        @if($category->status)
                                        <a href="javascript:void(0);" class="btn btn-success status-btn" id="{{$category->id}}" data-toggle="tooltip">Active</a>
                                        @else
                                        <a href="javascript:void(0);" class="btn btn-danger status-btn" id="{{$category->id}}" data-toggle="tooltip">Inactive</a>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('admin/editCategory', ['id' => $category->id]) }}" class="btn btn-primary" title="" data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-pencil"></i></a>
                                        <a href="{{ url('admin/deleteCategory', ['id' => $category->id]) }}" id="{{}}" class="btn btn-danger delete-btn" title="" data-toggle="tooltip" data-original-title="Delete" onclick="return confirm('Are you sure you want to delete this record');"><i class="fa fa-times"></i></a>
                                    </td>
                                </tr>
                                @endforeach
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
<script>
    $('.status-btn').on('click', function () {
        var status_button = $(this);

        var id = status_button.attr('id');
        var r = confirm("{!! trans('admin/common.status_confirmation') !!}");
        if (!r) {
            return false;
        }

        $.ajax({
            type: "POST",
            url: "{!! URL::to('admin/servicesCategory/changeStatus') !!}",
            data: {
                id: id,
                _token: "{!! csrf_token() !!}"
            },
            dataType: 'json',
            beforeSend: function () {
                $('.alert .msg-content').html('');
                $('.alert').hide();
            },
            success: function (resp) {
                $('.alert:not(".session-box")').show();
                if (resp.success) {
                    if (status_button.hasClass('btn-success')) {
                        status_button.addClass('btn-danger').removeClass('btn-success');
                        status_button.text('Inactive');
                    } else {
                        status_button.addClass('btn-success').removeClass('btn-danger');
                        status_button.text('Active');
                    }
                } else {
                    $('.alert-danger .msg-content').html(resp.message);
                    $('.alert-danger').removeClass('hide');
                }
                oTable.fnDraw();
            },
            error: function (e) {
                alert('Error: ' + e);
            }
        });
    });
</script>
@stop
