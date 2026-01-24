<?php

namespace App\Http\Services;

use App\Models\CreditNote;
use App\Models\Invoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditNoteService extends Service
{
	/*
     * Fetch All Credit Notes
     */
	public function index($request)
	{
		$query = new CreditNote;

		$query = $this->search($query, $request);

		$creditNotes = $query
			->with(['user', 'invoice.user'])
			->orderBy("id", "DESC")
			->paginate($request->per_page ?? 20)
			->appends($request->all());

		$sum = $query->sum("amount");

		return [$creditNotes, $sum];
	}

	/*
     * Fetch Credit Note
     */
	public function show($id)
	{
		return CreditNote::with(['user', 'invoice.user'])->find($id);
	}

	/*
     * Save Credit Note
     */
	public function store($request)
	{
		$creditNote = new CreditNote;
		$creditNote->user_id = $request->user()->id;
		$creditNote->invoice_id = $request->invoiceId;
		$creditNote->amount = $request->amount;
		$creditNote->issue_date = $request->issueDate;
		$creditNote->notes = $request->notes;
		$saved = $creditNote->save();

		return [$saved, "Credit Note Created Successfully", $creditNote];
	}

	/*
     * Update Credit Note
     */
	public function update($request, $id)
	{
		$creditNote = CreditNote::find($id);

		if (!$creditNote) {
			return [false, "Credit Note not found", null];
		}

		$creditNote->invoice_id = $request->input("invoiceId", $creditNote->invoice_id);
		$creditNote->amount = $request->input("amount", $creditNote->amount);
		$creditNote->issue_date = $request->input("issueDate", $creditNote->issue_date);
		$creditNote->notes = $request->input("notes", $creditNote->notes);
		$saved = $creditNote->save();

		return [$saved, "Credit Note Updated Successfully", $creditNote];
	}

	/*
     * Destroy Credit Note
     */
	public function destroy($id)
	{
		DB::beginTransaction();

		try {
			$ids = explode(",", $id);

			$creditNotes = CreditNote::whereIn("id", $ids)->get();

			$deleted = CreditNote::whereIn("id", $ids)->delete();

			$message = count($ids) > 1 ?
				"Credit Notes Deleted Successfully" :
				"Credit Note Deleted Successfully";

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
