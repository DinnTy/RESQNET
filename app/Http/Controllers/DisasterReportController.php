<?php

namespace App\Http\Controllers;

use App\Models\DisasterReport;
use App\Models\DisasterCategory;
use App\Notifications\ResqnetNotification;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DisasterReportController extends Controller
{
    public function index()
{
    $user = Auth::user();

    $query = DisasterReport::with([
        'user',
        'category',
        'location'
    ]);

    // Masyarakat hanya melihat laporan miliknya
    if ($user->role === 'masyarakat') {
        $query->where('user_id', $user->id);
    }

    $reports = $query->latest()->paginate(10);

    return view('reports.index', compact('reports'));
}

    public function create()
    {
        $categories = DisasterCategory::all();

        return view('reports.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:disaster_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:rendah,sedang,tinggi,kritis',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'evidence' => 'nullable|image|max:2048',
            'occurred_at' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $validated) {

            $location = Location::create([
                'name' => $validated['name'],
                'address' => $validated['address'] ?? null,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'district' => $validated['district'] ?? null,
                'city' => $validated['city'] ?? null,
                'province' => $validated['province'] ?? null,
            ]);

            $evidence = null;

            if ($request->hasFile('evidence')) {
                $evidence = $request->file('evidence')
                    ->store('reports', 'public');
            }

            DisasterReport::create([
                'user_id' => Auth::id(),
                'category_id' => $validated['category_id'],
                'location_id' => $location->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'severity' => $validated['severity'],
                'status' => 'menunggu_verifikasi',
                'evidence' => $evidence,
                'occurred_at' => $validated['occurred_at'] ?? null,
            ]);
        });

        return redirect()->route('reports.index')
            ->with('success', 'Laporan bencana berhasil dikirim.');
    }

    public function show(DisasterReport $report)
    {
        // Masyarakat hanya boleh membuka laporan miliknya
        if (
            Auth::user()->role === 'masyarakat' &&
            $report->user_id !== Auth::id()
        ) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        $report->load([
            'user',
            'category',
            'location',
            'aidRequests'
        ]);

        return view('reports.show', compact('report'));
    }

    
public function updateStatus(Request $request, DisasterReport $report)
{
    $validated = $request->validate([
        'status' => 'required|in:terverifikasi,ditolak',
    ]);

    if ($report->status !== 'menunggu_verifikasi') {
        return back()->with(
            'error',
            'Laporan ini sudah pernah diproses dan tidak dapat diverifikasi kembali.'
        );
    }

    $newStatus = $validated['status'];

    $report->update([
        'status' => $newStatus,
    ]);

    $reporter = $report->user;

    if ($reporter) {
        $statusLabel = $newStatus === 'terverifikasi'
            ? 'Terverifikasi'
            : 'Ditolak';

        $reporter->notify(new ResqnetNotification(
            'Pembaruan Status Laporan',
            'Laporan "' . $report->title . '" sekarang berstatus ' . $statusLabel . '.',
            route('reports.show', $report->id),
            $newStatus === 'ditolak' ? 'warning' : 'success'
        ));
    }

    return back()->with(
        'success',
        'Status laporan berhasil diperbarui dan notifikasi telah dikirim kepada pelapor.'
    );
}
}