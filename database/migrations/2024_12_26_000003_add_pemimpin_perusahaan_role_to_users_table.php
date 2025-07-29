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
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Admin','Pemimpin Perusahaan', 'Karyawan') DEFAULT 'Karyawan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'Pemimpin Perusahaan' from the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Admin','Pemimpin Perusahaan', 'Karyawan') DEFAULT 'Karyawan'");
    }
};
