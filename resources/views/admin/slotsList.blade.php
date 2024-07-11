@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/slot.slots') !!}
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
        <h1>{!! trans('admin/slot.slots_list') !!}</h1>
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
                                foreach ($slots as $slot) {
                                    $slot_from = date('H:i', strtotime($slot->slot_from));
                                    $slot_to = date('H:i', strtotime($slot->slot_to));
                                    $slot_id = $slot->id;
                                    if ($slot->day == 1) {
                                        $monday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter"><span id="start_time">' . $slot_from . '</span>-<span id="end_time">' . $slot_to . '</span><a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot->id . '"><i class=" fa fa-close"></i></a></span>';
                                    }
                                    if ($slot->day == 2) {
                                        $tuesday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter">' . $slot_from . '-' . $slot_to . '<a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot_id . '"><i class=" fa fa-close"></i></a></span>';
                                    }
                                    if ($slot->day == 3) {
                                        $wednesday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter">' . $slot_from . '-' . $slot_to . '<a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot->id . '"><i class=" fa fa-close"></i></a></span>';
                                    }
                                    if ($slot->day == 4) {
                                        $thrusday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter">' . $slot_from . '-' . $slot_to . '<a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot->id . '"><i class=" fa fa-close"></i></a></span>';
                                    }
                                    if ($slot->day == 5) {
                                        $friday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter">' . $slot_from . '-' . $slot_to . '<a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot->id . '"><i class=" fa fa-close"></i></a></span>';
                                    }
                                    if ($slot->day == 6) {
                                        $saturday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter">' . $slot_from . '-' . $slot_to . '<a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot->id . '"><i class=" fa fa-close"></i></a></span>';
                                    }
                                    if ($slot->day == 7) {
                                        $sunday .= '<span class="span-tab" data-id="' . $slot_id . '" data-toggle="modal" data-target="#exampleModalCenter">' . $slot_from . '-' . $slot_to . '<a href="javascrit:void(0)" style="display:inline-block; width:20px;" class="delete_this_slote" data-delid="' . $slot->id . '"><i class=" fa fa-close"></i></a></span>';
                                    }
                                }
                                ?>

                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#monday" role="tab" aria-controls="monday" aria-selected="true">Monday</a>
                                        <?php echo $monday; ?>
                                        <button type="button" title="Add new Slots" class="btn btn-primary modal-window" data-idval="1" data-toggle="modal" data-target="#exampleModalCenter"><i class="fa fa-plus"></i></button>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="profile-tab" data-toggle="tab" href="#tuesday" role="tab" aria-controls="tuesday" aria-selected="false">Tuesday</a>
                                        <?php echo $tuesday; ?>
                                        <button type="button" title="Add new Slots" class="btn btn-primary modal-window" data-idval="2" data-toggle="modal" data-target="#exampleModalCenter"><i class="fa fa-plus"></i></button>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="contact-tab" data-toggle="tab" href="#wednesday" role="tab" aria-controls="wednesday" aria-selected="false">Wednesday</a><?php echo $wednesday; ?>
                                        <button type="button" title="Add new Slots" class="btn btn-primary modal-window" data-idval="3" data-toggle="modal" data-target="#exampleModalCenter"><i class="fa fa-plus"></i></button>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="contact-tab" data-toggle="tab" href="#thrusday" role="tab" aria-controls="thrusday" aria-selected="false">Thrusday</a>
                                        <?php echo $thrusday; ?>
                                        <button type="button" title="Add new Slots" class="btn btn-primary modal-window" data-idval="4" data-toggle="modal" data-target="#exampleModalCenter"><i class="fa fa-plus"></i></button>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="contact-tab" data-toggle="tab" href="#friday" role="tab" aria-controls="friday" aria-selected="false">Friday</a>
                                        <?php echo $friday; ?>
                                        <button type="button" title="Add new Slots" class="btn btn-primary modal-window" data-idval="5" data-toggle="modal" data-target="#exampleModalCenter"><i class="fa fa-plus"></i></button>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="contact-tab" data-toggle="tab" href="#saturday" role="tab" aria-controls="saturday" aria-selected="false">Saturday</a>
                                        <?php echo $saturday; ?>
                                        <button type="button" title="Add new Slots" class="btn btn-primary modal-window" data-idval="6" data-toggle="modal" data-target="#exampleModalCenter"><i class="fa fa-plus"></i></button>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="contact-tab" data-toggle="tab" href="#sunday" role="tab" aria-controls="sunday" aria-selected="false">Sunday</a>
                                        <?php echo $sunday; ?>
                                        <button type="button" title="Add new Slots" class="btn btn-primary modal-window" data-idval="7" data-toggle="modal" data-target="#exampleModalCenter"><i class="fa fa-plus"></i></button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- Modal -->
                        <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalCenterTitle">Add Slot</h5>
                                        <button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group has-feedback">
                                                    {!! trans('admin/sidebar.from') !!}
                                                    <input type="hidden" id="edit_slot" class="form-control">
                                                    <input type="hidden" id="day" class="form-control">
                                                    <input type="text" id="from_time" class="form-control">
                                                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                {!! trans('admin/sidebar.to') !!}
                                                <input type="text" id="to_time" class="form-control">
                                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary close-modal" data-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="add_update_slot">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    var slot_id = null;
    var start_time = '';
    var end_time = '';

    $(document).on('click', '.modal-window', function() {
        $('#day').val($(this).data('idval'));
    });

    $(document).on('click', '.span-tab', function() {
        let start_time = $(this).children('#start_time').text();
        let end_time = $(this).children('#end_time').text();

        $('#from_time').val(start_time);
        $('#to_time').val(end_time);

        slot_id = $(this).data('id');
        $('#day').val($(this).siblings('button').data('idval'));
        $('#edit_slot').val($(this).siblings('button').data('idval'));
    });

    var oTable;
    $(document).ready(function() {
        $('#from_time').datetimepicker({
            format: 'HH:mm'
        });
        $('#to_time').datetimepicker({
            format: 'HH:mm'
        });

        $('#add_update_slot').click(function() {
            let start_time = $('#from_time').val();
            let end_time = $('#to_time').val();

            if (!start_time || !end_time) {
                alert('Start Time and End Time fields should not be empty');
            } else {
                if (start_time < end_time) {

                    //Checking for overlapping and same slots
                    $.ajax({
                        url: "{!! URL::to('admin/slots/checkOverlappingSlots') !!}",
                        type: 'POST',
                        data: {
                            day: $('#day').val(),
                            slot_from: $('#from_time').val(),
                            slot_to: $('#to_time').val(),
                            slot_id: slot_id,
                            _token: "{!! csrf_token() !!}"
                        },
                        success: function(response) {
                            response = JSON.parse(response);

                            if (response.overlap_slots_exists) {
                                alert("Can't create slot. Time duration overlaps with another existing slot");
                            } else if (response.same_slot_exists) {
                                alert("Slot already exists");
                            } else {
                                var url = '';
                                var data = {};
                                if ($('#edit_slot').val()) {
                                    url = "{!! URL::to('admin/slots/update') !!}";
                                    data = {
                                        day: $('#day').val(),
                                        slot_from: $('#from_time').val(),
                                        slot_to: $('#to_time').val(),
                                        slot_id: slot_id,
                                        _token: "{!! csrf_token() !!}"
                                    };
                                } else {
                                    url = "{!! URL::to('admin/slots') !!}";
                                    data = {
                                        day: $('#day').val(),
                                        slot_from: $('#from_time').val(),
                                        slot_to: $('#to_time').val(),
                                        _token: "{!! csrf_token() !!}"
                                    };
                                }

                                $.ajax({
                                    url: url,
                                    type: 'POST',
                                    data: data,
                                    success: function(response) {
                                        if (response) {
                                            if ($('#edit_slot').val()) {
                                                alert("{!! trans('admin/slot.slot_update_message') !!}");
                                            } else {
                                                alert("{!! trans('admin/slot.slot_add_message') !!}");
                                            }

                                            window.location = "{!! URL::to('admin/slots') !!}";
                                            $('#edit_slot').removeAttr('value');
                                        }

                                    },
                                    error: function(xhr) {

                                    }

                                });
                            }
                        },
                        error: function() {
                            alert('Error Occurred! Check your codes again :-p');
                        }
                    });
                } else {
                    alert('End time should not be less than Start time');
                }
            }
        });

        $('.close-modal').on('click', function() {
            $('#edit_slot').removeAttr('value');
        });

        $(document).on('click', '.delete_this_slote', function(e) {
            e.stopPropagation();
            var delete_icon = $(this);
            var id = delete_icon.data('delid');
            var r = confirm("{!! trans('admin/common.delete_confirmation') !!}");
            if (!r) {
                return false;
            }

            $.ajax({
                type: "POST",
                url: "slots/" + id,
                data: {
                    _method: 'DELETE',
                    _token: "{!! csrf_token() !!}"
                },
                dataType: 'json',
                beforeSend: function() {
                    $(this).attr('disabled', true);
                    $('.alert .msg-content').html('');
                    $('.alert').hide();
                },
                success: function(resp) {
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
                error: function(e) {
                    alert('Error: ' + e);
                }
            });
        });
    });
</script>
@stop