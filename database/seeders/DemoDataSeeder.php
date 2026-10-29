<?php

namespace Database\Seeders;

use App\Models\AidItem;
use App\Models\AidRequest;
use App\Models\AidRequestItem;
use App\Models\AidStock;
use App\Models\DisasterCategory;
use App\Models\DisasterReport;
use App\Models\Distribution;
use App\Models\DistributionActivity;
use App\Models\Location;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| DATA DEMO RESQNET (OPSIONAL)
|--------------------------------------------------------------------------
| Seeder ini TIDAK dijalankan otomatis.
| Jalankan hanya jika ingin mengisi database kosong untuk demo/pengujian:
|
|   php artisan db:seed --class=DemoDataSeeder
|
| Semua data memakai firstOrCreate, sehingga aman dijalankan ulang
| dan tidak menggandakan data yang sudah ada dengan nama/email yang sama.
|
| Akun demo (password: password123):
|   admin    / admin@resqnet.test     (role admin)
|   petugas  / petugas@resqnet.test   (role petugas)
|   warga    / warga@resqnet.test     (role masyarakat)
*/

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // Kategori bencana
            $categories = [];
            foreach ([
                'Banjir' => 'Genangan atau luapan air yang merendam permukiman.',
                'Tanah Longsor' => 'Pergerakan tanah atau batuan pada lereng.',
                'Gempa Bumi' => 'Guncangan akibat pergerakan lempeng bumi.',
                'Kebakaran Hutan' => 'Kebakaran pada kawasan hutan atau lahan.',
                'Tsunami' => 'Gelombang laut besar akibat gempa bawah laut.',
                'Gunung Meletus' => 'Erupsi gunung api.',
            ] as $name => $description) {
                $categories[$name] = DisasterCategory::firstOrCreate(
                    ['name' => $name],
                    ['description' => $description]
                );
            }

            // Pengguna demo
            $admin = $this->user('Administrator RESQNET', 'admin', 'admin@resqnet.test', 'admin');
            $petugas = $this->user('Petugas Lapangan', 'petugas', 'petugas@resqnet.test', 'petugas');
            $warga = $this->user('Athaya Ashalina', 'warga', 'warga@resqnet.test', 'masyarakat');

            // Gudang
            $gudangBengkulu = Warehouse::firstOrCreate(
                ['name' => 'Gudang Bantuan Bengkulu'],
                [
                    'address' => 'Jl. Soekarno Hatta, Bengkulu',
                    'phone' => '081234567890',
                    'manager_name' => 'Andi Saputra',
                    'latitude' => -3.7928,
                    'longitude' => 102.2655,
                    'district' => 'Ratu Agung',
                    'city' => 'Bengkulu',
                    'province' => 'Bengkulu',
                    'is_active' => true,
                ]
            );

            $gudangRawa = Warehouse::firstOrCreate(
                ['name' => 'Gudang Bantuan Rawa Makmur'],
                [
                    'address' => 'Kecamatan Muara Bangkahulu, Kota Bengkulu',
                    'phone' => '085664725458',
                    'manager_name' => 'Admin Gudang Rawa Makmur',
                    'latitude' => -3.7612,
                    'longitude' => 102.2754,
                    'district' => 'Muara Bangkahulu',
                    'city' => 'Bengkulu',
                    'province' => 'Bengkulu',
                    'is_active' => true,
                ]
            );

            // Barang bantuan
            $air = AidItem::firstOrCreate(['name' => 'Air Mineral'], ['category' => 'Logistik', 'unit' => 'dus']);
            $mie = AidItem::firstOrCreate(['name' => 'Mie Instan'], ['category' => 'Makanan', 'unit' => 'dus']);
            $selimut = AidItem::firstOrCreate(['name' => 'Selimut'], ['category' => 'Perlengkapan', 'unit' => 'lembar']);
            AidItem::firstOrCreate(['name' => 'Beras'], ['category' => 'Pangan', 'unit' => '50 kg']);

            // Stok
            $this->stock($gudangBengkulu, $selimut, 200);
            $this->stock($gudangBengkulu, $mie, 287);
            $this->stock($gudangBengkulu, $air, 90);
            $this->stock($gudangRawa, $mie, 600);

            // Laporan bencana
            $reportSelebar = $this->report($warga, $categories['Banjir'], 'Laporan Simulasi Banjir di Kecamatan Selebar', 'sedang', [
                'name' => 'Lokasi Simulasi Banjir', 'latitude' => -3.8296, 'longitude' => 102.3054,
                'district' => 'Selebar', 'city' => 'Bengkulu', 'province' => 'Bengkulu',
            ]);

            $reportLongsor = $this->report($warga, $categories['Tanah Longsor'], 'Tanah Longsor Liku Sembilan Kepahiang', 'tinggi', [
                'name' => 'Liku Sembilan', 'latitude' => -3.6512, 'longitude' => 102.5478,
                'district' => 'Kepahiang', 'city' => 'Kepahiang', 'province' => 'Bengkulu',
            ]);

            $reportKota = $this->report($warga, $categories['Banjir'], 'Banjir Di Kota Bengkulu', 'tinggi', [
                'name' => 'Rawa Makmur', 'latitude' => -3.7701, 'longitude' => 102.2701,
                'district' => 'Muara Bangkahulu', 'city' => 'Bengkulu', 'province' => 'Bengkulu',
            ]);

            // Permintaan bantuan + distribusi hanya dibuat sekali
            if (!AidRequest::where('disaster_report_id', $reportKota->id)->exists()) {
                $reqKota = $this->aidRequest($reportKota, [[$air, 400, 'dus', 'tinggi']], 'terpenuhi');
                $reqSelebar = $this->aidRequest($reportSelebar, [
                    [$air, 400, 'dus', 'tinggi'],
                    [$mie, 200, 'dus', 'sedang'],
                    [$selimut, 100, 'lembar', 'sedang'],
                ], 'diproses');
                $this->aidRequest($reportLongsor, [[$mie, 700, 'dus', 'tinggi']], 'menunggu');

                $this->distribution($reqKota, $gudangBengkulu, $petugas, [[$air, 20]], 'diterima');
                $this->distribution($reqSelebar, $gudangBengkulu, $petugas, [[$air, 20]], 'diterima');
                $this->distribution($reqSelebar, $gudangBengkulu, $admin, [[$mie, 13]], 'dikirim');
            }
        });
    }

    private function user(string $name, string $username, string $email, string $role): User
    {
        $user = User::firstOrNew(['email' => $email]);

        if (!$user->exists) {
            $user->fill([
                'name' => $name,
                'username' => $username,
                'phone' => '0812000000' . rand(10, 99),
                'password' => Hash::make('password123'),
                'role' => $role,
                'is_active' => true,
            ])->save();
        }

        return $user;
    }

    private function stock(Warehouse $warehouse, AidItem $item, int $quantity): void
    {
        AidStock::firstOrCreate(
            ['warehouse_id' => $warehouse->id, 'aid_item_id' => $item->id],
            ['quantity' => $quantity, 'reserved_quantity' => 0]
        );
    }

    private function report(User $user, DisasterCategory $category, string $title, string $severity, array $loc): DisasterReport
    {
        $existing = DisasterReport::where('title', $title)->first();

        if ($existing) {
            return $existing;
        }

        $location = Location::create($loc + ['address' => $loc['name'] . ', ' . $loc['city']]);

        return DisasterReport::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'title' => $title,
            'description' => 'Data demo untuk pengujian tampilan dan alur RESQNET.',
            'severity' => $severity,
            'status' => 'terverifikasi',
            'occurred_at' => now()->subDays(2),
        ]);
    }

    private function aidRequest(DisasterReport $report, array $items, string $status): AidRequest
    {
        [$firstItem, $firstQty, $firstUnit, $firstPriority] = $items[0];

        $request = AidRequest::create([
            'disaster_report_id' => $report->id,
            'item_name' => $firstItem->name,
            'quantity' => $firstQty,
            'unit' => $firstUnit,
            'priority' => $firstPriority,
            'status' => $status,
        ]);

        foreach ($items as [$item, $qty, $unit, $priority]) {
            AidRequestItem::create([
                'aid_request_id' => $request->id,
                'aid_item_id' => $item->id,
                'quantity' => $qty,
                'unit' => $unit,
                'priority' => $priority,
            ]);
        }

        return $request;
    }

    private function distribution(AidRequest $request, Warehouse $warehouse, User $by, array $items, string $status): void
    {
        $distribution = Distribution::create([
            'aid_request_id' => $request->id,
            'warehouse_id' => $warehouse->id,
            'assigned_by' => $by->id,
            'status' => $status,
            'tracking_code' => 'RSQ-' . strtoupper(uniqid()),
            'shipped_at' => now()->subDay(),
            'received_at' => $status === 'diterima' ? now() : null,
        ]);

        foreach ($items as [$item, $qty]) {
            $distribution->details()->create(['aid_item_id' => $item->id, 'quantity' => $qty]);
        }

        DistributionActivity::create([
            'distribution_id' => $distribution->id,
            'user_id' => $by->id,
            'activity' => 'Distribusi dibuat',
            'new_status' => $status,
            'description' => 'Data demo distribusi dari ' . $warehouse->name . '.',
        ]);
    }
}
