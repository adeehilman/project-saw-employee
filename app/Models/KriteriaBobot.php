<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KriteriaBobot extends Model
{
    use HasFactory;

    protected $table = 'kriteria_bobot';

    protected $primaryKey = 'id_kriteria';
    public $incrementing = false; // Since id_guru is not auto-incrementing
    protected $keyType = 'string';

    protected $fillable = [
        'id_kriteria', 'kriteria', 'bobot', 'status', 'createby'
    ];
}
