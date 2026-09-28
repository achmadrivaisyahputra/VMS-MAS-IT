<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KunjunganKonfirmasi extends Model
{
    protected $table = 'kunjungan_konfirmasi';
    protected $primaryKey = 'id_konfirmasi';
    protected $guarded = [];

    protected $casts = [
        'waktu_konfirmasi' => 'datetime',
    ];

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'id_kunjungan', 'id_kunjungan');
    }

    public function engineer()
    {
        return $this->belongsTo(Engineer::class, 'id_engineer', 'id_engineer');
    }
}
