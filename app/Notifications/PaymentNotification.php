<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentNotification extends Notification
{
    use Queueable;

    public $payment;
    public $pdf;

    /**
     * Create a new notification instance.
     */
    public function __construct($payment, $pdf)
    {
        $this->payment = $payment;
        $this->pdf = $pdf;
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
            ->subject("Receipt {$this->payment->number}")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new receipt has been created for you.')
            ->line('Receipt Number: ' . $this->payment->number)
            ->line('Payment Date: ' . $this->payment->payment_date->format('d M Y'))
            ->line('Amount: KES ' . number_format($this->payment->amount))
            ->line("Notes: " . $this->payment->notes)
            ->line("Thank you for your business!")
            // ->action('View Invoice', url('/invoices/' . $this->payment->id))
            ->salutation('Regards, Black Developers')
            ->attachData($this->pdf, "{$this->payment->number}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
