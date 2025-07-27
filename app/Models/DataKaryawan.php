<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataKaryawan extends Model
{
    protected $table = 'data_karyawan';

    protected $primaryKey = 'id_karyawan';
    public $incrementing = false; // Since id_guru is not auto-incrementing
    protected $keyType = 'string';

    protected $fillable = [
        'id_karyawan', 'nama_karyawan', 'jabatan', 'jenis_kelamin', 'tanggal_masuk', 'user_id', 'is_active'
    ];

    /**
     * Get the user account for this employee
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all assessments for this employee
     */
    public function penilaian()
    {
        return $this->hasMany(PenilaianKaryawan::class, 'id_karyawan', 'id_karyawan');
    }

    /**
     * Get assessments for a specific period
     */
    public function penilaianPeriode($period)
    {
        return $this->penilaian()->where('periode_penilaian', $period);
    }

    /**
     * Get latest assessment period for this employee
     */
    public function getLatestAssessmentPeriod()
    {
        return $this->penilaian()
            ->orderBy('periode_penilaian', 'desc')
            ->first()?->periode_penilaian;
    }



    /**
     * Calculate SAW score for a specific period
     */
    public function getSAWScore($period)
    {
        return PenilaianKaryawan::calculateSAWScore($this->id_karyawan, $period);
    }

    /**
     * Get SAW ranking for a specific period
     */
    public function getSAWRank($period)
    {
        return PenilaianKaryawan::getEmployeeRank($this->id_karyawan, $period);
    }


}
