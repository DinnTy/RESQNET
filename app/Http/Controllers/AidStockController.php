<?php

namespace App\Http\Controllers;

use App\Models\AidStock;
use App\Models\AidItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class AidStockController extends Controller
{
    // Menampilkan daftar stok bantuan
    public function index()
    {
        $stocks = AidStock::with(['warehouse', 'aidItem'])
            ->latest()
            ->paginate(10);

        return view('aid-stocks.index', compact('stocks'));
    }

    // Menampilkan form tambah stok
    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $aidItems = AidItem::all();

        return view('aid-stocks.create', compact('warehouses', 'aidItems'));
    }

    // Menyimpan stok baru atau memperbarui stok yang sudah ada
    public function store(Request $request)
    {
      $validated = $request->validate([
    'warehouse_id' => 'required|exists:warehouses,id,is_active,1',
    'aid_item_id' => 'required|exists:aid_items,id',
    'quantity' => 'required|integer|min:0',
]);

        DB::transaction(function () use ($validated) {
            $stock = AidStock::where('warehouse_id', $validated['warehouse_id'])
                ->where('aid_item_id', $validated['aid_item_id'])
                ->lockForUpdate()
                ->first();

            if ($stock) {
                if ($validated['quantity'] < $stock->reserved_quantity) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Jumlah stok tidak boleh lebih kecil dari stok yang sudah dipesan.',
                    ]);
                }

                $stock->update([
                    'quantity' => $validated['quantity'],
                ]);
            } else {
                AidStock::create([
                    'warehouse_id' => $validated['warehouse_id'],
                    'aid_item_id' => $validated['aid_item_id'],
                    'quantity' => $validated['quantity'],
                    'reserved_quantity' => 0,
                ]);
            }
        });

        return redirect()
            ->route('aid-stocks.index')
            ->with('success', 'Stok bantuan berhasil disimpan.');
    }

    // Menampilkan detail stok
    public function show(AidStock $aidStock)
    {
        $aidStock->load(['warehouse', 'aidItem']);

        return view('aid-stocks.show', compact('aidStock'));
    }

    // Menampilkan halaman edit stok
    public function edit(AidStock $aidStock)
    {
        $aidStock->load(['warehouse', 'aidItem']);

        return view('aid-stocks.edit', compact('aidStock'));
    }

    // Memperbarui stok
    public function update(Request $request, AidStock $aidStock)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated, $aidStock) {
            $stock = AidStock::whereKey($aidStock->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($validated['quantity'] < $stock->reserved_quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah stok tidak boleh lebih kecil dari stok yang sudah dipesan.',
                ]);
            }

            $stock->update([
                'quantity' => $validated['quantity'],
            ]);
        });

        return redirect()
            ->route('aid-stocks.index')
            ->with('success', 'Stok bantuan berhasil diperbarui.');
    }

    // Menghapus data stok
    public function destroy(AidStock $aidStock)
    {
        if ($aidStock->reserved_quantity > 0) {
            return back()->with(
                'error',
                'Stok tidak dapat dihapus karena masih ada barang yang dipesan.'
            );
        }

        if ($aidStock->quantity > 0) {
            return back()->with(
                'error',
                'Stok tidak dapat dihapus karena jumlah barang masih tersedia. Kosongkan stok terlebih dahulu.'
            );
        }

        $hasHistory = $aidStock->aidItem()
            ->whereHas('distributionDetails.distribution', function ($query) use ($aidStock) {
                $query->where('warehouse_id', $aidStock->warehouse_id);
            })
            ->exists();

        if ($hasHistory) {
            return back()->with(
                'error',
                'Stok tidak dapat dihapus karena memiliki riwayat distribusi.'
            );
        }

        try {
            $aidStock->delete();
        } catch (QueryException $e) {
            return back()->with(
                'error',
                'Stok tidak dapat dihapus karena masih terhubung dengan data lain.'
            );
        }

        return redirect()
            ->route('aid-stocks.index')
            ->with('success', 'Data stok berhasil dihapus.');
    }
}
