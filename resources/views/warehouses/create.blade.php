
@extends('layouts.app')

@section('title', 'Tambah Gudang | RESQNET')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Tambah Gudang Bantuan</h3>
            <p class="text-muted mb-0">
                Tambahkan lokasi gudang untuk mendukung distribusi bantuan RESQNET.
            </p>
        </div>

        <a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali data berikut:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <form action="{{ route('warehouses.store') }}" method="POST">
                @csrf

                <h5 class="fw-bold mb-3">Informasi Gudang</h5>

                <div class="mb-3">
                    <label class="form-label">Nama Gudang</label>
                    <input type="text" name="name"
                        class="form-control"
                        value="{{ old('name') }}"
                        placeholder="Contoh: Gudang Bantuan Selebar"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Gudang</label>
                    <textarea name="address" class="form-control" rows="3"
                        placeholder="Masukkan alamat lengkap">{{ old('address') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Pengelola</label>
                        <input type="text" name="manager_name"
                            class="form-control"
                            value="{{ old('manager_name') }}"
                            placeholder="Nama pengelola">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" name="phone"
                            class="form-control"
                            value="{{ old('phone') }}"
                            placeholder="Nomor telepon">
                    </div>
                </div>

                <h5 class="fw-bold mt-3 mb-3">Lokasi Geografis</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="any" name="latitude"
                            class="form-control"
                            value="{{ old('latitude') }}"
                            placeholder="-3.846" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="any" name="longitude"
                            class="form-control"
                            value="{{ old('longitude') }}"
                            placeholder="102.316" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kecamatan</label>
                        <input type="text" name="district"
                            class="form-control"
                            value="{{ old('district') }}"
                            placeholder="Selebar">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kota/Kabupaten</label>
                        <input type="text" name="city"
                            class="form-control"
                            value="{{ old('city', 'Bengkulu') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Provinsi</label>
                        <input type="text" name="province"
                            class="form-control"
                            value="{{ old('province', 'Bengkulu') }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Status Gudang</label>
                    <select name="is_active" class="form-select" required>
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>
                            Aktif
                        </option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>
                            Tidak Aktif
                        </option>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('warehouses.index') }}"
                        class="btn btn-light border">
                        Batal
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Simpan Gudang
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
