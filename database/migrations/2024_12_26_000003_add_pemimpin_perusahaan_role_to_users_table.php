<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'Pemimpin Perusahaan' to the existing role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Admin', 'Kepala Sekolah', 'Guru Mapel', 'Wali Kelas', 'Kepala Prodi', 'BPBK', 'Tata Usaha', 'Siswa', 'Pengguna', 'Pemimpin Perusahaan', 'Karyawan') DEFAULT 'Pengguna'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'Pemimpin Perusahaan' from the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Admin', 'Kepala Sekolah', 'Guru Mapel', 'Wali Kelas', 'Kepala Prodi', 'BPBK', 'Tata Usaha', 'Siswa', 'Pengguna') DEFAULT 'Pengguna'");
    }
};
