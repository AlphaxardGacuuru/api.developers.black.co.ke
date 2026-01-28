<?php

namespace App\Http\Services;

use App\Models\CreditNote;
use App\Models\Deduction;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Service
{
	public $id;

	public function __construct()
	{
		// Current User ID
		$auth = auth('sanctum')->user();

		$this->id = $auth ? $auth->id : 0;
	}

	public function updateInvoiceStatus($invoiceId)
	{
		// Get the target invoice
		$invoice = Invoice::find($invoiceId);

		// Get all invoices in chronological order
		$invoices = Invoice::where("id", $invoiceId)
			->orderBy("created_at", "ASC")
			->get();

		$paymentQuery = Payment::where("invoice_id", $invoiceId);

		$totalPayments = $paymentQuery->sum("amount");

		$creditNoteQuery = CreditNote::where("invoice_id", $invoiceId);

		$totalCreditNotes = $creditNoteQuery->sum("amount");

		$deductionQuery = Deduction::where("invoice_id", $invoiceId);

		$totalDeductions = $deductionQuery->sum("amount");

		$paid = $totalPayments + $totalCreditNotes - $totalDeductions;

		$invoices->each(function ($invoice) use (&$paid) {
			Log::info("Processing Invoice ID: " . $invoice->id);
			if ($paid <= 0) {
				$invoice->paid = 0;
				$invoice->balance = $invoice->total;
				$invoice->status = "not_paid";
			} else if ($paid < $invoice->total) {
				Log::info("Current Paid Amount: " . $paid);
				$invoice->paid = $paid;
				$invoice->balance = $invoice->total - $paid;
				$invoice->status = "partially_paid";
			} else if ($paid >= $invoice->total) {
				$invoice->paid = $invoice->total;
				$invoice->balance = 0;
				$invoice->status = "paid";
			}

			$invoice->save();

			$paid -= $invoice->paid;
		});
	}
}
