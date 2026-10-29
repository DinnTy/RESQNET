<?php

namespace App\Http\Controllers;

use App\Models\DisasterCategory;
use Illuminate\Http\Request;

class DisasterCategoryController extends Controller
{
    public function index()
    {
        $categories = DisasterCategory::latest()->paginate(10);

        return view('disaster-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('disaster-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:disaster_categories,name',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Kategori tersebut sudah tersedia.',
        ]);

        DisasterCategory::create($validated);

        return redirect()->route('disaster-categories.index')
            ->with('success', 'Kategori bencana berhasil ditambahkan.');
    }

    public function edit(DisasterCategory $disaster_category)
    {
        return view('disaster-categories.edit', [
            'category' => $disaster_category,
        ]);
    }

    public function update(Request $request, DisasterCategory $disaster_category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:disaster_categories,name,' . $disaster_category->id,
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Kategori tersebut sudah tersedia.',
        ]);

        $disaster_category->update($validated);

        return redirect()->route('disaster-categories.index')
            ->with('success', 'Kategori bencana berhasil diperbarui.');
    }

    public function destroy(DisasterCategory $disaster_category)
    {
        if ($disaster_category->reports()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena sudah digunakan oleh laporan.');
        }

        $disaster_category->delete();

        return redirect()->route('disaster-categories.index')
            ->with('success', 'Kategori bencana berhasil dihapus.');
    }
}
