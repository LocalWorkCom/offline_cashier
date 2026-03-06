<?php

namespace App\Traits;

use App\Models\ChatChannel;
use App\Models\City;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

trait ChatTrait
{
    protected $lang;
    function checkChatChannel($order)
    {
        $contact = [];
        $user = null;
        $userType = null;

        if (auth('api')->check()) {
            $user = auth('api')->user();
            $userType = 'client';
        } elseif (auth('client')->check()) {
            $user = auth('client')->user();
            $userType = 'client';
        } elseif (auth('employee')->check()) {
            $user = auth('employee')->user();
            $userType = 'employee';
        } elseif (auth('admin')->check()) {
            $user = auth('admin')->user();
            $userType = 'employee';
        }
        if (is_object($order) && isset($order->id)) {
            $order = Order::where('id', $order->id)->first();
        } else {
            $order = Order::where('id', $order)->first();
        }
        if (!$order) {
            return $contact;
        }
        if ($order->type = 'Delivery' && $order->tracking->last()->order_status == 'on_way') {
            $clientId = $order->client_id;
            $deliveryId = $order->delivery_id ?? null;

            $has_channel = ChatChannel::where(function ($query) use ($clientId, $deliveryId) {
                $query->where(function ($q) use ($clientId, $deliveryId) {
                    $q->where('initiator_id', $clientId)
                        ->where('participant_id', $deliveryId);
                })->orWhere(function ($q) use ($clientId, $deliveryId) {
                    $q->where('initiator_id', $deliveryId)
                        ->where('participant_id', $clientId);
                });
            })->whereIn('status', ['open', 'pending'])->first();


            if (!$has_channel) {
                $delivery = Employee::where('id', $deliveryId)->first();
                $user_sender =  $user->flag == 'admin' ? User::find($clientId) : $user;
                $user_sender_type =  $user->flag == 'admin' ? 'client' : $userType;

                $has_channel = $this->createChannel($user_sender,  $user_sender_type, $delivery, 'driver', 'driver');
            }
            $contact = [
                'delivery_id' => $order->delivery->id ?? null,
                'delivery_name' => $order->delivery->first_name . ' ' . $order->delivery->last_name ?? null,
                'delivery_phone' => $order->delivery->phone_number ?? null,
                'delivery_image' =>  $order->delivery->image ?? '/front/AlKout-Resturant/SiteAssets/images/delivery-man.png',
                'chat_channel_id' => $has_channel->id ?? null,
            ];
        }

        return $contact;
    }

    function checkChannel($order)
    {
        $contact = [];
        $user = null;
        $userType = null;
        $has_channel = null;
        if (auth('api')->check()) {
            $user = auth('api')->user();
            $userType = 'client';
        } elseif (auth('client')->check()) {
            $user = auth('client')->user();
            $userType = 'client';
        } elseif (auth('employee')->check()) {
            $user = auth('employee')->user();
            $userType = 'employee';
        } elseif (auth('admin')->check()) {
            $user = auth('admin')->user();
            $userType = 'employee';
        }
        if (is_object($order) && isset($order->id)) {
            $order = Order::where('id', $order->id)->first();
        } else {
            $order = Order::where('id', $order)->first();
        }
        if (!$order) {
            return $contact;
        }
        if ($order->type = 'Delivery') {
            $clientId = $order->client_id;
            $deliveryId = $order->delivery_id ?? null;

            $has_channel = ChatChannel::where(function ($query) use ($clientId, $deliveryId) {
                $query->where('initiator_id', $clientId)
                    ->where('participant_id', $deliveryId);
            })->orWhere(function ($query) use ($clientId, $deliveryId) {
                $query->where('initiator_id', $deliveryId)
                    ->where('participant_id', $clientId);
            })->whereIn('status', ['open', 'pending'])->first();
        }

        return $has_channel;
    }
    function createChannel($sender, $senderType, $receiver, $receiverType, $chatWith)
    {
        // Find or create chat channel
        $channel = ChatChannel::Create(
            [
                'initiator_id' => $sender->id,
                'initiator_type' => $senderType,
                'participant_id' => $receiver->id,
                'participant_type' => $receiverType,
                'chat_with' => $chatWith,
            ],
            [
                'status' => 'pending',
                'started_at' => now(),
            ]
        );
        return $channel;
    }
    function getCustommerServiceId()
    {
        $employees = getWorkingEmployeesByDate(now());
        if (!$employees['status']) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No employee is working today',
                'error' => 'No employee is working today'
            ], 403);
        }

        // Get customer service employees
        $customerServiceEmployees = Employee::whereIn('id', $employees['working_employee_ids'])
            ->where('flag', 'customer_service')
            ->get();

        if ($customerServiceEmployees->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No customer service is working today',
                'error' => 'No customer service is working today'
            ], 403);
        }

        // Get chat counts for today
        $employeeChatCounts = ChatChannel::where(function ($query) use ($customerServiceEmployees) {
            $query->whereIn('initiator_id', $customerServiceEmployees->pluck('id'))
                ->orWhereIn('participant_id', $customerServiceEmployees->pluck('id'));
        })
            ->whereDate('created_at', today())
            ->selectRaw('CASE
                WHEN initiator_id IN (?) THEN initiator_id
                ELSE participant_id
                END as employee_id', [$customerServiceEmployees->pluck('id')])
            ->selectRaw('count(*) as chat_count')
            ->groupBy('employee_id')
            ->pluck('chat_count', 'employee_id');

        // Find employee with least chats
        $selectedEmployee = null;
        $minChatCount = PHP_INT_MAX;

        foreach ($customerServiceEmployees as $employee) {
            $chatCount = $employeeChatCounts[$employee->id] ?? 0;

            // Immediately return if we find someone with no chats
            if ($chatCount === 0) {
                return $employee;
            }

            // Track employee with least chats
            if ($chatCount < $minChatCount) {
                $minChatCount = $chatCount;
                $selectedEmployee = $employee;
            }
        }

        // Return employee with least chats (guaranteed to have at least one)
        return $selectedEmployee;
    }
    function resolveAuthenticatedUser(array $guards)
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }
        return null;
    }
    function getUserByTypeAndId($type, $id)
    {
        switch ($type) {
            case 'client':
                return User::find($id);
            case 'employee':
            case 'driver':
            case 'customer_service':
                return Employee::find($id);
            default:
                return null;
        }
    }
    function formatChatMessages($messages, $chat)
    {
        return $messages->map(function ($message) use ($chat) {
            $senderType = $message->sender == $chat->initiator_id ? $chat->initiator_type : ($message->sender == $chat->participant_id ? $chat->participant_type : null);

            $receiverType = $message->receiver == $chat->initiator_id ? $chat->initiator_type : ($message->receiver == $chat->participant_id ? $chat->participant_type : null);

            $sender = $this->getUserByTypeAndId($senderType, $message->sender);
            $receiver = $this->getUserByTypeAndId($receiverType, $message->receiver);

            return [
                'sender_id' => $sender->id ?? null,
                'sender_name' => $sender ? (
                    $senderType === 'client' ? $sender->name : ($sender->first_name . ' ' . $sender->last_name)
                ) : null,
                'sender_image' => $sender->image ?? 'https://erpsystem.testdomain100.online/images/user.svg',
                'receiver_id' => $receiver->id ?? null,
                'receiver_name' => $receiver ? (
                    $receiverType === 'client' ? $receiver->name : ($receiver->first_name . ' ' . $receiver->last_name)
                ) : null,
                'receiver_image' => $receiver->image ?? 'https://erpsystem.testdomain100.online/images/user.svg',
                'message_id' => $message->id,
                'type' => empty($message->message) ? 'image' : 'text',
                'content' => empty($message->message) ? $message->media : $message->message,
                'is_read' => (bool) $message->seen,
                'sent_at' => $message->created_at->toDateTimeString(),
            ];
        });
    }
}
