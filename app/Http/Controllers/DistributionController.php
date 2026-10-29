<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\DistributionActivity;
use App\Models\AidRequest;
use App\Models\AidItem;
use App\Models\Warehouse;
use App\Models\AidStock;
use App\Notifications\ResqnetNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DistributionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Distribution::with([
            'aidRequest.disasterReport.location',
            'aidRequest.disasterReport.user',
            'warehouse',
            'assignedBy',
            'details.aidItem'
        ]);

        if ($user->role === 'masyarakat') {
            $query->whereHas('aidRequest.disasterReport', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $distributions = $query->latest()->paginate(10);

        return view('distributions.index', compact('distributions'));
    }

    public function create()
    {
        $aidRequests = AidRequest::with([
            'disasterReport.location',
            'items.aidItem'
        ])
            ->whereIn('status', ['disetujui', 'diproses'])
            ->latest()
            ->get();

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get();

        $stocks = AidStock::with(['warehouse', 'aidItem'])
            ->whereHas('warehouse', function ($query) {
                $query->where('is_active', true);
            })
            ->get()
            ->map(function ($stock) {
                return [
                    'warehouse_id' => $stock->warehouse_id,
                    'warehouse_name' => $stock->warehouse->name,
                    'warehouse_latitude' => $stock->warehouse->latitude,
                    'warehouse_longitude' => $stock->warehouse->longitude,
                    'aid_item_id' => $stock->aid_item_id,
                    'aid_item_name' => $stock->aidItem->name,
                    'unit' => $stock->aidItem->unit,
                    'available' => max(
                        0,
                        $stock->quantity - $stock->reserved_quantity
                    ),
                ];
            })
            ->values();

        $requestsData = $aidRequests->map(function ($request) {

            $location = $request->disasterReport?->location;

            $items = $request->items->map(function ($requestItem) {
                $aidItem = $requestItem->aidItem;

                if (!$aidItem) {
                    return null;
                }

                $alreadyAllocated = DB::table('distribution_details')
                    ->join(
                        'distributions',
                        'distributions.id',
                        '=',
                        'distribution_details.distribution_id'
                    )
                    ->where('distributions.aid_request_id', $requestItem->aid_request_id)
                    ->where('distribution_details.aid_item_id', $aidItem->id)
                    ->where('distributions.status', '!=', 'dibatalkan')
                    ->sum('distribution_details.quantity');

                $remaining = max(
                    0,
                    (int) $requestItem->quantity - (int) $alreadyAllocated
                );

                return [
                    'aid_item_id' => $aidItem->id,
                    'name' => $aidItem->name,
                    'quantity' => (int) $requestItem->quantity,
                    'unit' => $requestItem->unit,
                    'priority' => $requestItem->priority,
                    'already_allocated' => (int) $alreadyAllocated,
                    'remaining_need' => $remaining,
                ];
            })->filter()->values();

            // Kompatibilitas untuk permintaan lama yang belum memiliki item terpisah.
            if ($items->isEmpty()) {
                $legacyItem = AidItem::whereRaw(
                    'LOWER(name) = LOWER(?)',
                    [$request->item_name]
                )->first();

                if ($legacyItem) {
                    $alreadyAllocated = DB::table('distribution_details')
                        ->join(
                            'distributions',
                            'distributions.id',
                            '=',
                            'distribution_details.distribution_id'
                        )
                        ->where('distributions.aid_request_id', $request->id)
                        ->where('distribution_details.aid_item_id', $legacyItem->id)
                        ->where('distributions.status', '!=', 'dibatalkan')
                        ->sum('distribution_details.quantity');

                    $items = collect([[
                        'aid_item_id' => $legacyItem->id,
                        'name' => $legacyItem->name,
                        'quantity' => (int) $request->quantity,
                        'unit' => $request->unit,
                        'priority' => $request->priority,
                        'already_allocated' => (int) $alreadyAllocated,
                        'remaining_need' => max(
                            0,
                            (int) $request->quantity - (int) $alreadyAllocated
                        ),
                    ]]);
                }
            }

            return [
                'id' => $request->id,
                'status' => $request->status,
                'latitude' => $location?->latitude,
                'longitude' => $location?->longitude,
                'items' => $items->values(),
            ];
        })->values();

        // Hanya tampilkan permintaan yang masih mempunyai kebutuhan.
        $aidRequests = $aidRequests->filter(function ($request) use ($requestsData) {
            $data = $requestsData->firstWhere('id', $request->id);

            return $data &&
                collect($data['items'])->contains(function ($item) {
                    return $item['remaining_need'] > 0;
                });
        })->values();

        $requestsData = $requestsData
            ->whereIn('id', $aidRequests->pluck('id'))
            ->values();

        return view('distributions.create', compact(
            'aidRequests',
            'warehouses',
            'stocks',
            'requestsData'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'aid_request_id' => 'required|exists:aid_requests,id',
            'allocations' => 'required|array|min:1',
            'allocations.*' => 'required|array|min:1',
            'allocations.*.*' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {

            $aidRequest = AidRequest::whereKey(
                $validated['aid_request_id']
            )->lockForUpdate()->firstOrFail();

            if (!in_array($aidRequest->status, ['disetujui', 'diproses'])) {
                throw ValidationException::withMessages([
                    'aid_request_id' => 'Permintaan bantuan belum disetujui atau tidak dapat diproses.',
                ]);
            }

            $requestItems = $aidRequest->items()
                ->with('aidItem')
                ->get();

            // Kompatibilitas untuk permintaan lama.
            if ($requestItems->isEmpty()) {
                $legacyItem = AidItem::whereRaw(
                    'LOWER(name) = LOWER(?)',
                    [$aidRequest->item_name]
                )->first();

                if (!$legacyItem) {
                    throw ValidationException::withMessages([
                        'allocations' => 'Barang pada permintaan lama belum terdaftar di master barang.',
                    ]);
                }

                $requestItems = collect([(object) [
                    'aid_request_id' => $aidRequest->id,
                    'aid_item_id' => $legacyItem->id,
                    'quantity' => $aidRequest->quantity,
                    'unit' => $aidRequest->unit,
                    'aidItem' => $legacyItem,
                ]]);
            }

            $itemsById = $requestItems->keyBy('aid_item_id');
            $allocations = $validated['allocations'];

            // Validasi barang yang dialokasikan.
            foreach ($allocations as $aidItemId => $warehouseAllocations) {
                if (!$itemsById->has((int) $aidItemId)) {
                    throw ValidationException::withMessages([
                        'allocations' => 'Barang yang dipilih tidak termasuk dalam permintaan bantuan.',
                    ]);
                }

                if (!is_array($warehouseAllocations)) {
                    throw ValidationException::withMessages([
                        'allocations' => 'Format alokasi gudang tidak valid.',
                    ]);
                }
            }

            // Hitung sisa kebutuhan.
            $remainingByItem = [];

            foreach ($requestItems as $requestItem) {

                $alreadyAllocated = DB::table('distribution_details')
                    ->join(
                        'distributions',
                        'distributions.id',
                        '=',
                        'distribution_details.distribution_id'
                    )
                    ->where('distributions.aid_request_id', $aidRequest->id)
                    ->where('distribution_details.aid_item_id', $requestItem->aid_item_id)
                    ->where('distributions.status', '!=', 'dibatalkan')
                    ->sum('distribution_details.quantity');

                $remainingByItem[$requestItem->aid_item_id] = max(
                    0,
                    (int) $requestItem->quantity - (int) $alreadyAllocated
                );
            }

            // Validasi jumlah alokasi.
            foreach ($allocations as $aidItemId => $warehouseAllocations) {

                $totalAllocation = collect($warehouseAllocations)
                    ->sum(function ($quantity) {
                        return (int) $quantity;
                    });

                if ($totalAllocation > ($remainingByItem[(int) $aidItemId] ?? 0)) {
                    throw ValidationException::withMessages([
                        'allocations' => 'Total alokasi barang melebihi sisa kebutuhan.',
                    ]);
                }

                if ($totalAllocation < 1) {
                    throw ValidationException::withMessages([
                        'allocations' => 'Jumlah alokasi harus lebih dari nol.',
                    ]);
                }
            }

            // Kelompokkan alokasi berdasarkan gudang.
            $warehouseAllocations = [];

            foreach ($allocations as $aidItemId => $items) {
                foreach ($items as $warehouseId => $quantity) {

                    $quantity = (int) $quantity;

                    if ($quantity < 1) {
                        continue;
                    }

                    $warehouseAllocations[$warehouseId][$aidItemId] = $quantity;
                }
            }

            // Buat distribusi untuk setiap gudang.
            foreach ($warehouseAllocations as $warehouseId => $items) {

                $warehouse = Warehouse::whereKey($warehouseId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$warehouse->is_active) {
                    throw ValidationException::withMessages([
                        'allocations' => 'Gudang ' . $warehouse->name . ' tidak aktif.',
                    ]);
                }

                $distribution = Distribution::create([
                    'aid_request_id' => $aidRequest->id,
                    'warehouse_id' => $warehouse->id,
                    'assigned_by' => Auth::id(),
                    'status' => 'dialokasikan',
                    'tracking_code' => 'RSQ-' . strtoupper(uniqid()),
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($items as $aidItemId => $quantity) {

                    $stock = AidStock::where('warehouse_id', $warehouse->id)
                        ->where('aid_item_id', $aidItemId)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw ValidationException::withMessages([
                            'allocations' => 'Barang tidak tersedia di gudang ' . $warehouse->name . '.',
                        ]);
                    }

                    $availableStock = $stock->quantity
                        - $stock->reserved_quantity;

                    if ($availableStock < $quantity) {
                        throw ValidationException::withMessages([
                            'allocations' => 'Stok barang di gudang ' . $warehouse->name . ' tidak mencukupi.',
                        ]);
                    }

                    // Reservasi stok.
                    $stock->reserved_quantity += $quantity;
                    $stock->save();

                    $distribution->details()->create([
                        'aid_item_id' => $aidItemId,
                        'quantity' => $quantity,
                    ]);
                }

                DistributionActivity::create([
                    'distribution_id' => $distribution->id,
                    'user_id' => Auth::id(),
                    'activity' => 'Distribusi dibuat',
                    'old_status' => null,
                    'new_status' => 'dialokasikan',
                    'description' => 'Bantuan dari gudang ' . $warehouse->name . ' berhasil dialokasikan.',
                ]);
            }

            $aidRequest->update([
                'status' => 'diproses',
            ]);
        });

        return redirect()
            ->route('distributions.index')
            ->with('success', 'Distribusi multi-barang dan multi-gudang berhasil dibuat.');
    }

    public function show(Distribution $distribution)
    {
        $distribution->load([
            'aidRequest.disasterReport.location',
            'aidRequest.disasterReport.user',
            'warehouse',
            'assignedBy',
            'details.aidItem',
            'activities.user'
        ]);

        if (
            Auth::user()->role === 'masyarakat' &&
            $distribution->aidRequest->disasterReport->user_id !== Auth::id()
        ) {
            abort(403, 'Anda tidak memiliki akses ke distribusi ini.');
        }

        return view('distributions.show', compact('distribution'));
    }

    public function updateStatus(
        Request $request,
        Distribution $distribution
    ) {
        $validated = $request->validate([
            'status' => 'required|in:dialokasikan,dikirim,diterima,dibatalkan',
        ]);

        DB::transaction(function () use ($distribution, $validated) {

            $currentDistribution = Distribution::whereKey(
                $distribution->id
            )->lockForUpdate()->firstOrFail();

            $oldStatus = $currentDistribution->status;
            $newStatus = $validated['status'];

            $allowedTransitions = [
                'dialokasikan' => ['dikirim', 'dibatalkan'],
                'dikirim' => ['diterima'],
                'diterima' => [],
                'dibatalkan' => [],
            ];

            if (
                !isset($allowedTransitions[$oldStatus]) ||
                !in_array($newStatus, $allowedTransitions[$oldStatus])
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status distribusi tidak diperbolehkan.',
                ]);
            }

            $details = $currentDistribution->details()->get();

            // Jika bantuan dikirim, stok fisik dikurangi.
            if ($newStatus === 'dikirim') {

                foreach ($details as $detail) {

                    $stock = AidStock::where(
                        'warehouse_id',
                        $currentDistribution->warehouse_id
                    )
                        ->where('aid_item_id', $detail->aid_item_id)
                        ->lockForUpdate()
                        ->first();

                    if (
                        !$stock ||
                        $stock->quantity < $detail->quantity ||
                        $stock->reserved_quantity < $detail->quantity
                    ) {
                        throw ValidationException::withMessages([
                            'status' => 'Stok tidak mencukupi atau data stok tidak sesuai.',
                        ]);
                    }

                    $stock->quantity -= $detail->quantity;
                    $stock->reserved_quantity -= $detail->quantity;
                    $stock->save();
                }

                $currentDistribution->shipped_at = now();
            }

            // Jika bantuan diterima, catat waktu penerimaan.
            if ($newStatus === 'diterima') {
                $currentDistribution->received_at = now();
            }

            // Jika dibatalkan, lepaskan stok reservasi.
            if ($newStatus === 'dibatalkan') {

                foreach ($details as $detail) {

                    $stock = AidStock::where(
                        'warehouse_id',
                        $currentDistribution->warehouse_id
                    )
                        ->where('aid_item_id', $detail->aid_item_id)
                        ->lockForUpdate()
                        ->first();

                    if (
                        !$stock ||
                        $stock->reserved_quantity < $detail->quantity
                    ) {
                        throw ValidationException::withMessages([
                            'status' => 'Stok pesanan tidak ditemukan atau jumlahnya tidak sesuai.',
                        ]);
                    }

                    $stock->reserved_quantity -= $detail->quantity;
                    $stock->save();
                }
            }

            $currentDistribution->status = $newStatus;
            $currentDistribution->save();

            $activityDescription = match ($newStatus) {
                'dikirim' => 'Bantuan telah diberangkatkan menuju lokasi tujuan.',
                'diterima' => 'Bantuan telah diterima di lokasi tujuan.',
                'dibatalkan' => 'Distribusi bantuan telah dibatalkan.',
                default => 'Status distribusi diperbarui.',
            };

            DistributionActivity::create([
                'distribution_id' => $currentDistribution->id,
                'user_id' => Auth::id(),
                'activity' => 'Status distribusi diperbarui',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'description' => $activityDescription,
            ]);

            $aidRequest = AidRequest::with([
                'disasterReport.user',
                'items.aidItem'
            ])
                ->whereKey($currentDistribution->aid_request_id)
                ->lockForUpdate()
                ->firstOrFail();

            $distributions = Distribution::where(
                'aid_request_id',
                $aidRequest->id
            )->get();

            // Distribusi yang dibatalkan tidak dihitung sebagai distribusi aktif.
          // Ambil seluruh distribusi untuk permintaan ini.
$distributions = Distribution::with('details')
    ->where('aid_request_id', $aidRequest->id)
    ->get();

// Distribusi yang dibatalkan tidak dihitung.
$activeDistributions = $distributions->where(
    'status',
    '!=',
    'dibatalkan'
);

// Hitung jumlah barang yang benar-benar sudah diterima.
$receivedQuantities = [];

foreach ($activeDistributions->where('status', 'diterima') as $dist) {
    foreach ($dist->details as $detail) {
        $receivedQuantities[$detail->aid_item_id] =
            ($receivedQuantities[$detail->aid_item_id] ?? 0)
            + (int) $detail->quantity;
    }
}

// Ambil kebutuhan setiap barang.
$requestItems = $aidRequest->items;

if ($requestItems->isEmpty()) {
    // Kompatibilitas permintaan lama.
    $legacyItem = AidItem::whereRaw(
        'LOWER(name) = LOWER(?)',
        [$aidRequest->item_name]
    )->first();

    if ($legacyItem) {
        $requestItems = collect([(object) [
            'aid_item_id' => $legacyItem->id,
            'quantity' => $aidRequest->quantity,
        ]]);
    }
}

// Periksa apakah seluruh kebutuhan sudah diterima.
$allFulfilled = $requestItems->isNotEmpty();

foreach ($requestItems as $requestItem) {
    $received = $receivedQuantities[$requestItem->aid_item_id] ?? 0;

    if ($received < (int) $requestItem->quantity) {
        $allFulfilled = false;
        break;
    }
}

// Perbarui status permintaan berdasarkan kondisi sebenarnya.
if ($allFulfilled) {
    $aidRequest->update([
        'status' => 'terpenuhi',
    ]);
} elseif ($activeDistributions->isNotEmpty()) {
    $aidRequest->update([
        'status' => 'diproses',
    ]);
} else {
    $aidRequest->update([
        'status' => 'disetujui',
    ]);
}
            // Kirim notifikasi kepada pelapor.
            $reporter = $aidRequest->disasterReport?->user;

            if ($reporter) {

                $statusLabels = [
                    'dikirim' => 'Dikirim',
                    'diterima' => 'Diterima',
                    'dibatalkan' => 'Dibatalkan',
                ];

                $statusLabel = $statusLabels[$newStatus] ?? ucfirst($newStatus);

                $itemNames = $details->map(function ($detail) {
                    return $detail->aidItem?->name;
                })->filter()->implode(', ');

                $reporter->notify(new ResqnetNotification(
                    'Pembaruan Distribusi Bantuan',
                    'Distribusi ' . ($itemNames ?: 'bantuan') .
                    ' sekarang berstatus ' . $statusLabel . '.',
                    route('distributions.show', $currentDistribution->id),
                    $newStatus === 'dibatalkan' ? 'warning' : 'success'
                ));
            }
        });

        return back()->with(
            'success',
            'Status distribusi berhasil diperbarui, aktivitas dicatat, dan notifikasi dikirim.'
        );
    }
}
