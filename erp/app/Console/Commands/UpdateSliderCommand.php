<?php

namespace App\Console\Commands;

use App\Jobs\UpdateOfferStatuses;
use App\Jobs\UpdateSlider;
use Illuminate\Console\Command;

class UpdateSliderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slider:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the slider';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        UpdateSlider::dispatch();
        $this->info('Slider updated successfully.');
    }
}
