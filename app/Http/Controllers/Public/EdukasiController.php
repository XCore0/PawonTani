<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use App\Models\Artikel;
use App\Models\Panduan;

class EdukasiController extends Controller
{
    /** Menampilkan seluruh konten edukasi (Tips, Panduan, dan Artikel) yang dipublikasikan. */
    public function indexSemua()
    {
        $tips = Tip::with('komoditas')
            ->where('status', 'Publik')
            ->get()
            ->map(function (Tip $item) {
                $item->education_type = 'Tips';

                return $item;
            });

        $panduan = Panduan::with('komoditas')
            ->where('status', 'Publik')
            ->get()
            ->map(function (Panduan $item) {
                $item->education_type = 'Panduan';

                return $item;
            });

        $artikel = Artikel::with('komoditas')
            ->where('status', 'Publik')
            ->get()
            ->map(function (Artikel $item) {
                $item->education_type = 'Artikel';

                return $item;
            });

        $items = $tips
            ->concat($panduan)
            ->concat($artikel)
            ->sortByDesc(fn ($item) => $item->tanggal ?? $item->created_at)
            ->values();

        $type = 'Semua Edukasi';

        return view('public.edukasi.index', compact('items', 'type'));
    }

    /** Menampilkan seluruh tips yang sudah dipublikasikan. */
    public function indexTips()
    {
        $items = Tip::with('komoditas')
            ->where('status', 'Publik')
            ->latest('tanggal')
            ->get();

        $type = 'Tips';

        return view('public.edukasi.index', compact('items', 'type'));
    }

    /** Menampilkan seluruh artikel yang sudah dipublikasikan. */
    public function indexArtikel()
    {
        $items = Artikel::with('komoditas')
            ->where('status', 'Publik')
            ->latest('tanggal')
            ->get();

        $type = 'Artikel';

        return view('public.edukasi.index', compact('items', 'type'));
    }

    /** Menampilkan seluruh panduan yang sudah dipublikasikan. */
    public function indexPanduan()
    {
        $items = Panduan::with('komoditas')
            ->where('status', 'Publik')
            ->latest('tanggal')
            ->get();

        $type = 'Panduan';

        return view('public.edukasi.index', compact('items', 'type'));
    }

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
