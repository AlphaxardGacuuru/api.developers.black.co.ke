<?php

namespace App\Http\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\CreditNote;
use App\Models\Deduction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService extends Service
{
    /**
     * Get dashboard statistics
     *
     * @return array
     */
    public function index()
    {
        // Get clients stats
        $clientsStats = $this->getClientsStats();

        // Get invoices stats
        $invoicesStats = $this->getInvoicesStats();

        // Get payments stats
        $paymentsStats = $this->getPaymentsStats();

        // Get credit notes stats
        $creditNotesStats = $this->getCreditNotesStats();

        // Get deductions stats
        $deductionsStats = $this->getDeductionsStats();

        return [
            'clients' => $clientsStats,
            'invoices' => $invoicesStats,
            'payments' => $paymentsStats,
            'creditNotes' => $creditNotesStats,
            'deductions' => $deductionsStats,
        ];
    }

    /**
     * Get clients statistics
     *
     * @return array
     */
    private function getClientsStats()
    {
        $stats = User::where("type", "client")
            ->select(
                DB::raw('COUNT(*) as count')
            )
            ->first();

        return [
            'count' => $stats->count ?? 0,
        ];
    }

    /**
     * Get invoices statistics
     *
     * @return array
     */
    private function getInvoicesStats()
    {
        $stats = Invoice::select(
            DB::raw('COUNT(*) as count'),
            DB::raw('COALESCE(SUM(total), 0) as total')
        )
            ->first();

        return [
            'count' => $stats->count ?? 0,
            'total' => number_format($stats->total ?? 0, 2, '.', ',')
        ];
    }

    /**
     * Get payments statistics
     *
     * @return array
     */
    private function getPaymentsStats()
    {
        $stats = Payment::select(
            DB::raw('COUNT(*) as count'),
            DB::raw('COALESCE(SUM(amount), 0) as total')
        )
            ->first();

        return [
            'count' => $stats->count ?? 0,
            'total' => number_format($stats->total ?? 0, 2, '.', ',')
        ];
    }

    /**
     * Get credit notes statistics
     *
     * @return array
     */
    private function getCreditNotesStats()
    {
        $stats = CreditNote::select(
            DB::raw('COUNT(*) as count'),
            DB::raw('COALESCE(SUM(amount), 0) as total')
        )
            ->first();

        return [
            'count' => $stats->count ?? 0,
            'total' => number_format($stats->total ?? 0, 2, '.', ',')
        ];
    }

    /**
     * Get deductions statistics
     *
     * @return array
     */
    private function getDeductionsStats()
    {
        $stats = Deduction::select(
            DB::raw('COUNT(*) as count'),
            DB::raw('COALESCE(SUM(amount), 0) as total')
        )
            ->first();

        return [
            'count' => $stats->count ?? 0,
            'total' => number_format($stats->total ?? 0, 2, '.', ',')
        ];
    }
}
