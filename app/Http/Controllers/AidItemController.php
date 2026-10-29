<?php

namespace App\Http\Controllers;

use App\Models\AidItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class AidItemController extends Controller
{
    // Menampilkan daftar barang bantuan
    public function index()
    {
        $aidItems = AidItem::withSum('stocks', 'quantity')
            ->latest()
            ->paginate(10);

        return view('aid-items.index', compact('aidItems'));
    }

    // Menampilkan form tambah barang
    public function create()
    {
        return view('aid-items.create');
    }

    // Menyimpan barang bantuan
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:aid_items,name',
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        AidItem::create($validated);

        return redirect()
            ->route('aid-items.index')
            ->with('success', 'Barang bantuan berhasil ditambahkan.');
    }

    // Menampilkan detail barang
    public function show(AidItem $aidItem)
    {
        $aidItem->load('stocks.warehouse');

        return view('aid-items.show', compact('aidItem'));
    }

    // Menampilkan form edit barang
    public function edit(AidItem $aidItem)
    {
        return view('aid-items.edit', compact('aidItem'));
    }

    // Memperbarui barang
    public function update(Request $request, AidItem $aidItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:aid_items,name,' . $aidItem->id,
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $aidItem->update($validated);

        return redirect()
            ->route('aid-items.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    // Menghapus barang
    public function destroy(AidItem $aidItem)
    {
        // Periksa stok yang terhubung
        if ($aidItem->stocks()->exists()) {
            return back()->with(
                'error',
                'Barang tidak dapat dihapus karena masih memiliki data stok.'
            );
        }

        // Periksa riwayat distribusi
        if ($aidItem->distributionDetails()->exists()) {
            return back()->with(
                'error',
                'Barang tidak dapat dihapus karena sudah digunakan dalam distribusi.'
            );
        }

        try {
            DB::transaction(function () use ($aidItem) {
                $aidItem->delete();
            });
        } catch (QueryException $e) {
            return back()->with(
                'error',
                'Barang tidak dapat dihapus karena masih terhubung dengan data lain.'
            );
        }

        return redirect()
            ->route('aid-items.index')
            ->with('success', 'Barang berhasil dihapus.');
    }
}
