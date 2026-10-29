<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Menambahkan kolom username ke tabel users
|--------------------------------------------------------------------------
| LoginController dan RegisterController memakai kolom "username",
| tetapi migration sebelumnya belum membuat kolom tersebut.
|
| Migration ini AMAN untuk database yang sudah berjalan:
| jika kolom username sudah ada (misalnya ditambahkan lewat pgAdmin),
| migration tidak mengubah apa pun.
*/

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                // nullable agar data user lama tetap valid
                $table->string('username', 50)->nullable()->unique();
            });
        }
    }

    public function down(): void
    {
        // Sengaja dikosongkan.
        // Kolom username mungkin sudah ada sebelum migration ini dibuat,
        // sehingga rollback tidak boleh menghapusnya.
    }
};
