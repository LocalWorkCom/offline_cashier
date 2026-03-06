<?php

namespace App\Console\Commands;

use App\Models\ChatChannel;
use Illuminate\Console\Command;

class CloseInactiveChats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chats:close-inactive';
    protected $description = 'Close chat channels inactive for 20 minutes';

    public function handle()
    {
        $inactiveChannels = ChatChannel::where('status', 'open')
            ->where('auto_close_at', '<=', now())
            ->get();


        foreach ($inactiveChannels as $channel) {
            $channel->status = 'closed';
            $channel->save();
        }

        return 0;
    }
}
