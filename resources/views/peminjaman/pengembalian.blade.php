@extends('layouts.app')

@section('title', 'Pengembalian Tools')
@section('header_title', 'Pengembalian Tools & Alat Kerja')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-lg font-bold text-[#002266]">Tools yang Sedang Dipinjam</h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Kembalikan tools setelah selesai dipakai agar stok bertambah kembali</p>
        </div>
        <button onclick="document.getElementById('modalPinjamTool').classList.remove('hidden')"
                class="px-4 py-2.5 bg-[#002266] hover:bg-[#001233] text-white rounded-xl text-xs font-bold shadow-lg shadow-blue-900/20 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Pinjam Tool (Keperluan Lain)
        </button>
    </div>

    <!-- Daftar Pinjaman Aktif -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
            <h4 class="text-sm font-bold text-[#002266]">Belum Dikembalikan <span class="ml-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold">{{ $pinjaman->total() }}</span></h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] uppercase bg-white text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-bold">Tool</th>
                        <th class="p-4 font-bold text-center">Jumlah</th>
                        <th class="p-4 font-bold">Dipinjam Pada</th>
                        <th class="p-4 font-bold">Keperluan</th>
                        <th class="p-4 text-center font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pinjaman as $p)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4">
                                @if($p->tool)
                                    <a href="{{ route('master.tool.show', $p->tool->kode) }}" class="font-mono text-[10px] text-[#003399] font-bold uppercase hover:underline">{{ $p->tool->kode }}</a>
                                @else
                                    <span class="font-mono text-[10px] text-slate-400 font-bold uppercase">-</span>
                                @endif
                                <p class="font-bold text-slate-800 mt-1">{{ $p->tool->nama_alat ?? '-' }}</p>
                            </td>
                            <td class="p-4 text-center"><span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-[11px] font-bold">{{ $p->jumlah }}</span></td>
                            <td class="p-4 font-medium">{{ $p->tanggal_pinjam->format('d M Y, H:i') }}</td>
                            <td class="p-4 font-medium text-slate-500 max-w-xs">
                                @if($p->id_kunjungan)
                                    <span class="text-[#003399] font-bold">{{ $p->kunjungan->nomor ?? '-' }}</span>
                                    <span class="block text-[11px]">{{ $p->kunjungan->customer->nama_perusahaan ?? '' }}</span>
                                @else
                                    <span class="italic">{{ $p->keterangan ?? 'Keperluan lain' }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <button onclick="openKembalikanModal({{ $p->id_peminjaman }}, '{{ addslashes($p->tool->nama_alat ?? '') }}', {{ $p->jumlah }})"
                                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-xl shadow-sm transition">
                                    Kembalikan
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400 font-medium italic">Tidak ada tools yang sedang dipinjam. Semua sudah dikembalikan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pinjaman->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">{{ $pinjaman->links() }}</div>
        @endif
    </div>

    <!-- Riwayat Pengembalian Saya -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
            <h4 class="text-sm font-bold text-[#002266]">Riwayat Pengembalian Saya</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] uppercase bg-white text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-bold">Tool</th>
                        <th class="p-4 font-bold text-center">Jumlah</th>
                        <th class="p-4 font-bold">Dipinjam</th>
                        <th class="p-4 font-bold">Dikembalikan</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 font-bold">Kondisi</th>
                        <th class="p-4 font-bold">Keperluan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($riwayat as $r)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4">
                                <span class="font-mono text-[10px] text-[#003399] font-bold uppercase">{{ $r->tool->kode ?? '-' }}</span>
                                <p class="font-bold text-slate-800 mt-1">{{ $r->tool->nama_alat ?? '-' }}</p>
                            </td>
                            <td class="p-4 text-center font-bold">{{ $r->jumlah }}</td>
                            <td class="p-4 font-medium">{{ $r->tanggal_pinjam->format('d M Y, H:i') }}</td>
                            <td class="p-4 font-medium text-emerald-600">{{ $r->tanggal_kembali?->format('d M Y, H:i') ?? '-' }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $r->status == 'Dibatalkan' ? 'bg-slate-100 text-slate-600 border-slate-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200' }}">
                                    {{ $r->status }}
                                </span>
                            </td>
                            <td class="p-4">
                                @if($r->kondisi_kembali)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $r->kondisi_kembali == 'Baik' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : ($r->kondisi_kembali == 'Rusak Ringan' ? 'bg-amber-100 text-amber-700 border border-amber-200' : 'bg-rose-100 text-rose-700 border border-rose-200') }}">
                                        {{ $r->kondisi_kembali }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="p-4 font-medium text-slate-500">
                                @if($r->id_kunjungan)
                                    <span class="text-[#003399] font-bold">{{ $r->kunjungan->nomor ?? '-' }}</span>
                                @else
                                    <span class="italic">{{ $r->keterangan ?? 'Keperluan lain' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-400 font-medium italic">Belum ada riwayat pengembalian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($riwayat->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">{{ $riwayat->appends(['riwayat_page' => $riwayat->currentPage()])->links() }}</div>
        @endif
    </div>
</div>

<!-- Modal Pinjam Tool -->
<div id="modalPinjamTool" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-base font-bold text-[#002266]">Pinjam Tool (Keperluan Lain)</h4>
            <button onclick="document.getElementById('modalPinjamTool').classList.add('hidden')" class="text-slate-400 hover:text-rose-500 transition text-lg">&times;</button>
        </div>
        <form action="{{ route('peminjaman.pinjam') }}" method="POST" class="space-y-4 text-xs font-medium">
            @csrf
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Pilih Tool</label>
                <select name="id_tool" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <option value="">-- Pilih Tool --</option>
                    @foreach($tools as $t)
                        <option value="{{ $t->id_tool }}">{{ $t->nama_alat }} (stok: {{ $t->stok }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Jumlah</label>
                <input type="number" name="jumlah" value="1" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]">
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Keterangan Keperluan</label>
                <textarea name="keterangan" rows="2" placeholder="Contoh: Dipakai untuk perbaikan di kantor..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003399]"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalPinjamTool').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Batal</button>
                <button type="submit" class="px-4 py-2.5 bg-[#002266] hover:bg-[#001233] text-white font-bold rounded-xl shadow-lg shadow-blue-900/20 transition">Pinjam</button>
            </div>
        </form>
    </div>
</div>
<!-- Modal Kembalikan Tool (dengan kondisi) -->
<div id="modalKembalikan" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-base font-bold text-[#002266]">Kembalikan Tool</h4>
            <button onclick="document.getElementById('modalKembalikan').classList.add('hidden')" class="text-slate-400 hover:text-rose-500 transition text-lg">&times;</button>
        </div>
        <p class="text-xs text-slate-500 font-medium mb-4">Tool: <span id="kembaliNama" class="font-bold text-slate-800"></span> (<span id="kembaliJumlah" class="font-bold text-[#003399]"></span>x)</p>
        <form id="formKembalikan" method="POST" class="space-y-4 text-xs font-medium">
            @csrf
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Kondisi Tool Saat Dikembalikan <span class="text-rose-500">*</span></label>
                <select name="kondisi_kembali" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                    <option value="Baik">Baik — tidak ada kerusakan</option>
                    <option value="Rusak Ringan">Rusak Ringan — masih bisa dipakai</option>
                    <option value="Rusak Berat">Rusak Berat — tidak bisa dipakai</option>
                </select>
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1.5">Catatan (opsional)</label>
                <textarea name="catatan_kembali" rows="2" placeholder="Contoh: kabel sedikit terkelupas..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-600"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalKembalikan').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Batal</button>
                <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 transition">Kembalikan & Tambah Stok</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openKembalikanModal(id, nama, jumlah) {
        document.getElementById('formKembalikan').action = `/peminjaman/${id}/kembalikan`;
        document.getElementById('kembaliNama').textContent = nama;
        document.getElementById('kembaliJumlah').textContent = jumlah;
        document.getElementById('modalKembalikan').classList.remove('hidden');
    }
</script>
@endsection
