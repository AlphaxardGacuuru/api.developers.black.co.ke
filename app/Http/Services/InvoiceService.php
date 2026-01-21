<?php

namespace App\Http\Services;

use App\Http\Resources\InvoiceResource;
use App\Mail\InvoiceMail;
use App\Models\CreditNote;
use App\Models\Deduction;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\UserUnit;
use App\Models\WaterReading;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Http\Services\EmailService;
use App\Http\Services\SMSSendService;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Notifications\InvoiceReminderNotification;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\HttpTransportException;

class InvoiceService extends Service
{
	/*
     * Fetch All Invoices
     */
	public function index($request)
	{
		$invoiceQuery = new Invoice;

		$invoiceQuery = $this->search($invoiceQuery, $request);

		$invoices = $invoiceQuery
			->orderBy("id", "DESC")
			->paginate($request->per_page ?? 20)
			->appends($request->all());

		$sum = $invoiceQuery->sum("total");
		$balance = $invoiceQuery->sum("balance");
		$paid = $invoiceQuery->sum("paid");

		return [$invoices, $sum, $balance, $paid];
	}

	/*
     * Fetch Invoice
     */
	public function show($id)
	{
		$invoice = Invoice::find($id);

		return new InvoiceResource($invoice);
	}

	/*
     * Save Invoice
     */
	public function store($request)
	{
		// Generate Invoice Number
		$latestInvoice = Invoice::latest()->first();

		if ($latestInvoice) {
			$lastNumber = intval(substr($latestInvoice->number, -6));
			$invoiceNumber = "I-" . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
		} else {
			$invoiceNumber = 'I-000001';
		}

		$invoice = new Invoice;
		$invoice->number = $invoiceNumber;
		$invoice->user_id = $request->clientId;
		$invoice->issue_date = $request->issueDate;
		$invoice->due_date = $request->dueDate;
		$invoice->total = $request->total;
		$invoice->notes = $request->notes;
		$invoice->terms = $request->terms;
		$invoice->status = $request->status;
		$saved = $invoice->save();

		// Invoice Items
		foreach ($request->lineItems as $lineItem) {
			$invoiceItem = new InvoiceItem;
			$invoiceItem->invoice_id = $invoice->id;
			$invoiceItem->description = $lineItem['description'];
			$invoiceItem->quantity = $lineItem['quantity'];
			$invoiceItem->rate = $lineItem['rate'];
			$invoiceItem->amount = $lineItem['amount'];
			$saved = $invoiceItem->save();
		}


		return [$saved, "Invoice Created Successfully", $invoice];
	}

	/*
     * Destroy Invoice
     */
	public function destroy($id)
	{
		$ids = explode(",", $id);

		$deleted = Invoice::whereIn("id", $ids)->delete();

		$message = count($ids) > 1 ?
			"Invoices Deleted Successfully" :
			"Invoice Deleted Successfully";

		return [$deleted, $message, ""];
	}

	/*
     * Handle Search
     */
	public function search($query, $request)
	{
		$number = $request->input("number");

		if ($request->filled("number")) {
			$query = $query->where("number", "LIKE", "%" . $number . "%");
		}

		$unit = $request->input("unit");

		if ($request->filled("unit")) {
			$query = $query->whereHas("userUnit.unit", function ($query) use ($unit) {
				$query->where("name", "LIKE", "%" . $unit . "%");
			});
		}

		$status = $request->input("status");

		if ($request->filled("status")) {
			$query = $query->where("status", $status);
		}

		$startMonth = $request->input("startMonth");
		$endMonth = $request->input("endMonth");
		$startYear = $request->input("startYear");
		$endYear = $request->input("endYear");

		// Build start date filter
		if ($request->filled("startMonth") || $request->filled("startYear")) {
			$year = $startYear ?? date('Y');
			$month = $startMonth ?? 1;
			$startDate = Carbon::create($year, $month, 1)->startOfMonth();
			$query = $query->where("created_at", ">=", $startDate);
		}

		// Build end date filter
		if ($request->filled("endMonth") || $request->filled("endYear")) {
			$year = $endYear ?? date('Y');
			$month = $endMonth ?? 12;
			$endDate = Carbon::create($year, $month, 1)->endOfMonth();
			$query = $query->where("created_at", "<=", $endDate);
		}

		return $query;
	}

	/*
     * Handle Invoice Adjustment
     */
	public function adjustInvoice($invoiceId)
	{
		$paid = Payment::where("invoice_id", $invoiceId)->sum("amount");

		$creditNotes = CreditNote::where("invoice_id", $invoiceId)->sum("amount");

		$deductions = Deduction::where("invoice_id", $invoiceId)->sum("amount");

		$paid = $paid + $creditNotes - $deductions;

		$invoice = Invoice::find($invoiceId);

		$balance = $invoice->amount - $paid;

		// Check if paid is enough
		if ($paid == 0) {
			$status = "not_paid";
		} else if ($paid < $invoice->amount) {
			$status = "partially_paid";
		} else if ($paid == $invoice->amount) {
			$status = "paid";
		} else {
			$status = "over_paid";
		}

		$invoice->paid = $paid;
		$invoice->balance = $balance;
		$invoice->status = $status;

		return $invoice->save();
	}
}
