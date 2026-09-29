<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FormatNomor extends Model
{
    protected $table = 'format_nomor';
    protected $guarded = [];

    /**
     * Format nomor: {prefix}[2 digit tahun]{counter dengan padding nol}
     * Tahun opsional — kalau dikosongkan, tidak dipakai.
     * Contoh: prefix=TLS, tahun=null, digit=3, counter=1 -> TLS001
     * Contoh: prefix=TLS, tahun=2026, digit=3, counter=1 -> TLS26001
     */
    public function format(int $counter): string
    {
        $yy = $this->tahun ? substr((string) $this->tahun, -2) : '';
        return $this->prefix . $yy . str_pad((string) $counter, $this->digit, '0', STR_PAD_LEFT);
    }

    /** Nomor yang terakhir dipakai */
    public function nomorTerkini(): string
    {
        return $this->format($this->nomor_terakhir);
    }

    /** Preview nomor berikutnya (tanpa mengubah counter) */
    public function nomorBerikutnya(): string
    {
        return $this->format($this->nomor_terakhir + 1);
    }

    /**
     * Generate nomor baru yang unik & berurutan.
     * Aman dari duplikat walau banyak user bikin barengan (row lock).
     * Kalau format belum ada (seeder belum jalan), otomatis dibuatkan default
     * supaya input data baru tidak error 404.
     * Kalau kode hasil generate ternyata sudah dipakai data lama (counter tidak
     * sinkron), counter otomatis dimajukan sampai dapat kode yang unik.
     */
    public static function generate(string $kode): string
    {
        return DB::transaction(function () use ($kode) {
            $format = self::where('kode', $kode)->lockForUpdate()->first();
            if (!$format) {
                $format = self::defaultUntuk($kode);
                $format->save();
                $format = self::where('kode', $kode)->lockForUpdate()->first();
            }
            // Maju terus sampai dapat kode yang belum dipakai
            $tries = 0;
            do {
                $format->increment('nomor_terakhir');
                $candidate = $format->format($format->nomor_terakhir);
                $tries++;
            } while ($tries < 1000 && self::kodeSudahDipakai($kode, $candidate));
            return $candidate;
        });
    }

    /**
     * Pemetaan kode format -> model & kolom kode, untuk cek duplikat.
     */
    protected static function targetUntuk(string $kode): ?array
    {
        return [
            'tool'      => [\App\Models\Tool::class, 'kode'],
            'customer'  => [\App\Models\Customer::class, 'kode'],
            'kunjungan' => [\App\Models\Kunjungan::class, 'nomor'],
            'engineer'  => [\App\Models\Engineer::class, 'kode'],
        ][$kode] ?? null;
    }

    /**
     * Cek apakah kode sudah dipakai di tabel target.
     */
    protected static function kodeSudahDipakai(string $kode, string $candidate): bool
    {
        $target = self::targetUntuk($kode);
        if (!$target) {
            return false;
        }
        [$model, $column] = $target;
        return $model::where($column, $candidate)->exists();
    }

    /**
     * Format default untuk tiap kode — dipakai saat generate() dipanggil
     * padahal baris formatnya belum ada di database.
     */
    protected static function defaultUntuk(string $kode): self
    {
        $defaults = [
            'kunjungan' => ['jenis' => 'ID Kunjungan', 'prefix' => 'vmsmit', 'tahun' => 2026, 'digit' => 3],
            'customer'  => ['jenis' => 'Kode Customer', 'prefix' => 'cst', 'tahun' => 2026, 'digit' => 3],
            'tool'      => ['jenis' => 'Kode Tool', 'prefix' => 'tls', 'tahun' => 2026, 'digit' => 3],
            'engineer'  => ['jenis' => 'Kode Engineer', 'prefix' => 'eng', 'tahun' => 2026, 'digit' => 3],
        ];
        $d = $defaults[$kode] ?? ['jenis' => 'Kode ' . ucfirst($kode), 'prefix' => substr($kode, 0, 3), 'tahun' => 2026, 'digit' => 3];
        $m = new self();
        $m->kode = $kode;
        $m->jenis = $d['jenis'];
        $m->deskripsi = 'Dibuat otomatis saat pertama kali dipakai.';
        $m->prefix = $d['prefix'];
        $m->tahun = $d['tahun'];
        $m->digit = $d['digit'];
        $m->nomor_terakhir = 0;
        return $m;
    }

    /**
     * Sinkronkan semua kode yang sudah ada mengikuti format terbaru.
     * Dipanggil otomatis saat prefix/tahun/digit diubah dari halaman Format Nomor.
     * Counter tiap data dipertahankan (diambil dari digit akhir kode lama),
     * sehingga urutan tidak berubah — hanya prefix/tahun/digit yang menyesuaikan.
     *
     * @param int $digitLama Jumlah digit pada format sebelum diubah (untuk membaca counter lama)
     * @return int Jumlah data yang kodenya berubah
     */
    public function syncExistingCodes(int $digitLama): int
    {
        $map = [
            'kunjungan' => [\App\Models\Kunjungan::class, 'nomor'],
            'customer' => [\App\Models\Customer::class, 'kode'],
            'tool' => [\App\Models\Tool::class, 'kode'],
        ];

        if (!isset($map[$this->kode])) {
            return 0;
        }

        [$modelClass, $kolom] = $map[$this->kode];
        $keyName = (new $modelClass)->getKeyName();

        $records = $modelClass::orderBy($keyName)->get();

        $terpakai = [];
        $berubah = 0;
        $counterMax = 0;

        foreach ($records as $record) {
            $counter = $this->bacaCounter((string) $record->$kolom, $digitLama);

            // Fallback: nomor urut baru jika kode lama tidak terbaca atau counter-nya duplikat
            if ($counter === null || $counter < 1 || in_array($counter, $terpakai, true)) {
                $counter = empty($terpakai) ? 1 : (max($terpakai) + 1);
                while (in_array($counter, $terpakai, true)) {
                    $counter++;
                }
            }

            $terpakai[] = $counter;
            $counterMax = max($counterMax, $counter);

            $kodeBaru = $this->format($counter);
            if ($record->$kolom !== $kodeBaru) {
                $record->update([$kolom => $kodeBaru]);
                $berubah++;
            }
        }

        // Pastikan nomor baru lanjut dari counter terbesar, tidak menimpa yang sudah ada
        if ($counterMax > $this->nomor_terakhir) {
            $this->nomor_terakhir = $counterMax;
            $this->save();
        }

        return $berubah;
    }

    /**
     * Baca counter dari digit akhir sebuah kode berdasarkan jumlah digit format lama.
     * Contoh: kode "tls26001" dengan digit lama 3 -> counter 1.
     * Return null jika tidak terbaca (kode manual / tidak mengikuti pola).
     */
    protected function bacaCounter(string $kode, int $digitLama): ?int
    {
        if ($digitLama < 1 || strlen($kode) < $digitLama) {
            return null;
        }
        $ekor = substr($kode, -$digitLama);
        if (!ctype_digit($ekor)) {
            return null;
        }
        return (int) $ekor;
    }
}
