<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArtikelController extends Controller
{
    public function index(Request $request)
    {
        $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $query = Artikel::query();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($q) use ($search, $operator) {
                $q->where('judul', $operator, "%{$search}%")
                    ->orWhere('kategori', $operator, "%{$search}%")
                    ->orWhere('komoditas', $operator, "%{$search}%");
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

        $kategoriOptions = Artikel::query()->whereNotNull('kategori')->where('kategori', '<>', '')->distinct()->orderBy('kategori')->pluck('kategori');
        $komoditasOptions = Artikel::query()->whereNotNull('komoditas')->where('komoditas', '<>', '')->distinct()->orderBy('komoditas')->pluck('komoditas');

        return view('Admin.Content.Artikel', compact('artikelList', 'stats', 'kategoriOptions', 'komoditasOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateArticle($request);
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

    public function update(Request $request, Artikel $artikel)
    {
        $validated = $this->validateArticle($request, true);
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

    public function destroy(Artikel $artikel)
    {
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
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'komoditas' => ['nullable', 'string', 'max:100'],
            'ringkasan' => ['required', 'string', 'max:1000'],
            'isi' => ['required', 'string'],
            'gambar' => [$isUpdate ? 'nullable' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'tanggal' => ['nullable', 'date'],
            'status' => ['required', 'in:Publik,Draft'],
        ], [
            'judul.required' => 'Judul artikel wajib diisi.',
            'kategori.required' => 'Kategori artikel wajib diisi.',
            'ringkasan.required' => 'Ringkasan artikel wajib diisi.',
            'isi.required' => 'Isi artikel wajib diisi.',
            'gambar.image' => 'File gambar harus berupa gambar.',
            'gambar.mimes' => 'Gambar harus berformat JPG, JPEG, PNG, atau WEBP.',
            'gambar.max' => 'Ukuran gambar maksimal 4 MB.',
            'status.in' => 'Status artikel tidak valid.',
        ]);
    }

    private function storeImage(Request $request): string
    {
        $directory = public_path('uploads/edukasi/artikel');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = $request->file('gambar');
        $extension = strtolower($file->extension());
        $filename = Str::uuid()->toString() . '.' . $extension;
        $file->move($directory, $filename);

        return 'uploads/edukasi/artikel/' . $filename;
    }

    private function deleteImage(?string $path): void
    {
        if (! $path || ! Str::startsWith($path, 'uploads/edukasi/artikel/')) {
            return;
        }

        $fullPath = public_path($path);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
