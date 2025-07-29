<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianKaryawan extends Model
{
    use HasFactory;

    protected $table = 'penilaian_karyawan';

    protected $primaryKey = 'id_penilaian';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_penilaian',
        'id_kriteria_bobot',
        'id_karyawan',
        'waktu_penilaian',
        'nilai',
        'catatan',
        'dinilai_oleh'
    ];

    protected $casts = [
        'waktu_penilaian' => 'date',
        'nilai' => 'decimal:2'
    ];

    /**
     * Get the criteria/weight for this assessment
     */
    public function kriteriaBobot()
    {
        return $this->belongsTo(KriteriaBobot::class, 'id_kriteria_bobot', 'id_kriteria');
    }

    /**
     * Get the employee being assessed
     */
    public function karyawan()
    {
        return $this->belongsTo(DataKaryawan::class, 'id_karyawan', 'id_karyawan');
    }

    /**
     * Get the user who gave the score
     */
    public function penilai()
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }

    /**
     * Generate unique ID for new assessment
     */
    public static function generateId()
    {
        $count = static::count() + 1;
        return 'PNL' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }



    /**
     * Get assessments for a specific employee and date range
     */
    public static function getEmployeeAssessmentsForDateRange($employeeId, $startDate, $endDate)
    {
        return static::where('id_karyawan', $employeeId)
            ->whereBetween('waktu_penilaian', [$startDate, $endDate])
            ->with(['kriteriaBobot', 'penilai'])
            ->orderBy('waktu_penilaian', 'desc')
            ->get();
    }



    /**
     * Get all assessments for a date range
     */
    public static function getDateRangeAssessments($startDate, $endDate)
    {
        return static::whereBetween('waktu_penilaian', [$startDate, $endDate])
            ->with(['karyawan', 'kriteriaBobot', 'penilai'])
            ->get();
    }



    /**
     * Calculate SAW score for an employee in a date range
     */
    public static function calculateSAWScore($employeeId, $startDate, $endDate)
    {
        $sawService = app(\App\Services\SAWCalculationService::class);
        $employeeDetails = $sawService->getEmployeeSAWDetails($employeeId, $startDate, $endDate);

        return $employeeDetails ? $employeeDetails['saw_score_percentage'] : 0;
    }

    /**
     * Get SAW ranking for an employee in a date range
     */
    public static function getEmployeeRank($employeeId, $startDate, $endDate)
    {
        $sawService = app(\App\Services\SAWCalculationService::class);
        $employeeDetails = $sawService->getEmployeeSAWDetails($employeeId, $startDate, $endDate);

        return $employeeDetails ? $employeeDetails['rank'] : null;
    }




}
