<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop table if exists to recreate with proper structure
        Schema::dropIfExists('penilaian_karyawan');
        
        Schema::create('penilaian_karyawan', function (Blueprint $table) {
            $table->string('id_penilaian', 20)->primary();
            $table->unsignedBigInteger('id_kriteria_bobot');
            $table->unsignedInteger('id_karyawan');
            $table->date('waktu_penilaian'); // Waktu penilaian
            $table->decimal('nilai', 5, 2); // Nilai dengan 2 desimal (0.00 - 100.00)
            $table->text('catatan')->nullable(); // Catatan penilaian
            $table->unsignedBigInteger('dinilai_oleh'); // User yang memberikan nilai
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_karyawan')->references('id_karyawan')->on('data_karyawan')->onDelete('cascade');
            $table->foreign('dinilai_oleh')->references('id')->on('users')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate scoring for same employee-criteria-period
            $table->unique(['id_kriteria_bobot', 'id_karyawan', 'waktu_penilaian'], 'unique_employee_criteria_period');
            
            // Indexes for better performance
            $table->index('waktu_penilaian');
            $table->index('dinilai_oleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_karyawan');
    }
};
