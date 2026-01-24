<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $invoiceNumber = str_pad($this->invoice_id, 6, '0', STR_PAD_LEFT);
        $invoiceNumber = "I-" . $invoiceNumber;

        return [
            'id' => $this->id,
            'invoiceId' => $this->invoice_id,
            'invoiceNumber' => $invoiceNumber,
            'clientName' => $this->invoice->user->name,
            'clientEmail' => $this->invoice->user->email,
            'clientPhone' => $this->invoice->user->phone,
            'amount' => number_format($this->amount, 2),
            'paymentDate' => $this->payment_date,
            'notes' => $this->notes,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
