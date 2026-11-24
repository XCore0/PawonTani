<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Models\Produk;
use App\Services\MarketplaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function __construct(
        protected MarketplaceService $marketplaceService
    ) {}

    /**
     * Homepage Promosi PawonTani & Pintu Masuk Marketplace Customer.
     */
    public function home(): View
    {
        $produkTerbaru = Produk::with(['pengguna.kelompokTani', 'komoditas'])
            ->publik()
            ->latest()
            ->take(6)
            ->get();

        $komoditasList = Komoditas::orderBy('nama_komoditas')->get();

        $totalPetani = \App\Models\Pengguna::where('role', 'Anggota')->where('status', 'Aktif')->count();
        $totalProduk = Produk::publik()->count();
        $totalKelompok = \App\Models\KelompokTani::where('status', 'Aktif')->count();

        return view('Customer.Home', compact(
            'produkTerbaru',
            'komoditasList',
            'totalPetani',
            'totalProduk',
            'totalKelompok'
        ));
    }

    /**
     * Halaman Katalog Marketplace Customer.
     */
    public function katalog(Request $request): View
    {
        $produkList = $this->marketplaceService->getPublicCatalog($request, 12);
        $komoditasList = Komoditas::orderBy('nama_komoditas')->get();

        return view('Customer.Katalog', compact(
            'produkList',
            'komoditasList'
        ));
    }

    /**
     * Halaman Detail Produk & Profil Penjual.
     */
    public function detail(string $id): View
    {
        $data = $this->marketplaceService->getPublicDetail($id);

        return view('Customer.Detail', [
            'produk' => $data['produk'],
            'produkLain' => $data['produk_lain'],
        ]);
    }

    /**
     * Halaman Profil Publik Penjual beserta Produk Aktifnya.
     */
    public function penjual(string $id): View
    {
        $data = $this->marketplaceService->getPublicSellerProfile($id);

        return view('Customer.Penjual', [
            'penjual' => $data['penjual'],
            'produkList' => $data['produk'],
        ]);
    }
}
