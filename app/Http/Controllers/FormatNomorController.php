<?php

namespace App\Http\Controllers;

use App\Models\FormatNomor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormatNomorController extends Controller
{
    public function index()
    {
        $formats = FormatNomor::orderBy('id')->get();
        return view('master.format_nomor.index', compact('formats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:format_nomor,kode|regex:/^[a-z0-9_]+$/',
            'jenis' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'prefix' => 'required|string|max:50',
            'tahun' => 'nullable|integer|min:2000|max:2100',
            'digit' => 'required|integer|min:1|max:10',
            'nomor_terakhir' => 'required|integer|min:0',
        ], [
            'kode.regex' => 'Kode hanya boleh huruf kecil, angka, dan underscore.',
        ]);

        FormatNomor::create($validated);

        return redirect()->route('master.format-nomor.index')
            ->with('success', 'Format nomor berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $format = FormatNomor::findOrFail($id);

        $validated = $request->validate([
            'jenis' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'prefix' => 'required|string|max:50',
            'tahun' => 'nullable|integer|min:2000|max:2100',
            'digit' => 'required|integer|min:1|max:10',
            'nomor_terakhir' => 'required|integer|min:0',
        ]);

        // Simpan format lama untuk deteksi perubahan & membaca counter kode lama
        $prefixLama = $format->prefix;
        $tahunLama = $format->tahun;
        $digitLama = $format->digit;

        $format->update($validated);

        // Jika prefix/tahun/digit berubah, sinkronkan semua kode yang sudah ada
        $berubah = 0;
        if ($format->prefix !== $prefixLama || (int) $format->tahun !== (int) $tahunLama || (int) $format->digit !== (int) $digitLama) {
            $berubah = $format->syncExistingCodes((int) $digitLama);
        }

        $pesan = 'Format "' . $format->jenis . '" berhasil diperbarui! Nomor berikutnya: ' . $format->fresh()->nomorBerikutnya();
        if ($berubah > 0) {
            $pesan .= ' — ' . $berubah . ' kode lama ikut disinkronkan ke format baru.';
        }

        return redirect()->route('master.format-nomor.index')->with('success', $pesan);
    }

    public function destroy($id)
    {
        $format = FormatNomor::findOrFail($id);

        // Format bawaan (kunjungan, customer, tool) tidak boleh dihapus, hanya boleh diubah
        if (in_array($format->kode, ['kunjungan', 'customer', 'tool'])) {
            return redirect()->route('master.format-nomor.index')
                ->with('error', 'Format bawaan "' . $format->jenis . '" tidak boleh dihapus, hanya bisa diubah.');
        }

        $format->delete();

        return redirect()->route('master.format-nomor.index')
            ->with('success', 'Format nomor berhasil dihapus!');
    }
}
