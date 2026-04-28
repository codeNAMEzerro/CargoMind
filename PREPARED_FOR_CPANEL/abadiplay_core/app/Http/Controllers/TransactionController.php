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

    /**
     * God Mode: Toggle edit mode for Master.
     */
    public function toggleGodMode()
    {
        $current = session('master_god_mode', false);
        session(['master_god_mode' => !$current]);

        $status = !$current ? 'aktif' : 'nonaktif';
        return back()->with('success', "God Mode (Edit Mode) berhasil di{$status}kan!");
    }

    /**
     * God Mode: Tampilkan form edit transaksi.
     */
    public function edit(Transaction $transaction)
    {
        if (!auth()->user()->isMaster() || !session('master_god_mode')) {
            abort(403, 'God Mode harus aktif untuk mengedit transaksi.');
        }

        $transaction->load('details');
        return view('transactions.edit', compact('transaction'));
    }

    /**
     * God Mode: Update data transaksi & sesuaikan stok.
     */
    public function update(Request $request, Transaction $transaction)
    {
        if (!auth()->user()->isMaster() || !session('master_god_mode')) {
            abort(403);
        }

        $request->validate([
            'created_at' => 'required|date',
            'discount_amount' => 'required|numeric|min:0',
            'discount_note' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:transaction_details,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $transaction) {
                $subtotal = 0;

                foreach ($request->items as $itemData) {
                    $detail = TransactionDetail::findOrFail($itemData['id']);
                    $item = Item::findOrFail($detail->item_id);

                    // Sesuaikan stok: kembalikan stok lama, kurangi stok baru
                    $diff = $detail->quantity - $itemData['quantity'];
                    $item->increment('stock', $diff);

                    $lineSubtotal = ($itemData['unit_price'] * $itemData['quantity']) - $itemData['discount'];
                    
                    $detail->update([
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'discount' => $itemData['discount'],
                        'subtotal' => $lineSubtotal,
                    ]);

                    $subtotal += $lineSubtotal;
                }

                $total = $subtotal - $request->discount_amount;

                $transaction->update([
                    'created_at' => $request->created_at,
                    'subtotal' => $subtotal,
                    'discount_amount' => $request->discount_amount,
                    'discount_note' => $request->discount_note,
                    'total' => $total,
                ]);

                ActivityLog::log('update_transaction', "Master mengedit transaksi {$transaction->invoice_number} (God Mode)", Transaction::class, $transaction->id);
            });

            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil diperbarui via God Mode!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * God Mode: Hapus transaksi & kembalikan stok.
     */
    public function destroy(Transaction $transaction)
    {
        if (!auth()->user()->isMaster() || !session('master_god_mode')) {
            abort(403);
        }

        try {
            \Illuminate\Support\Facades\Log::info("Mencoba menghapus transaksi: " . $transaction->invoice_number);
            
            DB::transaction(function () use ($transaction) {
                // Pastikan details ter-load
                $transaction->load('details');
                
                // Kembalikan semua stok
                foreach ($transaction->details as $detail) {
                    $item = Item::find($detail->item_id);
                    if ($item) {
                        $item->increment('stock', $detail->quantity);
                    }
                }

                // Log sebelum hapus
                ActivityLog::log('delete_transaction', "Master menghapus transaksi {$transaction->invoice_number} (God Mode)", Transaction::class, $transaction->id);
                
                // Hapus transaksi (details akan ikut terhapus karena cascade di migration)
                $transaction->delete();
            });

            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus & stok dikembalikan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }
}
