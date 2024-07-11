<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Helpers\Datatable\SSP;
use App\User;
use App\Service;
use App\ServiceCategory;
use App\Notification;
use App\Chat as usrChat;
use App\Booking;
use App\AvatarImage;
use App\Review;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ChatCollection;
use App\Http\Resources\Avatar;
use Illuminate\Support\Facades\Validator;
use Chat;
use Musonza\Chat\Models\Conversation;

class ChatController extends Controller {

    protected $response = [
        'status' => 0,
        'message' => '',
    ];

    public function __construct() {
        $this->response['data'] = new \stdClass();
    }

    const totalRow = 20;

    public function listChats() {
        $user = Auth::User();

        $user_type = '';
        $getUserFromBooking = '';
        if ($user->hasRole('vendor')) {
            $user_type = 'user';
            $getUserFromBooking = Booking::where('vender_id', '=', $user->id)->groupby('user_id')->distinct()->get();
        } else {
            $user_type = 'vender';
            $getUserFromBooking = Booking::where([['user_id', '=', $user->id], ['vender_id', '!=', NULL]])->groupby('vender_id')->distinct()->get();
        }
        $data = array();
        $i = 0;
        if (count($getUserFromBooking) > 0) {
            foreach ($getUserFromBooking as $chat) {
                if (isset($chat->user->id) && isset($chat->vender->id)) {
                    if ($user_type == 'vender') {

                        $data[$i]['userName'] = $chat->vender->firstname . ' ' . $chat->vender->lastname;
                        $data[$i]['userId'] = $chat->vender->id;
                        $data[$i]['image'] = $chat->vender->image ? url('images/avatars/' . $chat->vender->image) : '';
                    } else {
                        $data[$i]['userName'] = $chat->user->firstname . ' ' . $chat->user->lastname;
                        $data[$i]['userId'] = $chat->user->id;
                        $data[$i]['image'] = $chat->user->image ? url('images/avatars/' . $chat->user->image) : '';
                        $avtar_image = '';
                        if ($chat->user->avtaar_image) {
                            $avtar_image = AvatarImage::select('image_name')->where('id', $chat->user->avtaar_image)->first();
                        }
                        $data[$i]['avatarImage'] = isset($avtar_image->image_name) ? url('assets/avatar/' . $avtar_image->image_name) : '';
                    }
                    $cat_id = '';
                    $main_service_name = '';
                    if ($chat->service_id) {
                        $cat_id = Service::select('cat_id')->where('id', '=', $chat->service_id)->first();
                    }
                    if ($cat_id) {
                        $main_service_name = ServiceCategory::where('id', '=', $cat_id->cat_id)->first();
                    }
                    $data[$i]['lastMessage'] = '';
                    $data[$i]['lastMessageTime'] = '';
                    $data[$i]['messageType'] = '';
                    $data[$i]['serviceName'] = $main_service_name->cat_name ? $main_service_name->cat_name : $chat->service_name;
                    $get_rating = 0.0;
                    $get_ratings = array();
                    if ($user_type == 'vender') {

                        $get_ratings = Review::where('review_submitted_to', '=', $chat->vender_id)->get();
                    } else {
                        $get_ratings = Review::where('review_submitted_to', '=', $chat->user_id)->get();
                    }
                    if (!empty($get_ratings)) {
                        foreach ($get_ratings as $get_rat) {
                            $get_rating = ($get_rating + $get_rat->rating);
                        }
                    }
                    if (is_numeric($get_rating) && $get_rating > 0) {
                        $get_rating = $get_rating / $get_ratings->count();
                    }
                    $data[$i]['rating'] = (double)number_format($get_rating, 2);
                    $participants = [$data[$i]['userId'], $user->id];
                    $conversation = Chat::conversations()->common($participants);
                    $converId = '';
                    foreach($conversation as $conversationMsg){
                        if(!empty($conversationMsg)){
                            $converId = $conversationMsg;
                            break;
                        }
                    }
                    if ($converId) {
                        $messages = Chat::conversation($converId)->setUser($user)->getMessages();
                        $data[$i]['lastMessage'] = $messages[count($messages) - 1]->body;
                        $data[$i]['lastMessageTime'] = $messages[count($messages) - 1]->created_at->format('Y-m-d H:i:s');
                        $data[$i]['messageType'] = $messages[count($messages) - 1]->type;
                    }
                    $i++;
                }
            }
        }
        $this->response['status'] = 1;
        $this->response['message'] = trans('api/service.all_chats');
        $this->response['data'] = $data;
        return response()->json($this->response, 200);
        /* return (new ChatCollection($chats))->additional([
          'status' => 1,
          'message' => trans('api/user.all_chats')
          ]); */
    }

    public function get_chat(Request $request) {

        $validator = Validator::make($request->all(), [
                    'user_id' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $page = $request['page'] ? $request['page'] : 1;
        $user = Auth::User();
        $userId = $user->id;
        $userId2 = $request['user_id'];
        $user2 = User::where('id', '=', $userId2);
        $message = $request['message'];
        $participants = [$userId, $userId2];
        $conversation = Chat::conversations()->common($participants);

        $data = array();
        $messages = array();
        $converId = '';

        foreach ($conversation as $key => $con) {
            $converId = $con;
        }
        if ($converId) {

            $param['sorting'] = 'desc';
            $messages = Chat::conversation($converId)->setUser($user)->limit(self::totalRow)->page($page)->setPaginationParams($param)->getMessages();
            $data = (new ChatCollection($messages));
        }

        $message = trans('api/service.all_messages');
        if (!$data) {
            $message = trans('api/service.no_message_found');
        }
        $this->response['message'] = $message;
        $this->response['status'] = 1;
        $this->response['data'] = $data;
        return response()->json($this->response, 200);
    }

    public function sendMessage(Request $request) {

        $validator = Validator::make($request->all(), [
                    'user_id' => ['required'],
                    'message' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }

        $user = Auth::User();
        $userId = $user->id;
        $userId2 = $request['user_id'];
        $message = $request['message'];
        $participants = [$userId2, $userId];
        $conversation = Chat::conversations()->common($participants);

        $converId = '';

        foreach ($conversation as $key => $con) {
            $converId = $con;
        }

        if (!$converId) {
            $conversation = Chat::createConversation($participants)->makePrivate(false);
        } else {
            $conversation = $converId;
        }
        $message = Chat::message($message)
                ->from($user)
                ->to($conversation)
                ->send();

        Notification::createNotification(0, Notification::chatNotification, Notification::chatMessage, $request['message'], $request['user_id']);
        $conversation = Chat::conversations()->common($participants);
        $converId = '';
        foreach ($conversation as $key => $con) {
            $converId = $con;
        }
        $data = array();
        if ($converId) {
            $messages = Chat::conversation($converId)->setUser($user)->getMessages();
            $data['id'] = $messages[count($messages) - 1]->id;
            $data['user_id'] = $messages[count($messages) - 1]->user_id;
            $data['body'] = $messages[count($messages) - 1]->body;
            $data['image'] = $user->image ? url('images/avatars/' . $user->image) : '';
            $data['createdAt'] = $messages[count($messages) - 1]->created_at->format('Y-m-d H:i:s');
            ;
        }

        if ($message) {
            $this->response['message'] = trans('api/service.message_sent_successfully');
            $this->response['status'] = 1;
            $this->response['data'] = $data;
            return response()->json($this->response, 200);
        }
    }

}
