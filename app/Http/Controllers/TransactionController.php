<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display POS / Kasir page.
     */
    public function create()
    {
        $items = Item::where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('transactions.create', compact('items'));
    }

    /**
     * Process the payment and create a transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.discount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_note' => 'nullable|string|max:255',
            'payment_amount' => 'required|numeric|min:0',
        ]);

        try {
            $transaction = DB::transaction(function () use ($request) {
                $subtotal = 0;
                $details = [];

                foreach ($request->items as $cartItem) {
                    $item = Item::findOrFail($cartItem['id']);

                    if ($item->stock < $cartItem['quantity']) {
                        throw new \Exception("Stok {$item->name} tidak mencukupi. Tersisa: {$item->stock}");
                    }

                    $lineDiscount = $cartItem['discount'] ?? 0;
                    $lineSubtotal = ($item->price * $cartItem['quantity']) - $lineDiscount;

                    $details[] = [
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'rack_location' => $item->rack_display,
                        'quantity' => $cartItem['quantity'],
                        'unit_price' => $item->price,
                        'purchase_price' => $item->purchase_price,
                        'discount' => $lineDiscount,
                        'subtotal' => $lineSubtotal,
                    ];

                    $subtotal += $lineSubtotal;

                    // Decrease stock
                    $item->decrement('stock', $cartItem['quantity']);
                }

                $globalDiscount = $request->discount_amount ?? 0;
                $total = $subtotal - $globalDiscount;
                $paymentAmount = $request->payment_amount;
                $changeAmount = $paymentAmount - $total;

                if ($changeAmount < 0) {
                    throw new \Exception('Pembayaran kurang! Kurang: ' . format_rupiah(abs($changeAmount)));
                }

                $transaction = Transaction::create([
                    'invoice_number' => Transaction::generateInvoiceNumber(),
                    'cashier_id' => auth()->id(),
                    'subtotal' => $subtotal,
                    'discount_amount' => $globalDiscount,
                    'discount_note' => $request->discount_note,
                    'total' => $total,
                    'payment_amount' => $paymentAmount,
                    'change_amount' => $changeAmount,
                    'status' => 'completed',
                ]);

                foreach ($details as $detail) {
                    $transaction->details()->create($detail);
                }

                ActivityLog::log(
                    'transaction',
                    "Transaksi {$transaction->invoice_number} sebesar " . format_rupiah($total) . " oleh " . auth()->user()->name,
                    Transaction::class,
                    $transaction->id
                );

                return $transaction;
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'transaction_id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'redirect' => route('transactions.receipt', $transaction->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show transaction history.
     */
    /**
     * Nampilke riwayat transaksi - Ben ngerti sing laku opo wae.
     */
    public function index(Request $request)
    {
        $query = Transaction::with('cashier');

        if ($search = $request->get('search')) {
            $query->where('invoice_number', 'like', "%{$search}%");
        }

        if ($date = $request->get('date')) {
            $query->whereDate('created_at', $date);
        }

        $transactions = $query->latest()->paginate(20);

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Nampilke nota / print preview - Ben pembeli marem ndelok rinciane.
     */
    public function receipt(Transaction $transaction)
    {
        $transaction->load('details', 'cashier');

        return view('transactions.receipt', compact('transaction'));
    }

    /**
     * Gawe invoice PDF - Ben iso dadi file nek arep dikirim atau disimpan.
     */
    public function downloadPdf(Transaction $transaction)
    {
        $transaction->load('details', 'cashier');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('transactions.invoice-pdf', compact('transaction'));
        $pdf->setPaper('a5', 'portrait');

        return $pdf->download("invoice-{$transaction->invoice_number}.pdf");
    }
}
