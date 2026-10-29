<?php

namespace App\Http\Controllers;

use App\Models\DisasterReport;
use App\Models\AidRequest;
use App\Models\Warehouse;
use App\Models\Distribution;
use App\Models\AidStock;
use App\Models\DisasterCategory;
use App\Models\AidItem;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Dashboard khusus masyarakat
        if ($user->role === 'masyarakat') {
            $myReports = DisasterReport::where('user_id', $user->id)
                ->count();

            $myPendingReports = DisasterReport::where('user_id', $user->id)
                ->where('status', 'menunggu_verifikasi')
                ->count();

            $myAidRequests = AidRequest::whereHas('disasterReport', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count();

            $myPendingAidRequests = AidRequest::whereHas('disasterReport', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'menunggu')->count();

            $latestReports = DisasterReport::with([
                'category',
                'location'
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

            $latestAidRequests = AidRequest::with('disasterReport')
                ->whereHas('disasterReport', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard-masyarakat', compact(
                'myReports',
                'myPendingReports',
                'myAidRequests',
                'myPendingAidRequests',
                'latestReports',
                'latestAidRequests'
            ));
        }

        // Dashboard Admin dan Petugas
        $totalReports = DisasterReport::count();

        $pendingReports = DisasterReport::where(
            'status',
            'menunggu_verifikasi'
        )->count();

        $verifiedReports = DisasterReport::where(
            'status',
            'terverifikasi'
        )->count();

        $totalAidRequests = AidRequest::count();

        $pendingAidRequests = AidRequest::where(
            'status',
            'menunggu'
        )->count();

        $fulfilledAidRequests = AidRequest::where(
            'status',
            'terpenuhi'
        )->count();

        $totalWarehouses = Warehouse::count();

        $activeWarehouses = Warehouse::where(
            'is_active',
            true
        )->count();

        $totalDistributions = Distribution::count();

        $allocatedDistributions = Distribution::where(
            'status',
            'dialokasikan'
        )->count();

        $shippedDistributions = Distribution::where(
            'status',
            'dikirim'
        )->count();

        $receivedDistributions = Distribution::where(
            'status',
            'diterima'
        )->count();

        $cancelledDistributions = Distribution::where(
            'status',
            'dibatalkan'
        )->count();

        $totalStock = (int) AidStock::sum('quantity');
        $reservedStock = (int) AidStock::sum('reserved_quantity');
        $availableStock = $totalStock - $reservedStock;

        $latestReports = DisasterReport::with([
            'user',
            'category',
            'location'
        ])
        ->latest()
        ->take(5)
        ->get();

        $latestDistributions = Distribution::with([
            'aidRequest.disasterReport.location',
            'warehouse',
            'assignedBy',
            'details.aidItem'
        ])
        ->latest()
        ->take(5)
        ->get();

        $reportChart = DisasterCategory::withCount('reports')
            ->get()
            ->filter(function ($category) {
                return $category->reports_count > 0;
            });

        $reportChartLabels = $reportChart->pluck('name')->values();
        $reportChartValues = $reportChart->pluck('reports_count')->values();

        $distributionChartLabels = [
            'Dialokasikan',
            'Dikirim',
            'Diterima',
            'Dibatalkan'
        ];

        $distributionChartValues = [
            $allocatedDistributions,
            $shippedDistributions,
            $receivedDistributions,
            $cancelledDistributions
        ];

        $stockChart = AidItem::withSum('stocks', 'quantity')
            ->get()
            ->filter(function ($item) {
                return ($item->stocks_sum_quantity ?? 0) > 0;
            });

        $stockChartLabels = $stockChart->pluck('name')->values();

        $stockChartValues = $stockChart
            ->pluck('stocks_sum_quantity')
            ->map(function ($value) {
                return (int) $value;
            })
            ->values();

        return view('dashboard', compact(
            'totalReports',
            'pendingReports',
            'verifiedReports',
            'totalAidRequests',
            'pendingAidRequests',
            'fulfilledAidRequests',
            'totalWarehouses',
            'activeWarehouses',
            'totalDistributions',
            'allocatedDistributions',
            'shippedDistributions',
            'receivedDistributions',
            'cancelledDistributions',
            'totalStock',
            'reservedStock',
            'availableStock',
            'latestReports',
            'latestDistributions',
            'reportChartLabels',
            'reportChartValues',
            'distributionChartLabels',
            'distributionChartValues',
            'stockChartLabels',
            'stockChartValues'
        ));
    }
}
