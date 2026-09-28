@extends('layouts.app')

@section('title', 'Format Nomor')
@section('header_title', 'Pengaturan Format Nomor')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-bold flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-bold shadow-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-lg font-bold text-[#002266]">Format Penomoran</h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Atur prefix, tahun, dan digit nomor untuk kunjungan, customer, tool, dan lainnya. Nomor naik +1 otomatis setiap ada data baru.</p>
        </div>
        <button onclick="document.getElementById('modalTambah').classList.remove('hidden')"
                class="px-4 py-2.5 bg-[#002266] hover:bg-[#001233] text-white rounded-xl text-xs font-bold shadow-lg shadow-blue-900/20 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Format
        </button>
    </div>

    <!-- Table Card -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-bold">Jenis</th>
                        <th class="p-4 font-bold">Deskripsi</th>
                        <th class="p-4 font-bold">Prefix</th>
                        <th class="p-4 font-bold">Tahun</th>
                        <th class="p-4 font-bold text-center">Digit</th>
                        <th class="p-4 font-bold text-center">Terakhir</th>
                        <th class="p-4 font-bold">Nomor Berikutnya</th>
                        <th class="p-4 text-center font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($formats as $f)
                    <tr class="hover:bg-slate-50/60">
                        <td class="p-4">
                            <div class="font-bold text-[#002266]">{{ $f->jenis }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $f->kode }}</div>
                        </td>
                        <td class="p-4 max-w-[220px]"><span class="text-slate-500">{{ $f->deskripsi ?? '-' }}</span></td>
                        <td class="p-4"><span class="px-2 py-1 bg-blue-50 border border-blue-200 rounded-lg font-mono font-bold text-blue-700">{{ $f->prefix }}</span></td>
                        <td class="p-4 font-bold">{{ $f->tahun ?? '-' }}</td>
                        <td class="p-4 text-center font-bold">{{ $f->digit }}</td>
                        <td class="p-4 text-center">
                            @if($f->nomor_terakhir > 0)
                                <span class="font-mono text-slate-500">{{ $f->nomorTerkini() }}</span>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="p-4"><span class="px-2.5 py-1.5 bg-emerald-50 border border-emerald-200 rounded-lg font-mono font-bold text-emerald-700">{{ $f->nomorBerikutnya() }}</span></td>
                        <td class="p-4">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="bukaEdit({{ $f->id }})" class="px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-lg text-[11px] font-bold transition">Ubah</button>
                                @if(!in_array($f->kode, ['kunjungan', 'customer', 'tool']))
                                <form action="{{ route('master.format-nomor.destroy', $f->id) }}" method="POST" onsubmit="return confirm('Hapus format ini?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-[11px] font-bold transition">Hapus</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="p-8 text-center text-slate-400">Belum ada format nomor.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-[11px] text-blue-800 font-medium">
        <span class="font-bold">Cara kerja:</span> setiap ada kunjungan/customer baru, sistem otomatis mengambil nomor berikutnya dari format ini (counter naik +1). Format bawaan <span class="font-mono font-bold">kunjungan</span>, <span class="font-mono font-bold">customer</span>, dan <span class="font-mono font-bold">tool</span> tidak bisa dihapus, tapi prefix, tahun, digit, dan counter-nya bebas diubah.
    </div>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <form action="{{ route('master.format-nomor.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <h4 class="text-sm font-bold text-[#002266]">Tambah Format Nomor</h4>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kode (kunci, huruf kecil tanpa spasi)</label>
                <input type="text" name="kode" required placeholder="misal: laporan" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-[#003399]">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jenis</label>
                <input type="text" name="jenis" required placeholder="misal: ID Laporan" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#003399]">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Deskripsi</label>
                <textarea name="deskripsi" rows="2" placeholder="Menjelaskan ID ini untuk apa..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#003399]"></textarea>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Prefix</label>
                    <input type="text" name="prefix" data-preview required placeholder="vmsmit" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-[#003399]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tahun</label>
                    <input type="number" name="tahun" data-preview value="{{ date('Y') }}" min="2000" max="2100" placeholder="Opsional" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <p class="text-[10px] text-slate-400 mt-1">Kosongkan kalau tidak pakai tahun.</p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Digit</label>
                    <input type="number" name="digit" data-preview required value="3" min="1" max="10" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#003399]">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nomor terakhir dipakai</label>
                <input type="number" name="nomor_terakhir" data-preview required value="0" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#003399]">
            </div>
            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-1">Nomor berikutnya</div>
                <div data-preview-out class="font-mono font-bold text-emerald-700 text-base">-</div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-[#002266] hover:bg-[#001233] text-white rounded-xl text-xs font-bold transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <form id="formEdit" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <h4 class="text-sm font-bold text-[#002266]">Ubah Format Nomor</h4>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jenis</label>
                <input type="text" name="jenis" id="edit_jenis" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#003399]">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Deskripsi</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#003399]"></textarea>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Prefix</label>
                    <input type="text" name="prefix" id="edit_prefix" data-preview required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-[#003399]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tahun</label>
                    <input type="number" name="tahun" id="edit_tahun" data-preview min="2000" max="2100" placeholder="Opsional" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#003399]">
                    <p class="text-[10px] text-slate-400 mt-1">Kosongkan kalau tidak pakai tahun.</p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Digit</label>
                    <input type="number" name="digit" id="edit_digit" data-preview required min="1" max="10" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#003399]">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nomor terakhir dipakai</label>
                <input type="number" name="nomor_terakhir" id="edit_nomor_terakhir" data-preview required min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#003399]">
                <p class="text-[10px] text-slate-400 mt-1">Ubah angka ini kalau mau lompat/mundur nomor urut.</p>
            </div>
            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-1">Nomor berikutnya</div>
                <div data-preview-out class="font-mono font-bold text-emerald-700 text-base">-</div>
            </div>
            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200">
                <p class="text-[10px] text-amber-700 leading-relaxed"><span class="font-bold">Perhatian:</span> kalau prefix, tahun, atau digit diubah, <span class="font-bold">semua kode yang sudah ada</span> otomatis disinkronkan ke format baru.</p>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-[#002266] hover:bg-[#001233] text-white rounded-xl text-xs font-bold transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
const formats = @json($formats->keyBy('id'));

function previewNomor(prefix, tahun, digit, terakhir) {
    const yy = tahun ? String(tahun).slice(-2) : '';
    const next = String(Number(terakhir) + 1).padStart(Number(digit) || 1, '0');
    return (prefix || '') + yy + next;
}

function bindPreview(form) {
    const out = form.querySelector('[data-preview-out]');
    const update = () => {
        const get = n => form.querySelector(`[name="${n}"]`).value;
        out.textContent = previewNomor(get('prefix'), get('tahun'), get('digit'), get('nomor_terakhir'));
    };
    form.querySelectorAll('[data-preview]').forEach(el => el.addEventListener('input', update));
    update();
}

document.querySelectorAll('#modalTambah form, #formEdit').forEach(bindPreview);

function bukaEdit(id) {
    const f = formats[id];
    document.getElementById('formEdit').action = "{{ url('master/format-nomor') }}/" + id;
    document.getElementById('edit_jenis').value = f.jenis;
    document.getElementById('edit_deskripsi').value = f.deskripsi ?? '';
    document.getElementById('edit_prefix').value = f.prefix;
    document.getElementById('edit_tahun').value = f.tahun ?? '';
    document.getElementById('edit_digit').value = f.digit;
    document.getElementById('edit_nomor_terakhir').value = f.nomor_terakhir;
    document.getElementById('edit_prefix').dispatchEvent(new Event('input'));
    document.getElementById('modalEdit').classList.remove('hidden');
}
</script>
@endsection
