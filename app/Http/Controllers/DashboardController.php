<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Nampilke dashboard utama - Ben ngerti statistik toko piye kabare hari ini.
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'daily'); // daily, weekly, monthly

        $todayTransactions = Transaction::whereDate('created_at', today())->count();
        $todayRevenue = Transaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total');
        
        // Profit calculation (Master only)
        $todayProfit = 0;
        if (auth()->user()->isMaster()) {
            $todayProfit = Transaction::whereDate('created_at', today())
                ->where('status', 'completed')
                ->with('details')
                ->get()
                ->sum(function ($t) {
                    $cost = $t->details->sum(fn($d) => $d->purchase_price * $d->quantity);
                    return $t->total - $cost;
                });
        }

        $totalItems = Item::where('is_active', true)->count();
        $lowStockItems = Item::where('is_active', true)->where('stock', '<=', 10)->count();

        // Chart data logic with zero-filling
        $chartData = [];
        $query = Transaction::where('status', 'completed')->with('details');

        if ($period === 'monthly') {
            $startDate = now()->subMonths(11)->startOfMonth();
            $query->where('created_at', '>=', $startDate);
            $transactions = $query->get();

            for ($i = 0; $i < 12; $i++) {
                $date = $startDate->copy()->addMonths($i);
                $key = $date->format('Y-m');
                $label = $date->translatedFormat('M Y');
                
                $periodTransactions = $transactions->filter(fn($t) => $t->created_at->format('Y-m') === $key);
                $revenue = $periodTransactions->sum('total');
                $cost = $periodTransactions->sum(fn($t) => $t->details->sum(fn($d) => $d->purchase_price * $d->quantity));

                $chartData[] = [
                    'date' => $label,
                    'revenue' => $revenue,
                    'profit' => $revenue - $cost,
                ];
            }
        } elseif ($period === 'weekly') {
            $startDate = now()->subWeeks(11)->startOfWeek();
            $query->where('created_at', '>=', $startDate);
            $transactions = $query->get();

            for ($i = 0; $i < 12; $i++) {
                $date = $startDate->copy()->addWeeks($i);
                $key = $date->format('Y-W');
                $label = 'Mgg ' . $date->weekOfYear . ' (' . $date->format('d/m') . ')';
                
                $periodTransactions = $transactions->filter(fn($t) => $t->created_at->format('Y-W') === $key);
                $revenue = $periodTransactions->sum('total');
                $cost = $periodTransactions->sum(fn($t) => $t->details->sum(fn($d) => $d->purchase_price * $d->quantity));

                $chartData[] = [
                    'date' => $label,
                    'revenue' => $revenue,
                    'profit' => $revenue - $cost,
                ];
            }
        } else { // daily
            $startDate = now()->subDays(6)->startOfDay();
            $query->where('created_at', '>=', $startDate);
            $transactions = $query->get();

            for ($i = 0; $i < 7; $i++) {
                $date = $startDate->copy()->addDays($i);
                $key = $date->format('Y-m-d');
                $label = $date->translatedFormat('d M');
                
                $periodTransactions = $transactions->filter(fn($t) => $t->created_at->format('Y-m-d') === $key);
                $revenue = $periodTransactions->sum('total');
                $cost = $periodTransactions->sum(fn($t) => $t->details->sum(fn($d) => $d->purchase_price * $d->quantity));

                $chartData[] = [
                    'date' => $label,
                    'revenue' => $revenue,
                    'profit' => $revenue - $cost,
                ];
            }
        }

        // Recent transactions
        $recentTransactions = Transaction::with('cashier')
            ->latest()
            ->take(10)
            ->get();

        // Recent activity logs (for Master role)
        $recentLogs = ActivityLog::with('user')
            ->latest()
            ->take(15)
            ->get();

        return view('dashboard', compact(
            'todayTransactions',
            'todayRevenue',
            'todayProfit',
            'totalItems',
            'lowStockItems',
            'chartData',
            'recentTransactions',
            'recentLogs',
            'period'
        ));
    }
}
