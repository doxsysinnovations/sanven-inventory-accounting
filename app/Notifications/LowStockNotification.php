<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Stock;
use App\Models\Product;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $product;
    public $totalQuantity;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product, $totalQuantity)
    {
        $this->product = $product;
        $this->totalQuantity = $totalQuantity;
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
            ->subject('Low Stock Alert: ' . $this->product->name)
            ->line('The stock for product (' . $this->product->product_code . ') - ' . $this->product->name . ' is low.')
            ->line('Current total quantity: ' . $this->totalQuantity)
            ->line('Low stock threshold: ' . $this->product->low_stock_value)
            ->action('View Product', url('/products/' . $this->product->id));
    }
}
