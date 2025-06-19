<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stock;
use App\Models\NotificationRecipient;
use App\Notifications\Product7DayExpiryNotification;
use App\Notifications\ProductExpiredTodayNotification;
use Carbon\Carbon;

class SendDailyExpiryAlerts extends Command
{

    protected $signature = 'reminders:daily-expiry-alerts';
    protected $description = 'Send daily expiry and expired product alerts';

    public function handle()
    {
        $now = Carbon::now('Asia/Manila')->startOfDay();

        // 1. Products expiring in the next 7 days (including today)
        $tomorrow = $now->copy()->addDay()->startOfDay();
        $sevenDaysFromNow = $now->copy()->addDays(7)->endOfDay();
        $sevenDayStocks = Stock::with('product')
            ->whereNotNull('expiration_date')
            ->whereBetween('expiration_date', [$tomorrow, $sevenDaysFromNow])
            ->get()
            ->filter(function ($stock) use ($now) {
                $expiry = Carbon::parse($stock->expiration_date);
                $days = $now->diffInDays($expiry, false);
                return $days > 0 && $days <= 7;
            });

        // 2. Products expired today
        $expiredTodayStocks = Stock::with('product')
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', $now)
            ->get();

        // 3. Products already expired (optional: send separate alert or include in expired today)
        // $alreadyExpiredStocks = Stock::with('product')
        //     ->whereNotNull('expiration_date')
        //     ->whereDate('expiration_date', '<', $now)
        //     ->get();

        $recipients = NotificationRecipient::where('is_active', true)
            ->whereNull('deleted_at')
            ->pluck('email')
            ->toArray();

        // Send 7-day window alert
        if ($sevenDayStocks->isNotEmpty()) {
            foreach ($recipients as $email) {
                \Notification::route('mail', $email)
                    ->notify(new Product7DayExpiryNotification($sevenDayStocks));
            }
            $this->info('7-day expiry alerts sent.');
        }

        // Send expired today alert
        if ($expiredTodayStocks->isNotEmpty()) {
            foreach ($recipients as $email) {
                \Notification::route('mail', $email)
                    ->notify(new ProductExpiredTodayNotification($expiredTodayStocks));
            }
            $this->info('Expired today alerts sent.');
        }

        $this->info('Daily expiry check complete.');
        return 0;
    }
}
