<?php

namespace App\Http\Controllers\Api\CustomerServiceAPIs;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ChatChannel;
use App\Models\Country;
use App\Models\Employee;
use App\Models\Nationality;
use App\Models\User;
use App\Services\HR_Services\TimetableService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CustomerServiceChatController extends Controller
{

    public function getActiveChats(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithUnauthorizedRequest($lang, 4);
        }

        $channels = ChatChannel::whereIn('status', ['pending', 'open'])
            ->where(function($query) use ($employee) {
                $query->where(function($q) use ($employee) {
                    // Employee is the initiator
                    $q->where('initiator_id', $employee->id)
                      ->where('initiator_type',  'customer_service'); // Fixed
                })->orWhere(function($q) use ($employee) {
                    // Employee is the participant
                    $q->where('participant_id', $employee->id)
                      ->where('participant_type', 'customer_service'); // Fixed
                });
            })
            ->when($request->has('chat_with'), function($query) use ($request) {
                $query->where('chat_with', $request->chat_with);
            })
            ->with(['messages' => function($query) {
                $query->orderBy('created_at', 'desc'); // Fixed
            }])
            ->get()
            ->map(function($channel) use ($employee) {
                // Identify the other participant
                if ($channel->initiator_id == $employee->id && $channel->initiator_type ==  'customer_service') {
                    if ($channel->participant_type == 'client') {
                        $otherParticipant = User::find($channel->participant_id);
                    } else {
                        $otherParticipant = $channel->participant_type::find($channel->participant_id);
                    }
                    // $otherParticipant = $channel->participant_type::find($channel->participant_id);
                } else {
                    if ($channel->initiator_type == 'client') {
                        $otherParticipant = User::find($channel->initiator_type);
                    } else {
                        $otherParticipant = $channel->initiator_type::find($channel->initiator_type);
                    }
                    // $otherParticipant = $channel->initiator_type::find($channel->initiator_id);
                }

                return [
                    'channel_id' => $channel->id,
                    'status' => $channel->status,
                    'chat_with' => $channel->chat_with,
                    'last_message_at' => $channel->last_message_at,
                    'messages' => $channel->messages->map(function($message) {
                        return [
                            'id' => $message->id,
                            'content' => $message->message,
                            'media' => $message->media,
                            'sender_id' => $message->sender,
                            'receiver_id' => $message->receiver,
                            'receiver_type' => $message->guard_type,
                            'created_at' => $message->created_at,
                            'is_read' => $message->seen,
                            'is_employee' => $message->sender_type === 'driver' ? false : true,
                        ];
                    }),
                ];
            });

        return ResponseWithSuccessData($lang, $channels, 20);
    }


}
