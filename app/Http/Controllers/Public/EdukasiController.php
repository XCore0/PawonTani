<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use App\Models\Artikel;
use App\Models\Panduan;

class EdukasiController extends Controller
{
    public function showTip(string $id_tips)
    {
        $konten = Tip::with('komoditas')
            ->where('status', 'Publik')
            ->where('id_tips', $id_tips)
            ->firstOrFail();

        $jenis = 'Tips';

        return view('public.edukasi.detail', compact('konten', 'jenis'));
    }

    public function showArtikel(string $id_artikel)
    {
        $konten = Artikel::with('komoditas')
            ->where('status', 'Publik')
            ->where('id_artikel', $id_artikel)
            ->firstOrFail();

        $jenis = 'Artikel';

        return view('public.edukasi.detail', compact('konten', 'jenis'));
    }

    public function showPanduan(string $slug)
    {
        $konten = Panduan::with('komoditas')
            ->where('status', 'Publik')
            ->where('slug', $slug)
            ->firstOrFail();

        $jenis = 'Panduan';

        return view('public.edukasi.detail', compact('konten', 'jenis'));
    }
}
