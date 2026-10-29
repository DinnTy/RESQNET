@extends('layouts.app')

@section('title', 'Edit Barang Bantuan - RESQNET')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('aid-items.index') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Kembali ke Data Barang
        </a>
        <h2 class="fw-bold mt-3 mb-1">Edit Barang Bantuan</h2>
        <p class="text-muted">Perbarui informasi barang bantuan RESQNET.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('aid-items.update', $aidItem->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Barang *</label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $aidItem->name) }}"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kategori</label>
                        <input type="text" name="category"
                               class="form-control @error('category') is-invalid @enderror"
                               value="{{ old('category', $aidItem->category) }}">
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Satuan *</label>
                        <input type="text" name="unit"
                               class="form-control @error('unit') is-invalid @enderror"
                               value="{{ old('unit', $aidItem->unit) }}"
                               required>
                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="description" rows="4"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $aidItem->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('aid-items.index') }}"
                       class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection