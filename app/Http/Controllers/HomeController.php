<?php

namespace App\Http\Controllers;

use App\Models\AidRequest;
use App\Models\AidStock;
use App\Models\DisasterReport;
use App\Models\Distribution;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Throwable;

/*
|--------------------------------------------------------------------------
| HomeController — landing page RESQNET
|--------------------------------------------------------------------------
| Landing page dapat dibuka siapa saja (tanpa login), jadi data yang
| ditampilkan HANYA berupa angka ringkasan (agregat). Nama pelapor,
| judul laporan, dan lokasi detail tidak ditampilkan di sini.
*/

class HomeController extends Controller
{
    public function index()
    {
        $stats = $this->defaultStats();

        try {
            $totalStock = (int) AidStock::sum('quantity');
            $reservedStock = (int) AidStock::sum('reserved_quantity');

            $stats = [
                'verified_reports' => DisasterReport::where('status', 'terverifikasi')->count(),
                'total_reports' => DisasterReport::count(),
                'aid_requests' => AidRequest::count(),
                'pending_requests' => AidRequest::where('status', 'menunggu')->count(),
                'available_stock' => max(0, $totalStock - $reservedStock),
                'active_warehouses' => Warehouse::where('is_active', true)->count(),
                'distributions' => Distribution::count(),
                'allocated' => Distribution::where('status', 'dialokasikan')->count(),
                'shipped' => Distribution::where('status', 'dikirim')->count(),
                'received' => Distribution::where('status', 'diterima')->count(),

                // Jumlah laporan terverifikasi per kategori (maksimal 3 teratas)
                'categories' => DisasterReport::query()
                    ->join('disaster_categories', 'disaster_categories.id', '=', 'disaster_reports.category_id')
                    ->where('disaster_reports.status', 'terverifikasi')
                    ->select('disaster_categories.name', DB::raw('COUNT(*) as total'))
                    ->groupBy('disaster_categories.name')
                    ->orderByDesc('total')
                    ->limit(3)
                    ->get()
                    ->map(fn ($row) => ['name' => $row->name, 'total' => (int) $row->total])
                    ->values()
                    ->all(),
            ];
        } catch (Throwable $e) {
            // Jika database belum siap, landing page tetap tampil dengan angka 0.
            report($e);
        }

        return view('home', compact('stats'));
    }

    private function defaultStats(): array
    {
        return [
            'verified_reports' => 0,
            'total_reports' => 0,
            'aid_requests' => 0,
            'pending_requests' => 0,
            'available_stock' => 0,
            'active_warehouses' => 0,
            'distributions' => 0,
            'allocated' => 0,
            'shipped' => 0,
            'received' => 0,
            'categories' => [],
        ];
    }
}
