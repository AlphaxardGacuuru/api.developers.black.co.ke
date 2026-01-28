<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $number = str_pad($this->id, 4, '0', STR_PAD_LEFT);
        $number = "I-" . $number;

        return [
            "id" => $this->id,
            "number" => $number,
            "clientId" => $this->user->id,
            "clientName" => $this->user->name,
            "amount" => number_format($this->total),
            "paid" => number_format($this->paid),
            "balance" => number_format($this->balance),
            "credits" => number_format($this->creditNotes->sum('amount')),
            "deductions" => number_format($this->deductions->sum('amount')),
            "status" => $this->status,
            "emailsSent" => $this->emails_sent,
            "issueDate" => $this->issue_date,
            "dueDate" => $this->due_date,
            "notes" => $this->notes,
            "terms" => $this->terms,
            "lineItems" => $this->invoiceItems,
            "updatedAt" => $this->updated_at,
            "createdAt" => $this->created_at,
        ];
    }
}
