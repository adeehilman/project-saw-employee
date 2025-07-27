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
        'periode_penilaian',
        'nilai',
        'catatan',
        'dinilai_oleh'
    ];

    protected $casts = [
        'periode_penilaian' => 'date',
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
     * Get assessments for a specific employee and period
     */
    public static function getEmployeeAssessments($employeeId, $period = null)
    {
        $query = static::where('id_karyawan', $employeeId)
            ->with(['kriteriaBobot', 'penilai']);

        if ($period) {
            $query->where('periode_penilaian', $period);
        }

        return $query->orderBy('periode_penilaian', 'desc')->get();
    }

    /**
     * Get all assessments for a specific period
     */
    public static function getPeriodAssessments($period)
    {
        return static::where('periode_penilaian', $period)
            ->with(['karyawan', 'kriteriaBobot', 'penilai'])
            ->get();
    }



    /**
     * Calculate SAW score for an employee in a period
     */
    public static function calculateSAWScore($employeeId, $period)
    {
        $sawService = app(\App\Services\SAWCalculationService::class);
        $employeeDetails = $sawService->getEmployeeSAWDetails($employeeId, $period);

        return $employeeDetails ? $employeeDetails['saw_score_percentage'] : 0;
    }

    /**
     * Get SAW ranking for an employee in a period
     */
    public static function getEmployeeRank($employeeId, $period)
    {
        $sawService = app(\App\Services\SAWCalculationService::class);
        $employeeDetails = $sawService->getEmployeeSAWDetails($employeeId, $period);

        return $employeeDetails ? $employeeDetails['rank'] : null;
    }

    /**
     * Get available assessment periods
     */
    public static function getAvailablePeriods()
    {
        return static::selectRaw('DISTINCT periode_penilaian')
            ->orderBy('periode_penilaian', 'desc')
            ->pluck('periode_penilaian')
            ->map(function ($date) {
                return [
                    'value' => $date,
                    'label' => \Carbon\Carbon::parse($date)->format('F Y')
                ];
            });
    }


}
