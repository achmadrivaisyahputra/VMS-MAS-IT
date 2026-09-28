<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AktivitasPekerjaan extends Model
{
    protected $table = 'aktivitas_pekerjaan';
    protected $primaryKey = 'id_aktivitas';
    protected $guarded = [];

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'id_kunjungan', 'id_kunjungan');
    }

    public function engineer()
    {
        return $this->belongsTo(Engineer::class, 'id_engineer', 'id_engineer');
    }
}