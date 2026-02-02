<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceNotification extends Notification
{
    use Queueable;

    public $invoice;
    public $pdf;

    /**
     * Create a new notification instance.
     */
    public function __construct($invoice, $pdf)
    {
        $this->invoice = $invoice;
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
            ->subject("Invoice {$this->invoice->number}")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new invoice has been created for you.')
            ->line('Invoice Number: ' . $this->invoice->number)
            ->line('Issue Date: ' . $this->invoice->issue_date->format('d M Y'))
            ->line('Due Date: ' . $this->invoice->due_date->format('d M Y'))
            ->line('Total Amount: KES ' . number_format($this->invoice->total))
            ->line("Notes: " . $this->invoice->notes)
            ->line("Thank you for your business!")
            // ->action('View Invoice', url('/invoices/' . $this->invoice->id))
            ->salutation('Regards, Black Developers')
            ->attachData($this->pdf, "{$this->invoice->number}.pdf", [
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
