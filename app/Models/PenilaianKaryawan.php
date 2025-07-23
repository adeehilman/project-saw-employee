<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianKaryawan extends Model
{
    use HasFactory;

    protected $table = 'penilaian_karyawan';

    protected $primaryKey = 'id_penilaian';
    public $incrementing = false; // Since id_guru is not auto-incrementing
    protected $keyType = 'string';

    protected $fillable = [
        'id_penilaian', 'id_kriteria_bobot','id_karyawan', 'waktu_pelayanan', 'nilai',
    ];
}
