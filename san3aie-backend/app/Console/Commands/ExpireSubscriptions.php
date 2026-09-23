<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\WorkerFeature;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Expire subscriptions and featured workers';

    public function handle(): int
    {
        Subscription::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => 'expired',
            ]);

        WorkerFeature::where('expires_at', '<=', now())
            ->delete();

        $this->info('Subscriptions and features updated successfully.');

        return self::SUCCESS;
    }
}
