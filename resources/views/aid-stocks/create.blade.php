@extends('layouts.app')

@section('title', 'Tambah Stok Bantuan | RESQNET')

@section('content')
<div class="container">
    <h2>Tambah Stok Bantuan</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('aid-stocks.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Gudang</label>
            <select name="warehouse_id" class="form-control" required>
                <option value="">Pilih Gudang</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}"
                        {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Barang Bantuan</label>
            <select name="aid_item_id" class="form-control" required>
                <option value="">Pilih Barang</option>
                @foreach ($aidItems as $item)
                    <option value="{{ $item->id }}"
                        {{ old('aid_item_id') == $item->id ? 'selected' : '' }}>
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Jumlah Stok</label>
            <input type="number" name="quantity" class="form-control"
                min="0" value="{{ old('quantity', 0) }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Stok</button>
        <a href="{{ route('aid-stocks.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
