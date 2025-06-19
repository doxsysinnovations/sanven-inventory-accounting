<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductExpiredTodayNotification extends Notification implements ShouldQueue
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
            ->subject('🚨 Products Expired Today')
            ->greeting('High Alert!')
            ->line('The following products have expired today:');

        $mail->line('| Code | Name | Expiry Date |');
        $mail->line('|------|------|-------------|');

        foreach ($this->stocks as $stock) {
            $expiry = \Carbon\Carbon::parse($stock->expiration_date);
            $mail->line(sprintf(
                '| %s | %s | %s |',
                $stock->product->product_code,
                $stock->product_name,
                $expiry->format('Y-m-d')
            ));
        }

        $mail->line('Please remove or quarantine these products immediately.');
        return $mail;
    }
}
