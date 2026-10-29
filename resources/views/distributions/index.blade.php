
@extends('layouts.app')

@section('title', 'Distribusi Bantuan | RESQNET')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2>Data Distribusi Bantuan</h2>
            <p class="text-muted mb-0">
                Pantau perkembangan penyaluran bantuan bencana.
            </p>
        </div>

        @if (in_array(auth()->user()->role, ['admin', 'petugas']))
            <a href="{{ route('distributions.create') }}" class="btn btn-primary">
                + Buat Distribusi
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode Pelacakan</th>
                            <th>Permintaan Bantuan</th>
                            <th>Gudang</th>
                            <th>Status</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($distributions as $distribution)
                            <tr>
                                <td>{{ $distributions->firstItem() + $loop->index }}</td>

                                <td>
                                    <strong>{{ $distribution->tracking_code }}</strong>
                                </td>

                                <td>
                                    {{ $distribution->aidRequest->item_name ?? '-' }}
                                </td>

                                <td>
                                    {{ $distribution->warehouse->name ?? '-' }}
                                </td>

                                <td>
                                    @if ($distribution->status === 'dialokasikan')
                                        <span class="badge bg-warning text-dark">Dialokasikan</span>
                                    @elseif ($distribution->status === 'dikirim')
                                        <span class="badge bg-primary">Dikirim</span>
                                    @elseif ($distribution->status === 'diterima')
                                        <span class="badge bg-success">Diterima</span>
                                    @elseif ($distribution->status === 'dibatalkan')
                                        <span class="badge bg-danger">Dibatalkan</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $distribution->created_at->format('d-m-Y H:i') }}
                                </td>

                                <td>
                                    <a href="{{ route('distributions.show', $distribution->id) }}"
                                       class="btn btn-sm btn-info text-white">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    Belum ada data distribusi bantuan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $distributions->links() }}
        </div>
    </div>
</div>
@endsection
