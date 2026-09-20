<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArtikelController extends Controller
{
    public function index(Request $request)
    {
        $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $query = Artikel::query()->where('status', 'Publik');

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($q) use ($search, $operator) {
                $q->where('judul', $operator, "%{$search}%")
                    ->orWhere('kategori', $operator, "%{$search}%")
                    ->orWhere('komoditas', $operator, "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->input('kategori'));
        }

        $artikels = $query->latest('tanggal')
            ->latest('created_at')
            ->paginate(9)
            ->withQueryString();

        $kategoriList = Artikel::where('status', 'Publik')
            ->whereNotNull('kategori')
            ->where('kategori', '<>', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('artikel.index', compact('artikels', 'kategoriList'));
    }

    public function show(int $id)
    {
        $artikel = Artikel::where('status', 'Publik')->findOrFail($id);

        return view('artikel.show', compact('artikel'));
    }
}
