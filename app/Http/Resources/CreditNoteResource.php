<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditNoteResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		$number = str_pad($this->id, 4, '0', STR_PAD_LEFT);
		$number = "CN-" . $number;

		$invoiceNumber = str_pad($this->invoice_id, 4, '0', STR_PAD_LEFT);
		$invoiceNumber = "I-" . $invoiceNumber;

		return [
			'id' => $this->id,
			'userId' => $this->user_id,
			'userName' => $this->user->name,
			'userEmail' => $this->user->email,
			'invoiceId' => $this->invoice_id,
			'invoiceNumber' => $invoiceNumber,
			'number' => $number,
			'clientName' => $this->invoice->user->name,
			'clientEmail' => $this->invoice->user->email,
			'clientPhone' => $this->invoice->user->phone,
			'amount' => number_format($this->amount, 2),
			'issueDate' => $this->issue_date,
			'notes' => $this->notes,
			'createdAt' => $this->created_at,
			'updatedAt' => $this->updated_at,
		];
	}
}
