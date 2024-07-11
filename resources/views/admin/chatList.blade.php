@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent :: {!! trans('admin/booking.booking_chat') !!}
@stop
@section('styles')
<link href="{!!asset('assets/css/chat.css')!!}" rel="stylesheet" type="text/css" />
@endsection


{{-- Content --}}
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{!! trans('admin/booking.booking_chat_history') !!}</h1>
        <br>
    </section>
    <!-- Main content -->
    <section class="content chat-box">
        <!-- Main row -->
        {{-- if messages are there --}}
        <div class="col-xs-12">
            <div class="box">
                <div class="box-body table-responsive ">
                    <div class="row no-gutters mrgn_10b">
                        <div class="col-md-8">
                            @if (count($chat_data['data']))
                            <div class="comments-container ">
                                <ul id="comments-list" class="comments-list">
                                    @foreach ($chat_data['data'] as $single_record)
                                    @php
                                    $vendor_class='user';
                                    $area_section='user-area';
                                    if ($single_record['role']=='vendor')
                                    {
                                    $vendor_class ='by-vendor';
                                    $area_section='vendor-area';
                                    }
                                    @endphp
                                    <li>
                                        <div class="comment-main-level <?= $area_section?>">
                                            <!-- Avatar -->
                                            <!-- Contenedor del Comentario -->
                                            <div class="comment-avatar"><img src="{{$single_record['picture']}}" alt="">
                                            </div>
                                            <div class="comment-box">
                                                <div class="comment-head">
                                                    <h6 class="comment-name <?=$vendor_class ?>"><a
                                                            href="{{$single_record['link']}}">{{$single_record['name']}}</a>
                                                    </h6>
                                                    <span>{{$single_record['send_at']}}</span>
                                                </div>
                                                <div class="comment-content">
                                                    {{$single_record['message']}}
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @else
                            <h4>{{ trans('admin/booking.no_chat_found') }}</h4>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end of chat module -->
    </section><!-- /.content -->
    <div class="col-md-12 text-center pagination pagination-sm no-margin">
        @if($chat_data['link'])
        {!! $chat_data['link'] !!}
        @endif
    </div>
</div><!-- /.content-wrapper -->
@stop
{{-- Scripts --}}
@section('scripts')
@stop