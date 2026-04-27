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
    /**
     * Halaman Analisa Omset & Keuntungan - Detail banget nggo mantau duit.
     */
    public function revenue(Request $request)
    {
        $period = $request->get('period', 'week'); // today, week, month, year
        $isMaster = auth()->user()->isMaster();

        $labels = [];
        $revenues = [];
        $profits = [];

        $query = Transaction::where('status', 'completed')->with('details');

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', now());
                $results = $query->get()->groupBy(function($t) {
                    return $t->created_at->format('H');
                });
                
                for ($i = 0; $i < 24; $i++) {
                    $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $labels[] = $hour . ':00';
                    $trans = $results->get($hour, collect());
                    $rev = $trans->sum('total');
                    $revenues[] = $rev;
                    if ($isMaster) {
                        $profits[] = $trans->sum(function($t) {
                            $cost = $t->details->sum(fn($d) => $d->purchase_price * $d->quantity);
                            return $t->total - $cost;
                        });
                    }
                }
                break;

            case 'week':
                $query->where('created_at', '>=', now()->subDays(6)->startOfDay());
                $results = $query->get()->groupBy(function($t) {
                    return $t->created_at->format('Y-m-d');
                });

                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i)->format('Y-m-d');
                    $labels[] = now()->subDays($i)->format('d M');
                    $trans = $results->get($date, collect());
                    $rev = $trans->sum('total');
                    $revenues[] = $rev;
                    if ($isMaster) {
                        $profits[] = $trans->sum(function($t) {
                            $cost = $t->details->sum(fn($d) => $d->purchase_price * $d->quantity);
                            return $t->total - $cost;
                        });
                    }
                }
                break;

            case 'month':
                $query->where('created_at', '>=', now()->subDays(29)->startOfDay());
                $results = $query->get()->groupBy(function($t) {
                    return $t->created_at->format('Y-m-d');
                });

                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i)->format('Y-m-d');
                    $labels[] = now()->subDays($i)->format('d/m');
                    $trans = $results->get($date, collect());
                    $rev = $trans->sum('total');
                    $revenues[] = $rev;
                    if ($isMaster) {
                        $profits[] = $trans->sum(function($t) {
                            $cost = $t->details->sum(fn($d) => $d->purchase_price * $d->quantity);
                            return $t->total - $cost;
                        });
                    }
                }
                break;

            case 'year':
                $query->where('created_at', '>=', now()->startOfYear());
                $results = $query->get()->groupBy(function($t) {
                    return $t->created_at->format('m');
                });

                for ($i = 1; $i <= 12; $i++) {
                    $month = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $labels[] = \Carbon\Carbon::create(null, $i)->format('M');
                    $trans = $results->get($month, collect());
                    $rev = $trans->sum('total');
                    $revenues[] = $rev;
                    if ($isMaster) {
                        $profits[] = $trans->sum(function($t) {
                            $cost = $t->details->sum(fn($d) => $d->purchase_price * $d->quantity);
                            return $t->total - $cost;
                        });
                    }
                }
                break;
        }

        $totalRevenue = array_sum($revenues);
        $totalProfit = array_sum($profits);

        return view('reports.revenue', compact(
            'labels',
            'revenues',
            'profits',
            'totalRevenue',
            'totalProfit',
            'period',
            'isMaster'
        ));
    }
}
