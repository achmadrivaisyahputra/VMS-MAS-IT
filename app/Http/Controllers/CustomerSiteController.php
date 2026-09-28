<?php

namespace App\Http\Controllers;

use App\Models\CustomerSite;
use Illuminate\Http\Request;

class CustomerSiteController extends Controller
{
    // Simpan Data Cabang Baru
    public function store(Request $request, $kode)
    {
        $validated = $request->validate([
            'nama_cabang' => 'required|string|max:100',
            'alamat_lengkap' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ]);

        $customer = \App\Models\Customer::where('kode', $kode)->firstOrFail();
        $validated['id_customer'] = $customer->id_customer;
        CustomerSite::create($validated);

        return redirect()->back()->with('success', 'Cabang/Site berhasil ditambahkan!');
    }

    // Update Data Cabang
    public function update(Request $request, $id_site)
    {
        $site = CustomerSite::findOrFail($id_site);

        $validated = $request->validate([
            'nama_cabang' => 'required|string|max:100',
            'alamat_lengkap' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ]);

        $site->update($validated);

        return redirect()->back()->with('success', 'Data Cabang berhasil diperbarui!');
    }

    // Hapus Data Cabang
    public function destroy($id_site)
    {
        $site = CustomerSite::findOrFail($id_site);
        $site->delete();

        return redirect()->back()->with('success', 'Cabang/Site berhasil dihapus!');
    }
}