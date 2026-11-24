@extends('Customer.Layout._layout')

@section('title', 'Katalog Marketplace PawonTani - Produk Tani Segar')

@section('content')
  <!-- Page Header Banner -->
  <section class="bg-white border-b border-[#E4F0D6] py-8 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <nav class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
            <a href="{{ route('marketplace.home') }}" class="hover:text-pawon-700">Marketplace</a>
            <span>/</span>
            <span class="text-pawon-800 font-bold">Katalog Produk</span>
          </nav>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Katalog Hasil Tani PawonTani
          </h1>
          <p class="text-sm text-slate-500 mt-1">
            Produk berkualitas panen segar langsung dari petani anggota kelompok tani.
          </p>
        </div>

        <!-- Metric pill -->
        <div class="shrink-0 bg-pawon-50 border border-[#C5DFB0] px-4 py-2.5 rounded-2xl flex items-center gap-3">
          <span class="text-2xl">🌱</span>
          <div>
            <div class="text-xs text-slate-500 font-medium">Total Produk Aktif</div>
            <div class="text-base font-extrabold text-pawon-900">{{ $produkList->total() }} Produk Terdaftar</div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- Filter, Search, and Catalog Container -->
  <section class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Filter and Search Controls Bar -->
      <form action="{{ route('marketplace.katalog') }}" method="GET" class="bg-white rounded-3xl p-5 border border-[#E4F0D6] shadow-xs mb-8 space-y-4">
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
          
          <!-- Search Input (6 Cols) -->
          <div class="md:col-span-6 relative">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari beras, jagung, gabah, cabai..." class="w-full pl-11 pr-4 py-3 bg-[#F5F8F1] border border-[#E4F0D6] rounded-2xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-pawon-700 focus:bg-white transition-all">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            @if(request('q'))
              <a href="{{ route('marketplace.katalog', request()->except('q')) }}" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs text-slate-400 hover:text-slate-600">
                ✕ Reset
              </a>
            @endif
          </div>

          <!-- Commodity Filter Dropdown (3 Cols) -->
          <div class="md:col-span-3">
            <select name="komoditas" onchange="this.form.submit()" class="w-full px-4 py-3 bg-[#F5F8F1] border border-[#E4F0D6] rounded-2xl text-sm text-slate-800 focus:outline-none focus:border-pawon-700 focus:bg-white transition-all">
              <option value="">Semua Komoditas</option>
              @foreach($komoditasList as $kmdt)
                <option value="{{ $kmdt->id_komoditas }}" {{ request('komoditas') == $kmdt->id_komoditas ? 'selected' : '' }}>
                  {{ $kmdt->nama_komoditas }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Sorting Dropdown (3 Cols) -->
          <div class="md:col-span-3">
            <select name="sort" onchange="this.form.submit()" class="w-full px-4 py-3 bg-[#F5F8F1] border border-[#E4F0D6] rounded-2xl text-sm text-slate-800 focus:outline-none focus:border-pawon-700 focus:bg-white transition-all">
              <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru</option>
              <option value="nama_asc" {{ request('sort') == 'nama_asc' ? 'selected' : '' }}>Nama Produk (A - Z)</option>
              <option value="nama_desc" {{ request('sort') == 'nama_desc' ? 'selected' : '' }}>Nama Produk (Z - A)</option>
              <option value="harga_asc" {{ request('sort') == 'harga_asc' ? 'selected' : '' }}>Harga Terendah</option>
              <option value="harga_desc" {{ request('sort') == 'harga_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
            </select>
          </div>

        </div>

        <!-- Quick Commodity Pills Row -->
        <div class="flex items-center gap-2 overflow-x-auto pt-2 border-t border-slate-100 no-scrollbar">
          <span class="text-xs font-bold text-slate-500 shrink-0">Filter Cepat:</span>
          <a href="{{ route('marketplace.katalog', request()->except('komoditas')) }}" class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold {{ !request('komoditas') ? 'bg-pawon-700 text-white' : 'bg-[#F5F8F1] text-slate-600 hover:bg-pawon-100' }}">
            Semua
          </a>
          @foreach($komoditasList as $kmdt)
            <a href="{{ route('marketplace.katalog', array_merge(request()->all(), ['komoditas' => $kmdt->id_komoditas])) }}" class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold {{ request('komoditas') == $kmdt->id_komoditas ? 'bg-pawon-700 text-white' : 'bg-[#F5F8F1] text-slate-600 hover:bg-pawon-100' }}">
              {{ $kmdt->nama_komoditas }}
            </a>
          @endforeach
        </div>

      </form>

      <!-- Active Filter Tags indicator -->
      @if(request('q') || request('komoditas') || request('sort'))
        <div class="flex items-center gap-2 mb-6 text-xs text-slate-500">
          <span>Filter aktif:</span>
          @if(request('q'))
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-200 text-slate-700 font-medium">
              Cari: "{{ request('q') }}"
            </span>
          @endif
          @if(request('komoditas'))
            @php $selKmdt = $komoditasList->firstWhere('id_komoditas', request('komoditas')); @endphp
            @if($selKmdt)
              <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-pawon-100 text-pawon-800 font-medium">
                Komoditas: {{ $selKmdt->nama_komoditas }}
              </span>
            @endif
          @endif
          <a href="{{ route('marketplace.katalog') }}" class="text-rose-600 hover:underline font-semibold ml-2">
            Hapus Semua Filter
          </a>
        </div>
      @endif

      <!-- Product Grid -->
      @if($produkList->isEmpty())
        <div class="bg-white rounded-3xl p-16 text-center border border-[#E4F0D6] max-w-lg mx-auto">
          <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-3xl mb-4">
            🔍
          </div>
          <h3 class="text-lg font-bold text-slate-900">Produk Tidak Ditemukan</h3>
          <p class="text-sm text-slate-500 mt-2 leading-relaxed">
            Tidak ada produk yang sesuai dengan kriteria pencarian atau filter yang Anda pilih. Silakan gunakan kata kunci lain atau reset filter.
          </p>
          <div class="mt-6">
            <a href="{{ route('marketplace.katalog') }}" class="inline-flex items-center px-5 py-2.5 text-xs font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full transition-all">
              Tampilkan Semua Produk
            </a>
          </div>
        </div>
      @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          @foreach($produkList as $prd)
            <div class="bg-white rounded-[20px] border border-[#E4F0D6] overflow-hidden shadow-xs hover:shadow-lg transition-all duration-300 hover:-translate-y-1 flex flex-col group">
              
              <!-- Foto Produk -->
              <a href="{{ route('marketplace.detail', $prd->id_produk) }}" class="relative h-48 bg-slate-100 overflow-hidden block">
                @if($prd->foto_produk)
                  <img src="{{ $prd->foto_produk }}" alt="{{ $prd->nama_produk }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-[#EBF6E0] text-pawon-700 font-bold text-3xl\'>🌾</div>';">
                @else
                  <div class="w-full h-full flex items-center justify-center bg-[#EBF6E0] text-pawon-700 font-bold text-3xl">
                    🌾
                  </div>
                @endif

                <!-- Commodity Tag Badge -->
                @if($prd->komoditas)
                  <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/95 text-pawon-800 backdrop-blur-xs border border-[#C5DFB0]">
                    {{ $prd->komoditas->nama_komoditas }}
                  </span>
                @endif

                <!-- Stock Status Badge -->
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

              <!-- Card Content -->
              <div class="p-4 flex flex-col flex-grow">
                
                <a href="{{ route('marketplace.detail', $prd->id_produk) }}">
                  <h3 class="text-sm font-bold text-slate-900 group-hover:text-pawon-700 transition-colors line-clamp-1">
                    {{ $prd->nama_produk }}
                  </h3>
                </a>

                <div class="mt-1.5 text-base font-extrabold text-pawon-800">
                  {{ $prd->harga_formatted }}
                  <span class="text-xs font-normal text-slate-500">/ {{ $prd->satuan }}</span>
                </div>

                <!-- Seller & General Location -->
                <div class="mt-3 pt-2.5 border-t border-slate-100 space-y-1 text-xs">
                  <div class="flex items-center gap-1.5 text-slate-700 truncate">
                    <span class="w-4 h-4 rounded-full bg-pawon-100 text-pawon-800 flex items-center justify-center font-bold text-[9px] shrink-0">
                      {{ strtoupper(substr($prd->pengguna?->nama ?: 'P', 0, 1)) }}
                    </span>
                    <a href="{{ route('marketplace.penjual', $prd->id_pengguna) }}" class="truncate font-semibold hover:text-pawon-700">
                      {{ $prd->pengguna?->nama ?: 'Petani PawonTani' }}
                    </a>
                  </div>

                  <div class="flex items-center justify-between text-[11px] text-slate-500">
                    <span class="truncate">{{ $prd->pengguna?->kelompokTani?->nama_kelompok ?: 'Poktan Binaan' }}</span>
                    <span class="shrink-0 text-slate-400">
                      {{ $prd->pengguna?->lokasi_nama ?: 'Banyumas' }}
                    </span>
                  </div>
                </div>

                <!-- Bottom Stock & Action -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                  <div class="text-[11px]">
                    <span class="text-slate-400">Stok:</span>
                    <span class="font-bold {{ $prd->is_stok_habis ? 'text-rose-600' : 'text-slate-800' }}">
                      {{ $prd->stok_tersedia }} {{ $prd->satuan }}
                    </span>
                  </div>

                  <a href="{{ route('marketplace.detail', $prd->id_produk) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-xl transition-all">
                    Detail
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                  </a>
                </div>

              </div>

            </div>
          @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-10">
          {{ $produkList->links() }}
        </div>
      @endif

    </div>
  </section>
@endsection
