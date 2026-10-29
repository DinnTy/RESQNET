@extends('layouts.app')

@section('title', 'Permintaan Bantuan - RESQNET')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold mb-1">Permintaan Bantuan</h3>
        <p class="text-muted mb-0">
            Kelola dan pantau kebutuhan bantuan bencana.
        </p>
    </div>

    <a href="{{ route('aid-requests.create') }}" class="btn btn-primary">
        + Ajukan Bantuan
    </a>
</div>

@if($aidRequests->count() > 0)

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Bantuan</th>
                        <th>Jumlah</th>
                        <th>Prioritas</th>
                        <th>Pelapor</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($aidRequests as $request)
                    <tr>
                        <td>{{ $loop->iteration + ($aidRequests->currentPage() - 1) * $aidRequests->perPage() }}</td>

                        <td>
                            <strong>{{ $request->item_name }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $request->disasterReport->title ?? '-' }}
                            </small>
                        </td>

                        <td>
                            {{ number_format($request->quantity) }}
                            {{ $request->unit }}
                        </td>

                        <td>
                            @php
                                $priorityColors = [
                                    'rendah' => 'secondary',
                                    'sedang' => 'info',
                                    'tinggi' => 'warning',
                                    'kritis' => 'danger'
                                ];
                            @endphp

                            <span class="badge bg-{{ $priorityColors[$request->priority] ?? 'secondary' }}">
                                {{ ucfirst($request->priority) }}
                            </span>
                        </td>

                        <td>
                            {{ $request->disasterReport->user->name ?? '-' }}
                        </td>

                        <td>
                            @php
                                $statusColors = [
                                    'menunggu' => 'warning',
                                    'disetujui' => 'success',
                                    'diproses' => 'primary',
                                    'terpenuhi' => 'success',
                                    'ditolak' => 'danger'
                                ];
                            @endphp

                            <span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>

                        <td>
                            <a href="{{ route('aid-requests.show', $request->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $aidRequests->links() }}
        </div>
    </div>
</div>

@else

<div class="card">
    <div class="card-body text-center py-5">
        <h5 class="fw-bold">Belum Ada Permintaan Bantuan</h5>
        <p class="text-muted">
            Belum terdapat pengajuan bantuan yang tersedia.
        </p>

        <a href="{{ route('aid-requests.create') }}" class="btn btn-primary">
            Buat Pengajuan Pertama
        </a>
    </div>
</div>

@endif

@endsection
