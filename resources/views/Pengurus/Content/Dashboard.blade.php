@extends('Pengurus.Layout._layout')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

  <!-- ==================== 1. HERO WELCOME BANNER ==================== -->
  <div class="p-6 sm:p-8 rounded-[22px] bg-gradient-to-r from-[#2B571B] via-[#4D9830] to-[#6EB844] text-white shadow-sm relative overflow-hidden">
    <div class="absolute -right-8 -bottom-8 w-60 h-60 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
    <div class="absolute right-1/3 -top-12 w-40 h-40 rounded-full bg-emerald-300/10 blur-xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
      <div class="max-w-2xl space-y-2.5">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-white text-xs font-semibold backdrop-blur-xs border border-white/20">
          <i data-lucide="sparkles" class="w-3.5 h-3.5 text-yellow-300"></i>
          <span>{{ $sapaanWaktu }}, {{ $pengurus->nama ?? 'Pengurus' }}</span>
          <span class="text-white/40">•</span>
          <span class="text-emerald-100 font-medium">{{ $kelompok?->nama_kelompok ?? 'Kelompok Tani' }}</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
          Pusat Operasional Kelompok Tani
        </h1>

        <p class="text-xs sm:text-sm text-emerald-50 leading-relaxed font-medium">
          @if ($lahanMenunggu > 0)
            Perhatian: Terdapat <span class="font-bold underline text-white">{{ $lahanMenunggu }} petak lahan</span> anggota yang menunggu verifikasi lapangan.
          @else
            Pantau perkembangan budidaya tanaman, administrasi anggota, dan aktivitas perawatan harian lahan anggota secara terpadu.
          @endif
        </p>
      </div>

      <!-- Tombol Aksi Cepat -->
      <div class="flex flex-wrap items-center gap-2.5 self-start lg:self-center shrink-0">
        <a href="{{ route('pengurus.anggota') }}"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-[#1A2D10] text-xs sm:text-sm font-bold shadow-xs hover:bg-[#F5F8F1] hover:shadow-md transition-all">
          <i data-lucide="users" class="w-4 h-4 text-[#4D9830]"></i>
          <span>Kelola Anggota</span>
        </a>
        <a href="{{ route('pengurus.monitoring') }}"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1A2D10]/40 hover:bg-[#1A2D10]/60 border border-white/25 text-white text-xs sm:text-sm font-bold backdrop-blur-xs transition-all">
          <i data-lucide="activity" class="w-4 h-4 text-emerald-300"></i>
          <span>Monitoring Lahan</span>
          @if ($lahanMenunggu > 0)
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
          @endif
        </a>
      </div>
    </div>
  </div>

  <!-- ==================== 2. GRID 4 KARTU KPI RINGKASAN ==================== -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

    <!-- Total Anggota Petani -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Anggota Petani</span>
        <div class="w-10 h-10 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center">
          <i data-lucide="users" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalAnggota) }}</div>
        <div class="mt-1 flex items-center gap-1.5 text-xs text-[#4A6030]">
          <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
          <span class="font-semibold">Petani Terdaftar di Poktan</span>
        </div>
      </div>
    </div>

    <!-- Total Petak & Luas Lahan -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Lahan Garapan</span>
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
          <i data-lucide="map-pin" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">
          {{ number_format($totalLuasLahan, 2) }} <span class="text-sm font-semibold text-slate-500">Ha</span>
        </div>
        <div class="mt-1 text-xs text-slate-500 font-medium">
          Terbagi dalam {{ number_format($totalPetakLahan) }} petak lahan
        </div>
      </div>
    </div>

    <!-- Status Verifikasi Lahan -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Verifikasi Lahan</span>
        <div class="w-10 h-10 rounded-xl {{ $lahanMenunggu > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
          <i data-lucide="check-circle-2" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">
          {{ $lahanTerverifikasi }} <span class="text-sm font-semibold text-slate-400">/ {{ $totalPetakLahan }}</span>
        </div>
        <div class="mt-1 text-xs {{ $lahanMenunggu > 0 ? 'text-amber-600 font-semibold' : 'text-[#4A6030]' }}">
          {{ $lahanMenunggu > 0 ? $lahanMenunggu . ' petak butuh verifikasi' : 'Seluruh lahan terverifikasi' }}
        </div>
      </div>
    </div>

    <!-- Aktivitas 30 Hari Terakhir -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Aktivitas Petani</span>
        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
          <i data-lucide="sprout" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalAktivitasBulanIni) }}</div>
        <div class="mt-1 text-xs text-slate-500 font-medium">
          Log perawatan dalam 30 hari
        </div>
      </div>
    </div>

  </div>

  <!-- ==================== 3. DUA KOLOM UTAMA ==================== -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    <!-- ========== KOLOM KIRI: FOKUS OPERASIONAL POKTAN (7 Kolom) ========== -->
    <div class="lg:col-span-7 space-y-6">

      <!-- Widget 1: Verifikasi Lahan Petani -->
      <div class="bg-white rounded-[22px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-[#E4F0D6]/70 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
              <i data-lucide="clipboard-check" class="w-4 h-4"></i>
            </div>
            <div>
              <h2 class="text-base font-extrabold text-[#1A2D10]">Verifikasi Lahan Petani</h2>
              <p class="text-xs text-slate-500 font-medium">Petak lahan yang baru diajukan atau butuh revisi berkas</p>
            </div>
          </div>
          <a href="{{ route('pengurus.lahan') }}"
            class="text-xs font-bold text-[#4D9830] hover:text-[#3D8024] hover:underline flex items-center gap-1">
            <span>Daftar Lahan</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>

        <div class="p-5 sm:p-6">
          @forelse ($lahanMenungguTinjauan as $itemLahan)
            @php
              $statusBadge = match($itemLahan->status_verifikasi) {
                'terverifikasi' => ['bg' => 'bg-[#EBF6E0]', 'text' => 'text-[#2E5A1C]', 'border' => 'border-[#D1DFC4]', 'label' => 'Terverifikasi'],
                'perlu_perbaikan' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'label' => 'Perlu Perbaikan'],
                default => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'label' => 'Menunggu Verifikasi'],
              };
            @endphp
            <div class="p-4 rounded-xl border border-[#E4F0D6] bg-[#FBFDF9] hover:bg-[#F5F8F1] transition-colors mb-3 last:mb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div class="space-y-1">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-bold text-[#1A2D10]">{{ $itemLahan->nama_lahan }}</span>
                  <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} border {{ $statusBadge['border'] }}">
                    {{ $statusBadge['label'] }}
                  </span>
                </div>
                <div class="text-xs text-slate-600 flex items-center gap-2 flex-wrap">
                  <span class="flex items-center gap-1 font-medium text-[#1A2D10]">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-[#4D9830]"></i>
                    <span>{{ $itemLahan->pengguna->nama ?? 'Petani' }}</span>
                  </span>
                  <span class="text-slate-300">•</span>
                  <span>{{ $itemLahan->komoditas->nama_komoditas ?? 'Padi' }}</span>
                  <span class="text-slate-300">•</span>
                  <span class="font-semibold text-[#4A6030]">{{ number_format((float) $itemLahan->luas, 2) }} Ha</span>
                </div>
              </div>

              <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                <a href="{{ route('pengurus.lahan.show', $itemLahan->id_lahan) }}"
                  class="h-8 px-3 rounded-lg bg-[#4D9830] hover:bg-[#3D8024] text-white text-xs font-bold flex items-center gap-1 transition-colors shadow-2xs">
                  <span>Tinjau Lahan</span>
                  <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
              </div>
            </div>
          @empty
            <div class="py-8 text-center">
              <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center mx-auto mb-2.5">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
              </div>
              <h3 class="text-sm font-bold text-[#1A2D10]">Semua Lahan Terverifikasi</h3>
              <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                Tidak ada petak lahan yang sedang menunggu verifikasi atau perlu perbaikan saat ini.
              </p>
            </div>
          @endforelse
        </div>
      </div>

      <!-- Widget 2: Catatan Aktivitas Tanam Petani Terkini -->
      <div class="bg-white rounded-[22px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-[#E4F0D6]/70 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] flex items-center justify-center text-[#4D9830] shrink-0">
              <i data-lucide="clock" class="w-4 h-4"></i>
            </div>
            <div>
              <h2 class="text-base font-extrabold text-[#1A2D10]">Aktivitas Perawatan Petak Terkini</h2>
              <p class="text-xs text-slate-500 font-medium">Laporan kegiatan harian petani lewat aplikasi mobile</p>
            </div>
          </div>
          <a href="{{ route('pengurus.monitoring') }}"
            class="text-xs font-bold text-[#4D9830] hover:text-[#3D8024] hover:underline flex items-center gap-1">
            <span>Selengkapnya</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>

        <div class="divide-y divide-[#E4F0D6]/60">
          @forelse ($aktivitasTerkini as $akt)
            @php
              $kondisiBadge = match(strtolower($akt->kondisi_tanaman ?? '')) {
                'sehat', 'sangat baik', 'baik' => ['bg' => 'bg-[#EBF6E0]', 'text' => 'text-[#2E5A1C]', 'border' => 'border-[#D1DFC4]', 'label' => $akt->kondisi_tanaman],
                'terserang hama', 'kurang sehat', 'kering' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'label' => $akt->kondisi_tanaman],
                default => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'label' => $akt->kondisi_tanaman ?? 'Normal'],
              };
            @endphp
            <div class="p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-[#F5F8F1]/50 transition-colors">
              <div class="space-y-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="text-xs font-bold text-[#4D9830] uppercase tracking-wider">
                    {{ $akt->kategori ?? 'Perawatan' }}
                  </span>
                  <span class="text-slate-300">•</span>
                  <h4 class="text-sm font-bold text-[#1A2D10]">{{ $akt->judul }}</h4>
                  @if($akt->kondisi_tanaman)
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $kondisiBadge['bg'] }} {{ $kondisiBadge['text'] }} border {{ $kondisiBadge['border'] }}">
                      {{ $kondisiBadge['label'] }}
                    </span>
                  @endif
                </div>
                <div class="text-xs text-slate-500 flex items-center gap-2">
                  <span class="font-medium text-slate-700">{{ $akt->pengguna->nama ?? 'Petani' }}</span>
                  <span class="text-slate-300">•</span>
                  <span>{{ $akt->lahan->nama_lahan ?? 'Petak Lahan' }}</span>
                  @if($akt->hari_setelah_tanam)
                    <span class="text-slate-300">•</span>
                    <span>{{ $akt->hari_setelah_tanam }} HST</span>
                  @endif
                </div>
              </div>

              <div class="text-right shrink-0">
                <span class="text-[11px] font-semibold text-slate-500 block">
                  {{ $akt->tanggal ? \Carbon\Carbon::parse($akt->tanggal)->locale('id')->isoFormat('D MMM YYYY') : '-' }}
                </span>
                <span class="text-[10px] text-slate-400">
                  {{ $akt->created_at ? $akt->created_at->diffForHumans() : '' }}
                </span>
              </div>
            </div>
          @empty
            <div class="py-8 text-center">
              <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center mx-auto mb-2.5">
                <i data-lucide="inbox" class="w-6 h-6"></i>
              </div>
              <h3 class="text-sm font-bold text-[#1A2D10]">Belum Ada Aktivitas</h3>
              <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                Aktivitas tanam atau catatan rawat dari aplikasi mobile anggota akan muncul di sini.
              </p>
            </div>
          @endforelse
        </div>
      </div>

    </div>

    <!-- ========== KOLOM KANAN: MONITORING & PROFIL POKTAN (5 Kolom) ========== -->
    <div class="lg:col-span-5 space-y-6">

      <!-- Kartu 1: Identitas Kelompok Tani -->
      <div class="bg-white rounded-[22px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-[#E4F0D6]/70 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] flex items-center justify-center text-[#4D9830] shrink-0">
              <i data-lucide="building-2" class="w-4 h-4"></i>
            </div>
            <div>
              <h2 class="text-base font-extrabold text-[#1A2D10]">Profil Kelompok Tani</h2>
              <p class="text-xs text-slate-500 font-medium">Informasi kelembagaan dan domisili</p>
            </div>
          </div>
          <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-[#EBF6E0] text-[#2E5A1C] border border-[#D1DFC4]">
            {{ $kelompok?->status ?? 'Aktif' }}
          </span>
        </div>

        <div class="p-5 sm:p-6 space-y-3.5">
          <div class="p-4 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6] space-y-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880] block">Nama Poktan</span>
            <div class="text-base font-extrabold text-[#1A2D10]">{{ $kelompok?->nama_kelompok ?? 'Kelompok Tani Belum Ditentukan' }}</div>
            <div class="text-xs text-slate-600 flex items-center gap-1.5">
              <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#4D9830]"></i>
              <span>{{ $kelompok?->alamat ?? ($pengurus->alamat ?? 'Wilayah Kerja Pertanian') }}</span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="p-3 rounded-xl bg-white border border-[#E4F0D6]">
              <span class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880] block">ID Kelompok</span>
              <span class="font-mono font-bold text-[#1A2D10] mt-0.5 block">{{ $kelompok?->id_kelompok ?? '-' }}</span>
            </div>
            <div class="p-3 rounded-xl bg-white border border-[#E4F0D6]">
              <span class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880] block">Jabatan Anda</span>
              <span class="font-bold text-[#4D9830] mt-0.5 block">{{ $pengurus->jabatan ?? 'Pengurus Poktan' }}</span>
            </div>
          </div>

          <div class="pt-2 border-t border-[#E4F0D6] flex items-center justify-between text-xs">
            <span class="text-slate-500">Anggota Poktan Aktif:</span>
            <span class="font-bold text-[#1A2D10]">{{ $totalAnggota }} Orang</span>
          </div>
        </div>
      </div>

      <!-- Kartu 2: Breakdown Komoditas Unggulan -->
      <div class="bg-white rounded-[22px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-[#E4F0D6]/70 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] flex items-center justify-center text-[#4D9830] shrink-0">
              <i data-lucide="wheat" class="w-4 h-4"></i>
            </div>
            <div>
              <h2 class="text-base font-extrabold text-[#1A2D10]">Komoditas Budidaya</h2>
              <p class="text-xs text-slate-500 font-medium">Sebaran luasan tanaman anggota</p>
            </div>
          </div>
          <span class="text-xs font-bold text-[#4A6030]">{{ number_format($totalLuasLahan, 2) }} Ha Total</span>
        </div>

        <div class="p-5 sm:p-6 space-y-4">
          @forelse ($sebaranKomoditas as $itemKmdt)
            @php
              $luasKmdt = (float) ($itemKmdt->lahan_sum_luas ?? 0);
              $persenKmdt = $totalLuasLahan > 0 ? round(($luasKmdt / $totalLuasLahan) * 100) : 0;
            @endphp
            <div class="space-y-1.5">
              <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-[#1A2D10] flex items-center gap-1.5">
                  <span class="w-2.5 h-2.5 rounded-full bg-[#4D9830]"></span>
                  <span>{{ $itemKmdt->nama_komoditas }}</span>
                </span>
                <span class="text-slate-600 font-semibold">
                  {{ number_format($luasKmdt, 2) }} Ha <span class="text-slate-400 font-normal">({{ $persenKmdt }}%)</span>
                </span>
              </div>
              <div class="h-2.5 w-full bg-[#EBF6E0] rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-[#4D9830] to-[#72BE4A] rounded-full transition-all duration-500" style="width: {{ max($persenKmdt, 4) }}%"></div>
              </div>
            </div>
          @empty
            <div class="py-6 text-center text-xs text-slate-400">
              Belum ada data komoditas yang terdaftar pada petak lahan anggota.
            </div>
          @endforelse
        </div>
      </div>

    </div>

  </div>

</div>
@endsection
