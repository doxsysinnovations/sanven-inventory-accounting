<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class ProductExpiryReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $expiringStocks;

    public function __construct(Collection $expiringStocks)
    {
        $this->expiringStocks = $expiringStocks;
    }


    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Monthly Expiry Reminder: Products Expiring Within 6 Months')
            ->greeting('Hello!')
            ->line('Here is the list of products expiring within the next 6 months:');

        $mail->line('');
        $mail->line('| Code | Name | Months Left | Days Left | Expiry Date |');
        $mail->line('|------|------|-------------|-----------|-------------|');

        foreach ($this->expiringStocks as $stock) {
            $expiry = \Carbon\Carbon::parse($stock->expiration_date);
            $now = now();
            $months = $now->diffInMonths($expiry, false);
            $days = $now->diffInDays($expiry, false);
            $mail->line(sprintf(
                '| %s | %s | %d | %d | %s |',
                $stock->product->product_code,
                $stock->product_name,
                $months,
                $days,
                $expiry->format('Y-m-d')
            ));
        }

        $mail->line('');
        $mail->line('Please take necessary action.');

        return $mail;
    }

}
