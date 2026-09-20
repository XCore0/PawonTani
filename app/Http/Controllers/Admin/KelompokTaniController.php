<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelompokTani;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelompokTaniController extends Controller
{
    /**
     * Display a listing of Kelompok Tani.
     */
    public function index(Request $request)
    {
        $query = KelompokTani::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $operator = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $operator) {
                $q->where('nama_kelompok', $operator, "%{$search}%")
                  ->orWhere('alamat', $operator, "%{$search}%")
                  ->orWhere('id_kelompok', $operator, "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $kelompokList = $query->orderBy('created_at', 'desc')->get();
        $totalCount = KelompokTani::count();
        $aktifCount = KelompokTani::where('status', 'Aktif')->count();
        $tidakAktifCount = KelompokTani::where('status', 'Tidak Aktif')->count();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $kelompokList,
                'total' => $totalCount,
            ]);
        }

        return view('Admin.Content.Kelompok', compact(
            'kelompokList',
            'totalCount',
            'aktifCount',
            'tidakAktifCount'
        ));
    }

    /**
     * Store a newly created Kelompok Tani in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelompok' => [
                'required',
                'string',
                'max:150',
                'regex:/^[\pL\s]+$/u',
                Rule::unique('kelompok_tani', 'nama_kelompok'),
            ],
            'alamat' => 'required|string',
            'status' => 'required|in:Aktif,Tidak Aktif',
        ], [
            'nama_kelompok.required' => 'Nama kelompok tani wajib diisi.',
            'nama_kelompok.regex' => 'Nama kelompok tani hanya boleh berisi huruf dan spasi.',
            'nama_kelompok.unique' => 'Nama kelompok tani sudah terdaftar.',
            'alamat.required' => 'Alamat kelompok tani wajib diisi.',
            'status.required' => 'Status kelompok tani wajib dipilih.',
            'status.in' => 'Status harus berupa Aktif atau Tidak Aktif.',
        ]);

        $kelompok = KelompokTani::create([
            'id_kelompok' => KelompokTani::generateIdKelompok(),
            'nama_kelompok' => $validated['nama_kelompok'],
            'alamat' => $validated['alamat'],
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kelompok Tani berhasil ditambahkan dengan ID ' . $kelompok->id_kelompok,
                'data' => $kelompok,
            ], 201);
        }

        return redirect()->route('admin.kelompok')
            ->with('success', 'Kelompok Tani berhasil ditambahkan dengan ID ' . $kelompok->id_kelompok);
    }

    /**
     * Update the specified Kelompok Tani in storage.
     */
    public function update(Request $request, string $id_kelompok)
    {
        $kelompok = KelompokTani::findOrFail($id_kelompok);

        $validated = $request->validate([
            'nama_kelompok' => [
                'required',
                'string',
                'max:150',
                'regex:/^[\pL\s]+$/u',
                Rule::unique('kelompok_tani', 'nama_kelompok')->ignore($id_kelompok, 'id_kelompok'),
            ],
            'alamat' => 'required|string',
            'status' => 'required|in:Aktif,Tidak Aktif',
        ], [
            'nama_kelompok.required' => 'Nama kelompok tani wajib diisi.',
            'nama_kelompok.regex' => 'Nama kelompok tani hanya boleh berisi huruf dan spasi.',
            'nama_kelompok.unique' => 'Nama kelompok tani sudah terdaftar.',
            'alamat.required' => 'Alamat kelompok tani wajib diisi.',
            'status.required' => 'Status kelompok tani wajib dipilih.',
            'status.in' => 'Status harus berupa Aktif atau Tidak Aktif.',
        ]);

        $kelompok->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Kelompok Tani berhasil diperbarui!',
                'data' => $kelompok,
            ]);
        }

        return redirect()->route('admin.kelompok')
            ->with('success', 'Data Kelompok Tani ' . $kelompok->id_kelompok . ' berhasil diperbarui!');
    }

    /**
     * Remove the specified Kelompok Tani from storage.
     */
    public function destroy(Request $request, string $id_kelompok)
    {
        $kelompok = KelompokTani::findOrFail($id_kelompok);
        $deletedId = $kelompok->id_kelompok;
        $deletedName = $kelompok->nama_kelompok;
        $kelompok->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Kelompok Tani {$deletedName} ({$deletedId}) berhasil dihapus!",
            ]);
        }

        return redirect()->route('admin.kelompok')
            ->with('success', "Kelompok Tani {$deletedName} ({$deletedId}) berhasil dihapus!");
    }
}
