<?php

namespace App\Console\Commands;

use App\Events\ChatSent;
use App\Models\ChatChannel;
use App\Models\Employee;
use App\Models\Message;
use Illuminate\Console\Command;

class ReassignInactiveChats extends Command
{
    protected $signature = 'chats:reassign-inactive';
    protected $description = 'Reassign inactive chats to new customer service';

    public function handle()
    {
        $inactiveChats = ChatChannel::where('needs_reassignment', true)
            ->orWhere(function ($query) {
                $query->where('reassignment_at', '<=', now())
                    ->whereNull('last_customer_service_response');
            })->where('status', '!=', 'closed')->where('chat_with', 'customer_service')
            ->get();

        foreach ($inactiveChats as $chat) {

            if ($chat->isEmployeeInitiator()) {
                // Employee started the chat
                $employee = $chat->initiator_id;
            } elseif ($chat->isEmployeeParticipant()) {
                // Employee joined the chat
                $employee = $chat->participant_id;
            }
            $newAgent = $this->findAvailableAgent($employee);

            if ($newAgent) {
                $chat->update([
                    'participant_id' => $newAgent->id,
                    'last_customer_service_response' => null,
                    'reassignment_at' => now()->addMinutes(10),
                    'reassignment_from' => $employee,
                    'needs_reassignment' => false
                ]);

                $message = Message::where('chat_id', $chat->id)
                    ->where('initiator_id', $chat->initiator_id)
                    ->latest()
                    ->first();
                $type = $message->media ? 'video' : 'text';
                $mess = $message->media ? $message->media : $message->message;

                // Notify both parties
                broadcast(new ChatSent($chat->participant_id, $chat->initiator_id, $mess, $chat->participant_type, $type, $chat->id));

            }
        }
    }

    protected function findAvailableAgent($currentAgentId)
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
        $customerServiceEmployees = Employee::whereIn('id', $employees['working_employee_ids'])->where('id', '!=', $currentAgentId)
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
}
