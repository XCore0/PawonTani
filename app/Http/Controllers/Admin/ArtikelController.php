<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\Komoditas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArtikelController extends Controller
{
    public const CATEGORIES = [
        'Budidaya Tanaman',
        'Hama & Penyakit',
        'Irigasi & Air',
        'Nutrisi & Pupuk',
        'Perawatan Tanaman',
        'Panen & Pasca Panen',
    ];

    public function index(Request $request)
    {
        $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $query = Artikel::query();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($q) use ($search, $operator) {
                $q->where('judul', $operator, "%{$search}%")
                    ->orWhere('kategori', $operator, "%{$search}%")
                    ->orWhereHas('komoditas', function ($kq) use ($search, $operator) {
                        $kq->where('nama_komoditas', $operator, "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $artikelList = $query->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Artikel::count(),
            'publik' => Artikel::where('status', 'Publik')->count(),
            'draft' => Artikel::where('status', 'Draft')->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $artikelList,
                'stats' => $stats,
            ]);
        }

        $kategoriOptions = self::CATEGORIES;
        $komoditasOptions = Komoditas::query()
            ->orderBy('nama_komoditas')
            ->get()
            ->groupBy('kategori');

        return view('Admin.Content.Artikel', compact('artikelList', 'stats', 'kategoriOptions', 'komoditasOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateArticle($request);
        $isPublik = ($validated['status'] ?? 'Draft') === 'Publik';
        $validated['tanggal'] = $isPublik ? now()->toDateString() : null;
        $gambar = null;

        if ($request->hasFile('gambar')) {
            $gambar = $this->storeImage($request);
        }

        try {
            $artikel = Artikel::create(array_merge($validated, ['gambar' => $gambar]));
        } catch (\Throwable $e) {
            if ($gambar) {
                $this->deleteImage($gambar);
            }
            throw $e;
        }

        return redirect()->route('admin.edukasi.artikel')
            ->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function update(Request $request, string $id_artikel)
    {
        $artikel = Artikel::findOrFail($id_artikel);
        $validated = $this->validateArticle($request, true);
        $isPublik = ($validated['status'] ?? 'Draft') === 'Publik';
        $validated['tanggal'] = $isPublik
            ? ($artikel->tanggal?->toDateString() ?: now()->toDateString())
            : null;
        $oldImage = $artikel->gambar;
        $newImage = null;

        if ($request->hasFile('gambar')) {
            $newImage = $this->storeImage($request);
            $validated['gambar'] = $newImage;
        }

        try {
            $artikel->update($validated);
        } catch (\Throwable $e) {
            if ($newImage) {
                $this->deleteImage($newImage);
            }
            throw $e;
        }

        if ($newImage && $oldImage && $oldImage !== $newImage) {
            $this->deleteImage($oldImage);
        }

        return redirect()->route('admin.edukasi.artikel')
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(string $id_artikel)
    {
        $artikel = Artikel::findOrFail($id_artikel);
        $image = $artikel->gambar;
        $artikel->delete();

        if ($image) {
            $this->deleteImage($image);
        }

        return redirect()->route('admin.edukasi.artikel')
            ->with('success', 'Artikel berhasil dihapus.');
    }

    private function validateArticle(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'min:5', 'max:255', $isUpdate ? Rule::unique('artikel', 'judul')->ignore($request->input('id_artikel'), 'id_artikel') : 'unique:artikel,judul'],
            'kategori' => ['required', 'string', 'in:' . implode(',', self::CATEGORIES)],
            'komoditas_id' => [
                'nullable',
                'string',
                'max:20',
                'exists:komoditas,id_komoditas',
            ],
            'ringkasan' => ['required', 'string', 'min:10', 'max:1000'],
            'isi' => ['required', 'string', 'min:20', $isUpdate ? Rule::unique('artikel', 'isi')->ignore($request->input('id_artikel'), 'id_artikel') : 'unique:artikel,isi'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'status' => ['required', 'in:Publik,Draft'],
        ], [
            'judul.required' => 'Judul artikel wajib diisi.',
            'judul.min' => 'Judul artikel minimal 5 karakter.',
            'judul.max' => 'Judul artikel maksimal 255 karakter.',
            'judul.unique' => 'Judul artikel sudah digunakan.',
            'kategori.required' => 'Kategori artikel wajib diisi.',
            'ringkasan.required' => 'Ringkasan artikel wajib diisi.',
            'ringkasan.min' => 'Ringkasan artikel minimal 10 karakter.',
            'ringkasan.max' => 'Ringkasan artikel maksimal 1000 karakter.',
            'isi.required' => 'Isi artikel wajib diisi.',
            'isi.min' => 'Isi artikel minimal 20 karakter.',
            'isi.unique' => 'Isi artikel sudah digunakan.',
            'gambar.image' => 'File gambar harus berupa gambar.',
            'gambar.mimes' => 'Gambar harus berformat JPG, JPEG, PNG, WEBP, atau SVG.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
            'status.in' => 'Status artikel tidak valid.',
        ]);
    }

    private function storeImage(Request $request): string
    {
        $cloudinary = app(\App\Services\CloudinaryService::class);
        return $cloudinary->upload($request->file('gambar')->getRealPath(), 'pawontani/artikel');
    }

    private function deleteImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        $cloudinary = app(\App\Services\CloudinaryService::class);
        $cloudinary->delete($path);
    }
}
