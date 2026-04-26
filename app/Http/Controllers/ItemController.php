<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    /**
     * Nampilke daftar barang - Kabeh stok barang dicheck neng kene yo.
     */
    public function index(Request $request)
    {
        $query = Item::where('is_active', true);

        if ($request->get('filter') === 'low_stock') {
            $query->where('stock', '<=', 10);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('rack_primary', 'like', "%{$search}%")
                    ->orWhere('rack_secondary', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('name')->paginate(20);

        return view('items.index', compact('items'));
    }

    /**
     * Nampilke form nggo nambah barang anyar - Ben gampang ngisike.
     */
    public function create()
    {
        return view('items.form', ['item' => new Item()]);
    }

    /**
     * Simpen barang anyar neng database - Ojo lali dicheck validasine.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:items,sku',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'rack_primary' => 'nullable|string|max:50',
            'rack_secondary' => 'nullable|string|max:50',
            'image' => 'nullable|image|max:5120', // Max 5MB
        ]);

        if (!auth()->user()->isMaster()) {
            $validated['purchase_price'] = 0;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $this->processImage($request->file('image'));
        }

        $item = Item::create($validated);

        ActivityLog::log('create_item', "Barang baru ditambahkan: {$item->name} (SKU: {$item->sku})", Item::class, $item->id);

        return redirect()->route('items.index')->with('success', 'Barang berhasil ditambahkan!');
    }

    /**
     * Nampilke form nggo ngedit barang - Nek ono sing kleru dibenerke neng kene.
     */
    public function edit(Item $item)
    {
        return view('items.form', compact('item'));
    }

    /**
     * Update data barang - Ben datane tetep seger lan bener.
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:items,sku,' . $item->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'rack_primary' => 'nullable|string|max:50',
            'rack_secondary' => 'nullable|string|max:50',
            'image' => 'nullable|image|max:5120',
        ]);

        if (!auth()->user()->isMaster()) {
            unset($validated['purchase_price']);
        }

        if ($request->hasFile('image')) {
            // hapus foto doang
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $validated['image'] = $this->processImage($request->file('image'));
        }

        $item->update($validated);

        ActivityLog::log('update_item', "Barang diperbarui: {$item->name}", Item::class, $item->id);

        return redirect()->route('items.index')->with('success', 'Barang berhasil diperbarui!');
    }

    /**
     * Nonaktifke barang
     */
    public function destroy(Item $item)
    {
        // Gunakan deaktifasi saja untuk menghindari error constraint jika barang sudah pernah dipesan
        ActivityLog::log('delete_item', "Barang dinonaktifkan: {$item->name}", Item::class, $item->id);
        
        $item->update(['is_active' => false]);

        return redirect()->route('items.index')->with('success', 'Barang berhasil dinonaktifkan!');
    }

    /**
     * Proses kompres foto
     */
    private function processImage($file): string
    {
        $filename = 'items/' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Try to compress with Intervention Image, fallback to direct storage
        try {
            $image = \Intervention\Image\Laravel\Facades\Image::read($file);
            $image->scale(width: 800);
            $path = storage_path('app/public/' . $filename);

            // Ensure directory exists
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $image->save($path, quality: 75);
        } catch (\Exception $e) {
            // Fallback: store without compression
            $filename = $file->store('items', 'public');
        }

        return $filename;
    }
}
