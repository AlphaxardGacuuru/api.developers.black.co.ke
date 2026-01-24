<?php

namespace App\Http\Services;

use App\Models\Payment;
use App\Models\Invoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentService extends Service
{
	/*
     * Fetch All Payments
     */
	public function index($request)
	{
		$query = new Payment;

		$query = $this->search($query, $request);

		$payments = $query
			->with(['user', 'invoice.user'])
			->orderBy("id", "DESC")
			->paginate($request->per_page ?? 20)
			->appends($request->all());

		$sum = $query->sum("amount");

		return [$payments, $sum];
	}

	/*
     * Fetch Payment
     */
	public function show($id)
	{
		return Payment::with(['user', 'invoice.user'])->find($id);
	}

	/*
     * Save Payment
     */
	public function store($request)
	{
		$payment = new Payment;
		$payment->user_id = $request->user()->id;
		$payment->invoice_id = $request->invoiceId;
		$payment->amount = $request->amount;
		$payment->payment_date = $request->paymentDate;
		$payment->notes = $request->notes;
		$saved = $payment->save();

		return [$saved, "Payment Created Successfully", $payment];
	}

	/*
     * Update Payment
     */
	public function update($request, $id)
	{
		$payment = Payment::find($id);

		if (!$payment) {
			return [false, "Payment not found", null];
		}

		$payment->invoice_id = $request->input("invoiceId", $payment->invoice_id);
		$payment->amount = $request->input("amount", $payment->amount);
		$payment->payment_date = $request->input("paymentDate", $payment->payment_date);
		$payment->notes = $request->input("notes", $payment->notes);
		$saved = $payment->save();

		return [$saved, "Payment Updated Successfully", $payment];
	}

	/*
     * Destroy Payment
     */
	public function destroy($id)
	{
		DB::beginTransaction();

		try {
			$ids = explode(",", $id);

			$payments = Payment::whereIn("id", $ids)->get();

			$deleted = Payment::whereIn("id", $ids)->delete();

			$message = count($ids) > 1 ?
				"Payments Deleted Successfully" :
				"Payment Deleted Successfully";

			DB::commit();

			return [$deleted, $message, ""];
		} catch (Exception $e) {
			DB::rollBack();
			
			return [false, $e->getMessage(), ""];
		}
	}

	/*
     * Handle Search
     */
	public function search($query, $request)
	{
		// Search by invoice number
		if ($request->filled("invoiceId")) {
			$query = $query->where("invoice_id", $request->invoiceId);
		}

		// Search by date range
		if ($request->filled("startDate")) {
			$query = $query->whereDate("payment_date", ">=", $request->startDate);
		}

		if ($request->filled("endDate")) {
			$query = $query->whereDate("payment_date", "<=", $request->endDate);
		}

		// Search by amount range
		if ($request->filled("minAmount")) {
			$query = $query->where("amount", ">=", $request->minAmount);
		}

		if ($request->filled("maxAmount")) {
			$query = $query->where("amount", "<=", $request->maxAmount);
		}

		return $query;
	}
}
