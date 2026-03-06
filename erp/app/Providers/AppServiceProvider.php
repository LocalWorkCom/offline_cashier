<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\BranchTime;
use App\Models\ChatChannel;
use App\Models\Employee;
use App\Models\Message;
use App\Models\User;
use App\Traits\ChatTrait;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Symfony\Component\Uid\NilUlid;

class AppServiceProvider extends ServiceProvider
{
    use ChatTrait;
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Existing security check
        view()->composer('*', function () {
            if (Auth::check()) {
                Auth::user()->refresh();
                if (Auth::guard('web')->check() && Auth::user()->flag !== 'admin') {
                    Auth::guard('web')->logout();
                    return redirect()->route('login')->with('error', 'Unauthorized access.');
                }
            }
        });

        // Set locale
        $locale = Session::get('locale', config('app.locale'));
        App::setLocale($locale);

        // Share branches data
        if (Schema::hasTable('branches')) {
            $branches = Branch::where('is_active', 1)->get();
            View::share('branches', $branches);

            // Get current branch ID
            $branchId = request()->cookie('branch_id') ?? ($_COOKIE['branch_id'] ?? null);

            if (!$branchId) {
                $branchId = getDefaultBranch();
            }

            View::share('branchId', $branchId);

            $branch = Branch::with('country')->find($branchId);
            $currency = $branch->country->currency_symbol ?? 'ج.م';
            View::share('currency', $currency);
        } else {
            View::share([
                'branches' => collect([]),
                'branchId' => null,
                'currency' => 'ج.م'
            ]);
        }

        // Share chat data for authenticated clients
        View::composer('*', function ($view) {
            if (Auth::guard('client')->check()) {
                $guard_type = 'client';
                $sender = User::find(Auth::guard('client')->user()->id);
                // Get receiver
                $receiver = $this->getCustommerServiceId();
                if ($receiver instanceof \Illuminate\Http\JsonResponse) {
                    $receiver = null;
                }

                $channel = ChatChannel::where(function ($query) use ($sender) {
                    $query->where('initiator_id', $sender->id)
                        ->orWhere('participant_id', $sender->id);
                })
                    ->where('chat_with', 'customer_service')
                    ->whereIn('status', ['open', 'pending'])
                    ->whereDate('created_at', today())
                    ->first();

                if ($channel) {
                    $receiver = Employee::find($channel->participant_id);
                    $messages = Message::where('chat_id', $channel->id)
                        ->orderBy('created_at', 'asc')
                        ->get();
                    $unreadMessages = $messages->where('seen', 0)
                        ->where('sender', '!=', $sender->id)
                        ->count();
                } else {
                    if ($receiver == null) {
                        $receiver = Employee::where('flag', 'customer_service')->first();
                        $channel = ChatChannel::create([
                            'initiator_id' => $sender->id,
                            'initiator_type' => 'client',
                            'participant_id' => $receiver->id,
                            'participant_type' => 'customer_service',
                            'chat_with' => 'customer_service',
                            'flag' => 'customer_service',
                            'status' => 'pending'
                        ]);
                    } else {
                        $channel = $this->createChannel($sender, 'client', $receiver, 'customer_service', 'customer_service');
                    }
                    // Create channel even if $receiver is null
                    $messages = collect();
                    $unreadMessages = 0;
                }

                $view->with([
                    'chat_guard_type' => $guard_type,
                    'chat_receiver' => $receiver,
                    'chat_sender' => $sender,
                    'chat_messages' => $messages,
                    'chat_channel' => $channel,
                    'unreadMessages'    => $unreadMessages,
                ]);
            }
        });

        // Existing footer composer
        View::composer('website.layouts.footer', function ($view) {
            $request = request();
            $userLat = request()->cookie('latitude') ?? ($_COOKIE['latitude'] ?? null);
            $userLon = request()->cookie('longitude') ?? ($_COOKIE['longitude'] ?? null);
            $branchId = request()->cookie('branch_id') ?? ($_COOKIE['branch_id'] ?? null);

            if (!$branchId) {
                $branchId = getDefaultBranch();
            }

            if (Schema::hasTable('branches')) {
                // $branchTimes = BranchTime::where('branch_id', $branchId)
                //     ->where('is_active', 1)
                //     ->orderBy('day')
                //     ->get();

                $branchPhone = Branch::find($branchId)?->phone;
                $branchWorkingHours = getBranchWorkingHours($branchId);
                $branch = Branch::find($branchId);
                $view->with([
                    'branch' => $branch,

                    'branchPhone' => $branchPhone,
                    'branchWorkingHours' => $branchWorkingHours,
                ]);
            } else {
                $view->with([
                    'branchWorkingHours' => [],
                    'branchPhone' => null,
                ]);
            }
        });
        ini_set('precision', 10);
        ini_set('serialize_precision', -1);
    }


    /**
     * Create new chat channel
     */
}
