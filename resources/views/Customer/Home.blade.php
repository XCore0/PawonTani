@extends('Customer.Layout._layout')

@section('title', 'PawonTani Marketplace - Kenali Hasil Tani Lokal, Dukung Petani Indonesia')

@section('content')
  <!-- ==================== 1. HERO SECTION ==================== -->
  <section class="relative pt-8 pb-16 md:pt-14 md:pb-24 overflow-hidden">
    <!-- Atmospheric blurs matching PawonTani theme -->
    <div class="absolute top-0 right-0 -z-10 w-[550px] h-[550px] bg-pawon-100/60 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 -z-10 w-[450px] h-[450px] bg-[#E4F0D6]/40 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

        <!-- Hero Text (7 Cols) -->
        <div class="lg:col-span-7 space-y-6 text-left">
          
          <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-pawon-100 border border-[#C5DFB0] text-pawon-900 text-xs font-bold tracking-wide shadow-xs">
            <span class="flex h-2.5 w-2.5 rounded-full bg-pawon-700 animate-pulse"></span>
            Marketplace Resmi Petani PawonTani
          </div>

          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 tracking-tight leading-[1.15]">
            Kenali Hasil Tani Lokal, <br class="hidden sm:inline">
            <span class="text-pawon-700">Dukung Petani Indonesia.</span>
          </h1>

          <p class="text-base sm:text-lg text-slate-600 max-w-2xl font-normal leading-relaxed">
            PawonTani mempertemukan konsumen dan mitra langsung dengan petani anggota kelompok tani. Dapatkan komoditas segar berkualitas tanpa perantara panjang dengan transparansi harga dan ketersediaan stok nyata.
          </p>

          <!-- Hero Buttons -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 pt-2">
            <a href="{{ route('marketplace.katalog') }}" class="inline-flex items-center justify-center gap-2.5 px-8 py-4 text-base font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow-lg shadow-pawon-700/25 transition-all hover:translate-y-[-1px]">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
              <span>Jelajahi Marketplace</span>
            </a>
            <a href="#tentang-pawontani" class="inline-flex items-center justify-center gap-2 px-7 py-4 text-base font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-[#C5DFB0] rounded-full shadow-xs transition-all">
              <span>Tentang PawonTani</span>
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </a>
          </div>

          <!-- Trust Badges -->
          <div class="pt-8 border-t border-[#E4F0D6] grid grid-cols-3 gap-4 max-w-lg">
            <div>
              <div class="text-2xl sm:text-3xl font-bold text-pawon-900 tracking-tight">{{ $totalPetani > 0 ? $totalPetani : '100+' }}</div>
              <div class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Petani Anggota</div>
            </div>
            <div>
              <div class="text-2xl sm:text-3xl font-bold text-pawon-900 tracking-tight">{{ $totalKelompok > 0 ? $totalKelompok : '10+' }}</div>
              <div class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Kelompok Tani</div>
            </div>
            <div>
              <div class="text-2xl sm:text-3xl font-bold text-pawon-900 tracking-tight">100%</div>
              <div class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Langsung ke Petani</div>
            </div>
          </div>

        </div>

        <!-- Hero Visual Card (5 Cols) - ANTI PUTIH / ANTI GAGAL DENGAN INLINE STYLING LENGKAP -->
        <div class="lg:col-span-5 relative">
          <div class="relative mx-auto max-w-md bg-white rounded-3xl p-6 shadow-2xl shadow-slate-200/80 border border-[#E4F0D6]">
            
            <!-- Banner Visual: Background Hijau Tua Gelap Pasti Tampil dengan Inline Style & Gradient -->
            <div class="relative h-64 rounded-2xl overflow-hidden mb-5 flex flex-col justify-between p-5 border border-[#163820] shadow-md"
                 style="background: #183C23 linear-gradient(135deg, #163E24 0%, #255C37 55%, #13331E 100%); color: #ffffff;">
              
              <!-- Aksen Dekoratif Halus -->
              <div class="absolute -right-8 -bottom-8 w-40 h-40 rounded-full blur-2xl pointer-events-none" style="background: rgba(77, 152, 48, 0.25);"></div>
              <div class="absolute -left-6 -top-6 w-32 h-32 rounded-full blur-xl pointer-events-none" style="background: rgba(245, 158, 11, 0.15);"></div>

              <!-- Top Bar di Banner: Badge & Logo -->
              <div class="relative z-10 flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold shadow-sm"
                      style="background: rgba(255, 255, 255, 0.2); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(8px);">
                  <span class="w-2 h-2 rounded-full animate-pulse" style="background: #6EE7B7;"></span>
                  Panen Terverifikasi
                </span>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center p-1.5 shadow-sm"
                     style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(8px);">
                  <img src="{{ asset('images/logo.png') }}" alt="PawonTani" class="w-full h-full object-contain brightness-125">
                </div>
              </div>

              <!-- Bottom Content di Banner: Teks Putih Kontras Tinggi di atas Latar Hijau Tua -->
              <div class="relative z-10 mt-auto">
                <span class="text-[11px] font-bold uppercase tracking-wider block mb-1" style="color: #A7F3D0;">
                  Komoditas Pangan Lokal
                </span>
                <h3 class="text-xl sm:text-2xl font-bold leading-tight drop-shadow-md" style="color: #ffffff;">
                  Gabah, Jagung, Sayur &amp; Hasil Bumi
                </h3>
                <p class="text-xs font-medium mt-1.5 flex items-center gap-1.5" style="color: #D1FAE5;">
                  <svg class="w-4 h-4 shrink-0" style="color: #6EE7B7;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                  Dipanen langsung oleh petani kelompok tani binaan
                </p>
              </div>

            </div>

            <!-- Card Bottom Info -->
            <div class="space-y-3.5">
              
              <!-- Info Listing Petani -->
              <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                  <div class="w-11 h-11 rounded-2xl bg-pawon-100 text-pawon-800 flex items-center justify-center font-bold text-2xl shadow-xs border border-[#C5DFB0]">
                    🌾
                  </div>
                  <div>
                    <h3 class="text-sm font-bold text-slate-900">Katalog Petani Terkini</h3>
                    <p class="text-xs text-slate-500 font-medium">Tersedia {{ $totalProduk }} produk aktif siap pesan</p>
                  </div>
                </div>
                <!-- Badge Petani Asli -->
                <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                  <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  </svg>
                  Petani Asli
                </span>
              </div>

              <!-- 2 Box Keunggulan -->
              <div class="grid grid-cols-2 gap-2.5 text-xs">
                <div class="bg-pawon-50/80 p-3 rounded-2xl border border-[#E4F0D6]">
                  <span class="text-slate-500 font-semibold block">Pemesanan</span>
                  <p class="font-extrabold text-slate-900 mt-1 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    WhatsApp Langsung
                  </p>
                </div>
                <div class="bg-pawon-50/80 p-3 rounded-2xl border border-[#E4F0D6]">
                  <span class="text-slate-500 font-semibold block">Harga Hasil Panen</span>
                  <p class="font-extrabold text-slate-900 mt-1 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-pawon-600"></span>
                    Transparan &amp; Adil
                  </p>
                </div>
              </div>

              <!-- CTA Button -->
              <a href="{{ route('marketplace.katalog') }}" class="w-full inline-flex items-center justify-center gap-2 py-3.5 text-sm font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-2xl transition-all shadow-md shadow-pawon-700/20 hover:translate-y-[-1px]">
                <span>Buka Katalog Lengkap</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              </a>
            </div>

          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ==================== 2. KATEGORI KOMODITAS HASIL BUMI ==================== -->
  @if($komoditasList->isNotEmpty())
  <section class="py-14 bg-white border-y border-[#E4F0D6]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
        <div>
          <span class="text-xs font-bold uppercase tracking-wider text-pawon-700">Kategori Hasil Bumi</span>
          <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-1">Komoditas Kelompok Tani</h2>
          <p class="text-sm text-slate-500 mt-1">
            Pilih jenis hasil bumi untuk menelusuri penawaran produk panen segar dari petani binaan di Jawa Timur dan sekitarnya.
          </p>
        </div>
        <a href="{{ route('marketplace.katalog') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-pawon-700 hover:text-pawon-800 shrink-0">
          <span>Lihat Semua Produk</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
      </div>

      <!-- Grid Komoditas dengan Ikon Representatif & Jelas Terlihat -->
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
        @foreach($komoditasList as $kmdt)
          @php
            $namaLower = strtolower($kmdt->nama_komoditas ?? '');
            
            // Pemetaan ikon spesifik dan gradien warna representatif tiap komoditas
            if (str_contains($namaLower, 'padi') || str_contains($namaLower, 'gabah') || str_contains($namaLower, 'beras')) {
              $ikon = '🌾';
              $bgGrad = 'from-amber-100 to-yellow-50 text-amber-700 border-amber-200';
            } elseif (str_contains($namaLower, 'jagung')) {
              $ikon = '🌽';
              $bgGrad = 'from-yellow-100 to-amber-50 text-amber-700 border-yellow-200';
            } elseif (str_contains($namaLower, 'cabai') || str_contains($namaLower, 'cabe')) {
              $ikon = '🌶️';
              $bgGrad = 'from-rose-100 to-red-50 text-rose-700 border-rose-200';
            } elseif (str_contains($namaLower, 'bawang')) {
              $ikon = '🧅';
              $bgGrad = 'from-purple-100 to-rose-50 text-purple-700 border-purple-200';
            } elseif (str_contains($namaLower, 'tomat')) {
              $ikon = '🍅';
              $bgGrad = 'from-red-100 to-rose-50 text-red-700 border-red-200';
            } elseif (str_contains($namaLower, 'bayam') || str_contains($namaLower, 'kangkung') || str_contains($namaLower, 'sawi') || str_contains($namaLower, 'sayur') || str_contains($namaLower, 'pakcoy') || str_contains($namaLower, 'kubis') || str_contains($namaLower, 'kol')) {
              $ikon = '🥬';
              $bgGrad = 'from-emerald-100 to-green-50 text-emerald-700 border-emerald-200';
            } elseif (str_contains($namaLower, 'singkong') || str_contains($namaLower, 'ubi') || str_contains($namaLower, 'kentang')) {
              $ikon = '🍠';
              $bgGrad = 'from-orange-100 to-amber-50 text-orange-800 border-orange-200';
            } elseif (str_contains($namaLower, 'kedelai') || str_contains($namaLower, 'kacang')) {
              $ikon = '🫘';
              $bgGrad = 'from-stone-100 to-amber-50 text-stone-700 border-stone-200';
            } elseif (str_contains($namaLower, 'wortel')) {
              $ikon = '🥕';
              $bgGrad = 'from-orange-100 to-amber-50 text-orange-700 border-orange-200';
            } elseif (str_contains($namaLower, 'mentimun') || str_contains($namaLower, 'timun')) {
              $ikon = '🥒';
              $bgGrad = 'from-green-100 to-emerald-50 text-green-700 border-green-200';
            } elseif (str_contains($namaLower, 'terong')) {
              $ikon = '🍆';
              $bgGrad = 'from-purple-100 to-violet-50 text-purple-700 border-purple-200';
            } elseif (str_contains($namaLower, 'pisang')) {
              $ikon = '🍌';
              $bgGrad = 'from-yellow-100 to-amber-50 text-yellow-700 border-yellow-200';
            } elseif (str_contains($namaLower, 'semangka')) {
              $ikon = '🍉';
              $bgGrad = 'from-emerald-100 to-rose-50 text-emerald-800 border-emerald-200';
            } elseif (str_contains($namaLower, 'melon')) {
              $ikon = '🍈';
              $bgGrad = 'from-lime-100 to-emerald-50 text-lime-800 border-lime-200';
            } elseif (str_contains($namaLower, 'mangga')) {
              $ikon = '🥭';
              $bgGrad = 'from-amber-100 to-yellow-50 text-amber-800 border-amber-200';
            } elseif (str_contains($namaLower, 'jeruk')) {
              $ikon = '🍊';
              $bgGrad = 'from-orange-100 to-amber-50 text-orange-700 border-orange-200';
            } elseif (str_contains($namaLower, 'durian') || str_contains($namaLower, 'nanas')) {
              $ikon = '🍍';
              $bgGrad = 'from-amber-100 to-yellow-50 text-amber-800 border-amber-200';
            } elseif (str_contains($namaLower, 'kopi')) {
              $ikon = '☕';
              $bgGrad = 'from-amber-100 to-stone-100 text-stone-800 border-stone-300';
            } elseif (str_contains($namaLower, 'kelapa')) {
              $ikon = '🥥';
              $bgGrad = 'from-stone-100 to-amber-50 text-stone-800 border-stone-200';
            } elseif (str_contains($namaLower, 'jahe') || str_contains($namaLower, 'kunyit') || str_contains($namaLower, 'lengkuas') || str_contains($namaLower, 'serai') || str_contains($namaLower, 'rempah')) {
              $ikon = '🫚';
              $bgGrad = 'from-amber-100 to-orange-50 text-amber-800 border-amber-200';
            } else {
              $ikon = '🌿';
              $bgGrad = 'from-emerald-100 to-green-50 text-emerald-700 border-emerald-200';
            }
          @endphp

          <a href="{{ route('marketplace.katalog', ['komoditas' => $kmdt->id_komoditas]) }}" 
             class="group bg-[#F5F8F1] hover:bg-white border border-[#E4F0D6] hover:border-pawon-400 rounded-2xl p-4 text-center transition-all duration-200 hover:shadow-lg hover:-translate-y-1 flex flex-col items-center">
            
            <!-- Lingkaran Ikon Komoditas yang Jelas dan Kontras -->
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br {{ $bgGrad }} border shadow-xs flex items-center justify-center text-3xl mb-3 group-hover:scale-110 transition-transform duration-300">
              {{ $ikon }}
            </div>

            <!-- Nama Komoditas -->
            <h3 class="text-sm font-bold text-slate-900 group-hover:text-pawon-800 transition-colors line-clamp-1 leading-snug">
              {{ $kmdt->nama_komoditas }}
            </h3>

            <!-- Label Kategori Komoditas -->
            <span class="text-[11px] text-slate-500 font-medium mt-0.5 line-clamp-1">
              {{ $kmdt->kategori ?: 'Pangan Lokal' }}
            </span>
          </a>
        @endforeach
      </div>

    </div>
  </section>
  @endif

  <!-- ==================== 3. PRODUK TERBARU PILIHAN (KATALOG UNGGULAN LAPANG) ==================== -->
  <section class="py-16 md:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Header Katalog Unggulan -->
      <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
        <div>
          <span class="text-xs font-bold uppercase tracking-wider text-pawon-700">Katalog Unggulan</span>
          <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-1">Produk Terbaru Petani</h2>
          <p class="text-sm text-slate-600 mt-1.5">
            Komoditas panen segar yang dipasarkan langsung oleh anggota kelompok tani binaan PawonTani.
          </p>
        </div>
        <a href="{{ route('marketplace.katalog') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-pawon-700 bg-white hover:bg-pawon-50 border border-[#C5DFB0] rounded-full shadow-xs transition-all shrink-0">
          <span>Buka Semua Produk</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
      </div>

      @if($produkTerbaru->isEmpty())
        <!-- KONDISI KATALOG KOSONG / BELUM ADA PRODUK: LEBAR, LAPANG, DAN ESTETIK -->
        <div class="w-full bg-white rounded-3xl border border-[#E4F0D6] py-16 px-6 sm:px-12 text-center shadow-xs">
          
          <div class="w-20 h-20 mx-auto rounded-3xl bg-pawon-100/70 border border-[#C5DFB0] flex items-center justify-center text-4xl mb-5 shadow-xs">
            🌱
          </div>

          <h3 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
            Katalog Panen Baru Sedang Disiapkan
          </h3>

          <p class="text-slate-500 text-sm sm:text-base mt-2.5 max-w-xl mx-auto leading-relaxed">
            Petani anggota kelompok tani sedang melakukan verifikasi dan pembaruan stok panen terbaru melalui aplikasi mobile. Anda tetap dapat menelusuri kategori komoditas di atas atau menghubungi kelompok tani binaan.
          </p>

          <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('marketplace.katalog') }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow-md shadow-pawon-700/20 transition-all">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
              <span>Lihat Seluruh Katalog</span>
            </a>
            <a href="#tentang-pawontani" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-slate-700 bg-[#F5F8F1] hover:bg-pawon-100 border border-[#C5DFB0] rounded-full transition-all">
              <span>Pelajari Ekosistem Petani</span>
            </a>
          </div>

        </div>
      @else
        <!-- Grid Produk Saat Data Tersedia -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
          @foreach($produkTerbaru as $prd)
            <div class="bg-white rounded-[22px] border border-[#E4F0D6] overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col group">
              
              <!-- Product Image Container -->
              <div class="relative h-52 bg-slate-100 overflow-hidden">
                @if($prd->foto_produk)
                  <img src="{{ $prd->foto_produk }}" alt="{{ $prd->nama_produk }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-[#EBF6E0] text-5xl\'>🌾</div>';">
                @else
                  <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#EBF6E0] to-[#D5ECC0] text-5xl">
                    🌾
                  </div>
                @endif

                <!-- Commodity Tag Badge -->
                @if($prd->komoditas)
                  <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold bg-white/95 text-pawon-800 backdrop-blur-xs border border-[#C5DFB0] shadow-xs">
                    {{ $prd->komoditas->nama_komoditas }}
                  </span>
                @endif

                <!-- Stock Status Badge -->
                @if($prd->is_stok_habis)
                  <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold bg-rose-600 text-white shadow-xs">
                    Stok Habis
                  </span>
                @else
                  <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white shadow-xs">
                    Tersedia
                  </span>
                @endif
              </div>

              <!-- Product Info -->
              <div class="p-5 flex flex-col flex-grow">
                
                <h3 class="text-base font-bold text-slate-900 group-hover:text-pawon-700 transition-colors line-clamp-1">
                  {{ $prd->nama_produk }}
                </h3>

                <div class="mt-2 text-lg font-extrabold text-pawon-800">
                  {{ $prd->harga_formatted }}
                  <span class="text-xs font-normal text-slate-500">/ {{ $prd->satuan }}</span>
                </div>

                <!-- Seller Info -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                  <div class="flex items-center gap-2 truncate">
                    <div class="w-7 h-7 rounded-full bg-pawon-100 text-pawon-800 font-bold flex items-center justify-center text-xs shrink-0">
                      {{ strtoupper(substr($prd->pengguna?->nama ?: 'P', 0, 1)) }}
                    </div>
                    <span class="truncate font-semibold text-slate-800">{{ $prd->pengguna?->nama ?: 'Petani PawonTani' }}</span>
                  </div>
                  <span class="shrink-0 text-slate-400 font-medium text-[11px]">
                    {{ $prd->pengguna?->kelompokTani?->nama_kelompok ?: 'Poktan Binaan' }}
                  </span>
                </div>

                <!-- Stock & Action -->
                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                  <div class="text-xs">
                    <span class="text-slate-500">Stok:</span>
                    <span class="font-bold {{ $prd->is_stok_habis ? 'text-rose-600' : 'text-slate-800' }}">
                      {{ $prd->stok_tersedia }} {{ $prd->satuan }}
                    </span>
                  </div>
                  <a href="{{ route('marketplace.detail', $prd->id_produk) }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-xl transition-all">
                    <span>Lihat Detail</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                  </a>
                </div>

              </div>

            </div>
          @endforeach
        </div>
      @endif

    </div>
  </section>

  <!-- ==================== 4. TENTANG PAWONTANI (SECTION LEGA DENGAN JARAK ATAS YANG NYAMAN) ==================== -->
  <section id="tentang-pawontani" class="pt-28 md:pt-36 pb-20 md:pb-28 bg-white border-t border-[#E4F0D6] scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Section Header: Jarak lapang & proporsional -->
      <div class="text-center max-w-3xl mx-auto mb-16 pt-4">
        <span class="text-xs font-bold uppercase tracking-wider text-pawon-700 bg-pawon-50 px-3.5 py-1.5 rounded-full border border-[#C5DFB0]">
          Tentang PawonTani
        </span>
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 mt-4 leading-tight tracking-tight">
          Ekosistem Pertanian Digital untuk Petani Indonesia
        </h2>
        <p class="text-slate-600 mt-5 text-base sm:text-lg leading-relaxed font-normal">
          PawonTani bukan sekadar marketplace digital, melainkan ekosistem terintegrasi yang mendampingi petani sejak pencatatan lahan, pemantauan aktivitas tanam, hingga pencatatan panen dan pemasaran hasil tani langsung kepada Anda.
        </p>
      </div>

      <!-- 3 Pilar Keunggulan -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 mb-16">
        
        <div class="bg-[#F5F8F1] rounded-3xl p-8 border border-[#E4F0D6] flex flex-col justify-between hover:shadow-md transition-shadow">
          <div>
            <div class="w-14 h-14 rounded-2xl bg-white border border-[#C5DFB0] flex items-center justify-center text-3xl mb-5 shadow-xs">
              📱
            </div>
            <h3 class="text-lg font-bold text-slate-900 leading-snug">
              Pengelolaan Mandiri via Aplikasi Mobile
            </h3>
            <p class="text-sm text-slate-600 mt-3 leading-relaxed">
              Petani anggota kelompok tani mengelola katalog, memperbarui ketersediaan stok fisik, dan mencatat transaksi penjualan langsung melalui aplikasi <strong>pawon_mobile</strong>.
            </p>
          </div>
          <div class="mt-6 pt-4 border-t border-[#E4F0D6] text-xs font-bold text-pawon-700 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            Data Akurat &amp; Terpusat
          </div>
        </div>

        <div class="bg-[#F5F8F1] rounded-3xl p-8 border border-[#E4F0D6] flex flex-col justify-between hover:shadow-md transition-shadow">
          <div>
            <div class="w-14 h-14 rounded-2xl bg-white border border-[#C5DFB0] flex items-center justify-center text-3xl mb-5 shadow-xs">
              🤝
            </div>
            <h3 class="text-lg font-bold text-slate-900 leading-snug">
              Hubungan Langsung Tanpa Rantai Tengkulak
            </h3>
            <p class="text-sm text-slate-600 mt-3 leading-relaxed">
              Konsumen, pelaku UMKM, dan mitra kuliner dapat langsung terhubung ke WhatsApp petani bersangkutan. Negosiasi jumlah, waktu panen, dan pengiriman berlangsung transparan dan adil.
            </p>
          </div>
          <div class="mt-6 pt-4 border-t border-[#E4F0D6] text-xs font-bold text-pawon-700 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            Harga Menguntungkan Petani
          </div>
        </div>

        <div class="bg-[#F5F8F1] rounded-3xl p-8 border border-[#E4F0D6] flex flex-col justify-between hover:shadow-md transition-shadow">
          <div>
            <div class="w-14 h-14 rounded-2xl bg-white border border-[#C5DFB0] flex items-center justify-center text-3xl mb-5 shadow-xs">
              🛡️
            </div>
            <h3 class="text-lg font-bold text-slate-900 leading-snug">
              Dukungan DP &amp; Kepastian Alokasi Stok
            </h3>
            <p class="text-sm text-slate-600 mt-3 leading-relaxed">
              Sistem reservasi stok cerdas memastikan bahwa produk yang sudah disepakati (misal melalui pembayaran uang muka/DP) langsung ditahan agar tidak terjual ganda ke pembeli lain.
            </p>
          </div>
          <div class="mt-6 pt-4 border-t border-[#E4F0D6] text-xs font-bold text-pawon-700 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            Transaksi Aman &amp; Terkendali
          </div>
        </div>

      </div>

      <!-- Banner Ajak Bertemu Sistem Utama: ANTI PUTIH DENGAN INLINE STYLING LENGKAP -->
      <div class="rounded-3xl p-8 sm:p-12 shadow-xl relative overflow-hidden border border-[#163820]"
           style="background: #183C23 linear-gradient(135deg, #183C23 0%, #265C38 55%, #122F1B 100%); color: #ffffff;">
        
        <!-- Ornamen Latar -->
        <div class="absolute right-0 top-0 w-80 h-80 rounded-full blur-3xl pointer-events-none" style="background: rgba(77, 152, 48, 0.2);"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
          
          <div class="max-w-2xl space-y-3">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold"
                  style="background: rgba(255, 255, 255, 0.2); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(8px);">
              Portal Kelompok Tani
            </span>
            <h3 class="text-2xl sm:text-3xl font-bold tracking-tight leading-tight" style="color: #ffffff;">
              Ingin Mengetahui Sistem Pengelolaan Kelompok Tani Secara Utuh?
            </h3>
            <p class="text-sm sm:text-base leading-relaxed font-normal" style="color: #D1FAE5;">
              Pelajari bagaimana PawonTani mendampingi kelompok tani binaan di Jawa Timur dalam digitalisasi buku kas, sertifikasi internal lahan, pemantauan musim tanam, hingga pelaporan aktivitas pertanian di portal utama kami.
            </p>
          </div>

          <!-- Tombol Arah ke Homepage Utama PawonTani -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 shrink-0">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 px-7 py-4 text-sm font-bold rounded-full shadow-lg transition-all hover:scale-[1.02]"
               style="background: #ffffff; color: #142F1C;">
              <svg class="w-4 h-4" style="color: #142F1C;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              <span>Pelajari Selengkapnya di Homepage</span>
            </a>
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-6 py-4 text-sm font-semibold rounded-full transition-all"
               style="background: rgba(255, 255, 255, 0.15); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3);">
              <span>Login Sistem Poktan</span>
            </a>
          </div>

        </div>

      </div>

    </div>
  </section>
@endsection
