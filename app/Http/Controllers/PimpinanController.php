<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PimpinanController extends Controller
{
    public function index(Request $request)
    {
        // Hanya akun dengan role Pimpinan (id_role = 2)
        $query = User::where('id_role', 2);

        // Fitur Pencarian (Search by Nama atau Username)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('username', 'like', '%' . $search . '%');
            });
        }

        // Fitur Sortir (Terbaru / Terlama)
        $sort = $request->get('sort', 'terbaru');
        if ($sort == 'terlama') {
            $query->oldest('id_pengguna');
        } else {
            $query->latest('id_pengguna');
        }

        // Pagination max 10, dan bawa parameter pencarian ke halaman berikutnya
        $pimpinans = $query->paginate(10)->appends($request->all());

        return view('master.pimpinan.index', compact('pimpinans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:pengguna,username',
            'email' => 'required|email|max:100|unique:pengguna,email',
            'password' => 'required|string|min:6',
            'kontak' => 'required|string|max:20',
        ]);

        User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'kontak' => $request->kontak,
            'id_role' => 2, // Pimpinan
            'status_akun' => 'Aktif',
            'dibuat_oleh' => Auth::user()->id_pengguna,
        ]);

        return redirect()->back()->with('success', 'Data Pimpinan & Akun Login berhasil dibuat!');
    }

    public function update(Request $request, $id)
    {
        $pimpinan = User::where('id_role', 2)->findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:pengguna,username,' . $pimpinan->id_pengguna . ',id_pengguna',
            'email' => 'required|email|max:100|unique:pengguna,email,' . $pimpinan->id_pengguna . ',id_pengguna',
            'kontak' => 'required|string|max:20',
            'status_akun' => 'required|in:Aktif,Nonaktif',
        ]);

        $userData = [
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'kontak' => $request->kontak,
            'status_akun' => $request->status_akun,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $pimpinan->update($userData);

        return redirect()->back()->with('success', 'Data Pimpinan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $pimpinan = User::where('id_role', 2)->findOrFail($id);

        // Jangan hapus akun sendiri
        if ($pimpinan->id_pengguna == Auth::user()->id_pengguna) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri!');
        }

        $pimpinan->delete();

        return redirect()->back()->with('success', 'Pimpinan berhasil dihapus!');
    }
}
