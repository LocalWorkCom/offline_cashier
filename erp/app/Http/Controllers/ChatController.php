<?php

namespace App\Http\Controllers;

use App\Events\ChatSent;
use App\Models\Branch;
use App\Models\ChatChannel;
use App\Models\Employee;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\User;
use App\Traits\ChatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;
use function Laravel\Prompts\error;

class ChatController extends Controller
{
    use ChatTrait;
    public function markAsRead(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $guards = ['client', 'employee', 'api'];
        $auth = null;

        foreach ($guards as $guardName) {
            if (Auth::guard($guardName)->check()) {
                $auth = Auth::guard($guardName)->user();
                break;
            }
        }

        if (!$auth) {
            return RespondWithBadRequest($lang, 4);
        }
        $channelId = $request->input('channel_id');
        $messages = Message::where('chat_id', $channelId)
            ->orderBy('created_at', 'asc')
            ->get();
        foreach ($messages as $message) {
            $message->seen = '1';
            $message->save();
        }
        return response()->json([
            'code' => 200,

            'status' => 'success',
            'message' => 'Messages retrieved successfully',
            'data' => null
        ], 200);
    }
    public function closeChat($channelId, Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $guards = ['client', 'employee', 'api'];
        $auth = null;

        // Loop through guards to find the authenticated one
        foreach ($guards as $guardName) {
            if (Auth::guard($guardName)->check()) {
                $auth = Auth::guard($guardName)->user();
                break;
            }
        }

        if (!$auth) {
            return RespondWithBadRequest($lang, 4);
        }
        $messages = ChatChannel::where('id', $channelId)
            ->first();
        $messages->status = 'closed';
        $messages->save();
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Chat has been closed successfully',
            'data' => null
        ], 200);
    }
    //this function for fetch the messages between the sender and receiver
    public function index()
    {

        $guard_type = 'client';
        $receiver = $this->getCustommerServiceId();
        $sender = User::find(auth('client')->user()->id);

        // Retrieve existing chat channel if available
        $channel = ChatChannel::where('initiator_id', $sender->id)
            ->where('chat_with', 'customer_service')
            ->whereIn('status', ['open', 'pending'])
            ->whereDate('created_at', today())
            ->first(); // Get single chat instead of a collection
        $user = $channel->initiator_id == $sender->id ? $channel->participant_id : $channel->initiator_id;

        if ($channel) {
            $receiver = Employee::find($user);

            $messages = Message::where('chat_id', $channel->id)
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            // Create a new chat channel if none exists
            $channel = $this->createChannel($sender, 'client', $receiver, 'employee', 'customer_service');
            $messages = collect(); // Return an empty collection instead of null
        }

        return view('website.chat', compact('receiver', 'sender', 'messages', 'guard_type', 'channel'));
    }


    //this function for store the message from client or employee
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = null;
        $auth = null;
        $guards = ['client', 'employee', 'api'];
        $guard = null;

        foreach ($guards as $guard1) {
            if (Auth::guard($guard1)->check()) {
                $guard = $guard1;
                $auth = Auth::guard($guard)->user();

                break;
            }
        }
        if (!$auth) {
            return RespondWithBadRequest($lang, 4);
        }
        $data['sender'] = auth($guard)->user()->id; // Current authenticated user
        $data['guard_type'] = $request->guard_type; // Guard type (e.g., 'client' or 'employee')
        $data['message'] = $request->message;
        $data['media'] = null;
        $receiver = null;
        $channel = null;
        $order = null;
        $branchId = null;

        $sender = auth($guard)->user();
        $flag = auth($guard)->user()->flag;
        $data['receiver'] = $request->receiver;
        $guard = $guard === 'api' ? 'client' : $guard;

        // CASE 1: Existing channel
        if ($request->channel_id) {
            $channel = ChatChannel::find($request->channel_id);

            if (!$channel) {
                return response()->json([
                    "code" => 404,

                    'status' => 'failed',
                    'message' => 'error.',
                    'data' => null,
                    'error' => ['error' => 'there is no channel open with this id']
                ], 403);
            }

            if ($channel->initiator_id != $data['sender'] && $channel->participant_id != $data['sender']) {
                return response()->json([
                    "code" => 404,

                    'status' => 'failed',
                    'message' => 'error.',
                    'data' => null,
                    'error' => ['error' => 'you cannot access this channel']
                ], 403);
            }

            // Set receiver as the other participant in the channel
            $data['receiver'] = ($channel->initiator_id == $data['sender'])
                ? $channel->participant_id
                : $channel->initiator_id;

            // Determine guard_type based on the receiver
            $employeeRoles = ['driver', 'customer_service'];

            if (
                in_array($channel->initiator_type, $employeeRoles) ||
                in_array($channel->participant_type, $employeeRoles)
            ) {
                $data['guard_type'] = 'employee';
            } else {
                $data['guard_type'] = 'client';
            }


            $channel->status = 'open';
            $channel->last_message_at = now();
            $channel->save();
        }

        // CASE 2: No channel ID — need to create a new channel
        else {
            if ($guard === 'employee') {
                if ($data['guard_type'] === 'client') {
                    $receiver = User::find($data['receiver']);
                    $flag = $flag === 'customer_service' ? 'customer_service' : 'driver';
                    if ($flag === 'driver') {
                        $order = OrderTracking::with('Order')
                            ->whereHas('Order', function ($query) use ($guard, $receiver) {
                                $query->where('delivery_id', auth($guard)->user()->id)
                                    ->where('client_id', $receiver->id);
                            })
                            ->where('order_status', 'on_way')
                            ->whereDate('created_at', today())
                            ->first();

                        if (!$order) {
                            return response()->json([
                                "code" => 404,

                                'status' => 'failed',
                                'message' => 'You can only chat with this driver if you have an assigned order.',
                                'data' => null,
                                'error' => ['error' => 'No assigned order found']
                            ], 403);
                        }
                    }
                } elseif ($data['guard_type'] === 'employee') {
                    $receiver = Employee::find($data['receiver']);
                    $flag = $flag === 'customer_service' ? 'driver' : 'customer_service';
                }
            } elseif ($guard === 'client') {
                if ($data['guard_type'] === 'customer_service') {
                    $receiver = $this->getCustommerServiceId()->where('flag', 'customer_service')->first();
                    $flag = 'customer_service';
                } elseif ($data['guard_type'] === 'driver') {
                    $receiver = Employee::find($data['receiver']);
                    $flag = 'driver';
                }
            }

            // Check if there's already an open channel today between the sender and this receiver
            $existingChannel = ChatChannel::where(function ($query) use ($sender, $receiver) {
                $query->where('initiator_id', $sender->id)
                    ->orWhere('participant_id', $sender->id);
                $query->where('initiator_id', $receiver->id)
                    ->orWhere('participant_id', $receiver->id);
            })
                ->where('chat_with', $flag)
                ->whereIn('status', ['open', 'pending'])
                ->whereDate('created_at', today())
                ->first();

            if ($existingChannel) {
                $channel = $existingChannel; // Reuse the channel
                $channel->status = 'open';
            } else {
                $data['receiver'] = $receiver->id;
                $channel = $this->createChannel($sender, $guard, $receiver, $data['guard_type'], $flag);
            }
            $channel->last_message_at = now();
            $channel->save();
        }

        $employee = null;
        if ($channel->isEmployeeInitiator()) {
            // Employee started the chat
            $employee = $channel->initiator_id;
        } elseif ($channel->isEmployeeParticipant()) {
            // Employee joined the chat
            $employee = $channel->participant_id;
        }

        // Get the receiver model based on guard_type
        if ($data['guard_type'] === 'client' || $guard === 'employee') {
            $receiver = User::find($data['receiver']);
            $receiver->image = 'https://erpsystem.testdomain100.online/images/user.svg';
            $receiver = $this->formatUserData($receiver, 'client');
        } else {
            $receiver = Employee::find($data['receiver']);
            $receiver = $this->formatUserData($receiver, 'employee');
        }
        // ✅ Prevent sending if channel is closed
        if ($channel->status === 'closed') {
            return response()->json([
                "code" => 404,

                'status' => 'failed',
                'message' => 'This chat is closed. You cannot send or receive messages.',
                'data' => null,
                'error' => ['error' => 'Chat is closed']
            ], 404);
        }
        // Save the message
        $message = new Message();
        $message->sender = $data['sender'];
        $message->receiver = $data['receiver'];
        $message->guard_type = $data['guard_type'];
        $message->chat_id = $channel->id;
        $message->message = $data['message'];
        $message->media = $request->media ?? null;
        $message->save();

        $type = 'text';
        $data['channel_id'] = $channel->id;

        // Handle file upload
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $mimeType = $file->getMimeType();
            if (str_starts_with($mimeType, 'image')) {
                $type = 'image';
                UploadFile('images/messages', 'media', $message, $file);
            } elseif (Str::startsWith($mimeType, ['video', 'application'])) {
                $type = 'video';
                UploadFile('videos/messages', 'media', $message, $file);
            } else {
                return response()->json(['error' => 'Invalid file type.'], 400);
            }
        }

        $mess = $request->media ? $message->media : $message->message;
        $sender = $this->formatUserData($sender, $data['guard_type']);

        // Broadcast the event
        broadcast(new ChatSent($receiver, $sender, $message->id, $mess, $data['guard_type'], $type, $channel->id));

        // For web requests, return a redirect or a view
        if ($request->type_view === 'web') {
            return redirect()->back()->with('success', 'Message sent successfully');
        }
        send_push_notification(
            $receiver['device_token'],
            "رساله جديدة  ",
            "New message  ",
            "رساله جديدة  ",
            "New message  ",
            $flag,
            $receiver['id'],
            $sender['id'],
            $channel->id, // or request ID
            $lang,
            'open_chat'
        );
        addNotification(
            'chat',
            $flag,
            "رساله جديدة  ",
            "New message  ",
            "رساله جديدة  ",
            "New message  ",
            $receiver['id'],
            $sender['id'],
            $lang,
            $channel->id,
        );
        // Check if the request is an API request
        return response()->json([
            "code" => 200,
            'status' => 'success',
            'message' => 'Message sent successfully',
            'data' => [
                'channel_id' => $channel->id,
                'sender_id' => $sender['id'] ?? null,
                'sender_name' => $sender['name'] ?? null,
                'sender_image' => $sender['image'] ?? 'https://erpsystem.testdomain100.online/images/user.svg',

                'receiver_id' => $receiver['id'] ?? null,
                'receiver_name' => $receiver['name'] ?? null,
                'receiver_image' => $receiver['image'] ?? 'https://erpsystem.testdomain100.online/images/user.svg',
                'message_id' => $message->id,

                'type' => $message->message === '' || $message->message === null ? 'image' : 'text',
                'content' => $message->message === '' || $message->message === null ? $message->media : $message->message,
                'is_read' => (bool) $message->seen,
                'sent_at' => now(),
            ]
        ], 200);
    }

    protected function formatUserData($user, $guardType)
    {
        return [
            'id' => $user->id,
            'name' => $guardType === 'client'
                ? $user->name
                : ($user->first_name . ' ' . $user->last_name),
            'image' => $user->image ?? asset('images/user.svg'),
            'flag' => $user->flag ?? $guardType,
            'device_token' => $guardType === 'client' ? $user->fcm_token : $user->device_token,
        ];
    }

    public function getChannel($orderId)
    {
        return response()->json([
            'channel' => $this->checkChatChannel($orderId)
        ]);
    }
    public function getChatMessages($channelId, Request $request, array $guards = ['client', 'employee', 'api'])
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        // Get authenticated user
        $auth = $this->resolveAuthenticatedUser($guards);
        if (!$auth) {
            return RespondWithBadRequest($lang, 4);
        }

        // Retrieve chat channel
        $chat = ChatChannel::find($channelId);
        if (!$chat) {
            return response()->json([
                "code" => 404,
                'status' => 'error',
                'message' => 'Chat not found',
            ], 404);
        }

        // Authorization check
        if (!in_array($auth->id, [$chat->initiator_id, $chat->participant_id])) {
            return response()->json([
                "code" => 403,
                'status' => 'error',
                'message' => 'You are not authorized to view this chat',
            ], 403);
        }

        // Fetch and format messages
        $messages = Message::where('chat_id', $channelId)
            ->orderBy('created_at', 'asc')
            ->get();

        $formattedMessages = $this->formatChatMessages($messages, $chat);

        return response()->json([
            "code" => 200,
            'status' => 'success',
            'message' => 'Messages retrieved successfully',
            'data' => [
                'channel_id' => $channelId,
                'messages' => $formattedMessages,
            ]
        ], 200);
    }
}
