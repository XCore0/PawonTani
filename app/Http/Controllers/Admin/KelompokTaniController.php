<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelompokTani;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class KelompokTaniController extends Controller
{
    /**
     * Display a listing of Kelompok Tani.
     */
    public function index(Request $request)
    {
        $kelompokList = $this->filteredGroups($request)->orderBy('created_at', 'desc')->get();
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
     * Unduh daftar kelompok tani sebagai Excel XML (SpreadsheetML 2003).
     * Format ini dapat dibuka langsung melalui Microsoft Excel tanpa paket tambahan.
     */
    public function exportExcel(Request $request)
    {
        $kelompokList = $this->filteredGroups($request)
            ->withCount(['anggota as total_anggota' => fn ($query) => $query->whereHas('anggota', fn ($membership) => $membership->where('status_keanggotaan', '!=', 'Keluar'))])
            ->orderBy('nama_kelompok')
            ->get();

        $escape = static fn ($value): string => htmlspecialchars((string) ($value ?? '-'), ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $rows = [];
        $rows[] = '<Row ss:StyleID="Title"><Cell ss:MergeAcross="6"><Data ss:Type="String">DATA KELOMPOK TANI - PAWONTANI</Data></Cell></Row>';
        $rows[] = '<Row><Cell ss:MergeAcross="6"><Data ss:Type="String">Diekspor pada ' . $escape(now()->format('d-m-Y H:i')) . '</Data></Cell></Row>';
        $rows[] = '<Row ss:StyleID="Header">'
            . '<Cell><Data ss:Type="String">No</Data></Cell>'
            . '<Cell><Data ss:Type="String">ID Kelompok</Data></Cell>'
            . '<Cell><Data ss:Type="String">Nama Kelompok</Data></Cell>'
            . '<Cell><Data ss:Type="String">Alamat</Data></Cell>'
            . '<Cell><Data ss:Type="String">Status</Data></Cell>'
            . '<Cell><Data ss:Type="String">Jumlah Anggota</Data></Cell>'
            . '<Cell><Data ss:Type="String">Tanggal Dibuat</Data></Cell>'
            . '</Row>';

        foreach ($kelompokList as $index => $kelompok) {
            $rows[] = '<Row>'
                . '<Cell><Data ss:Type="Number">' . ($index + 1) . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">' . $escape($kelompok->id_kelompok) . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">' . $escape($kelompok->nama_kelompok) . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">' . $escape($kelompok->alamat) . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">' . $escape($kelompok->status) . '</Data></Cell>'
                . '<Cell><Data ss:Type="Number">' . (int) $kelompok->total_anggota . '</Data></Cell>'
                . '<Cell><Data ss:Type="String">' . $escape($kelompok->created_at?->format('d-m-Y H:i') ?? '-') . '</Data></Cell>'
                . '</Row>';
        }

        if ($kelompokList->isEmpty()) {
            $rows[] = '<Row><Cell ss:MergeAcross="6"><Data ss:Type="String">Tidak ada data kelompok tani.</Data></Cell></Row>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<?mso-application progid="Excel.Sheet"?>'
            . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
            . 'xmlns:o="urn:schemas-microsoft-com:office:office" '
            . 'xmlns:x="urn:schemas-microsoft-com:office:excel" '
            . 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" '
            . 'xmlns:html="http://www.w3.org/TR/REC-html40">'
            . '<Styles>'
            . '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="10"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E4F0D6"/></Borders></Style>'
            . '<Style ss:ID="Title"><Font ss:FontName="Calibri" ss:Size="16" ss:Bold="1" ss:Color="#1A2D10"/><Alignment ss:Vertical="Center"/><Interior ss:Color="#EBF6E0" ss:Pattern="Solid"/></Style>'
            . '<Style ss:ID="Header"><Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#4D9830" ss:Pattern="Solid"/><Alignment ss:Vertical="Center"/></Style>'
            . '</Styles>'
            . '<Worksheet ss:Name="Kelompok Tani"><Table>'
            . '<Column ss:Width="40"/><Column ss:Width="125"/><Column ss:Width="190"/><Column ss:Width="260"/><Column ss:Width="90"/><Column ss:Width="90"/><Column ss:Width="120"/>'
            . implode('', $rows)
            . '</Table><WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><PageSetup><Layout x:Orientation="Landscape"/></PageSetup><Print><FitWidth>1</FitWidth><ValidPrinterInfo/></Print></WorksheetOptions></Worksheet>'
            . '</Workbook>';

        $filename = 'data-kelompok-tani-' . now()->format('Ymd-His') . '.xls';

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /**
     * Menampilkan lembar khusus yang siap dicetak atau disimpan sebagai PDF.
     */
    public function exportPdf(Request $request)
    {
        $kelompokList = $this->filteredGroups($request)
            ->withCount(['anggota as total_anggota' => fn ($query) => $query->whereHas('anggota', fn ($membership) => $membership->where('status_keanggotaan', '!=', 'Keluar'))])
            ->orderBy('nama_kelompok')
            ->get();

        return view('Admin.Content.Kelompok.Print', compact('kelompokList'));
    }

    /** Query daftar kelompok yang juga dipakai oleh fitur ekspor. */
    private function filteredGroups(Request $request): Builder
    {
        $query = KelompokTani::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $operator = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($builder) use ($search, $operator) {
                $builder->where('nama_kelompok', $operator, "%{$search}%")
                    ->orWhere('alamat', $operator, "%{$search}%")
                    ->orWhere('id_kelompok', $operator, "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $query;
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
                function ($attribute, $value, $fail) {
                    $cleaned = trim(preg_replace('/\s+/', ' ', $value));
                    if (KelompokTani::whereRaw('LOWER(TRIM(nama_kelompok)) = ?', [mb_strtolower($cleaned)])->exists()) {
                        $fail('Nama kelompok tani sudah terdaftar.');
                    }
                },
            ],
            'alamat' => 'required|string',
            'status' => 'required|in:Aktif,Tidak Aktif',
        ], [
            'nama_kelompok.required' => 'Nama kelompok tani wajib diisi.',
            'nama_kelompok.regex' => 'Nama kelompok tani hanya boleh berisi huruf dan spasi.',
            'alamat.required' => 'Alamat kelompok tani wajib diisi.',
            'status.required' => 'Status kelompok tani wajib dipilih.',
            'status.in' => 'Status harus berupa Aktif atau Tidak Aktif.',
        ]);

        $kelompok = KelompokTani::create([
            'id_kelompok' => KelompokTani::generateIdKelompok(),
            'nama_kelompok' => trim(preg_replace('/\s+/', ' ', $validated['nama_kelompok'])),
            'alamat' => $validated['alamat'],
            'status' => $validated['status'],
        ]);

        AuditLogger::log('kelompok.tambah', 'Admin menambahkan kelompok tani.', 'KelompokTani', $kelompok->id_kelompok, $kelompok->id_kelompok, $request);

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
                function ($attribute, $value, $fail) use ($id_kelompok) {
                    $cleaned = trim(preg_replace('/\s+/', ' ', $value));
                    if (KelompokTani::whereRaw('LOWER(TRIM(nama_kelompok)) = ?', [mb_strtolower($cleaned)])
                        ->where('id_kelompok', '!=', $id_kelompok)
                        ->exists()) {
                        $fail('Nama kelompok tani sudah terdaftar.');
                    }
                },
            ],
            'alamat' => 'required|string',
            'status' => 'required|in:Aktif,Tidak Aktif',
        ], [
            'nama_kelompok.required' => 'Nama kelompok tani wajib diisi.',
            'nama_kelompok.regex' => 'Nama kelompok tani hanya boleh berisi huruf dan spasi.',
            'alamat.required' => 'Alamat kelompok tani wajib diisi.',
            'status.required' => 'Status kelompok tani wajib dipilih.',
            'status.in' => 'Status harus berupa Aktif atau Tidak Aktif.',
        ]);

        $oldStatus = $kelompok->status;
        $validated['nama_kelompok'] = trim(preg_replace('/\s+/', ' ', $validated['nama_kelompok']));
        $kelompok->update($validated);

        // Jika status kelompok berubah, sinkronkan status akun seluruh anggota dan pengurus di kelompok ini
        if ($oldStatus !== $validated['status']) {
            \App\Models\Pengguna::where('id_kelompok', $kelompok->id_kelompok)
                ->whereIn('role', ['Anggota', 'Pengurus'])
                ->update(['status' => $validated['status']]);

            AuditLogger::log(
                'kelompok.sinkron_status_akun',
                "Status seluruh akun kelompok {$kelompok->nama_kelompok} disinkronkan menjadi {$validated['status']}.",
                'KelompokTani',
                $kelompok->id_kelompok,
                $kelompok->id_kelompok,
                $request
            );
        }

        AuditLogger::log('kelompok.ubah', 'Admin memperbarui data kelompok tani.', 'KelompokTani', $kelompok->id_kelompok, $kelompok->id_kelompok, $request);

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
        AuditLogger::log('kelompok.hapus', 'Admin menghapus kelompok tani.', 'KelompokTani', $deletedId, $deletedId, $request);

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
