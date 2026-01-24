<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Http\Resources\DeductionResource;
use App\Http\Services\DeductionService;
use Illuminate\Http\Request;

class DeductionController extends Controller
{
    public function __construct(protected DeductionService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        [$deductions, $sum] = $this->service->index($request);

        return DeductionResource::collection($deductions)
            ->additional([
                "sum" => number_format($sum, 2),
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoiceId' => 'required|integer|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'issueDate' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        [$saved, $message, $deduction] = $this->service->store($request);

        return (new DeductionResource($deduction))->additional([
            'saved' => $saved,
            'message' => $message,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $deduction = $this->service->show($id);

        return new DeductionResource($deduction);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'invoiceId' => 'sometimes|required|integer|exists:invoices,id',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'issueDate' => 'sometimes|required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        [$updated, $message, $deduction] = $this->service->update($request, $id);

        return (new DeductionResource($deduction))->additional([
            'updated' => $updated,
            'message' => $message,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        [$deleted, $message, $deduction] = $this->service->destroy($id);

        return response([
            "status" => $deleted,
            "message" => $message,
            "data" => $deduction,
        ], 200);
    }
}
