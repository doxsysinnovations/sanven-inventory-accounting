<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stock;
use App\Models\NotificationRecipient;
use App\Notifications\ProductExpiryReminderNotification;
use Carbon\Carbon;

class SendMonthlyExpiryReminder extends Command
{

    protected $signature = 'reminders:expiry-products';
    protected $description = 'Send monthly email reminders for products expiring within 6 months';

    public function handle()
    {
        $now = Carbon::now('Asia/Manila')->startOfDay();
        $sixMonthsLater = $now->copy()->addMonths(6)->endOfDay();

        $expiringStocks = Stock::with('product')
            ->whereNotNull('expiration_date')
            ->whereBetween('expiration_date', [$now, $sixMonthsLater])
            ->get();

        if ($expiringStocks->isEmpty()) {
            $this->info('No expiring products found.');
            return 0;
        }

        $recipients = NotificationRecipient::where('is_active', true)
            ->whereNull('deleted_at')
            ->pluck('email')
            ->toArray();

        foreach ($recipients as $email) {
            \Notification::route('mail', $email)
                ->notify(new ProductExpiryReminderNotification($expiringStocks));
        }

        $this->info('Expiry reminders sent.');
        return 0;
    }
}
