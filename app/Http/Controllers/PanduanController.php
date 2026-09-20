<?php

namespace App\Http\Controllers;

use App\Models\Panduan;
use Illuminate\View\View;

class PanduanController extends Controller
{
    public function index(): View
    {
        $panduan = Panduan::publik()
            ->latest('tanggal')
            ->latest('id_panduan')
            ->paginate(9);

        return view('Edukasi.Panduan.index', compact('panduan'));
    }

    public function show(string $slug): View
    {
        $panduan = Panduan::publik()
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Panduan::publik()
            ->where('id_panduan', '<>', $panduan->id_panduan)
            ->latest('tanggal')
            ->latest('id_panduan')
            ->limit(3)
            ->get();

        return view('Edukasi.Panduan.show', compact('panduan', 'related'));
    }
}
