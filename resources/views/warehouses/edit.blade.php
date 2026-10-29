
@extends('layouts.app')

@section('title', 'Edit Gudang | RESQNET')

@section('content')
<div class="container py-4">

    <div class="mb-4">
        <h3 class="fw-bold">Edit Gudang Bantuan</h3>
        <p class="text-muted">Perbarui informasi gudang RESQNET.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST">
                @csrf
                @method('PUT')

                <h5 class="fw-bold mb-3">Informasi Gudang</h5>

                <div class="mb-3">
                    <label class="form-label">Nama Gudang</label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name', $warehouse->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="3">{{ old('address', $warehouse->address) }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Pengelola</label>
                        <input type="text" name="manager_name" class="form-control"
                               value="{{ old('manager_name', $warehouse->manager_name) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $warehouse->phone) }}">
                    </div>
                </div>

                <hr>

                <h5 class="fw-bold mb-3">Lokasi Geografis</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="any" name="latitude"
                               class="form-control"
                               value="{{ old('latitude', $warehouse->latitude) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="any" name="longitude"
                               class="form-control"
                               value="{{ old('longitude', $warehouse->longitude) }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kecamatan</label>
                        <input type="text" name="district" class="form-control"
                               value="{{ old('district', $warehouse->district) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kota/Kabupaten</label>
                        <input type="text" name="city" class="form-control"
                               value="{{ old('city', $warehouse->city) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Provinsi</label>
                        <input type="text" name="province" class="form-control"
                               value="{{ old('province', $warehouse->province) }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Status Gudang</label>
                    <select name="is_active" class="form-select" required>
                        <option value="1" {{ old('is_active', $warehouse->is_active) == 1 ? 'selected' : '' }}>
                            Aktif
                        </option>
                        <option value="0" {{ old('is_active', $warehouse->is_active) == 0 ? 'selected' : '' }}>
                            Tidak Aktif
                        </option>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('warehouses.index') }}"
                       class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection
