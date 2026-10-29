
@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="mb-4">
        <h3 class="fw-bold">Tambah Kategori Bencana</h3>
        <p class="text-muted">Tambahkan kategori bencana baru ke RESQNET.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <form action="{{ route('disaster-categories.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="Contoh: Gempa Bumi"
                           required>

                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="4"
                              placeholder="Masukkan deskripsi kategori bencana">{{ old('description') }}</textarea>

                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Simpan Kategori
                    </button>

                    <a href="{{ route('disaster-categories.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
