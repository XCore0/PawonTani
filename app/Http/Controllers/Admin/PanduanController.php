<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePanduanRequest;
use App\Http\Requests\UpdatePanduanRequest;
use App\Models\Komoditas;
use App\Models\Panduan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PanduanController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status');
        $kategori = trim((string) $request->input('kategori', ''));
        $komoditas = trim((string) $request->input('komoditas', ''));

        $query = Panduan::query()
            ->search($search)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($kategori, fn ($q) => $q->where('kategori', $kategori))
            ->when($komoditas, fn ($q) => $q->where('komoditas_id', $komoditas));

        $panduan = $query->with(['author', 'komoditas'])
            ->latest('tanggal')
            ->latest('id_panduan')
            ->paginate(10)
            ->withQueryString();

        $categories = StorePanduanRequest::CATEGORIES;

        $commodities = Komoditas::query()
            ->orderBy('nama_komoditas')
            ->get()
            ->groupBy('kategori');

        $counts = [
            'total' => Panduan::count(),
            'publik' => Panduan::publik()->count(),
            'draft' => Panduan::draft()->count(),
        ];

        return view('Admin.Content.Panduan', compact(
            'panduan', 'categories', 'commodities', 'counts',
            'search', 'status', 'kategori', 'komoditas'
        ));
    }

    public function store(StorePanduanRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $isPublik = ($data['status'] ?? 'Draft') === 'Publik';
        $data['tanggal'] = $isPublik ? now()->toDateString() : null;
        $data['slug'] = $this->uniqueSlug($data['judul']);
        $data['created_by'] = auth()->id();

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $this->storeImage($request);
        }

        $panduan = Panduan::create($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Panduan berhasil ditambahkan.',
                'data' => $panduan->load('author'),
                'redirect' => route('admin.edukasi.panduan'),
            ], 201);
        }

        return redirect()->route('admin.edukasi.panduan')
            ->with('success', 'Panduan berhasil ditambahkan.');
    }

    public function update(UpdatePanduanRequest $request, string $id_panduan): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $panduan = Panduan::findOrFail($id_panduan);
        $data = $request->validated();
        $isPublik = ($data['status'] ?? 'Draft') === 'Publik';
        $data['tanggal'] = $isPublik
            ? ($panduan->tanggal?->toDateString() ?: now()->toDateString())
            : null;

        if ($panduan->judul !== $data['judul']) {
            $data['slug'] = $this->uniqueSlug($data['judul'], $panduan->id_panduan);
        }

        if ($request->hasFile('gambar')) {
            $this->deleteImage($panduan->gambar);
            $data['gambar'] = $this->storeImage($request);
        }

        $panduan->update($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Panduan berhasil diperbarui.',
                'data' => $panduan->fresh('author'),
                'redirect' => route('admin.edukasi.panduan'),
            ]);
        }

        return redirect()->route('admin.edukasi.panduan')
            ->with('success', 'Panduan berhasil diperbarui.');
    }

    public function destroy(Request $request, string $id_panduan): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $panduan = Panduan::findOrFail($id_panduan);
        $this->deleteImage($panduan->gambar);
        $panduan->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Panduan berhasil dihapus.',
                'redirect' => route('admin.edukasi.panduan'),
            ]);
        }

        return redirect()->route('admin.edukasi.panduan')
            ->with('success', 'Panduan berhasil dihapus.');
    }

    private function uniqueSlug(string $title, ?string $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'panduan';
        $slug = $base;
        $suffix = 2;

        while (Panduan::query()->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id_panduan', '<>', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    private function storeImage(Request $request): string
    {
        $directory = public_path('uploads/panduan');
        File::ensureDirectoryExists($directory);

        $file = $request->file('gambar');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/panduan/' . $filename;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}
