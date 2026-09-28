<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    protected $table = 'kunjungan';
    protected $primaryKey = 'id_kunjungan';
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    // TAMBAHAN BARU: Relasi ke lokasi cabang/site
    public function site()
    {
        return $this->belongsTo(CustomerSite::class, 'id_site', 'id_site');
    }

    public function engineer()
    {
        return $this->belongsTo(Engineer::class, 'id_engineer', 'id_engineer');
    }

    public function tools()
    {
        return $this->belongsToMany(Tool::class, 'kunjungan_tool', 'id_kunjungan', 'id_tool')
                    ->withPivot('jumlah', 'keterangan')
                    ->withTimestamps();
    }

    public function aktivitas()
    {
        return $this->hasMany(AktivitasPekerjaan::class, 'id_kunjungan', 'id_kunjungan');
    }

    public function dokumentasi()
    {
        return $this->hasMany(Dokumentasi::class, 'id_kunjungan', 'id_kunjungan');
    }

    public function laporan()
    {
        return $this->hasOne(Laporan::class, 'id_kunjungan', 'id_kunjungan');
    }

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'id_kunjungan', 'id_kunjungan');
    }

    public function supportEngineers()
    {
        return $this->belongsToMany(Engineer::class, 'kunjungan_support', 'id_kunjungan', 'id_engineer')
                    ->withTimestamps();
    }

    /**
     * Alamat kunjungan yang selalu sinkron dengan data site terkini.
     * Jika kunjungan terhubung ke site, pakai alamat site (live dari master).
     */
    public function getAlamatSinkronAttribute()
    {
        return $this->site->alamat_lengkap ?? $this->lokasi;
    }
}