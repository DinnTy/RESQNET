<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class WarehouseController extends Controller
{
    // Menampilkan daftar gudang
    public function index()
    {
        $warehouses = Warehouse::withCount('stocks')
            ->latest()
            ->paginate(10);

        return view('warehouses.index', compact('warehouses'));
    }

    // Menampilkan form tambah gudang
    public function create()
    {
        return view('warehouses.create');
    }

    // Menyimpan data gudang
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
        ]);

        Warehouse::create($validated);

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Gudang berhasil ditambahkan.');
    }

    // Menampilkan detail gudang
    public function show(Warehouse $warehouse)
    {
        $warehouse->load('stocks.aidItem');

        return view('warehouses.show', compact('warehouse'));
    }

    // Menampilkan form edit gudang
    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    // Memperbarui data gudang
    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $warehouse->update($validated);

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Data gudang berhasil diperbarui.');
    }

    // Menghapus gudang
    public function destroy(Warehouse $warehouse)
    {
        // Periksa apakah gudang memiliki stok
        if ($warehouse->stocks()->exists()) {
            return back()->with(
                'error',
                'Gudang tidak dapat dihapus karena masih memiliki data stok.'
            );
        }

        // Periksa apakah gudang memiliki riwayat distribusi
        if ($warehouse->distributions()->exists()) {
            return back()->with(
                'error',
                'Gudang tidak dapat dihapus karena memiliki riwayat distribusi.'
            );
        }

        try {
            DB::transaction(function () use ($warehouse) {
                $warehouse->delete();
            });
        } catch (QueryException $e) {
            return back()->with(
                'error',
                'Gudang tidak dapat dihapus karena masih terhubung dengan data lain.'
            );
        }

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Gudang berhasil dihapus.');
    }
}
