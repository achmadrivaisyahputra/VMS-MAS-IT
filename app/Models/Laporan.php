<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporan';
    protected $primaryKey = 'id_laporan';
    protected $guarded = [];

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'id_kunjungan', 'id_kunjungan');
    }

    public function buktiPenyelesaian()
    {
        return $this->hasOne(BuktiPenyelesaian::class, 'id_laporan', 'id_laporan');
    }

    public function pembuat()
    {
        return $this->belongsTo(Engineer::class, 'id_engineer_pembuat', 'id_engineer');
    }
}