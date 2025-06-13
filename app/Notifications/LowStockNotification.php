<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Stock;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $stock;

    /**
     * Create a new notification instance.
     */
    public function __construct(Stock $stock)
    {
        $this->stock = $stock;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low Stock Alert: ' . $this->stock->product_name)
            ->line('The stock for product (' . $this->stock->product->product_code . ') - ' . $this->stock->product_name . ' is low.')
            ->line('Current quantity: ' . $this->stock->quantity)
            ->line('Low stock threshold: ' . $this->stock->product->low_stock_value)
            ->action('View Stock', url('/stocks/' . $this->stock->id));
    }
}
