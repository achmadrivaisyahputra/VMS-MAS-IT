<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeminjamanTool extends Model
{
    protected $table = 'peminjaman_tools';
    protected $primaryKey = 'id_peminjaman';
    protected $guarded = [];

    protected $casts = [
        'tanggal_pinjam' => 'datetime',
        'tanggal_kembali' => 'datetime',
    ];

    public function tool()
    {
        return $this->belongsTo(Tool::class, 'id_tool', 'id_tool');
    }

    public function engineer()
    {
        return $this->belongsTo(Engineer::class, 'id_engineer', 'id_engineer');
    }

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'id_kunjungan', 'id_kunjungan');
    }
}
