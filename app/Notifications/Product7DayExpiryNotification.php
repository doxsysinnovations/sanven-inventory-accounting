<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Product7DayExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $stocks;

    public function __construct($stocks)
    {
        $this->stocks = $stocks;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('⚠️ Products Expiring in 7 Days')
            ->greeting('Attention!')
            ->line('The following products are within 7 days of expiry:');

        $mail->line('| Code | Name | Days Left | Expiry Date |');
        $mail->line('|------|------|-----------|-------------|');

        foreach ($this->stocks as $stock) {
            $expiry = \Carbon\Carbon::parse($stock->expiration_date);
            $days = now('Asia/Manila')->diffInDays($expiry, false);
            $mail->line(sprintf(
                '| %s | %s | %d | %s |',
                $stock->product->product_code,
                $stock->product_name,
                $days,
                $expiry->format('Y-m-d')
            ));
        }

        $mail->line('Please take immediate action.');
        return $mail;
    }
}
