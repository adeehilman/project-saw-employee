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
        'id_karyawan', 'nama_karyawan', 'jabatan', 'jenis_kelamin', 'tanggal_masuk', 'user_id'
    ];
}
