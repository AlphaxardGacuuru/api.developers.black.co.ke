<?php

namespace App\Http\Services;

use App\Models\Deduction;
use App\Models\Invoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeductionService extends Service
{
	/*
     * Fetch All Deductions
     */
	public function index($request)
	{
		$query = new Deduction;

		$query = $this->search($query, $request);

		$deductions = $query
			->with(['user', 'invoice.user'])
			->orderBy("id", "DESC")
			->paginate($request->per_page ?? 20)
			->appends($request->all());

		$sum = $query->sum("amount");

		return [$deductions, $sum];
	}

	/*
     * Fetch Deduction
     */
	public function show($id)
	{
		return Deduction::with(['user', 'invoice.user'])->find($id);
	}

	/*
     * Save Deduction
     */
	public function store($request)
	{
		$invoice = Invoice::find($request->invoiceId);

		$deduction = new Deduction;
		$deduction->user_id = $invoice->user_id;
		$deduction->invoice_id = $request->invoiceId;
		$deduction->amount = $request->amount;
		$deduction->issue_date = $request->issueDate;
		$deduction->notes = $request->notes;

		$saved = DB::transaction(function () use ($deduction) {
			$saved = $deduction->save();

			$this->updateInvoiceStatus($deduction->invoice_id);

			return $saved;
		});

		return [$saved, "Deduction Created Successfully", $deduction];
	}

	/*
     * Update Deduction
     */
	public function update($request, $id)
	{
		$deduction = Deduction::find($id);
		$deduction->amount = $request->input("amount", $deduction->amount);
		$deduction->issue_date = $request->input("issueDate", $deduction->issue_date);
		$deduction->notes = $request->input("notes", $deduction->notes);

		$saved = DB::transaction(function () use ($deduction) {
			$saved = $deduction->save();

			$this->updateInvoiceStatus($deduction->invoice_id);

			return $saved;
		});

		return [$saved, "Deduction Updated Successfully", $deduction];
	}

	/*
     * Destroy Deduction
     */
	public function destroy($id)
	{
		$ids = explode(",", $id);

		$deleted = DB::transaction(function () use ($ids) {
			$query = Deduction::whereIn("id", $ids);
			
			$deleted = $query->delete();

			$this->updateInvoiceStatus($query->first()->invoice_id);

			return $deleted;
		});

		$message = count($ids) > 1 ?
			"Deductions Deleted Successfully" :
			"Deduction Deleted Successfully";

		return [$deleted, $message, ""];
	}

	/*
     * Handle Search
     */
	public function search($query, $request)
	{
		if ($request->filled("number")) {
			$query = $query->where("id", "LIKE", "%" . $request->number . "%");
		}

		if ($request->filled("invoiceId")) {
			$query = $query->where("invoice_id", $request->invoiceId);
		}

		$clientId = $request->input("clientId");

		if ($request->filled("clientId")) {
			$query = $query->whereHas("user", function ($query) use ($clientId) {
				$query->where("id", $clientId);
			});
		}

		if ($request->filled("startDate")) {
			$query = $query->whereDate("issue_date", ">=", $request->startDate);
		}

		if ($request->filled("endDate")) {
			$query = $query->whereDate("issue_date", "<=", $request->endDate);
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
