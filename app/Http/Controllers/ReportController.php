<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman laporan - Biar ngerti untung rugine piye kabare.
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $transactions = Transaction::with('cashier', 'details')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->get();

        $totalRevenue = $transactions->sum('total');
        $totalTransactions = $transactions->count();
        $totalDiscount = $transactions->sum('discount_amount');
        
        $totalProfit = 0;
        if (auth()->user()->isMaster()) {
            $totalProfit = $transactions->sum(function($t) {
                $cost = $t->details->sum(fn($d) => $d->purchase_price * $d->quantity);
                return $t->total - $cost;
            });
        }

        // Activity logs (for Master)
        $activityLogs = ActivityLog::with('user')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->paginate(30);

        return view('reports.index', compact(
            'transactions',
            'totalRevenue',
            'totalTransactions',
            'totalDiscount',
            'totalProfit',
            'activityLogs',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Ekspor transaksi ke Excel/CSV - Ben iso dicheck sat-set neng Excel.
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $transactions = Transaction::with('cashier', 'details')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->get();

        ActivityLog::log('export_report', "Laporan diekspor: {$startDate} s/d {$endDate}");

        // Create a simple CSV export
        $filename = "laporan-{$startDate}-{$endDate}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $isMaster = auth()->user()->isMaster();

        $callback = function () use ($transactions, $isMaster) {
            $file = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            $headerFields = ['No. Invoice', 'Tanggal', 'Kasir', 'Subtotal', 'Diskon', 'Total'];
            if ($isMaster) $headerFields[] = 'Laba Bersih';
            $headerFields[] = 'Status';
            
            fputcsv($file, $headerFields);

            foreach ($transactions as $t) {
                $row = [
                    $t->invoice_number,
                    $t->created_at->format('d/m/Y H:i'),
                    $t->cashier->name ?? '-',
                    $t->subtotal,
                    $t->discount_amount,
                    $t->total,
                ];

                if ($isMaster) {
                    $cost = $t->details->sum(fn($d) => $d->purchase_price * $d->quantity);
                    $row[] = $t->total - $cost;
                }

                $row[] = $t->status;
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
