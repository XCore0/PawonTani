@extends('Customer.Layout._layout')

@section('title', $produk->nama_produk . ' - Marketplace PawonTani')
@section('meta_description', Str::limit(strip_tags($produk->deskripsi ?: $produk->nama_produk . ' segar dari petani PawonTani.'), 150))

@section('content')
  @php
    // Normalisasi nomor WhatsApp penjual jika diizinkan untuk kontak publik
    $rawPhone = $produk->pengguna?->kontak_publik_aktif ? $produk->pengguna?->no_telepon : null;
    $waNumber = null;
    if ($rawPhone) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (str_starts_with($cleanPhone, '0')) {
            $waNumber = '62' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '62')) {
            $waNumber = $cleanPhone;
        }
    }

    $productUrl = url()->current();
    $waMessage = rawurlencode("Halo, saya tertarik dengan produk {$produk->nama_produk} yang Anda jual di Marketplace PawonTani. Apakah produk ini masih tersedia?\n{$productUrl}");
    $waLink = $waNumber ? "https://wa.me/{$waNumber}?text={$waMessage}" : null;
  @endphp

  <!-- Breadcrumbs Bar -->
  <section class="bg-white border-b border-[#E4F0D6] py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium truncate">
        <a href="{{ route('marketplace.home') }}" class="hover:text-pawon-700">Marketplace</a>
        <span>/</span>
        <a href="{{ route('marketplace.katalog') }}" class="hover:text-pawon-700">Katalog</a>
        @if($produk->komoditas)
          <span>/</span>
          <a href="{{ route('marketplace.katalog', ['komoditas' => $produk->id_komoditas]) }}" class="hover:text-pawon-700">
            {{ $produk->komoditas->nama_komoditas }}
          </a>
        @endif
        <span>/</span>
        <span class="text-pawon-900 font-bold truncate">{{ $produk->nama_produk }}</span>
      </nav>
    </div>
  </section>

  <!-- Product Detail Section -->
  <section class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

        <!-- Left Column: Product Photo (5 Cols) -->
        <div class="lg:col-span-5">
          <div class="sticky top-28 bg-white rounded-3xl p-4 border border-[#E4F0D6] shadow-xs">
            
            <div class="relative h-80 sm:h-96 rounded-2xl bg-slate-100 overflow-hidden flex items-center justify-center">
              @if($produk->foto_produk)
                <img src="{{ $produk->foto_produk }}" alt="{{ $produk->nama_produk }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-[#EBF6E0] text-pawon-700 font-bold text-6xl\'>🌾</div>';">
              @else
                <div class="w-full h-full flex items-center justify-center bg-[#EBF6E0] text-pawon-700 font-bold text-6xl">
                  🌾
                </div>
              @endif

              <!-- Floating Stock Badge -->
              @if($produk->is_stok_habis)
                <span class="absolute top-4 right-4 px-3.5 py-1.5 rounded-full text-xs font-bold bg-rose-600 text-white shadow-md">
                  Stok Habis
                </span>
              @else
                <span class="absolute top-4 right-4 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-600 text-white shadow-md flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                  Tersedia {{ $produk->stok_tersedia }} {{ $produk->satuan }}
                </span>
              @endif
            </div>

            <!-- Share & Copy Link Actions -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
              <span class="text-xs text-slate-500 font-medium">Bagikan produk:</span>
              <button type="button" onclick="copyProductLink()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-slate-700 bg-[#F5F8F1] hover:bg-pawon-100 border border-[#E4F0D6] rounded-xl transition-all">
                <svg class="w-4 h-4 text-pawon-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>Salin Link</span>
              </button>
            </div>

          </div>
        </div>

        <!-- Right Column: Product Detail & Order (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">

          <!-- Main Info Card -->
          <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E4F0D6] shadow-xs space-y-6">
            
            <div>
              @if($produk->komoditas)
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-pawon-100 text-pawon-800 border border-[#C5DFB0] mb-2.5">
                  {{ $produk->komoditas->nama_komoditas }} &bull; {{ $produk->komoditas->kategori ?: 'Hasil Panen' }}
                </span>
              @endif
              <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-snug">
                {{ $produk->nama_produk }}
              </h1>
            </div>

            <!-- Price Display -->
            <div class="bg-[#F5F8F1] rounded-2xl p-4 sm:p-5 border border-[#E4F0D6] flex flex-wrap items-baseline justify-between gap-3">
              <div>
                <span class="text-xs text-slate-500 block font-medium">Harga Penawaran:</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-pawon-800">
                  {{ $produk->harga_formatted }}
                  <span class="text-sm font-normal text-slate-500">/ {{ $produk->satuan }}</span>
                </div>
              </div>

              @if($produk->jenis_harga === 'rentang')
                <div class="text-xs text-amber-800 bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-200">
                  ⚠️ Harga fleksibel / negosiasi langsung sesuai kualitas & volume panen
                </div>
              @endif
            </div>

            <!-- Stock Breakdown -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
              <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="text-slate-400">Stok Tersedia</span>
                <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $produk->stok_tersedia }} {{ $produk->satuan }}</p>
              </div>
              <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="text-slate-400">Total Terjual</span>
                <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $produk->total_terjual }} {{ $produk->satuan }}</p>
              </div>
              <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 col-span-2 sm:col-span-1">
                <span class="text-slate-400">Status Produk</span>
                <p class="text-sm font-bold {{ $produk->is_stok_habis ? 'text-rose-600' : 'text-emerald-700' }} mt-0.5">
                  {{ $produk->is_stok_habis ? 'Stok Habis' : 'Aktif & Siap Kirim' }}
                </p>
              </div>
            </div>

            <!-- Description -->
            <div>
              <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider text-pawon-900 mb-2">Deskripsi Produk</h3>
              <div class="text-sm text-slate-600 leading-relaxed space-y-2 whitespace-pre-line bg-slate-50/70 p-4 rounded-2xl border border-slate-100">
                {{ $produk->deskripsi ?: 'Petani belum menambahkan rincian deskripsi spesifik. Hubungi petani untuk menanyakan varietas atau spesifikasi hasil panen.' }}
              </div>
            </div>

            <!-- Order via WhatsApp Action Button -->
            <div class="pt-4 border-t border-slate-100">
              @if($produk->is_stok_habis)
                <button type="button" disabled class="w-full py-4 px-6 rounded-2xl bg-slate-200 text-slate-400 font-bold text-base cursor-not-allowed flex items-center justify-center gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                  <span>Stok Produk Sedang Habis</span>
                </button>
                <p class="text-[11px] text-slate-400 text-center mt-2">Pemesanan dinonaktifkan sementara hingga petani memperbarui stok.</p>
              @elseif($waLink)
                <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="w-full inline-flex items-center justify-center gap-3 py-4 px-6 rounded-2xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-extrabold text-base shadow-lg shadow-emerald-500/25 transition-all hover:translate-y-[-1px]">
                  <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.634.053-.984-.047-.369-.105-.845-.262-1.488-.539-1.428-.616-2.378-2.033-2.457-2.14-.078-.106-.576-.767-.576-1.464 0-.698.362-1.042.491-1.185.129-.143.282-.179.376-.179.094 0 .188.001.27.006.088.004.205-.034.321.246.12.288.409 1.002.445 1.076.036.074.06.16.012.257-.048.096-.073.155-.144.24-.072.084-.15.187-.215.251-.072.072-.148.151-.064.296.084.144.374.618.802 1.001.551.493 1.015.646 1.159.718.144.072.228.06.312-.036.084-.096.36-419.456-.563.096-.144.192-.12.324-.072.132.048.841.396.985.468.144.072.24.108.276.168.036.06.036.348-.108.753z"/>
                  </svg>
                  <span>Pesan via WhatsApp Petani</span>
                </a>
                <p class="text-[11px] text-slate-500 text-center mt-2">
                  Terhubung langsung dengan nomor resmi petani. Kesepakatan harga & pengiriman dilakukan melalui WhatsApp.
                </p>
              @else
                <div class="bg-amber-50 rounded-2xl p-4 border border-amber-200 text-amber-800 text-xs">
                  <p class="font-bold">Kontak WhatsApp Belum Diaktifkan untuk Publik</p>
                  <p class="mt-1">Petani pemilik belum mengaktifkan kontak WhatsApp publik. Silakan kunjungi kelompok tani terkait untuk pemesanan langsung.</p>
                </div>
              @endif
            </div>

          </div>

          <!-- Seller Profile Card (WAJIB SESUAI BAGIAN 6 & 13) -->
          <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#E4F0D6] shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-pawon-700 block mb-3">Profil Penjual</span>
            
            <div class="flex items-start gap-4">
              <!-- Seller Avatar -->
              <div class="w-14 h-14 rounded-2xl bg-pawon-100 text-pawon-800 font-extrabold text-xl flex items-center justify-center shrink-0 border border-[#C5DFB0]">
                @if($produk->pengguna?->foto_profil)
                  <img src="{{ $produk->pengguna->foto_profil }}" alt="{{ $produk->pengguna->nama }}" class="w-full h-full object-cover rounded-2xl">
                @else
                  {{ strtoupper(substr($produk->pengguna?->nama ?: 'P', 0, 1)) }}
                @endif
              </div>

              <!-- Seller Metadata -->
              <div class="flex-grow">
                <div class="flex flex-wrap items-center justify-between gap-2">
                  <h4 class="text-base font-bold text-slate-900">
                    {{ $produk->pengguna?->nama ?: 'Petani Anggota' }}
                  </h4>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-pawon-100 text-pawon-800">
                    Petani Terverifikasi
                  </span>
                </div>

                <div class="mt-1 space-y-0.5 text-xs text-slate-600">
                  <p class="font-medium text-pawon-800">
                    🌾 Kelompok Tani: {{ $produk->pengguna?->kelompokTani?->nama_kelompok ?: 'Kelompok Tani Binaan' }}
                  </p>
                  <p class="text-slate-500">
                    📍 Wilayah: {{ $produk->pengguna?->lokasi_nama ?: ($produk->pengguna?->kelompokTani?->alamat ?: 'Kabupaten Banyumas') }}
                  </p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                  <a href="{{ route('marketplace.penjual', $produk->id_pengguna) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-pawon-700 hover:text-pawon-800">
                    <span>Lihat Semua Produk Penjual Ini</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                  </a>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>

      <!-- Other Products from this Seller -->
      @if($produkLain->isNotEmpty())
        <div class="mt-16 pt-12 border-t border-[#E4F0D6]">
          <div class="flex items-center justify-between mb-8">
            <div>
              <span class="text-xs font-bold uppercase tracking-wider text-pawon-700">Etalase Petani</span>
              <h3 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1">Produk Lain dari Penjual Ini</h3>
            </div>
            <a href="{{ route('marketplace.penjual', $produk->id_pengguna) }}" class="text-xs sm:text-sm font-bold text-pawon-700 hover:text-pawon-800">
              Lihat Profil Penjual &rarr;
            </a>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($produkLain as $item)
              <div class="bg-white rounded-2xl border border-[#E4F0D6] overflow-hidden shadow-xs hover:shadow-md transition-all flex flex-col group">
                <a href="{{ route('marketplace.detail', $item->id_produk) }}" class="relative h-40 bg-slate-100 block overflow-hidden">
                  @if($item->foto_produk)
                    <img src="{{ $item->foto_produk }}" alt="{{ $item->nama_produk }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                  @else
                    <div class="w-full h-full flex items-center justify-center bg-[#EBF6E0] text-pawon-700 font-bold text-2xl">
                      🌾
                    </div>
                  @endif
                </a>
                <div class="p-3.5 flex flex-col flex-grow">
                  <a href="{{ route('marketplace.detail', $item->id_produk) }}" class="text-xs font-bold text-slate-900 hover:text-pawon-700 truncate">
                    {{ $item->nama_produk }}
                  </a>
                  <div class="text-sm font-extrabold text-pawon-800 mt-1">
                    {{ $item->harga_formatted }}
                  </div>
                  <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Stok: {{ $item->stok_tersedia }} {{ $item->satuan }}</span>
                    <a href="{{ route('marketplace.detail', $item->id_produk) }}" class="text-pawon-700 font-bold hover:underline">
                      Lihat
                    </a>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endif

    </div>
  </section>
@endsection

@section('scripts')
<script>
  function copyProductLink() {
    const url = window.location.href;
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(url).then(() => {
        showToast('Link produk berhasil disalin ke clipboard!', 'success');
      }).catch(() => {
        fallbackCopyTextToClipboard(url);
      });
    } else {
      fallbackCopyTextToClipboard(url);
    }
  }

  function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
      document.execCommand('copy');
      showToast('Link produk berhasil disalin!', 'success');
    } catch (err) {
      showToast('Gagal menyalin link otomatis.', 'error');
    }
    document.body.removeChild(textArea);
  }
</script>
@endsection
