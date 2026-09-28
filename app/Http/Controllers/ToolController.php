<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\FormatNomor;
use App\Models\PeminjamanTool;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    /**
     * Cari tool berdasarkan KODE (bukan id angka),
     * karena URL memakai kode tool, misal: /master/tool/tls26001
     */
    private function cariTool(string $kode)
    {
        return Tool::where('kode', $kode)->firstOrFail();
    }
    public function index(Request $request)
    {
        $query = Tool::query();

        // Fitur Pencarian (Search by Nama Alat, Kode, atau Kategori)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_alat', 'like', '%' . $search . '%')
                  ->orWhere('kode', 'like', '%' . $search . '%')
                  ->orWhere('kategori', 'like', '%' . $search . '%');
        }

        // Fitur Sortir
        $sort = $request->get('sort', 'terbaru');
        if ($sort == 'terlama') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $tools = $query->withSum(['peminjaman as sedang_dipinjam' => function ($q) {
            $q->where('status', 'Dipinjam');
        }], 'jumlah')->paginate(10)->appends($request->all());

        // Ringkasan stok untuk kartu statistik
        $ringkasan = [
            'jenis' => Tool::count(),
            'total_unit' => (int) Tool::sum('stok') + (int) \App\Models\PeminjamanTool::where('status', 'Dipinjam')->sum('jumlah'),
            'tersedia' => (int) Tool::sum('stok'),
            'dipinjam' => (int) \App\Models\PeminjamanTool::where('status', 'Dipinjam')->sum('jumlah'),
        ];

        return view('master.tool.index', compact('tools', 'ringkasan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_alat' => 'required|string|max:100',
            'kategori' => 'required|string|max:100',
            'spesifikasi' => 'nullable|string',
            'stok' => 'required|integer|min:0|max:100000',
            'status_ketersediaan' => 'required|in:Tersedia,Tidak Tersedia',
            'keterangan' => 'nullable|string',
        ]);

        // Kode tool dibuat otomatis dari Format Nomor (bisa diatur di Master Data > Format Nomor)
        $validated['kode'] = FormatNomor::generate('tool');

        Tool::create($validated);

        return redirect()->back()->with('success', 'Tool / Alat Kerja berhasil ditambahkan dengan kode ' . $validated['kode'] . '!');
    }

    /**
     * Halaman detail tool: info + riwayat peminjaman.
     */
    public function show($kode)
    {
        $tool = $this->cariTool($kode);
        $riwayat = PeminjamanTool::with(['engineer.user', 'kunjungan.customer'])
            ->where('id_tool', $tool->id_tool)
            ->latest('tanggal_pinjam')
            ->paginate(10);

        return view('master.tool.show', compact('tool', 'riwayat'));
    }

    public function update(Request $request, $kode)
    {
        $tool = $this->cariTool($kode);

        $validated = $request->validate([
            'nama_alat' => 'required|string|max:100',
            'kategori' => 'required|string|max:100',
            'spesifikasi' => 'nullable|string',
            'stok' => 'required|integer|min:0|max:100000',
            'status_ketersediaan' => 'required|in:Tersedia,Tidak Tersedia',
            'keterangan' => 'nullable|string',
        ]);

        $tool->update($validated);

        return redirect()->back()->with('success', 'Data Tool berhasil diperbarui!');
    }

    public function destroy($kode)
    {
        $tool = $this->cariTool($kode);
        $tool->delete();

        return redirect()->back()->with('success', 'Tool berhasil dihapus!');
    }

    /**
     * Tambah stok tools (restock).
     */
    public function tambahStok(Request $request, $kode)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1|max:100000',
        ]);

        $tool = $this->cariTool($kode);
        $tool->increment('stok', $request->jumlah);

        return redirect()->back()->with('success', "Stok {$tool->nama_alat} bertambah {$request->jumlah}. Total stok: {$tool->fresh()->stok}.");
    }
}