<?php

namespace App\Http\Controllers\Admin;

use App\AvatarImage;
use App\Booking;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Helpers\Datatable\SSP;
use App\User;
use Carbon\Carbon;
use Chat;
use Illuminate\Support\Facades\Auth;


class ChatController extends Controller
{
    const totalRow = 20;
    /**
     * User Model
     * @var User
     */
    protected $user;
    protected $pageLimit;

    /**
     * Inject the models.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->pageLimit = config('settings.pageLimit');
    }

    /**
     * Display a listing of user
     *
     * @return Response
     */
    public function index($id = null)
    {

        $user = $chatMessages = null;
        if ($id) {
            $user = User::findOrFail($id);
            Chat::where('user_id', $id)->where('message_type', 'in-msg')->update(['message_read' => '1']);
            $chatMessages = Chat::where('user_id', $id)->orderBy('created_at', 'ASC')->get();
        }

        // Grab all online user
        $users = User::active()->online()->get();

        // Show the page
        return view('admin/onlineList', compact('users', 'user', 'chatMessages'));
    }

    /**
     * Store a chat in database
     *
     * @return Response
     */
    public function store(Request $request)
    {

        $data = $request->all();

        $chat = new Chat;
        $user = Auth::User();
        $chat->user_id = $user->id;
        $chat->user_id2 = $data['id'];
        $chat->message_content = $data['message'];
        $chat->message_type = 'out-msg';
        $chat->save();
        return 'true';
    }

    /**
     * Display a chat history of user
     *
     * @return Response
     */
    public function history($id = null)
    {

        $user = $chatMessages = null;
        if ($id) {
            $user = User::findOrFail($id);
            Chat::where('user_id', $id)->where('message_type', 'in-msg')->update(['message_read' => '1']);
            $chatMessages = Chat::where('user_id', $id)->orderBy('created_at', 'ASC')->get();
        } else {
            return response()->view('errors.404', array(), 404);
        }

        // Show the page
        return view('admin/chatHistory', compact('user', 'chatMessages'));
    }

    /**
     * give message count of all online user which was not read by admin for notification
     *
     * @return int $msgCount
     */
    public function getNotificationCount()
    {
        $msgCount = Chat::where('message_read', '0')->where('message_type', 'in-msg')->count();
        return $msgCount;
    }

    /**
     * this function is used to get the booking chat between user and vendor
     *
     * @param Request $request
     * @return void
     */
    public function getBookingChat(Request $request)
    {
        $getBooking = Booking::where('id', '=', $request->booking_id)->first();
        $participants = [$getBooking->user_id, $getBooking->vender_id];
        $user1 = User::where('id', $getBooking->user_id)->first();
        $conversations = Chat::conversations()->common($participants);
        $chat_data['data'] = array();
        $chat_data['link'] = '';
        if ($conversations->count() > 0) {
            $conversationsId = '';
            foreach ($conversations as $single_conversation) {
                $conversationsId = $single_conversation;
            }
            $page = $request['page'] ? $request['page'] : 1;
            $param['sorting'] = 'desc';
            $messages = Chat::conversation($conversationsId)->setUser($user1)->limit(self::totalRow)->page($page)->setPaginationParams($param)->getMessages();
            if ($messages->count() > 0) {
                $chat_data['data'] = $this->get_chat_data($messages);
                $chat_data['link'] = $messages->render();
            }
            return view('admin.chatList', compact('chat_data'));
        }
        return view('admin.chatList', compact('chat_data'));
    }

    /**
     * Undocumented function
     *
     * @param Object $messages
     * @return array chat_data
     */
    private function get_chat_data($messages)
    {
        $chat_data = array();
        $loop_index = 0;
        foreach ($messages as  $single_message) {
            $user = $single_message->sender;
            $first_name =  $user->firstname ?? "";
            $last_name =  $user->lastname ?? "";

            if ($user->hasRole('vendor')) {
                $chat_data[$loop_index]['role'] = 'vendor';
                $chat_data[$loop_index]['link'] = "vendors/$user->id/edit";
                $chat_data[$loop_index]['picture'] = ($user->image) ? url('images/avatars/' . $user->image) : url('assets/avatar/pic.png');
            } else {
                $selected_image = AvatarImage::find($user->avtaar_image);
                $chat_data[$loop_index]['link'] = "users/$user->id/edit";
                $chat_data[$loop_index]['picture'] = ($selected_image) ?  url('assets/avatar/' . $selected_image->image_name) : url('assets/avatar/pic.png');
                $chat_data[$loop_index]['role'] = 'user';
            }
            $chat_data[$loop_index]['message'] = $single_message->body;
            $chat_data[$loop_index]['send_at'] =  Carbon::parse($single_message->created_at)->diffForHumans(Carbon::now());
            $chat_data[$loop_index]['name'] = $first_name . ' ' . $last_name;

            $loop_index++;
        }
        return $chat_data;
    }
}
