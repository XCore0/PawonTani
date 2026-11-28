@extends('Admin.Layout._layout')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

  <!-- ==================== 1. HERO WELCOME BANNER ==================== -->
  <div class="p-6 sm:p-8 rounded-[22px] bg-gradient-to-r from-[#2B571B] via-[#4D9830] to-[#6EB844] text-white shadow-sm relative overflow-hidden">
    <div class="absolute -right-8 -bottom-8 w-60 h-60 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
    <div class="absolute right-1/4 -top-12 w-40 h-40 rounded-full bg-emerald-300/10 blur-xl pointer-events-none"></div>
    
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
      <div class="max-w-2xl space-y-2.5">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-white text-xs font-semibold backdrop-blur-xs border border-white/20">
          <i data-lucide="sparkles" class="w-3.5 h-3.5 text-yellow-300"></i>
          <span>{{ $sapaanWaktu }}, {{ auth()->user()->nama ?? 'Admin PPL' }}</span>
          <span class="text-white/40">•</span>
          <span class="text-emerald-100 font-normal">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
          Pusat Kendali Operasional Pertanian
        </h1>
        
        <p class="text-xs sm:text-sm text-emerald-50 leading-relaxed font-medium">
          Selamat datang di platform PawonTani. Pantau statistik operasional kelompok tani, kelengkapan data anggota, dan pengelolaan lahan di seluruh ekosistem.
        </p>
      </div>

      <!-- Tombol Pintas Aksi Cepat -->
      <div class="flex flex-wrap items-center gap-2.5 self-start lg:self-center shrink-0">
        <a href="{{ route('admin.kelompok') }}"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-[#1A2D10] text-xs sm:text-sm font-bold shadow-xs hover:bg-[#F5F8F1] hover:shadow-md transition-all">
          <i data-lucide="users" class="w-4 h-4 text-[#4D9830]"></i>
          <span>Kelola Kelompok</span>
        </a>
        <a href="{{ route('admin.dokumen.ringkasan') }}"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1A2D10]/40 hover:bg-[#1A2D10]/60 border border-white/25 text-white text-xs sm:text-sm font-bold backdrop-blur-xs transition-all">
          <i data-lucide="file-check-2" class="w-4 h-4 text-emerald-300"></i>
          <span>Ringkasan Dokumen</span>
        </a>
      </div>
    </div>
  </div>

  <!-- ==================== 2. GRID KPI CARDS (5 METRICS) ==================== -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
    
    <!-- Kelompok Tani -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Kelompok Tani</span>
        <div class="w-10 h-10 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center">
          <i data-lucide="users" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalKelompok) }}</div>
        <div class="mt-1 flex items-center gap-1.5 text-xs text-[#4A6030]">
          <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
          <span class="font-semibold">{{ $kelompokAktif }} Aktif</span>
          <span class="text-slate-400">({{ $persenKelompokAktif }}%)</span>
        </div>
      </div>
    </div>

    <!-- Total Petani -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Petani / Anggota</span>
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <i data-lucide="sprout" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalPetani) }}</div>
        <div class="mt-1 text-xs text-slate-500 font-medium">
          + {{ $totalPengurus }} Pengurus Poktan
        </div>
      </div>
    </div>

    <!-- Akun Pengguna Aktif & Nonaktif -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Status Akun Pengguna</span>
        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
          <i data-lucide="user-check" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalPenggunaAktif) }} <span class="text-xs font-semibold text-emerald-600">Aktif</span></div>
        <div class="mt-1 text-xs text-slate-500 font-medium">
          {{ number_format($totalPenggunaNonaktif) }} Akun Nonaktif
        </div>
      </div>
    </div>

    <!-- Total Luas Lahan -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Luas Lahan Terdata</span>
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
          <i data-lucide="map-pin" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalLuasLahan, 2) }} <span class="text-sm font-semibold text-slate-500">Ha</span></div>
        <div class="mt-1 text-xs text-slate-500 font-medium">
          Dari {{ number_format($totalLahan) }} petak lahan
        </div>
      </div>
    </div>

    <!-- Kesiapan e-RDKK -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between sm:col-span-2 lg:col-span-1">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Kesiapan e-RDKK</span>
        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
          <i data-lucide="file-check-2" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ $dokumenLengkapCount }} / {{ $totalAnggotaValid }}</div>
        <div class="mt-1 text-xs text-[#4A6030] font-medium">
          Petani berkas lengkap KTP & KK
        </div>
      </div>
    </div>

  </div>

  <!-- ==================== 3. DUA KOLOM UTAMA ==================== -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- ========== KOLOM KIRI: TINDAKAN & OPERASIONAL (7 Kolom) ========== -->
    <div class="lg:col-span-7 space-y-6">

      <!-- Kartu 1: Aktivitas Sistem Terkini -->
      <div class="bg-white rounded-[22px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-[#E4F0D6]/70 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#4D9830] flex items-center justify-center shrink-0">
              <i data-lucide="activity" class="w-5 h-5"></i>
            </div>
            <div>
              <h2 class="text-base font-extrabold text-[#1A2D10]">Aktivitas Penggunaan Platform</h2>
              <p class="text-xs text-slate-500 font-medium">Aktivitas operasional sistem dan kelompok tani terkini</p>
            </div>
          </div>
          <a href="{{ route('admin.log-aktivitas') }}"
            class="text-xs font-bold text-[#4D9830] hover:text-[#3D8024] hover:underline flex items-center gap-1">
            <span>Lihat Log Lengkap</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>

        <div class="p-5 sm:p-6">
          @forelse ($recentLogs as $log)
            <div class="flex items-start justify-between gap-4 p-3.5 rounded-xl border border-[#E4F0D6] bg-[#F5F8F1]/40 mb-3 last:mb-0 hover:bg-[#F5F8F1] transition-colors">
              <div class="space-y-1">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-bold text-[#1A2D10]">{{ $log->deskripsi ?? 'Aktivitas Tercatat' }}</span>
                </div>
                <div class="text-xs text-slate-600 flex items-center gap-2 flex-wrap">
                  <span class="flex items-center gap-1 font-semibold text-[#4D9830]">
                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                    <span>{{ $log->actor?->nama ?? 'Sistem' }}</span>
                  </span>
                  <span class="text-slate-300">•</span>
                  <span class="text-slate-400">{{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}</span>
                </div>
              </div>
            </div>
          @empty
            <div class="py-8 text-center">
              <p class="text-xs text-slate-400">Belum ada aktivitas baru tercatat.</p>
            </div>
          @endforelse
        </div>

      <!-- Kartu 2: Sebaran Komoditas Wilayah Binaan -->
      <div class="bg-white rounded-[22px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-[#E4F0D6]/70 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
              <i data-lucide="wheat" class="w-5 h-5"></i>
            </div>
            <div>
              <h2 class="text-base font-extrabold text-[#1A2D10]">Sebaran Komoditas Garapan</h2>
              <p class="text-xs text-slate-500 font-medium">Rekapitulasi komoditas utama dan luasan lahan binaan</p>
            </div>
          </div>
          <span class="text-xs font-bold text-slate-500">{{ $totalLuasLahan > 0 ? number_format($totalLuasLahan, 2) . ' Ha' : '0 Ha' }} Total</span>
        </div>

        <div class="p-5 sm:p-6 space-y-4">
          @forelse ($sebaranKomoditas as $kmdt)
            @php
              $luas = (float) ($kmdt->lahan_sum_luas ?? 0);
              $persen = $totalLuasLahan > 0 ? round(($luas / $totalLuasLahan) * 100) : 0;
            @endphp
            <div class="space-y-1.5">
              <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-[#1A2D10] flex items-center gap-1.5">
                  <span class="w-2.5 h-2.5 rounded-full bg-[#4D9830]"></span>
                  <span>{{ $kmdt->nama_komoditas }}</span>
                </span>
                <span class="text-slate-600 font-semibold">
                  {{ number_format($luas, 2) }} Ha <span class="text-slate-400 font-normal">({{ $persen }}% • {{ $kmdt->lahan_count }} petak)</span>
                </span>
              </div>
              <div class="h-2.5 w-full bg-[#EBF6E0] rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-[#4D9830] to-[#72BE4A] rounded-full transition-all duration-500" style="width: {{ max($persen, 4) }}%"></div>
              </div>
            </div>
          @empty
            <div class="py-6 text-center text-xs text-slate-500 font-medium">
              Belum ada data komoditas yang terhubung dengan lahan terdata.
            </div>
          @endforelse
        </div>
      </div>

      <!-- Kartu 3: Kelompok Tani Binaan Terbesar -->
      <div class="bg-white rounded-[22px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-[#E4F0D6]/70 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
              <i data-lucide="building-2" class="w-5 h-5"></i>
            </div>
            <div>
              <h2 class="text-base font-extrabold text-[#1A2D10]">Kelompok Tani Binaan</h2>
              <p class="text-xs text-slate-500 font-medium">Distribusi anggota dan akumulasi lahan per kelompok</p>
            </div>
          </div>
          <a href="{{ route('admin.kelompok') }}"
            class="text-xs font-bold text-[#4D9830] hover:text-[#3D8024] hover:underline flex items-center gap-1">
            <span>Kelola Poktan</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>

        <div class="divide-y divide-[#E4F0D6]/60">
          @forelse ($topKelompok as $poktan)
            <div class="p-4 sm:px-6 flex items-center justify-between hover:bg-[#F5F8F1]/40 transition-colors">
              <div class="space-y-0.5">
                <h4 class="text-sm font-bold text-[#1A2D10]">{{ $poktan->nama_kelompok }}</h4>
                <div class="text-xs text-slate-500 flex items-center gap-2">
                  <span>{{ $poktan->desa ?? 'Kecamatan Binaan' }}</span>
                  <span class="text-slate-300">•</span>
                  <span class="font-semibold text-[#4A6030]">{{ $poktan->anggota_count }} Anggota</span>
                </div>
              </div>
              <div class="text-right">
                <span class="text-xs font-bold text-[#1A2D10] block">{{ number_format((float) ($poktan->lahan_sum_luas ?? 0), 2) }} Ha</span>
                <span class="text-[11px] text-slate-400 font-medium">{{ $poktan->lahan_count }} Petak</span>
              </div>
            </div>
          @empty
            <div class="p-6 text-center text-xs text-slate-500">
              Belum ada data kelompok tani.
            </div>
          @endforelse
        </div>
      </div>

    </div>

    <!-- ========== KOLOM KANAN: MONITORING, e-RDKK, & LOG AUDIT (5 Kolom) ========== -->
    <div class="lg:col-span-5 space-y-6">

      <!-- Kartu 1: Status Kesiapan e-RDKK & Berkas -->
      <div class="bg-white rounded-[22px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-[#E4F0D6]/70 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
              <i data-lucide="file-text" class="w-5 h-5"></i>
            </div>
            <div>
              <h2 class="text-base font-extrabold text-[#1A2D10]">Kesiapan Berkas e-RDKK</h2>
              <p class="text-xs text-slate-500 font-medium">Validasi syarat subsidi pupuk</p>
            </div>
          </div>
          <a href="{{ route('admin.dokumen.erdkk') }}" class="text-xs font-bold text-[#4D9830] hover:underline">
            Buka Berkas
          </a>
        </div>

        <div class="p-5 sm:p-6 space-y-4">
          <!-- Widget Status -->
          <div class="p-4 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6] space-y-2.5">
            <div class="flex items-center justify-between text-xs">
              <span class="font-semibold text-slate-700">KTP & KK Terverifikasi</span>
              <span class="font-bold text-[#1A2D10]">{{ $dokumenLengkapCount }} / {{ $totalAnggotaValid }} Petani</span>
            </div>
            @php
              $persenDokumen = $totalAnggotaValid > 0 ? round(($dokumenLengkapCount / $totalAnggotaValid) * 100) : 0;
            @endphp
            <div class="h-2 w-full bg-white rounded-full overflow-hidden border border-[#E4F0D6]">
              <div class="h-full bg-teal-500 rounded-full" style="width: {{ max($persenDokumen, 2) }}%"></div>
            </div>
            <p class="text-[11px] text-slate-500">
              {{ $dokumenLengkapCount }} petani siap diekspor ke format e-RDKK Kementan.
            </p>
          </div>

          <!-- Alert SPPT Kurang -->
          <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/70 flex items-start gap-3">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
            <div class="text-xs">
              <span class="font-bold text-amber-900 block">Catatan SPPT Lahan</span>
              <span class="text-amber-800">
                Terdapat <strong class="font-bold">{{ $spptKurangCount }} petani</strong> yang belum melampirkan berkas SPPT pada petak lahannya.
              </span>
            </div>
          </div>

          <!-- Lahan butuh verifikasi -->
          <div class="pt-2">
            <span class="text-xs font-bold text-[#9AB880] uppercase tracking-wider block mb-2.5">Lahan Menunggu Verifikasi</span>
            <div class="grid grid-cols-3 gap-2 text-center">
              <div class="p-2.5 rounded-xl bg-amber-50/80 border border-amber-200">
                <span class="text-lg font-extrabold text-amber-800 block">{{ $lahanMenungguVerifikasi }}</span>
                <span class="text-[10px] font-semibold text-amber-700 uppercase">Menunggu</span>
              </div>
              <div class="p-2.5 rounded-xl bg-emerald-50/80 border border-emerald-200">
                <span class="text-lg font-extrabold text-emerald-800 block">{{ $lahanTerverifikasi }}</span>
                <span class="text-[10px] font-semibold text-emerald-700 uppercase">Valid</span>
              </div>
              <div class="p-2.5 rounded-xl bg-rose-50/80 border border-rose-200">
                <span class="text-lg font-extrabold text-rose-800 block">{{ $lahanPerluPerbaikan }}</span>
                <span class="text-[10px] font-semibold text-rose-700 uppercase">Revisi</span>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Kartu 2: Log Aktivitas Sistem Terkini -->
      <div class="bg-white rounded-[22px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-[#E4F0D6]/70 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
              <i data-lucide="activity" class="w-5 h-5"></i>
            </div>
            <div>
              <h2 class="text-base font-extrabold text-[#1A2D10]">Aktivitas Terkini</h2>
              <p class="text-xs text-slate-500 font-medium">Jejak audit sistem terbaru</p>
            </div>
          </div>
          <a href="{{ route('admin.log-aktivitas') }}"
            class="text-xs font-bold text-[#4D9830] hover:text-[#3D8024] hover:underline flex items-center gap-1">
            <span>Audit Trail</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>

        <div class="p-5 sm:p-6">
          <div class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#E4F0D6]">
            @forelse ($recentLogs as $log)
              <div class="relative group">
                <!-- Dot icon -->
                <div class="absolute -left-6 top-1 w-3 h-3 rounded-full border-2 border-white bg-[#4D9830] shadow-xs"></div>
                
                <div class="text-xs space-y-1">
                  <div class="flex items-center justify-between gap-2">
                    <span class="font-bold text-[#1A2D10] truncate max-w-[160px]">
                      {{ $log->actor_name ?? ($log->actor->nama ?? 'Sistem') }}
                    </span>
                    <span class="text-[11px] text-slate-400 shrink-0">
                      {{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}
                    </span>
                  </div>
                  <p class="text-slate-600 line-clamp-2 leading-relaxed">
                    {{ $log->description }}
                  </p>
                  @if ($log->event)
                    <span class="inline-block text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                      {{ $log->event }}
                    </span>
                  @endif
                </div>
              </div>
            @empty
              <div class="text-center py-6 text-xs text-slate-400">
                Belum ada aktivitas tercatat hari ini.
              </div>
            @endforelse
          </div>
        </div>
      </div>

    </div>

  </div>

</div>
@endsection
