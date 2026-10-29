<?php

namespace App\Http\Controllers;

use App\Models\AidRequest;
use App\Models\AidRequestItem;
use App\Models\DisasterReport;
use App\Models\AidItem;
use App\Models\AidStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AidRequestController extends Controller
{
    // Menampilkan daftar permintaan bantuan
    public function index()
    {
        $user = Auth::user();

        $query = AidRequest::with([
            'disasterReport.user',
            'disasterReport.location',
            'disasterReport.category',
            'items.aidItem',
        ]);

        if ($user->role === 'masyarakat') {
            $query->whereHas('disasterReport', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $aidRequests = $query->latest()->paginate(10);

        return view('aid-requests.index', compact('aidRequests'));
    }

    // Menampilkan form permintaan bantuan
    public function create(Request $request)
    {
        $user = Auth::user();

        $query = DisasterReport::where('status', 'terverifikasi')
            ->with(['location', 'category']);

        if ($user->role === 'masyarakat') {
            $query->where('user_id', $user->id);
        }

        $reports = $query->latest()->get();

        $selectedReportId = $request->query('report_id');

        if ($selectedReportId) {
            $selectedReport = $reports->firstWhere(
                'id',
                (int) $selectedReportId
            );

            $selectedReportId = $selectedReport
                ? $selectedReport->id
                : null;
        }

        $aidItems = AidItem::orderBy('name')->get();

        return view('aid-requests.create', compact(
            'reports',
            'selectedReportId',
            'aidItems'
        ));
    }

    // Menyimpan satu permintaan dengan beberapa jenis barang
    public function store(Request $request)
    {
        $validated = $request->validate([
            'disaster_report_id' => 'required|exists:disaster_reports,id',
            'items' => 'required|array|min:1',
            'items.*.aid_item_id' => 'required|integer|distinct|exists:aid_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.priority' => 'required|in:rendah,sedang,tinggi,kritis',
            'notes' => 'nullable|string',
        ], [
            'items.required' => 'Tambahkan minimal satu jenis barang.',
            'items.min' => 'Minimal harus ada satu jenis barang.',
            'items.*.aid_item_id.required' => 'Pilih nama barang untuk setiap baris.',
            'items.*.aid_item_id.distinct' => 'Barang yang sama tidak boleh dipilih lebih dari satu kali.',
            'items.*.quantity.required' => 'Jumlah barang wajib diisi.',
            'items.*.quantity.min' => 'Jumlah barang minimal 1.',
        ]);

        $report = DisasterReport::findOrFail(
            $validated['disaster_report_id']
        );

        if ($report->status !== 'terverifikasi') {
            abort(403, 'Laporan belum terverifikasi.');
        }

        if (
            Auth::user()->role === 'masyarakat' &&
            $report->user_id !== Auth::id()
        ) {
            abort(403, 'Anda tidak memiliki akses ke laporan tersebut.');
        }

        DB::transaction(function () use ($validated) {
            $firstItem = $validated['items'][0];

            $firstAidItem = AidItem::findOrFail(
                $firstItem['aid_item_id']
            );

            $aidRequest = AidRequest::create([
                'disaster_report_id' => $validated['disaster_report_id'],
                'item_name' => $firstAidItem->name,
                'quantity' => $firstItem['quantity'],
                'unit' => $firstItem['unit'],
                'priority' => $firstItem['priority'],
                'status' => 'menunggu',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                AidRequestItem::create([
                    'aid_request_id' => $aidRequest->id,
                    'aid_item_id' => $item['aid_item_id'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'priority' => $item['priority'],
                ]);
            }
        });

        return redirect()
            ->route('aid-requests.index')
            ->with('success', 'Permintaan bantuan berhasil dibuat.');
    }

    // Menghitung jarak menggunakan rumus Haversine
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

        $latDifference = deg2rad($lat2 - $lat1);
        $lonDifference = deg2rad($lon2 - $lon1);

        $a = sin($latDifference / 2) ** 2
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * sin($lonDifference / 2) ** 2;

        $a = min(1, max(0, $a));

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    // Menampilkan detail dan rekomendasi geospasial setiap barang
    public function show(AidRequest $aidRequest)
    {
        $aidRequest->load([
            'disasterReport.user',
            'disasterReport.location',
            'disasterReport.category',
            'distributions',
            'items.aidItem',
        ]);

        if (
            Auth::user()->role === 'masyarakat' &&
            $aidRequest->disasterReport->user_id !== Auth::id()
        ) {
            abort(403, 'Anda tidak memiliki akses ke permintaan ini.');
        }

        $location = $aidRequest->disasterReport->location;

        $hasCoordinates = $location
            && is_numeric($location->latitude)
            && is_numeric($location->longitude);

        $requestItems = $aidRequest->items;

        // Mendukung data permintaan lama
        $itemsToMatch = collect();

        if ($requestItems->isNotEmpty()) {
            $itemsToMatch = $requestItems;
        } else {
            $legacyAidItem = AidItem::whereRaw(
                'LOWER(name) = ?',
                [strtolower(trim($aidRequest->item_name))]
            )->first();

            $itemsToMatch = collect([
                (object) [
                    'id' => null,
                    'aid_item_id' => $legacyAidItem?->id,
                    'aidItem' => $legacyAidItem,
                    'quantity' => $aidRequest->quantity,
                    'unit' => $aidRequest->unit,
                    'priority' => $aidRequest->priority,
                    'notes' => $aidRequest->notes,
                ],
            ]);
        }

        // Menyiapkan hasil pencocokan setiap barang
        $itemMatchingPlans = collect();

        foreach ($itemsToMatch as $requestItem) {
            $aidItem = $requestItem->aidItem;

            $requiredQuantity = (int) $requestItem->quantity;

            $matchingStocks = collect();
            $allocationPlan = collect();

            $totalAvailable = 0;
            $remainingNeed = $requiredQuantity;

            if ($aidItem) {
                $matchingStocks = AidStock::with('warehouse')
                    ->where('aid_item_id', $aidItem->id)
                    ->get()
                    ->filter(function ($stock) {
                        return $stock->warehouse
                            && $stock->warehouse->is_active
                            && (
                                (int) $stock->quantity -
                                (int) $stock->reserved_quantity
                            ) > 0;
                    });

                // Menghitung stok dan jarak setiap gudang
                $matchingStocks = $matchingStocks->map(function ($stock) use (
                    $location,
                    $hasCoordinates
                ) {
                    $warehouse = $stock->warehouse;

                    $available = (int) $stock->quantity
                        - (int) $stock->reserved_quantity;

                    $distance = null;

                    if (
                        $hasCoordinates &&
                        is_numeric($warehouse->latitude) &&
                        is_numeric($warehouse->longitude)
                    ) {
                        $distance = $this->calculateDistance(
                            (float) $location->latitude,
                            (float) $location->longitude,
                            (float) $warehouse->latitude,
                            (float) $warehouse->longitude
                        );
                    }

                    $stock->available_stock = $available;
                    $stock->distance_km = $distance;

                    return $stock;
                });

                // Mengurutkan gudang berdasarkan jarak
                if (
                    $hasCoordinates &&
                    $matchingStocks->contains(function ($stock) {
                        return $stock->distance_km !== null;
                    })
                ) {
                    $matchingStocks = $matchingStocks
                        ->sort(function ($a, $b) {
                            if ($a->distance_km === null) {
                                return 1;
                            }

                            if ($b->distance_km === null) {
                                return -1;
                            }

                            $comparison = $a->distance_km
                                <=> $b->distance_km;

                            if ($comparison === 0) {
                                return $b->available_stock
                                    <=> $a->available_stock;
                            }

                            return $comparison;
                        })
                        ->values();
                } else {
                    $matchingStocks = $matchingStocks
                        ->sortByDesc('available_stock')
                        ->values();
                }

                $totalAvailable = (int) $matchingStocks->sum(
                    'available_stock'
                );

                // Membuat rekomendasi alokasi dari beberapa gudang
                foreach ($matchingStocks as $stock) {
                    if ($remainingNeed <= 0) {
                        break;
                    }

                    $available = (int) $stock->available_stock;

                    $recommendedQuantity = min(
                        $available,
                        $remainingNeed
                    );

                    $distance = $stock->distance_km;

                    if ($distance !== null) {
                        $reason = 'Gudang berjarak '
                            . number_format($distance, 2)
                            . ' km dari lokasi bencana dan memiliki '
                            . number_format($available)
                            . ' stok tersedia.';
                    } else {
                        $reason = 'Direkomendasikan berdasarkan jumlah '
                            . 'stok tersedia karena koordinat belum lengkap.';
                    }

                    $allocationPlan->push([
                        'warehouse' => $stock->warehouse->name,
                        'available' => $available,
                        'recommended_quantity' => $recommendedQuantity,
                        'distance_km' => $distance,
                        'reason' => $reason,
                    ]);

                    $remainingNeed -= $recommendedQuantity;
                }
            }

            // Menghitung persentase ketersediaan stok
            $fulfillmentPercentage = 0;

            if ($requiredQuantity > 0) {
                $fulfillmentPercentage = min(
                    100,
                    round(
                        ($totalAvailable / $requiredQuantity) * 100,
                        1
                    )
                );
            }

            $isFulfilled = $remainingNeed <= 0;

            $itemMatchingPlans->push([
                'request_item' => $requestItem,
                'aid_item' => $aidItem,
                'matching_stocks' => $matchingStocks,
                'allocation_plan' => $allocationPlan,
                'required_quantity' => $requiredQuantity,
                'total_available' => $totalAvailable,
                'remaining_need' => $remainingNeed,
                'fulfillment_percentage' => $fulfillmentPercentage,
                'is_fulfilled' => $isFulfilled,
            ]);
        }

        // Variabel kompatibilitas untuk Blade lama
        $firstPlan = $itemMatchingPlans->first();

        $aidItem = $firstPlan['aid_item'] ?? null;
        $matchingStocks = $firstPlan['matching_stocks'] ?? collect();
        $allocationPlan = $firstPlan['allocation_plan'] ?? collect();

        $totalAvailable = $firstPlan['total_available'] ?? 0;
        $remainingNeed = $firstPlan['remaining_need'] ?? 0;

        $fulfillmentPercentage =
            $firstPlan['fulfillment_percentage'] ?? 0;

        $isFulfilled = $firstPlan['is_fulfilled'] ?? false;

        return view('aid-requests.show', compact(
            'aidRequest',
            'requestItems',
            'itemMatchingPlans',
            'aidItem',
            'matchingStocks',
            'allocationPlan',
            'totalAvailable',
            'remainingNeed',
            'isFulfilled',
            'fulfillmentPercentage',
            'hasCoordinates'
        ));
    }

    // Memperbarui status permintaan bantuan
    public function updateStatus(
        Request $request,
        AidRequest $aidRequest
    ) {
        $validated = $request->validate([
            'status' => 'required|in:menunggu,disetujui,diproses,terpenuhi,ditolak',
        ]);

        $aidRequest->update([
            'status' => $validated['status'],
        ]);

        return back()->with(
            'success',
            'Status permintaan bantuan berhasil diperbarui.'
        );
    }
}
