@extends('Customer.Layout._layout')

@section('title', 'Profil Penjual: ' . $penjual['nama'] . ' - Marketplace PawonTani')

@section('content')
  <!-- Seller Header Banner -->
  <section class="bg-white border-b border-[#E4F0D6] py-10 md:py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
        
        <!-- Avatar -->
        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-pawon-100 text-pawon-800 font-extrabold text-3xl flex items-center justify-center shrink-0 border-2 border-[#C5DFB0] shadow-sm">
          @if($penjual['foto_profil'])
            <img src="{{ $penjual['foto_profil'] }}" alt="{{ $penjual['nama'] }}" class="w-full h-full object-cover rounded-3xl">
          @else
            {{ strtoupper(substr($penjual['nama'], 0, 1)) }}
          @endif
        </div>

        <!-- Details -->
        <div class="space-y-1.5 flex-grow">
          <div class="flex flex-wrap items-center gap-2.5">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
              {{ $penjual['nama'] }}
            </h1>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-pawon-100 text-pawon-800 border border-[#C5DFB0]">
              Petani Terdaftar PawonTani
            </span>
          </div>

          <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs sm:text-sm text-slate-600">
            <span class="flex items-center gap-1 font-medium text-pawon-800">
              🌾 Kelompok Tani: {{ $penjual['kelompok_tani'] ?: 'Kelompok Tani Binaan' }}
            </span>
            <span class="text-slate-400">&bull;</span>
            <span class="text-slate-500">
              📍 {{ $penjual['lokasi_umum'] }}
            </span>
          </div>

          <p class="text-xs text-slate-500 pt-1">
            Produk panen dipasarkan langsung dari lahan pertanian anggota kelompok tani.
          </p>
        </div>

      </div>

    </div>
  </section>

  <!-- Products from this Seller -->
  <section class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="flex items-center justify-between mb-8">
        <div>
          <span class="text-xs font-bold uppercase tracking-wider text-pawon-700">Etalase Petani</span>
          <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-1">Daftar Produk Petani Ini</h2>
        </div>
        <a href="{{ route('marketplace.katalog') }}" class="text-xs sm:text-sm font-bold text-pawon-700 hover:text-pawon-800">
          &larr; Kembali ke Katalog Utama
        </a>
      </div>

      @if($produkList->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-[#E4F0D6] max-w-lg mx-auto">
          <div class="w-16 h-16 mx-auto rounded-2xl bg-pawon-50 text-pawon-700 flex items-center justify-center text-3xl mb-4">
            🌾
          </div>
          <h3 class="text-base font-bold text-slate-900">Belum Ada Produk Listing Aktif</h3>
          <p class="text-xs text-slate-500 mt-1">
            Petani ini belum memiliki produk yang sedang dipublikasikan di marketplace.
          </p>
        </div>
      @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          @foreach($produkList as $prd)
            <div class="bg-white rounded-[20px] border border-[#E4F0D6] overflow-hidden shadow-xs hover:shadow-lg transition-all flex flex-col group">
              <a href="{{ route('marketplace.detail', $prd->id_produk) }}" class="relative h-48 bg-slate-100 block overflow-hidden">
                @if($prd->foto_produk)
                  <img src="{{ $prd->foto_produk }}" alt="{{ $prd->nama_produk }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-[#EBF6E0] text-pawon-700 font-bold text-3xl\'>🌾</div>';">
                @else
                  <div class="w-full h-full flex items-center justify-center bg-[#EBF6E0] text-pawon-700 font-bold text-3xl">
                    🌾
                  </div>
                @endif

                @if($prd->komoditas)
                  <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/95 text-pawon-800 border border-[#C5DFB0]">
                    {{ $prd->komoditas->nama_komoditas }}
                  </span>
                @endif

                @if($prd->is_stok_habis)
                  <span class="absolute top-2.5 right-2.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-600 text-white">
                    Stok Habis
                  </span>
                @else
                  <span class="absolute top-2.5 right-2.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-600 text-white">
                    Tersedia
                  </span>
                @endif
              </a>

              <div class="p-4 flex flex-col flex-grow">
                <a href="{{ route('marketplace.detail', $prd->id_produk) }}">
                  <h3 class="text-sm font-bold text-slate-900 group-hover:text-pawon-700 transition-colors truncate">
                    {{ $prd->nama_produk }}
                  </h3>
                </a>

                <div class="mt-1 text-base font-extrabold text-pawon-800">
                  {{ $prd->harga_formatted }}
                  <span class="text-xs font-normal text-slate-500">/ {{ $prd->satuan }}</span>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                  <span class="text-slate-500">Stok: <strong class="text-slate-800">{{ $prd->stok_tersedia }} {{ $prd->satuan }}</strong></span>
                  <a href="{{ route('marketplace.detail', $prd->id_produk) }}" class="inline-flex items-center gap-1 text-pawon-700 font-bold hover:underline">
                    Detail &rarr;
                  </a>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-8">
          {{ $produkList->links() }}
        </div>
      @endif

    </div>
  </section>
@endsection
