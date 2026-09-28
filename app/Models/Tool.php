<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    protected $table = 'tools';
    protected $primaryKey = 'id_tool';
    protected $guarded = [];

    public function kunjungan()
    {
        return $this->belongsToMany(Kunjungan::class, 'kunjungan_tool', 'id_tool', 'id_kunjungan')
                    ->withPivot('jumlah', 'keterangan')
                    ->withTimestamps();
    }

    public function peminjaman()
    {
        return $this->hasMany(PeminjamanTool::class, 'id_tool', 'id_tool');
    }
}