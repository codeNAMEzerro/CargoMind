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
    public function index()
    {
        $todayTransactions = Transaction::whereDate('created_at', today())->count();
        $todayRevenue = Transaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total');
        $totalItems = Item::where('is_active', true)->count();
        $lowStockItems = Item::where('is_active', true)->where('stock', '<=', 10)->count();

        // Revenue chart data (last 7 days)
        $chartData = Transaction::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

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
            'totalItems',
            'lowStockItems',
            'chartData',
            'recentTransactions',
            'recentLogs'
        ));
    }
}
