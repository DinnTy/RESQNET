<?php

namespace App\Http\Controllers;

use App\Models\DisasterReport;
use App\Models\Warehouse;

class MapController extends Controller
{
    public function index()
    {
        $reports = DisasterReport::with([
            'category',
            'location',
            'aidRequests.items.aidItem',
            'aidRequests.distributions.details.aidItem',
        ])
            ->where('status', 'terverifikasi')
            ->latest()
            ->get();

        $warehouses = Warehouse::with('stocks.aidItem')
            ->where('is_active', true)
            ->get();

        // Menyiapkan data gudang untuk peta.
        $warehouseData = $warehouses->map(function ($warehouse) {
            $stocks = $warehouse->stocks->map(function ($stock) {
                $available = max(
                    0,
                    (int) $stock->quantity - (int) $stock->reserved_quantity
                );

                return [
                    'id' => $stock->id,
                    'aid_item_id' => $stock->aid_item_id,
                    'item_name' => $stock->aidItem->name ?? '-',
                    'category' => $stock->aidItem->category ?? '-',
                    'unit' => $stock->aidItem->unit ?? 'unit',
                    'quantity' => (int) $stock->quantity,
                    'reserved_quantity' => (int) $stock->reserved_quantity,
                    'available_quantity' => $available,
                ];
            });

            return [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'address' => $warehouse->address,
                'phone' => $warehouse->phone,
                'manager_name' => $warehouse->manager_name,
                'district' => $warehouse->district,
                'city' => $warehouse->city,
                'province' => $warehouse->province,
                'latitude' => $warehouse->latitude,
                'longitude' => $warehouse->longitude,
                'stocks' => $stocks->values()->all(),
            ];
        });

        // Menyiapkan laporan dan kebutuhan bantuan.
        $reportData = $reports->map(function ($report) use ($warehouseData) {
            $location = $report->location;

            $requestData = $report->aidRequests->flatMap(function ($request) use (
                $warehouseData,
                $location
            ) {
                // Mendukung permintaan multi-barang dan data legacy.
                $requestItems = $request->items->isNotEmpty()
                    ? $request->items
                    : collect([$request]);

                return $requestItems->map(function ($item) use (
                    $request,
                    $warehouseData,
                    $location
                ) {
                    $aidItemId = $item->aid_item_id ?? null;

                    $itemName = $item->aidItem->name
                        ?? $item->item_name
                        ?? '-';

                    $quantity = (int) ($item->quantity ?? 0);

                    $unit = $item->unit
                        ?? $item->aidItem->unit
                        ?? 'unit';

                    $priority = $item->priority
                        ?? $request->priority
                        ?? 'sedang';

                    // Menghitung jumlah yang sudah dialokasikan.
                    $allocated = 0;

                    foreach ($request->distributions as $distribution) {
                        if ($distribution->status === 'dibatalkan') {
                            continue;
                        }

                        foreach ($distribution->details as $detail) {
                            $sameItem = $aidItemId
                                ? (int) $detail->aid_item_id === (int) $aidItemId
                                : strcasecmp(
                                    $detail->aidItem->name ?? '',
                                    $itemName
                                ) === 0;

                            if ($sameItem) {
                                $allocated += (int) $detail->quantity;
                            }
                        }
                    }

                    $remaining = max(0, $quantity - $allocated);

                    /*
                     * Semua gudang aktif dengan koordinat valid
                     * dapat digunakan untuk melihat rute.
                     * Stok hanya menentukan status rekomendasi.
                     */
                    $recommendations = $warehouseData
                        ->filter(function ($warehouse) {
                            return is_numeric($warehouse['latitude'])
                                && is_numeric($warehouse['longitude']);
                        })
                        ->map(function ($warehouse) use (
                            $aidItemId,
                            $itemName,
                            $remaining,
                            $location,
                            $unit
                        ) {
                            $stock = collect($warehouse['stocks'])
                                ->first(function ($stock) use (
                                    $aidItemId,
                                    $itemName
                                ) {
                                    if ($aidItemId) {
                                        return (int) $stock['aid_item_id']
                                            === (int) $aidItemId;
                                    }

                                    return strcasecmp(
                                        $stock['item_name'],
                                        $itemName
                                    ) === 0;
                                });

                            $available = (int) (
                                $stock['available_quantity'] ?? 0
                            );

                            $distance = null;

                            if (
                                $location
                                && is_numeric($location->latitude)
                                && is_numeric($location->longitude)
                            ) {
                                $distance = $this->calculateDistance(
                                    (float) $location->latitude,
                                    (float) $location->longitude,
                                    (float) $warehouse['latitude'],
                                    (float) $warehouse['longitude']
                                );
                            }

                            return [
                                'warehouse_id' => $warehouse['id'],
                                'warehouse_name' => $warehouse['name'],
                                'warehouse_address' => $warehouse['address'],

                                'warehouse_latitude' => $warehouse['latitude'],
                                'warehouse_longitude' => $warehouse['longitude'],

                                'item_name' => $itemName,
                                'available_stock' => $available,

                                'recommended_quantity' => min(
                                    $available,
                                    $remaining
                                ),

                                'unit' => $stock['unit'] ?? $unit,

                                'distance_km' => $distance !== null
                                    ? round($distance, 2)
                                    : null,

                                // Status ketersediaan stok.
                                'has_stock' => $available > 0,

                                'can_fulfill' => $available >= $remaining
                                    && $remaining > 0,
                            ];
                        })
                        ->sortBy(function ($recommendation) {
                            return $recommendation['distance_km']
                                ?? PHP_FLOAT_MAX;
                        })
                        ->values();

                    return [
                        'aid_item_id' => $aidItemId,
                        'item_name' => $itemName,
                        'quantity' => $quantity,
                        'allocated_quantity' => $allocated,
                        'remaining_quantity' => $remaining,
                        'unit' => $unit,
                        'priority' => $priority,
                        'status' => $request->status,
                        'recommendations' => $recommendations->all(),
                    ];
                });
            });

            return [
                'id' => $report->id,
                'title' => $report->title,
                'severity' => $report->severity,
                'category' => $report->category->name ?? '-',
                'location' => $location->name ?? '-',
                'address' => $location->address ?? '-',
                'district' => $location->district ?? '-',
                'city' => $location->city ?? '-',
                'province' => $location->province ?? '-',
                'latitude' => $location->latitude ?? null,
                'longitude' => $location->longitude ?? null,
                'aid_requests' => $requestData->values()->all(),
            ];
        });

        return view('map.index', [
            'reportData' => $reportData,
            'warehouseData' => $warehouseData,
            'reports' => $reports,
            'warehouses' => $warehouses,
            'totalReports' => $reports->count(),
            'totalWarehouses' => $warehouses->count(),
        ]);
    }

    /**
     * Menghitung jarak garis lurus menggunakan rumus Haversine.
     */
    private function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * sin($dLon / 2) ** 2;

        $a = max(0, min(1, $a));

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
