<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerSite;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    // Halaman utama Master Data Site (semua site dari semua customer)
    public function index(Request $request)
    {
        $query = CustomerSite::with('customer');

        // Filter berdasarkan customer
        if ($request->filled('id_customer')) {
            $query->where('id_customer', $request->id_customer);
        }

        // Fitur pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_cabang', 'like', '%' . $search . '%')
                  ->orWhere('alamat_lengkap', 'like', '%' . $search . '%')
                  ->orWhereHas('customer', function ($qc) use ($search) {
                      $qc->where('nama_perusahaan', 'like', '%' . $search . '%');
                  });
            });
        }

        // Fitur sortir
        $sort = $request->get('sort', 'terbaru');
        if ($sort == 'terlama') {
            $query->oldest('id_site');
        } else {
            $query->latest('id_site');
        }

        $sites = $query->paginate(10)->appends($request->all());
        $customers = Customer::orderBy('nama_perusahaan')->get();

        return view('master.site.index', compact('sites', 'customers'));
    }

    // Simpan site baru (customer dipilih dari dropdown)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_customer' => 'required|exists:customers,id_customer',
            'nama_cabang' => 'required|string|max:100',
            'alamat_lengkap' => 'required|string',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
        ]);

        CustomerSite::create($validated);

        return redirect()->back()->with('success', 'Site berhasil ditambahkan!');
    }

    // Update data site
    public function update(Request $request, $id)
    {
        $site = CustomerSite::findOrFail($id);

        $validated = $request->validate([
            'id_customer' => 'required|exists:customers,id_customer',
            'nama_cabang' => 'required|string|max:100',
            'alamat_lengkap' => 'required|string',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
        ]);

        $site->update($validated);

        return redirect()->back()->with('success', 'Data Site berhasil diperbarui!');
    }

    // Hapus site
    public function destroy($id)
    {
        $site = CustomerSite::findOrFail($id);
        $site->delete();

        return redirect()->back()->with('success', 'Site berhasil dihapus!');
    }
}
