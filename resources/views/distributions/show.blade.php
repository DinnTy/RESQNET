
@extends('layouts.app')

@section('title', 'Detail Distribusi Bantuan | RESQNET')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Detail Distribusi Bantuan</h2>
            <p class="text-muted mb-0">Pantau perjalanan dan status penyaluran bantuan.</p>
        </div>
        <a href="{{ route('distributions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- INFORMASI DISTRIBUSI --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h5 class="fw-bold mb-1">Informasi Distribusi</h5>
                    <small class="text-muted">Identitas pengiriman bantuan RESQNET</small>
                </div>

                @php
                    $statusBadge = [
                        'dialokasikan' => 'warning',
                        'dikirim' => 'primary',
                        'diterima' => 'success',
                        'dibatalkan' => 'danger',
                    ];
                @endphp

                <span class="badge bg-{{ $statusBadge[$distribution->status] ?? 'secondary' }} fs-6 px-3 py-2">
                    {{ ucfirst($distribution->status) }}
                </span>
            </div>

            <div class="tracking-code-box mb-4">
                <small class="text-muted d-block mb-1">KODE PELACAKAN</small>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h4 class="fw-bold mb-0 text-primary">
                        {{ $distribution->tracking_code }}
                    </h4>
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            onclick="copyTrackingCode()">
                        Salin Kode
                    </button>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <small class="text-muted">Permintaan Bantuan</small>
                    <h6 class="fw-semibold mt-1">
                        {{ $distribution->aidRequest->item_name ?? '-' }}
                    </h6>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Lokasi Tujuan</small>
                    <h6 class="fw-semibold mt-1">
                        {{ $distribution->aidRequest->disasterReport->location->name ?? '-' }}
                    </h6>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Gudang Asal</small>
                    <h6 class="fw-semibold mt-1">
                        {{ $distribution->warehouse->name ?? '-' }}
                    </h6>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Petugas Penanggung Jawab</small>
                    <h6 class="fw-semibold mt-1">
                        {{ $distribution->assignedBy->name ?? '-' }}
                    </h6>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Tanggal Dibuat</small>
                    <h6 class="fw-semibold mt-1">
                        {{ $distribution->created_at->format('d-m-Y H:i') }}
                    </h6>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Catatan Distribusi</small>
                    <h6 class="fw-semibold mt-1">
                        {{ $distribution->notes ?: '-' }}
                    </h6>
                </div>
            </div>
        </div>
    </div>

    {{-- TIMELINE TRACKING --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1">Progres Pelacakan Bantuan</h5>
            <p class="text-muted mb-4">
                Riwayat tahapan penyaluran bantuan dari gudang hingga lokasi tujuan.
            </p>

            @if ($distribution->status === 'dibatalkan')

                <div class="cancelled-box text-center p-4">
                    <div class="timeline-icon bg-danger text-white mx-auto mb-3">
                        <i class="bi bi-x-lg"></i>
                    </div>
                    <h5 class="fw-bold text-danger">Distribusi Dibatalkan</h5>
                    <p class="text-muted mb-0">
                        Proses penyaluran bantuan ini telah dibatalkan.
                    </p>
                </div>

            @else

                @php
                    $steps = [
                        [
                            'title' => 'Bantuan Dialokasikan',
                            'description' => 'Bantuan telah disiapkan dan dialokasikan dari gudang.',
                            'date' => $distribution->created_at,
                            'icon' => 'bi-box-seam',
                            'done' => true,
                        ],
                        [
                            'title' => 'Bantuan Dalam Pengiriman',
                            'description' => 'Bantuan telah diberangkatkan menuju lokasi bencana.',
                            'date' => $distribution->shipped_at,
                            'icon' => 'bi-truck',
                            'done' => in_array($distribution->status, ['dikirim', 'diterima']),
                        ],
                        [
                            'title' => 'Bantuan Telah Diterima',
                            'description' => 'Bantuan telah sampai dan diterima di lokasi tujuan.',
                            'date' => $distribution->received_at,
                            'icon' => 'bi-check-lg',
                            'done' => $distribution->status === 'diterima',
                        ],
                    ];
                @endphp

                <div class="tracking-timeline">
                    @foreach ($steps as $index => $step)
                        <div class="timeline-item {{ $step['done'] ? 'completed' : '' }}">
                            <div class="timeline-marker">
                                <div class="timeline-icon">
                                    <i class="bi {{ $step['done'] ? 'bi-check-lg' : $step['icon'] }}"></i>
                                </div>
                                @if ($index !== 2)
                                    <div class="timeline-line"></div>
                                @endif
                            </div>

                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $step['title'] }}</h6>
                                        <p class="text-muted mb-2">{{ $step['description'] }}</p>
                                    </div>

                                    @if ($step['done'])
                                        <span class="badge bg-success">Selesai</span>
                                    @else
                                        <span class="badge bg-secondary">Menunggu</span>
                                    @endif
                                </div>

                                <small class="text-muted">
                                    @if ($step['date'])
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $step['date']->format('d-m-Y, H:i') }}
                                    @else
                                        Waktu belum tersedia
                                    @endif
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($distribution->status === 'diterima')
                    <div class="alert alert-success mt-3 mb-0">
                        <strong>Distribusi berhasil!</strong>
                        Bantuan telah sampai dan diterima di lokasi tujuan.
                    </div>
                @elseif ($distribution->status === 'dikirim')
                    <div class="alert alert-primary mt-3 mb-0">
                        Bantuan sedang dalam perjalanan menuju lokasi tujuan.
                    </div>
                @else
                    <div class="alert alert-warning mt-3 mb-0">
                        Bantuan telah dialokasikan dan sedang menunggu proses pengiriman.
                    </div>
                @endif

            @endif
        </div>
    </div>

    {{-- RIWAYAT AKTIVITAS DISTRIBUSI --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1">Riwayat Aktivitas Distribusi</h5>
            <p class="text-muted mb-4">
                Catatan setiap aktivitas dan perubahan status bantuan.
            </p>

            @forelse ($distribution->activities as $activity)
                <div class="activity-item d-flex gap-3 mb-4">
                    <div class="activity-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>

                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <h6 class="fw-bold mb-1">
                                {{ $activity->activity }}
                            </h6>
                            <small class="text-muted">
                                {{ $activity->created_at->format('d-m-Y H:i') }}
                            </small>
                        </div>

                        <p class="text-muted mb-2">
                            {{ $activity->description ?? '-' }}
                        </p>

                        @if ($activity->old_status || $activity->new_status)
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                @if ($activity->old_status)
                                    <span class="badge bg-secondary">
                                        {{ ucfirst($activity->old_status) }}
                                    </span>
                                    <i class="bi bi-arrow-right text-muted"></i>
                                @endif

                                @if ($activity->new_status)
                                    <span class="badge bg-primary">
                                        {{ ucfirst($activity->new_status) }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        <small class="text-muted">
                            <i class="bi bi-person me-1"></i>
                            {{ $activity->user->name ?? 'Pengguna tidak tersedia' }}
                        </small>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-clock-history fs-2 d-block mb-2"></i>
                    Belum ada riwayat aktivitas distribusi.
                </div>
            @endforelse
        </div>
    </div>

    {{-- DAFTAR BARANG --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1">Daftar Barang Distribusi</h5>
            <p class="text-muted mb-3">Rincian bantuan yang disalurkan.</p>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($distribution->details as $detail)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">
                                    {{ $detail->aidItem->name ?? '-' }}
                                </td>
                                <td>{{ $detail->quantity }}</td>
                                <td>{{ $detail->aidItem->unit ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Belum ada barang distribusi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- UPDATE STATUS --}}
    @if (
        in_array(auth()->user()->role, ['admin', 'petugas']) &&
        in_array($distribution->status, ['dialokasikan', 'dikirim'])
    )
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Perbarui Status Distribusi</h5>
                <p class="text-muted mb-3">
                    Perbarui status ketika bantuan berpindah ke tahap berikutnya.
                </p>

                <form action="{{ route('distributions.update-status', $distribution->id) }}"
                      method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">
                            Status Baru
                        </label>

                        <select name="status" id="status" class="form-select" required>
                            <option value="">Pilih Status</option>

                            @if ($distribution->status === 'dialokasikan')
                                <option value="dikirim">Dikirim</option>
                                <option value="dibatalkan">Dibatalkan</option>
                            @elseif ($distribution->status === 'dikirim')
                                <option value="diterima">Diterima</option>
                            @endif
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Yakin ingin memperbarui status distribusi?')">
                        Simpan Perubahan Status
                    </button>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection

@push('styles')
<style>
    /* Tema Siaga — hanya tampilan; struktur timeline & form status tidak diubah */
    .tracking-code-box {
        background: var(--ink); color: #fff;
        border: 2px solid var(--ink); border-radius: 6px;
        box-shadow: 5px 5px 0 var(--or);
        padding: 18px;
    }
    .tracking-code-box * { color: inherit; }
    .tracking-code-box .text-muted { color: #cfcdc7 !important; }

    .tracking-timeline { padding: 10px 5px 0; }

    .timeline-item { display: flex; gap: 20px; min-height: 125px; position: relative; }

    .timeline-marker { display: flex; flex-direction: column; align-items: center; width: 48px; flex-shrink: 0; }

    .timeline-icon {
        width: 46px; height: 46px; z-index: 2;
        display: flex; justify-content: center; align-items: center;
        background: #fff; color: var(--mut);
        border: 2px solid var(--ink); border-radius: 4px;
        font-size: 20px;
    }
    .timeline-item.completed .timeline-icon { background: var(--ok); color: #fff; box-shadow: 3px 3px 0 var(--ink); }

    .timeline-line { width: 0; flex: 1; min-height: 65px; margin: 5px 0; border-left: 3px dashed rgba(18,20,23,.35); }
    .timeline-item.completed .timeline-line { border-left: 3px solid var(--ok); }

    .timeline-content { flex: 1; padding: 7px 0 25px; }

    .cancelled-box {
        background: #fff6f6;
        border: 2px solid var(--ink); border-left: 10px solid var(--rd);
        border-radius: 4px;
    }

    .activity-icon {
        width: 42px; height: 42px; min-width: 42px;
        display: flex; align-items: center; justify-content: center;
        background: var(--or); color: var(--ink);
        border: 2px solid var(--ink); border-radius: 4px;
        font-size: 18px;
    }

    .activity-item { border-bottom: 1px solid rgba(18,20,23,.2); padding-bottom: 15px; }
    .activity-item:last-child { border-bottom: none; margin-bottom: 0 !important; padding-bottom: 0; }

    @media (max-width: 576px) {
        .timeline-item { gap: 12px; }
        .timeline-content h6 { font-size: 14px; }
    }
</style>
@endpush

@push('scripts')
<script>
    function copyTrackingCode() {
        const code = @json($distribution->tracking_code);

        navigator.clipboard.writeText(code).then(() => {
            alert('Kode pelacakan berhasil disalin: ' + code);
        }).catch(() => {
            alert('Gagal menyalin kode. Silakan salin secara manual: ' + code);
        });
    }
</script>
@endpush
