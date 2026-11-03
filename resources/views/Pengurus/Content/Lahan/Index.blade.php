@extends('Pengurus.Layout._layout')

@section('title', 'Kelola Lahan')

@section('content')
<div class="space-y-6">

  {{-- ==================== FLASH NOTIFICATIONS ==================== --}}
  @if(session('success'))
    <div id="flash-banner" class="flex items-center justify-between p-4 rounded-2xl bg-[#EBF6E0] border border-[#C5DFB0] text-[#1A2D10] text-xs sm:text-sm shadow-xs animate-in fade-in duration-200">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-[#4D9830] text-white flex items-center justify-center shrink-0">
          <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
        </div>
        <div>
          <span class="font-bold">Berhasil!</span>
          <span class="text-[#4A6030] ml-1">{{ session('success') }}</span>
        </div>
      </div>
      <button type="button" onclick="document.getElementById('flash-banner').remove()" class="p-1 text-[#4A6030] hover:text-[#1A2D10] rounded-lg hover:bg-[#dff0d0] cursor-pointer" aria-label="Tutup">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>
  @endif

  @if(isset($errors) && $errors->any())
    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm space-y-1 animate-in fade-in duration-200">
      <div class="flex items-center gap-2 font-bold text-red-900">
        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
        <span>Terjadi kesalahan:</span>
      </div>
      <ul class="list-disc list-inside pl-6 text-red-700 space-y-0.5">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- ==================== 1. TOP HEADER ==================== --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Kelola Lahan
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Pantau, periksa, dan verifikasi data lahan pertanian anggota {{ $kelompok?->nama_kelompok ?? 'Kelompok Tani' }}.
      </p>
    </div>

    <div class="flex items-center gap-2.5 shrink-0">
      <a href="{{ route('pengurus.lahan') }}"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] hover:border-[#9AB880] transition-all shadow-2xs cursor-pointer"
        title="Muat Ulang Halaman">
        <i data-lucide="refresh-cw" class="w-4 h-4 text-[#4D9830]"></i>
        <span>Segarkan</span>
      </a>
    </div>
  </div>

  {{-- ==================== 2. SUMMARY METRICS CARDS ==================== --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

    <!-- Card 1: Total Lahan -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
        <i data-lucide="map" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Total Lahan</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ $stats['total'] ?? 0 }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Lahan terdaftar di kelompok</span>
      </div>
    </div>

    <!-- Card 2: Menunggu Verifikasi -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
        <i data-lucide="clock" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Menunggu Verifikasi</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ $stats['menunggu_verifikasi'] ?? 0 }}</span>
        <span class="text-[11px] text-amber-600 font-semibold block mt-0.5">● Perlu ditinjau Ketua</span>
      </div>
    </div>

    <!-- Card 3: Terverifikasi -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
        <i data-lucide="check-circle" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Terverifikasi</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ $stats['terverifikasi'] ?? 0 }}</span>
        <span class="text-[11px] text-emerald-600 font-semibold block mt-0.5">● Data sah & tervalidasi</span>
      </div>
    </div>

    <!-- Card 4: Perlu Perbaikan -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Perlu Perbaikan</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ $stats['perlu_perbaikan'] ?? 0 }}</span>
        <span class="text-[11px] text-rose-600 font-semibold block mt-0.5">● Menunggu revisi anggota</span>
      </div>
    </div>

  </div>

  {{-- ==================== 3. FILTER & SEARCH TOOLBAR ==================== --}}
  <div class="p-4 sm:p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs">
    <form method="GET" action="{{ route('pengurus.lahan') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">

      <!-- Search Box (q) -->
      <div class="lg:col-span-5 relative">
        <i data-lucide="search" class="w-4 h-4 text-[#9AB880] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama lahan, alamat, atau nama anggota..."
          class="w-full h-11 pl-10 pr-4 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-slate-800 placeholder-[#9AB880] text-xs sm:text-sm focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
      </div>

      <!-- Filter Status Verifikasi -->
      <div class="lg:col-span-3">
        @php
          $currentStatus = request('status_verifikasi') ?? request('status');
        @endphp
        <select name="status_verifikasi"
          class="w-full h-11 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="">Semua Status Verifikasi</option>
          <option value="menunggu_verifikasi" {{ $currentStatus === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
          <option value="terverifikasi" {{ $currentStatus === 'terverifikasi' ? 'selected' : '' }}>Terverifikasi</option>
          <option value="perlu_perbaikan" {{ $currentStatus === 'perlu_perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
        </select>
      </div>

      <!-- Filter Komoditas -->
      <div class="lg:col-span-2">
        <select name="id_komoditas"
          class="w-full h-11 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="">Semua Komoditas</option>
          @foreach($daftarKomoditas as $kmd)
            <option value="{{ $kmd->id_komoditas }}" {{ request('id_komoditas') === $kmd->id_komoditas ? 'selected' : '' }}>
              {{ $kmd->nama_komoditas }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Action Buttons (Filter & Reset) -->
      <div class="lg:col-span-2 flex items-center gap-2">
        <button type="submit"
          class="flex-1 h-11 inline-flex items-center justify-center gap-2 px-3.5 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] active:scale-[0.99] text-white text-xs sm:text-sm font-bold shadow-xs transition-all cursor-pointer">
          <i data-lucide="filter" class="w-4 h-4"></i>
          <span>Filter</span>
        </button>

        @if(request()->filled('q') || request()->filled('status_verifikasi') || request()->filled('status') || request()->filled('id_komoditas'))
          <a href="{{ route('pengurus.lahan') }}"
            class="h-11 px-3 inline-flex items-center justify-center rounded-xl border border-[#C5DFB0] bg-white text-[#4A6030] hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition-colors shadow-2xs"
            title="Reset Filter">
            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
          </a>
        @endif
      </div>

    </form>
  </div>

  {{-- ==================== 4. DATA TABLE ==================== --}}
  <div class="bg-white rounded-[20px] border border-[#E4F0D6] shadow-2xs overflow-hidden">

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs sm:text-sm">
        <!-- Table Header -->
        <thead>
          <tr class="bg-[#F5F8F1] border-b border-[#E4F0D6] text-[#1A2D10] font-bold tracking-wider uppercase text-[11px]">
            <th class="py-3.5 px-4 text-center w-12">No</th>
            <th class="py-3.5 px-4">ID Lahan</th>
            <th class="py-3.5 px-4">Nama Lahan</th>
            <th class="py-3.5 px-4">Nama Anggota</th>
            <th class="py-3.5 px-4">Komoditas</th>
            <th class="py-3.5 px-4">Luas</th>
            <th class="py-3.5 px-4">Musim Tanam</th>
            <th class="py-3.5 px-4 text-center">Status Verifikasi</th>
            <th class="py-3.5 px-4 text-center">Revisi</th>
            <th class="py-3.5 px-4 text-center w-28">Aksi</th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="divide-y divide-[#E4F0D6]/60">
          @forelse($lahans as $index => $item)
            <tr class="hover:bg-[#F5F8F1]/50 transition-colors">
              <td class="py-3.5 px-4 text-center text-[#9AB880] font-medium">
                {{ ($lahans->currentPage() - 1) * $lahans->perPage() + $loop->iteration }}
              </td>

              <!-- ID Lahan -->
              <td class="py-3.5 px-4">
                <span class="font-mono text-[11px] font-semibold text-[#4A6030] bg-[#F5F8F1] px-2 py-0.5 rounded-md border border-[#E4F0D6]">
                  {{ $item->id_lahan }}
                </span>
              </td>

              <!-- Nama Lahan & Lokasi -->
              <td class="py-3.5 px-4">
                <div class="font-bold text-[#1A2D10]">{{ $item->nama_lahan }}</div>
                <div class="text-[11px] text-[#9AB880] max-w-xs truncate mt-0.5" title="{{ $item->alamat_lahan }}">
                  <i data-lucide="map-pin" class="w-3 h-3 inline-block -mt-0.5 mr-0.5 text-[#9AB880]"></i>{{ $item->alamat_lahan ?? '-' }}
                </div>
              </td>

              <!-- Nama Anggota (TANPA NIK) -->
              <td class="py-3.5 px-4">
                <div class="font-bold text-[#1A2D10]">{{ $item->pengguna?->nama ?? '-' }}</div>
                @if(!empty($item->pengguna?->no_telepon))
                  <div class="text-[10px] text-[#9AB880] mt-0.5">
                    <i data-lucide="phone" class="w-2.5 h-2.5 inline-block mr-0.5"></i>{{ $item->pengguna->no_telepon }}
                  </div>
                @endif
              </td>

              <!-- Komoditas -->
              <td class="py-3.5 px-4">
                @if($item->komoditas)
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#EBF6E0] text-[#4D9830] font-semibold text-[11px]">
                    <i data-lucide="sprout" class="w-3 h-3"></i>
                    {{ $item->komoditas->nama_komoditas }}
                  </span>
                @else
                  <span class="text-[#9AB880] text-xs italic">Belum ditentukan</span>
                @endif
              </td>

              <!-- Luas -->
              <td class="py-3.5 px-4 font-bold text-[#1A2D10]">
                {{ number_format((float)$item->luas, 2, ',', '.') }} <span class="text-[11px] font-normal text-[#9AB880]">ha</span>
              </td>

              <!-- Musim Tanam -->
              <td class="py-3.5 px-4 text-[#4A6030]">
                {{ $item->musim_tanam ?? '-' }}
              </td>

              <!-- Status Verifikasi Badge -->
              <td class="py-3.5 px-4 text-center">
                @if($item->status_verifikasi === 'menunggu_verifikasi')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-[10px] font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Menunggu Verifikasi
                  </span>
                @elseif($item->status_verifikasi === 'terverifikasi')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Terverifikasi
                  </span>
                @elseif($item->status_verifikasi === 'perlu_perbaikan')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-[10px] font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Perlu Perbaikan
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold">
                    {{ $item->status_verifikasi }}
                  </span>
                @endif
              </td>

              <!-- Nomor Revisi -->
              <td class="py-3.5 px-4 text-center font-mono text-xs font-semibold text-[#4A6030]">
                Rev. {{ $item->nomor_revisi ?? 1 }}
              </td>

              <!-- Aksi Detail -->
              <td class="py-3.5 px-4 text-center">
                <a href="{{ route('pengurus.lahan.show', $item->id_lahan) }}"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#EAF5DE] text-[#4D9830] hover:bg-[#DFF7E7] hover:text-[#3D8024] font-bold text-xs transition-colors cursor-pointer shadow-2xs"
                  title="Lihat Detail & Riwayat Lahan">
                  <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                  <span>Detail</span>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="py-14 text-center">
                <div class="flex flex-col items-center justify-center space-y-3">
                  <div class="w-14 h-14 rounded-2xl bg-[#F5F8F1] border border-[#E4F0D6] flex items-center justify-center text-[#9AB880]">
                    <i data-lucide="map-off" class="w-7 h-7"></i>
                  </div>
                  <div class="text-base font-bold text-[#1A2D10]">Tidak ada data lahan</div>
                  <p class="text-xs text-[#9AB880] max-w-sm">
                    @if(request()->filled('q') || request()->filled('status_verifikasi') || request()->filled('status') || request()->filled('id_komoditas'))
                      Tidak ditemukan data lahan yang cocok dengan kriteria pencarian dan filter Anda.
                    @else
                      Belum ada anggota yang mendaftarkan lahan pertanian di kelompok tani ini.
                    @endif
                  </p>
                  @if(request()->filled('q') || request()->filled('status_verifikasi') || request()->filled('status') || request()->filled('id_komoditas'))
                    <a href="{{ route('pengurus.lahan') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#4D9830] hover:underline mt-1">
                      <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                      <span>Reset filter pencarian</span>
                    </a>
                  @endif
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Table Footer / Pagination -->
    @if($lahans->hasPages() || $lahans->total() > 0)
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-[#E4F0D6] px-5 py-4 text-xs text-[#6B7F5B] bg-[#F5F8F1]/30">
        <div>
          Menampilkan <strong class="text-[#1A2D10] font-bold">{{ $lahans->firstItem() ?? 0 }}</strong>–<strong class="text-[#1A2D10] font-bold">{{ $lahans->lastItem() ?? 0 }}</strong> dari <strong class="text-[#1A2D10] font-bold">{{ $lahans->total() }}</strong> lahan
        </div>
        <div>
          {{ $lahans->links() }}
        </div>
      </div>
    @endif

  </div>

</div>
@endsection
