<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Models\Tip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TipsController extends Controller
{
    public const CATEGORIES = [
        'Budidaya Tanaman',
        'Hama & Penyakit',
        'Irigasi & Air',
        'Nutrisi & Pupuk',
        'Perawatan Tanaman',
        'Panen & Pasca Panen',
    ];
    /**
     * Daftar komoditas tanaman budidaya yang sah.
     */
    public static array $allowedCommodities = [
        'Semua Komoditas',
        // Pangan
        'Padi Sawah',
        'Padi Gogo',
        'Jagung',
        'Kedelai',
        'Kacang Tanah',
        'Kacang Hijau',
        'Singkong / Ubi Kayu',
        'Ubi Jalar',
        // Sayuran & Hortikultura
        'Cabai Rawit',
        'Cabai Merah',
        'Bawang Merah',
        'Bawang Putih',
        'Tomat',
        'Terong',
        'Bayam',
        'Kangkung',
        'Sawi & Pakcoy',
        'Kubis / Kol',
        'Mentimun',
        'Kacang Panjang',
        'Wortel',
        'Kentang',
        // Buah-buahan
        'Melon',
        'Semangka',
        'Pisang',
        'Pepaya',
        'Jeruk',
        'Mangga',
        'Alpukat',
        'Durian',
        'Nanas',
        'Jambu Kristal',
        // Perkebunan & Rempah
        'Kopi',
        'Kakao / Cokelat',
        'Kelapa',
        'Cengkeh',
        'Lada',
        'Jahe',
        'Kunyit',
        'Lengkuas',
        'Serai',
        // Variasi historis
        'Hortikultura / Sayur',
        'Semua Tanaman',
    ];
    /**
     * Display a listing of agricultural tips.
     */
    public function index(Request $request)
    {
        $query = Tip::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $operator) {
                $q->where('judul', $operator, "%{$search}%")
                  ->orWhere('ringkasan', $operator, "%{$search}%")
                  ->orWhere('komoditas', $operator, "%{$search}%")
                  ->orWhere('kategori', $operator, "%{$search}%")
                  ->orWhere('target', $operator, "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->input('kategori'));
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $tips = $query->orderBy('created_at', 'desc')->get();
        $totalCount = Tip::count();
        $publikCount = Tip::where('status', 'Publik')->count();
        $draftCount = Tip::where('status', 'Draft')->count();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $tips,
                'total' => $totalCount,
                'publik' => $publikCount,
                'draft' => $draftCount,
            ]);
        }

        $commodities = Komoditas::orderBy('nama_komoditas', 'asc')->get()->groupBy('kategori');

        return view('Admin.Content.Tips', [
            'tips' => $tips,
            'items' => $tips, // Backward compatibility
            'totalCount' => $totalCount,
            'publikCount' => $publikCount,
            'draftCount' => $draftCount,
            'commodities' => $commodities,
        ]);
    }

    /**
     * Store a newly created tip in storage.
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:255', 'unique:tips,judul'],
            'kategori' => ['required', 'string', 'in:' . implode(',', self::CATEGORIES)],
            'komoditas' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value !== 'Semua Komoditas' && !Komoditas::where('nama_komoditas', $value)->exists()) {
                        $fail('Komoditas sasaran harus berupa tanaman budidaya yang valid.');
                    }
                },
            ],
            'target' => 'nullable|string|max:150',
            'ringkasan' => 'required|string',
            'isi' => 'required|string|unique:tips,isi',
            'gambar_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'gambar' => 'nullable|string|max:255',
            'status' => 'required|in:Publik,Draft',
        ], [
            'judul.required' => 'Judul tips wajib diisi.',
            'judul.unique' => 'Judul tips sudah digunakan.',
            'kategori.required' => 'Kategori tips wajib dipilih.',
            'komoditas.required' => 'Komoditas sasaran wajib dipilih.',
            'komoditas.in' => 'Komoditas sasaran harus berupa tanaman budidaya yang valid.',
            'ringkasan.required' => 'Ringkasan / inti tips wajib diisi.',
            'isi.required' => 'Isi tips wajib diisi.',
            'isi.unique' => 'Isi tips sudah digunakan.',
            'status.required' => 'Status tips wajib dipilih.',
            'status.in' => 'Status harus bernilai Publik atau Draft.',
            'gambar_file.image' => 'File harus berupa gambar.',
            'gambar_file.mimes' => 'Format gambar yang diperbolehkan hanya JPG, JPEG, PNG, WEBP, atau SVG.',
            'gambar_file.max' => 'Ukuran gambar maksimal 2MB.',
        ])->validate();

        $gambarPath = $validated['gambar'] ?? 'tips-pertanian.jpg';
        if ($request->hasFile('gambar_file')) {
            $file = $request->file('gambar_file');
            $filename = 'tip_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/tips');
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $gambarPath = 'uploads/tips/' . $filename;
        }

        $tip = Tip::create([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'komoditas' => !empty($validated['komoditas']) ? $validated['komoditas'] : 'Semua Komoditas',
            'target' => 'Semua Kelompok',
            'ringkasan' => $validated['ringkasan'],
            'isi' => $validated['isi'] ?? null,
            'gambar' => $gambarPath,
            'tanggal' => now()->toDateString(),
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tips pertanian berhasil ditambahkan.',
                'data' => $tip,
            ], 201);
        }

        return redirect()->route('admin.edukasi.tips')
            ->with('success', 'Tips pertanian "' . $tip->judul . '" berhasil ditambahkan.');
    }

    /**
     * Update the specified tip in storage.
     */
    public function update(Request $request, string $id_tips)
    {
        $tip = Tip::findOrFail($id_tips);

        $validated = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:255', Rule::unique('tips', 'judul')->ignore($tip->id_tips, 'id_tips')],
            'kategori' => ['required', 'string', 'in:' . implode(',', self::CATEGORIES)],
            'komoditas' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value !== 'Semua Komoditas' && !Komoditas::where('nama_komoditas', $value)->exists()) {
                        $fail('Komoditas sasaran harus berupa tanaman budidaya yang valid.');
                    }
                },
            ],
            'target' => 'nullable|string|max:150',
            'ringkasan' => 'required|string',
            'isi' => ['required', 'string', Rule::unique('tips', 'isi')->ignore($tip->id_tips, 'id_tips')],
            'gambar_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'gambar' => 'nullable|string|max:255',
            'status' => 'required|in:Publik,Draft',
        ], [
            'judul.required' => 'Judul tips wajib diisi.',
            'judul.unique' => 'Judul tips sudah digunakan.',
            'kategori.required' => 'Kategori tips wajib dipilih.',
            'komoditas.required' => 'Komoditas sasaran wajib dipilih.',
            'komoditas.in' => 'Komoditas sasaran harus berupa tanaman budidaya yang valid.',
            'ringkasan.required' => 'Ringkasan / inti tips wajib diisi.',
            'isi.required' => 'Isi tips wajib diisi.',
            'isi.unique' => 'Isi tips sudah digunakan.',
            'status.required' => 'Status tips wajib dipilih.',
            'status.in' => 'Status harus bernilai Publik atau Draft.',
            'gambar_file.image' => 'File harus berupa gambar.',
            'gambar_file.mimes' => 'Format gambar yang diperbolehkan hanya JPG, JPEG, PNG, WEBP, atau SVG.',
            'gambar_file.max' => 'Ukuran gambar maksimal 2MB.',
        ])->validate();

        $gambarPath = $tip->gambar;
        if ($request->hasFile('gambar_file')) {
            $file = $request->file('gambar_file');
            $filename = 'tip_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/tips');
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
            $file->move($destination, $filename);

            // Delete old uploaded image if exists
            if ($tip->gambar && str_starts_with($tip->gambar, 'uploads/tips/') && File::exists(public_path($tip->gambar))) {
                File::delete(public_path($tip->gambar));
            }

            $gambarPath = 'uploads/tips/' . $filename;
        } elseif (!empty($validated['gambar'])) {
            $gambarPath = $validated['gambar'];
        }

        $tip->update([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'komoditas' => !empty($validated['komoditas']) ? $validated['komoditas'] : 'Semua Komoditas',
            'target' => 'Semua Kelompok',
            'ringkasan' => $validated['ringkasan'],
            'isi' => $validated['isi'] ?? null,
            'gambar' => $gambarPath,
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tips pertanian berhasil diperbarui.',
                'data' => $tip,
            ]);
        }

        return redirect()->route('admin.edukasi.tips')
            ->with('success', 'Tips pertanian "' . $tip->judul . '" berhasil diperbarui.');
    }

    /**
     * Remove the specified tip from storage.
     */
    public function destroy(Request $request, string $id_tips)
    {
        $tip = Tip::findOrFail($id_tips);
        $judul = $tip->judul;

        // Delete uploaded file if stored locally
        if ($tip->gambar && str_starts_with($tip->gambar, 'uploads/tips/') && File::exists(public_path($tip->gambar))) {
            File::delete(public_path($tip->gambar));
        }

        $tip->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tips pertanian "' . $judul . '" berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.edukasi.tips')
            ->with('success', 'Tips pertanian "' . $judul . '" berhasil dihapus.');
    }

    /**
     * Store a newly created commodity in storage.
     */
    public function storeKomoditas(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'nama_komoditas' => 'required|string|max:100|unique:komoditas,nama_komoditas',
            'kategori' => 'required|string|in:Tanaman Pangan,Hortikultura & Sayuran,Buah-buahan,Perkebunan & Rempah',
        ], [
            'nama_komoditas.required' => 'Nama tanaman komoditas wajib diisi.',
            'nama_komoditas.unique' => 'Tanaman komoditas ini sudah terdaftar.',
            'kategori.required' => 'Kategori tanaman wajib dipilih.',
            'kategori.in' => 'Kategori tanaman tidak valid.',
        ])->validate();

        $komoditas = Komoditas::create([
            'nama_komoditas' => trim($validated['nama_komoditas']),
            'kategori' => $validated['kategori'],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Komoditas '{$komoditas->nama_komoditas}' berhasil ditambahkan ke daftar tanaman.",
            'data' => $komoditas,
        ], 201);
    }
}
