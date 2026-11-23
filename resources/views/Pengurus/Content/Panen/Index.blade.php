@extends('Pengurus.Layout._layout')

@section('title', 'Kelola Panen')

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
        Kelola Panen
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Pantau, verifikasi, dan kelola catatan hasil panen anggota {{ $kelompok?->nama_kelompok ?? 'Kelompok Tani' }}.
      </p>
    </div>

    <div class="flex items-center gap-2.5 shrink-0">
      <a href="{{ route('pengurus.panen') }}"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] hover:border-[#9AB880] transition-all shadow-2xs cursor-pointer"
        title="Muat Ulang Halaman">
        <i data-lucide="refresh-cw" class="w-4 h-4 text-[#4D9830]"></i>
        <span>Segarkan</span>
      </a>
    </div>
  </div>

  {{-- ==================== 2. SUMMARY METRICS CARDS ==================== --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

    <!-- Card 1: Total Catatan Panen -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
        <i data-lucide="wheat" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Total Panen</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ $stats['total'] ?? 0 }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Total volume: {{ number_format($stats['total_berat_ton'] ?? 0, 2, ',', '.') }} Ton</span>
      </div>
    </div>

    <!-- Card 2: Menunggu Verifikasi -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
        <i data-lucide="clock" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider block">Menunggu</span>
        <span class="text-2xl font-extrabold text-amber-900 tracking-tight">{{ $stats['menunggu'] ?? 0 }}</span>
        <span class="text-[11px] text-amber-800 block mt-0.5 font-medium">Perlu ditinjau pengurus</span>
      </div>
    </div>

    <!-- Card 3: Disetujui -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
        <i data-lucide="check-circle-2" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider block">Disetujui</span>
        <span class="text-2xl font-extrabold text-emerald-900 tracking-tight">{{ $stats['disetujui'] ?? 0 }}</span>
        <span class="text-[11px] text-emerald-800 block mt-0.5 font-medium">Validasi poktan selesai</span>
      </div>
    </div>

    <!-- Card 4: Ditolak -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
        <i data-lucide="x-circle" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-rose-700 uppercase tracking-wider block">Ditolak</span>
        <span class="text-2xl font-extrabold text-rose-900 tracking-tight">{{ $stats['ditolak'] ?? 0 }}</span>
        <span class="text-[11px] text-rose-800 block mt-0.5 font-medium">Data ditolak / tidak sesuai</span>
      </div>
    </div>

  </div>

  {{-- ==================== 3. FILTER & SEARCH BAR ==================== --}}
  <div class="p-4 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs">
    <form method="GET" action="{{ route('pengurus.panen') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">

      <!-- Search Input -->
      <div class="lg:col-span-5 relative">
        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[#9AB880]"></i>
        <input type="text"
               name="q"
               value="{{ request('q') }}"
               placeholder="Cari kode panen, petani, varietas, atau lahan..."
               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:bg-white transition-all">
      </div>

      <!-- Filter Status -->
      <div class="lg:col-span-3">
        <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830] focus:bg-white transition-all">
          <option value="">-- Semua Status --</option>
          <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu Verifikasi</option>
          <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
          <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
        </select>
      </div>

      <!-- Filter Komoditas -->
      <div class="lg:col-span-2">
        <select name="komoditas" class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830] focus:bg-white transition-all">
          <option value="">-- Semua Komoditas --</option>
          <option value="Padi" {{ request('komoditas') === 'Padi' ? 'selected' : '' }}>Padi</option>
          <option value="Jagung" {{ request('komoditas') === 'Jagung' ? 'selected' : '' }}>Jagung</option>
        </select>
      </div>

      <!-- Buttons -->
      <div class="lg:col-span-2 flex items-center gap-2">
        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#4D9830] text-white text-xs sm:text-sm font-semibold hover:bg-[#3D7826] transition-colors shadow-xs cursor-pointer">
          <i data-lucide="filter" class="w-3.5 h-3.5"></i>
          <span>Filter</span>
        </button>
        @if(request()->hasAny(['q', 'status', 'komoditas']))
          <a href="{{ route('pengurus.panen') }}" class="p-2.5 rounded-xl border border-[#C5DFB0] text-[#4A6030] hover:text-[#1A2D10] hover:bg-[#F5F8F1] transition-colors" title="Reset Filter">
            <i data-lucide="x" class="w-4 h-4"></i>
          </a>
        @endif
      </div>

    </form>
  </div>

  {{-- ==================== 4. DATA TABLE ==================== --}}
  <div class="rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs sm:text-sm border-collapse">
        <thead>
          <tr class="bg-[#F5F8F1] border-b border-[#E4F0D6] text-[#4A6030] font-bold uppercase tracking-wider text-[11px]">
            <th class="py-3.5 px-4">Kode & Tanggal</th>
            <th class="py-3.5 px-4">Petani Anggota</th>
            <th class="py-3.5 px-4">Lahan & Varietas</th>
            <th class="py-3.5 px-4 text-right">Berat Panen</th>
            <th class="py-3.5 px-4 text-center">Status</th>
            <th class="py-3.5 px-4 text-center w-28">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E4F0D6] text-[#1A2D10]">
          @forelse($panens as $item)
            @php
              $statusBadge = match($item->status) {
                'disetujui' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Disetujui', 'icon' => 'check-circle-2'],
                'ditolak' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Ditolak', 'icon' => 'x-circle'],
                default => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Menunggu', 'icon' => 'clock'],
              };
              $beratTon = round(((float)$item->berat_kg) / 1000.0, 2);
            @endphp
            <tr class="hover:bg-[#F5F8F1]/60 transition-colors">
              {{-- Kode & Tanggal --}}
              <td class="py-4 px-4 align-top">
                <div class="font-bold text-[#1A2D10]">{{ $item->kode_panen }}</div>
                <div class="text-[11px] text-[#9AB880] mt-0.5 flex items-center gap-1 font-medium">
                  <i data-lucide="calendar" class="w-3 h-3 text-[#4D9830]"></i>
                  <span>{{ $item->tanggal_panen ? $item->tanggal_panen->translatedFormat('d M Y') : '-' }}</span>
                </div>
              </td>

              {{-- Petani --}}
              <td class="py-4 px-4 align-top">
                <div class="font-semibold text-[#1A2D10]">{{ $item->pengguna?->nama ?? 'Anggota Poktan' }}</div>
                <div class="text-[11px] text-[#9AB880] mt-0.5 flex items-center gap-1">
                  <i data-lucide="phone" class="w-3 h-3"></i>
                  <span>{{ $item->pengguna?->no_telepon ?? '-' }}</span>
                </div>
              </td>

              {{-- Lahan & Varietas --}}
              <td class="py-4 px-4 align-top">
                <div class="font-semibold text-[#4D9830]">{{ $item->varietas ?? $item->komoditas_nama }}</div>
                <div class="text-[11px] text-[#4A6030] mt-0.5">
                  {{ $item->lahan?->nama_lahan ?? 'Petak Lahan' }}
                  @if($item->lahan?->luas)
                    • {{ number_format($item->lahan->luas, 2) }} Ha
                  @endif
                </div>
                <div class="text-[11px] text-[#9AB880]">{{ $item->musim_tanam }}</div>
              </td>

              {{-- Berat Panen --}}
              <td class="py-4 px-4 align-top text-right">
                <div class="font-extrabold text-[#1A2D10] text-sm">{{ number_format((float)$item->berat_kg, 0, ',', '.') }} kg</div>
                <div class="text-[11px] text-[#4D9830] font-semibold">≈ {{ $beratTon }} Ton</div>
              </td>

              {{-- Status --}}
              <td class="py-4 px-4 align-top text-center">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $statusBadge['bg'] }}">
                  <i data-lucide="{{ $statusBadge['icon'] }}" class="w-3 h-3"></i>
                  <span>{{ $statusBadge['label'] }}</span>
                </span>
              </td>

              {{-- Aksi --}}
              <td class="py-4 px-4 align-top text-center">
                <div class="inline-flex items-center gap-1.5">
                  <a href="{{ route('pengurus.panen.show', $item->id_panen) }}"
                     class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#EBF6E0] text-[#4D9830] hover:bg-[#4D9830] hover:text-white text-xs font-semibold transition-all"
                     title="Lihat Detail & Verifikasi">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                    <span>Detail</span>
                  </a>

                  @if($item->status === 'menunggu')
                    <form method="POST" action="{{ route('pengurus.panen.verifikasi', $item->id_panen) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin MENYETUJUI hasil panen ini?')">
                      @csrf
                      <input type="hidden" name="status" value="disetujui">
                      <button type="submit" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all cursor-pointer" title="Setujui Cepat">
                        <i data-lucide="check" class="w-3.5 h-3.5 stroke-[3]"></i>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-12 px-4 text-center">
                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                  <div class="w-14 h-14 rounded-2xl bg-[#F5F8F1] text-[#9AB880] flex items-center justify-center mb-3">
                    <i data-lucide="inbox" class="w-7 h-7"></i>
                  </div>
                  <h3 class="text-sm sm:text-base font-bold text-[#1A2D10]">Belum Ada Catatan Hasil Panen</h3>
                  <p class="text-xs text-[#9AB880] mt-1 text-center">
                    Anggota kelompok tani Anda belum mengirimkan pencatatan panen dari aplikasi mobile atau filter pencarian tidak menemukan hasil.
                  </p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination Footer --}}
    @if($panens->hasPages())
      <div class="p-4 bg-[#F5F8F1] border-t border-[#E4F0D6]">
        {{ $panens->links() }}
      </div>
    @endif
  </div>

</div>
@endsection
