<?php

namespace App\Http\Controllers;

use App\Models\Engineer;
use App\Models\PeminjamanTool;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeminjamanToolController extends Controller
{
    /**
     * Ambil id_engineer dari user yang login (role engineer).
     */
    private function engineerId()
    {
        $eng = Engineer::where('id_pengguna', Auth::user()->id_pengguna)->first();
        return $eng ? $eng->id_engineer : null;
    }

    /**
     * Halaman Pengembalian Tools (engineer): daftar pinjaman aktif miliknya.
     */
    public function pengembalian()
    {
        $idEngineer = $this->engineerId();
        abort_if(!$idEngineer, 403, 'Akun Anda tidak terdaftar sebagai engineer.');

        $pinjaman = PeminjamanTool::with(['tool', 'kunjungan.customer'])
            ->where('id_engineer', $idEngineer)
            ->where('status', 'Dipinjam')
            ->latest('tanggal_pinjam')
            ->paginate(10);

        $riwayat = PeminjamanTool::with(['tool', 'kunjungan.customer'])
            ->where('id_engineer', $idEngineer)
            ->whereIn('status', ['Dikembalikan', 'Dibatalkan'])
            ->latest('tanggal_kembali')
            ->paginate(10, ['*'], 'riwayat_page');

        $tools = Tool::where('stok', '>', 0)->orderBy('nama_alat')->get();

        return view('peminjaman.pengembalian', compact('pinjaman', 'riwayat', 'tools'));
    }

    /**
     * Pinjam tools (keperluan kunjungan lain / hal lain).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_tool' => 'required|exists:tools,id_tool',
            'jumlah' => 'required|integer|min:1|max:100',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $idEngineer = $this->engineerId();
        // Pimpinan/admin boleh meminjamkan atas nama engineer tertentu
        if (!$idEngineer) {
            $request->validate(['id_engineer' => 'required|exists:engineers,id_engineer']);
            $idEngineer = $request->id_engineer;
        }

        $tool = Tool::lockForUpdate()->findOrFail($validated['id_tool']);

        if ($tool->stok < $validated['jumlah']) {
            return redirect()->back()->with('error', "Stok {$tool->nama_alat} tidak mencukupi (tersisa {$tool->stok}).");
        }

        DB::transaction(function () use ($tool, $validated, $idEngineer) {
            $tool->decrement('stok', $validated['jumlah']);
            PeminjamanTool::create([
                'id_tool' => $tool->id_tool,
                'id_engineer' => $idEngineer,
                'id_kunjungan' => null,
                'jumlah' => $validated['jumlah'],
                'tanggal_pinjam' => now(),
                'status' => 'Dipinjam',
                'keterangan' => $validated['keterangan'],
            ]);
        });

        return redirect()->back()->with('success', "Berhasil meminjam {$validated['jumlah']}x {$tool->nama_alat}. Stok tersisa: " . $tool->fresh()->stok);
    }

    /**
     * Kembalikan tools -> stok bertambah, kondisi dicatat.
     */
    public function kembalikan(Request $request, $id)
    {
        $request->validate([
            'kondisi_kembali' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'catatan_kembali' => 'nullable|string|max:500',
        ]);

        $pinjam = PeminjamanTool::findOrFail($id);
        abort_if($pinjam->status !== 'Dipinjam', 400, 'Pinjaman ini sudah dikembalikan.');

        $idEngineer = $this->engineerId();
        $roleId = Auth::user()->id_role;
        // Hanya peminjam atau pimpinan/admin yang boleh mengembalikan
        if ($idEngineer && $pinjam->id_engineer != $idEngineer && !in_array($roleId, [1, 2])) {
            abort(403, 'Anda tidak berhak mengembalikan pinjaman ini.');
        }

        DB::transaction(function () use ($pinjam, $request) {
            $pinjam->update([
                'status' => 'Dikembalikan',
                'tanggal_kembali' => now(),
                'kondisi_kembali' => $request->kondisi_kembali,
                'catatan_kembali' => $request->catatan_kembali,
            ]);
            $pinjam->tool()->increment('stok', $pinjam->jumlah);
        });

        return redirect()->back()->with('success', "Berhasil mengembalikan {$pinjam->jumlah}x {$pinjam->tool->nama_alat} (kondisi: {$request->kondisi_kembali}). Stok bertambah menjadi " . $pinjam->tool->fresh()->stok . ".");
    }

    /**
     * Riwayat pemakaian & pinjaman per tool.
     */
    public function riwayat($kode)
    {
        $tool = Tool::where('kode', $kode)->firstOrFail();
        $riwayat = PeminjamanTool::with(['engineer.user', 'kunjungan.customer'])
            ->where('id_tool', $tool->id_tool)
            ->latest('tanggal_pinjam')
            ->paginate(15);

        return view('peminjaman.riwayat', compact('tool', 'riwayat'));
    }

    /**
     * Riwayat semua peminjaman (pimpinan/admin).
     */
    public function riwayatSemua(Request $request)
    {
        $query = PeminjamanTool::with(['tool', 'engineer.user', 'kunjungan.customer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('tool', fn($q) => $q->where('nama_alat', 'like', "%$s%")->orWhere('kode', 'like', "%$s%"))
                  ->orWhereHas('engineer.user', fn($q) => $q->where('nama', 'like', "%$s%"));
        }

        $riwayat = $query->latest('tanggal_pinjam')->paginate(15)->appends($request->all());

        return view('peminjaman.riwayat_semua', compact('riwayat'));
    }
}
