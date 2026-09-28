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
     */
    public static function generate(string $kode): string
    {
        return DB::transaction(function () use ($kode) {
            $format = self::where('kode', $kode)->lockForUpdate()->firstOrFail();
            $format->increment('nomor_terakhir');
            return $format->format($format->nomor_terakhir);
        });
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
