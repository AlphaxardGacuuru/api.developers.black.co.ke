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
		$deduction = new Deduction;
		$deduction->user_id = $request->user()->id;
		$deduction->invoice_id = $request->invoiceId;
		$deduction->amount = $request->amount;
		$deduction->issue_date = $request->issueDate;
		$deduction->notes = $request->notes;
		$saved = $deduction->save();

		return [$saved, "Deduction Created Successfully", $deduction];
	}

	/*
     * Update Deduction
     */
	public function update($request, $id)
	{
		$deduction = Deduction::find($id);

		if (!$deduction) {
			return [false, "Deduction not found", null];
		}

		$deduction->invoice_id = $request->input("invoiceId", $deduction->invoice_id);
		$deduction->amount = $request->input("amount", $deduction->amount);
		$deduction->issue_date = $request->input("issueDate", $deduction->issue_date);
		$deduction->notes = $request->input("notes", $deduction->notes);
		$saved = $deduction->save();

		return [$saved, "Deduction Updated Successfully", $deduction];
	}

	/*
     * Destroy Deduction
     */
	public function destroy($id)
	{
		DB::beginTransaction();

		try {
			$ids = explode(",", $id);

			$deductions = Deduction::whereIn("id", $ids)->get();

			$deleted = Deduction::whereIn("id", $ids)->delete();

			$message = count($ids) > 1 ?
				"Deductions Deleted Successfully" :
				"Deduction Deleted Successfully";

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
		// Search by number (id)
		if ($request->filled("number")) {
			$query = $query->where("id", "LIKE", "%" . $request->number . "%");
		}

		// Search by invoice number
		if ($request->filled("invoiceId")) {
			$query = $query->where("invoice_id", $request->invoiceId);
		}

		// Search by date range
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
