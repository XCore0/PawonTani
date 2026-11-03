@extends('Pengurus.Layout._layout')

@section('title', 'Detail Lahan - ' . $lahan->nama_lahan)

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
        <span>Gagal memproses verifikasi lahan:</span>
      </div>
      <ul class="list-disc list-inside pl-6 text-red-700 space-y-0.5">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- ==================== 1. BREADCRUMB & HEADER ==================== --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <a href="{{ route('pengurus.lahan') }}"
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#4D9830] hover:text-[#3D8024] hover:underline mb-2 transition-colors">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Kembali ke Kelola Lahan</span>
      </a>
      <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
          {{ $lahan->nama_lahan }}
        </h1>
        <span class="font-mono text-xs font-bold text-[#4A6030] bg-[#F5F8F1] px-2.5 py-1 rounded-lg border border-[#C5DFB0]">
          {{ $lahan->id_lahan }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Lahan milik anggota <strong class="text-[#1A2D10]">{{ $lahan->pengguna?->nama ?? '-' }}</strong> di {{ $lahan->kelompokTani?->nama_kelompok ?? 'Kelompok Tani' }}
      </p>
    </div>

    <!-- Badges Row -->
    <div class="flex flex-wrap items-center gap-2 shrink-0">
      <!-- Status Verifikasi Badge -->
      @if($lahan->status_verifikasi === 'menunggu_verifikasi')
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold shadow-2xs">
          <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
          Menunggu Verifikasi
        </span>
      @elseif($lahan->status_verifikasi === 'terverifikasi')
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold shadow-2xs">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          Terverifikasi
        </span>
      @elseif($lahan->status_verifikasi === 'perlu_perbaikan')
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold shadow-2xs">
          <span class="w-2 h-2 rounded-full bg-rose-500"></span>
          Perlu Perbaikan
        </span>
      @else
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-bold shadow-2xs">
          {{ $lahan->status_verifikasi }}
        </span>
      @endif

      <!-- Nomor Revisi Badge -->
      <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-[#EBF6E0] border border-[#C5DFB0] text-[#4A6030] text-xs font-bold shadow-2xs font-mono">
        <i data-lucide="git-commit" class="w-3.5 h-3.5 text-[#4D9830]"></i>
        Rev. {{ $lahan->nomor_revisi ?? 1 }}
      </span>

      <!-- Status Operasional Badge (Read-only) -->
      <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-white border border-[#E4F0D6] text-[#4A6030] text-xs font-medium shadow-2xs">
        <i data-lucide="activity" class="w-3.5 h-3.5 text-[#9AB880]"></i>
        Operasional: <strong class="capitalize ml-0.5 text-[#1A2D10]">{{ $lahan->status_operasional }}</strong>
      </span>
    </div>
  </div>

  {{-- ==================== 2. MAIN GRID (2 COLUMNS) ==================== --}}
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    {{-- ==================== LEFT COLUMN (CONTENT & TIMELINE) ==================== --}}
    <div class="lg:col-span-7 space-y-6">

      <!-- Card Foto Lahan & Gambaran Umum -->
      <div class="rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]/40">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center">
              <i data-lucide="image" class="w-4 h-4"></i>
            </div>
            <h2 class="text-sm sm:text-base font-bold text-[#1A2D10]">Foto & Gambaran Lahan</h2>
          </div>
          <span class="text-xs text-[#9AB880] font-medium">Terdaftar sejak {{ $lahan->created_at?->translatedFormat('d M Y') ?? '-' }}</span>
        </div>

        <div class="p-5 sm:p-6 space-y-4">
          <!-- Foto Lahan Display -->
          @php
            $hasValidPhotoUrl = !empty($lahan->foto_lahan) && \Illuminate\Support\Str::startsWith($lahan->foto_lahan, ['http://', 'https://']);
          @endphp
          @if(!empty($lahan->foto_lahan))
            <div class="relative w-full rounded-2xl overflow-hidden border border-[#E4F0D6] bg-slate-900/5 aspect-video group">
              <img src="{{ $hasValidPhotoUrl ? $lahan->foto_lahan : '' }}" alt="Foto Lahan {{ $lahan->nama_lahan }}"
                class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-300"
                loading="lazy">
              @if($hasValidPhotoUrl)
                <a href="{{ $lahan->foto_lahan }}" target="_blank" rel="noopener noreferrer"
                  class="absolute bottom-3 right-3 px-3 py-1.5 rounded-xl bg-slate-900/70 hover:bg-slate-900 text-white text-xs font-semibold flex items-center gap-1.5 backdrop-blur-xs transition-colors shadow-md">
                  <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                  <span>Lihat Ukuran Penuh</span>
                </a>
              @endif
            </div>
          @else
            <div class="w-full rounded-2xl border-2 border-dashed border-[#C5DFB0] bg-[#F5F8F1]/50 p-8 text-center flex flex-col items-center justify-center space-y-2">
              <div class="w-12 h-12 rounded-2xl bg-white border border-[#E4F0D6] flex items-center justify-center text-[#9AB880]">
                <i data-lucide="camera-off" class="w-6 h-6"></i>
              </div>
              <p class="text-xs font-semibold text-[#6B7F5B]">Tidak ada foto lahan yang diunggah</p>
              <p class="text-[11px] text-[#9AB880]">Anggota belum menyertakan foto dokumentasi fisik lahan.</p>
            </div>
          @endif

          <!-- Keterangan / Catatan Fisik Tanah -->
          <div class="pt-2">
            <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block mb-1.5">
              Keterangan / Kondisi Lahan
            </span>
            <div class="p-4 rounded-xl bg-[#F5F8F1]/60 border border-[#E4F0D6] text-xs sm:text-sm text-[#4A6030] leading-relaxed">
              {{ $lahan->keterangan ?: 'Tidak ada keterangan atau catatan kondisi lahan yang dicantumkan.' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Card Informasi Teknis Lahan -->
      <div class="rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]/40">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center">
              <i data-lucide="layers" class="w-4 h-4"></i>
            </div>
            <h2 class="text-sm sm:text-base font-bold text-[#1A2D10]">Informasi Teknis & Lokasi</h2>
          </div>
        </div>

        <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">

          <!-- Luas Lahan -->
          <div class="p-4 rounded-xl bg-[#F5F8F1]/40 border border-[#E4F0D6]">
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Luas Lahan</span>
            <span class="text-lg font-extrabold text-[#1A2D10] block mt-0.5">
              {{ number_format((float)$lahan->luas, 2, ',', '.') }} <span class="text-xs font-normal text-[#9AB880]">Hektare (ha)</span>
            </span>
          </div>

          <!-- Komoditas Utama -->
          <div class="p-4 rounded-xl bg-[#F5F8F1]/40 border border-[#E4F0D6]">
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Komoditas Utama</span>
            <div class="flex items-center gap-2 mt-1">
              @if($lahan->komoditas)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#EBF6E0] text-[#4D9830] font-bold text-xs">
                  <i data-lucide="sprout" class="w-3.5 h-3.5"></i>
                  {{ $lahan->komoditas->nama_komoditas }}
                </span>
                @if(!empty($lahan->komoditas->kategori))
                  <span class="text-[11px] text-[#9AB880]">({{ $lahan->komoditas->kategori }})</span>
                @endif
              @else
                <span class="text-xs text-[#9AB880] italic">Belum ditentukan</span>
              @endif
            </div>
          </div>

          <!-- Musim Tanam -->
          <div class="p-4 rounded-xl bg-[#F5F8F1]/40 border border-[#E4F0D6]">
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Musim Tanam</span>
            <span class="text-sm font-bold text-[#1A2D10] block mt-0.5">
              {{ $lahan->musim_tanam ?: '-' }}
            </span>
          </div>

          <!-- Status Operasional -->
          <div class="p-4 rounded-xl bg-[#F5F8F1]/40 border border-[#E4F0D6]">
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Status Operasional</span>
            <span class="text-sm font-bold text-[#1A2D10] block mt-0.5 capitalize">
              {{ $lahan->status_operasional ?: 'Persiapan' }}
            </span>
          </div>

          <!-- Alamat Lengkap Lahan -->
          <div class="sm:col-span-2 p-4 rounded-xl bg-[#F5F8F1]/40 border border-[#E4F0D6]">
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Alamat / Letak Lahan</span>
            <p class="text-xs sm:text-sm text-[#4A6030] mt-1 leading-relaxed">
              {{ $lahan->alamat_lahan ?: '-' }}
            </p>
          </div>

          <!-- Titik Koordinat GPS & Tautan Google Maps -->
          <div class="sm:col-span-2 p-4 rounded-xl bg-[#F5F8F1]/40 border border-[#E4F0D6]">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div>
                <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Koordinat Geografis (GPS)</span>
                @if(!empty($lahan->latitude) && !empty($lahan->longitude))
                  <span class="font-mono text-xs font-bold text-[#1A2D10] block mt-0.5">
                    Lat: {{ $lahan->latitude }}, Long: {{ $lahan->longitude }}
                  </span>
                @else
                  <span class="text-xs text-[#9AB880] italic block mt-0.5">
                    Koordinat GPS belum ditandai pada lahan ini.
                  </span>
                @endif
              </div>

              @if(!empty($lahan->latitude) && !empty($lahan->longitude))
                <a href="https://www.google.com/maps/search/?api=1&query={{ $lahan->latitude }},{{ $lahan->longitude }}"
                  target="_blank" rel="noopener noreferrer"
                  class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-[#C5DFB0] text-xs font-bold text-[#4D9830] hover:bg-[#EBF6E0] hover:border-[#4D9830] transition-colors shadow-2xs shrink-0 cursor-pointer">
                  <i data-lucide="map-pin" class="w-4 h-4 text-[#4D9830]"></i>
                  <span>Buka di Google Maps</span>
                </a>
              @endif
            </div>
          </div>

        </div>
      </div>

      <!-- Card Riwayat & Jejak Audit Verifikasi (Audit Trail) -->
      <div class="rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]/40">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center">
              <i data-lucide="history" class="w-4 h-4"></i>
            </div>
            <div>
              <h2 class="text-sm sm:text-base font-bold text-[#1A2D10]">Riwayat Verifikasi Lahan</h2>
              <p class="text-[11px] text-[#9AB880]">Jejak audit seluruh perubahan status dan catatan verifikasi</p>
            </div>
          </div>
          <span class="font-mono text-xs font-bold text-[#4A6030] bg-[#F5F8F1] px-2.5 py-1 rounded-lg border border-[#C5DFB0]">
            {{ $lahan->riwayatVerifikasi->count() }} Catatan
          </span>
        </div>

        <div class="p-5 sm:p-6">
          @if($lahan->riwayatVerifikasi->isNotEmpty())
            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#E4F0D6]">
              @foreach($lahan->riwayatVerifikasi as $riwayat)
                <div class="relative">
                  <!-- Timeline Dot -->
                  @php
                    $dotColor = match($riwayat->aksi) {
                      'verifikasi_disetujui' => 'bg-emerald-500 ring-4 ring-emerald-50',
                      'perlu_perbaikan'      => 'bg-rose-500 ring-4 ring-rose-50',
                      'cabut_verifikasi'     => 'bg-amber-500 ring-4 ring-amber-50',
                      'pengajuan_ulang'      => 'bg-blue-500 ring-4 ring-blue-50',
                      'pengajuan_baru'       => 'bg-[#4D9830] ring-4 ring-[#EBF6E0]',
                      default                => 'bg-[#9AB880] ring-4 ring-slate-100',
                    };
                    $aksiLabel = match($riwayat->aksi) {
                      'verifikasi_disetujui' => 'Verifikasi Disetujui',
                      'perlu_perbaikan'      => 'Permintaan Perbaikan',
                      'cabut_verifikasi'     => 'Status Verifikasi Dicabut',
                      'pengajuan_ulang'      => 'Pengajuan Ulang Perbaikan',
                      'pengajuan_baru'       => 'Pengajuan Lahan Baru',
                      'pembaruan_draft'      => 'Pembaruan Data Draft',
                      'penghapusan_lahan'    => 'Penghapusan Lahan',
                      default                => str_replace('_', ' ', ucfirst($riwayat->aksi)),
                    };
                  @endphp
                  <span class="absolute -left-[19px] top-1.5 w-3 h-3 rounded-full {{ $dotColor }}"></span>

                  <div class="p-4 rounded-xl bg-[#F5F8F1]/50 border border-[#E4F0D6] space-y-2">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                      <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-[#1A2D10]">{{ $aksiLabel }}</span>
                        <span class="font-mono text-[10px] text-[#9AB880] bg-white px-2 py-0.5 rounded border border-[#E4F0D6]">
                          Rev. {{ $riwayat->nomor_revisi }}
                        </span>
                      </div>
                      <span class="text-[11px] text-[#9AB880] font-medium">
                        {{ $riwayat->created_at?->translatedFormat('d F Y, H:i') ?? '-' }}
                      </span>
                    </div>

                    <!-- Actor Info -->
                    <div class="text-[11px] text-[#6B7F5B]">
                      Dilakukan oleh: <strong class="text-[#1A2D10]">{{ $riwayat->pengguna?->nama ?? 'Sistem' }}</strong>
                      @if(!empty($riwayat->pengguna?->jabatan))
                        <span class="text-[#9AB880]">({{ $riwayat->pengguna->jabatan }})</span>
                      @elseif(!empty($riwayat->pengguna?->role))
                        <span class="text-[#9AB880]">({{ $riwayat->pengguna->role }})</span>
                      @endif
                    </div>

                    <!-- Status Change -->
                    <div class="flex items-center gap-1.5 text-[11px] font-mono">
                      <span class="px-2 py-0.5 rounded bg-white text-[#6B7F5B] border border-[#E4F0D6]">
                        {{ $riwayat->status_sebelumnya ?: 'awal' }}
                      </span>
                      <i data-lucide="arrow-right" class="w-3 h-3 text-[#9AB880]"></i>
                      <span class="px-2 py-0.5 rounded bg-[#EBF6E0] text-[#4D9830] font-bold border border-[#C5DFB0]">
                        {{ $riwayat->status_sesudahnya }}
                      </span>
                    </div>

                    <!-- Notes / Catatan -->
                    @if(!empty($riwayat->catatan))
                      <div class="mt-2 p-3 rounded-lg bg-white border border-[#E4F0D6] text-xs text-[#4A6030] italic">
                        "{{ $riwayat->catatan }}"
                      </div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="py-8 text-center text-xs text-[#9AB880]">
              Belum ada riwayat aktivitas atau verifikasi untuk lahan ini.
            </div>
          @endif
        </div>
      </div>

    </div>

    {{-- ==================== RIGHT COLUMN (OWNER & VERIFICATION FORM) ==================== --}}
    <div class="lg:col-span-5 space-y-6">

      <!-- Card Informasi Pemilik Lahan (Anggota) -->
      <div class="rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]/40">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center">
              <i data-lucide="user" class="w-4 h-4"></i>
            </div>
            <h2 class="text-sm sm:text-base font-bold text-[#1A2D10]">Pemilik Lahan</h2>
          </div>
          <span class="text-xs font-semibold text-[#4D9830] bg-[#EBF6E0] px-2.5 py-0.5 rounded-full">
            Anggota
          </span>
        </div>

        <div class="p-5 sm:p-6 space-y-3.5 text-xs sm:text-sm">
          <div>
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Nama Lengkap</span>
            <span class="text-sm font-bold text-[#1A2D10] block mt-0.5">{{ $lahan->pengguna?->nama ?? '-' }}</span>
          </div>

          <div>
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">NIK Anggota</span>
            @if(!empty($lahan->pengguna?->nik))
              @if($isKetua)
                <span class="font-mono text-xs font-bold text-[#4A6030] block mt-0.5">{{ $lahan->pengguna->nik }}</span>
              @else
                @php
                  $nikRaw = (string)$lahan->pengguna->nik;
                  $nikLen = strlen($nikRaw);
                  $maskedNik = $nikLen >= 10
                    ? substr($nikRaw, 0, 6) . str_repeat('*', max(0, $nikLen - 10)) . substr($nikRaw, -4)
                    : str_repeat('*', $nikLen);
                @endphp
                <span class="font-mono text-xs text-[#9AB880] block mt-0.5" title="NIK disamarkan untuk Pengurus biasa">{{ $maskedNik }}</span>
              @endif
            @else
              <span class="font-mono text-xs text-[#9AB880] block mt-0.5">-</span>
            @endif
          </div>

          <div>
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">No. Telepon / Kontak</span>
            @if(!empty($lahan->pengguna?->no_telepon))
              <div class="flex items-center gap-2 mt-0.5">
                <span class="text-xs font-bold text-[#1A2D10]">{{ $lahan->pengguna->no_telepon }}</span>
                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $lahan->pengguna->no_telepon)) }}"
                  target="_blank" rel="noopener noreferrer"
                  class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#4D9830] hover:underline"
                  title="Hubungi via WhatsApp">
                  <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                  <span>WhatsApp</span>
                </a>
              </div>
            @else
              <span class="text-xs text-[#9AB880] italic block mt-0.5">Belum ada nomor telepon</span>
            @endif
          </div>

          <div>
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Alamat Domisili</span>
            <p class="text-xs text-[#4A6030] mt-0.5 leading-relaxed">{{ $lahan->pengguna?->alamat ?? '-' }}</p>
          </div>

          <div class="pt-2 border-t border-[#E4F0D6]">
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Kelompok Tani</span>
            <span class="text-xs font-bold text-[#4D9830] block mt-0.5">{{ $lahan->kelompokTani?->nama_kelompok ?? '-' }}</span>
          </div>
        </div>
      </div>

      <!-- Card Status & Metadata Verifikasi Terkini -->
      <div class="rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]/40">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center">
              <i data-lucide="shield-check" class="w-4 h-4"></i>
            </div>
            <h2 class="text-sm sm:text-base font-bold text-[#1A2D10]">Status Verifikasi Saat Ini</h2>
          </div>
        </div>

        <div class="p-5 sm:p-6 space-y-3.5 text-xs sm:text-sm">
          <div>
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Status Terakhir</span>
            <div class="mt-1">
              @if($lahan->status_verifikasi === 'menunggu_verifikasi')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold">
                  <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                  Menunggu Verifikasi
                </span>
              @elseif($lahan->status_verifikasi === 'terverifikasi')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold">
                  <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                  Terverifikasi
                </span>
              @elseif($lahan->status_verifikasi === 'perlu_perbaikan')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold">
                  <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                  Perlu Perbaikan
                </span>
              @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                  {{ $lahan->status_verifikasi }}
                </span>
              @endif
            </div>
          </div>

          <div>
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Verifikator</span>
            @if($lahan->verifikator)
              <span class="text-xs font-bold text-[#1A2D10] block mt-0.5">
                {{ $lahan->verifikator->nama }}
                @if(!empty($lahan->verifikator->jabatan))
                  <span class="text-[11px] text-[#4D9830] font-normal">({{ $lahan->verifikator->jabatan }})</span>
                @endif
              </span>
            @else
              <span class="text-xs text-[#9AB880] italic block mt-0.5">Belum diverifikasi oleh Ketua</span>
            @endif
          </div>

          <div>
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Waktu Verifikasi Terakhir</span>
            <span class="text-xs text-[#4A6030] block mt-0.5">
              {{ $lahan->tanggal_verifikasi ? $lahan->tanggal_verifikasi->translatedFormat('d F Y, H:i') : '-' }}
            </span>
          </div>

          <div>
            <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Catatan Verifikator</span>
            <div class="mt-1 p-3 rounded-xl bg-[#F5F8F1]/60 border border-[#E4F0D6] text-xs text-[#4A6030] leading-relaxed">
              {{ $lahan->catatan_verifikasi ?: 'Tidak ada catatan verifikasi tertulis.' }}
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== 3. FORM VERIFIKASI (KHUSUS KETUA) ==================== -->
      @if($isKetua)
        <div class="rounded-[22px] bg-white border-2 border-[#C5DFB0] shadow-sm overflow-hidden">
          <div class="p-5 border-b border-[#C5DFB0] bg-[#EBF6E0] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-[#4D9830] text-white flex items-center justify-center shrink-0">
                <i data-lucide="check-square" class="w-4 h-4"></i>
              </div>
              <div>
                <h2 class="text-sm sm:text-base font-extrabold text-[#1A2D10]">Panel Verifikasi Ketua</h2>
                <p class="text-[11px] text-[#4A6030]">Kewenangan resmi verifikasi lahan kelompok</p>
              </div>
            </div>
          </div>

          <form action="{{ route('pengurus.lahan.verifikasi', $lahan->id_lahan) }}" method="POST" class="p-5 sm:p-6 space-y-5">
            @csrf

            <!-- Panduan Status Kontekstual -->
            <div class="p-3.5 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6] text-xs text-[#4A6030] space-y-1">
              <div class="font-bold flex items-center gap-1.5 text-[#1A2D10]">
                <i data-lucide="info" class="w-3.5 h-3.5 text-[#4D9830]"></i>
                <span>Tindakan yang Dapat Dilakukan:</span>
              </div>
              @if($lahan->status_verifikasi === 'menunggu_verifikasi')
                <p class="text-[11px]">Lahan ini sedang <strong>menunggu verifikasi</strong>. Anda dapat memilih <strong>Setujui Lahan</strong> jika data valid, atau <strong>Minta Perbaikan</strong> jika ada kekurangan.</p>
              @elseif($lahan->status_verifikasi === 'terverifikasi')
                <p class="text-[11px]">Lahan ini sudah <strong>terverifikasi</strong>. Anda dapat memilih <strong>Cabut Verifikasi</strong> jika ditemukan ketidaksesuaian baru di lapangan.</p>
              @elseif($lahan->status_verifikasi === 'perlu_perbaikan')
                <p class="text-[11px]">Lahan ini sedang dalam status <strong>perlu perbaikan</strong> oleh anggota. Anda dapat memperbarui catatan atau menunggu anggota mengajukan ulang perbaikan.</p>
              @endif
            </div>

            <!-- Pilihan Aksi (Radio Buttons) -->
            <div>
              <label class="block text-xs font-bold text-[#1A2D10] mb-2 uppercase tracking-wider">
                Pilih Aksi Verifikasi <span class="text-red-500">*</span>
              </label>

              <div class="space-y-2.5">

                <!-- Opsi 1: Setujui -->
                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-[#C5DFB0] bg-white hover:bg-[#F5F8F1] cursor-pointer transition-colors has-[:checked]:border-[#4D9830] has-[:checked]:bg-[#EBF6E0]/60 has-[:checked]:ring-1 has-[:checked]:ring-[#4D9830]">
                  <input type="radio" name="aksi" value="setujui"
                    {{ old('aksi', $lahan->status_verifikasi === 'menunggu_verifikasi' ? 'setujui' : '') === 'setujui' ? 'checked' : '' }}
                    class="mt-1 text-[#4D9830] focus:ring-[#4D9830] cursor-pointer">
                  <div class="text-xs">
                    <span class="font-bold text-[#1A2D10] block">Setujui Lahan (Terverifikasi)</span>
                    <span class="text-[#9AB880] text-[11px]">Data lahan dan batas fisik dinyatakan sah dan memenuhi syarat.</span>
                  </div>
                </label>

                <!-- Opsi 2: Perlu Perbaikan -->
                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-[#C5DFB0] bg-white hover:bg-[#F5F8F1] cursor-pointer transition-colors has-[:checked]:border-rose-400 has-[:checked]:bg-rose-50/60 has-[:checked]:ring-1 has-[:checked]:ring-rose-400">
                  <input type="radio" name="aksi" value="perlu_perbaikan"
                    {{ old('aksi') === 'perlu_perbaikan' ? 'checked' : '' }}
                    class="mt-1 text-rose-600 focus:ring-rose-500 cursor-pointer">
                  <div class="text-xs">
                    <span class="font-bold text-[#1A2D10] block">Minta Perbaikan ke Anggota</span>
                    <span class="text-[#9AB880] text-[11px]">Data atau foto lahan kurang lengkap/tidak sesuai, anggota wajib merevisi.</span>
                  </div>
                </label>

                <!-- Opsi 3: Cabut Verifikasi -->
                @if($lahan->status_verifikasi === 'terverifikasi')
                  <label class="flex items-start gap-3 p-3.5 rounded-xl border border-[#C5DFB0] bg-white hover:bg-[#F5F8F1] cursor-pointer transition-colors has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50/60 has-[:checked]:ring-1 has-[:checked]:ring-amber-400">
                    <input type="radio" name="aksi" value="cabut_verifikasi"
                      {{ old('aksi') === 'cabut_verifikasi' ? 'checked' : '' }}
                      class="mt-1 text-amber-600 focus:ring-amber-500 cursor-pointer">
                    <div class="text-xs">
                      <span class="font-bold text-[#1A2D10] block">Cabut Verifikasi Lahan</span>
                      <span class="text-[#9AB880] text-[11px]">Membatalkan status persetujuan dan mengembalikan ke status perbaikan.</span>
                    </div>
                  </label>
                @endif

              </div>

              @error('aksi')
                <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
              @enderror
            </div>

            <!-- Field Catatan Verifikasi -->
            <div>
              <label for="catatan" class="block text-xs font-bold text-[#1A2D10] mb-1.5 uppercase tracking-wider">
                Catatan Verifikasi / Alasan Perbaikan
              </label>
              <textarea name="catatan" id="catatan" rows="4"
                placeholder="Tuliskan catatan verifikasi, rekomendasi, atau detail bagian data lahan yang perlu diperbaiki oleh anggota (minimal 5 karakter jika meminta perbaikan/mencabut)..."
                class="w-full p-3.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all leading-relaxed">{{ old('catatan') }}</textarea>
              <p class="text-[11px] text-[#9AB880] mt-1">
                Catatan <strong class="text-rose-600 font-semibold">wajib diisi</strong> jika memilih aksi <em>Minta Perbaikan</em> atau <em>Cabut Verifikasi</em>.
              </p>
              @error('catatan')
                <p class="text-red-600 text-xs mt-1 font-medium">{{ $message }}</p>
              @enderror
            </div>

            <!-- Tombol Submit Verifikasi -->
            <button type="submit"
              onclick="return confirm('Apakah Anda yakin ingin menyimpan keputusan verifikasi untuk lahan ini?')"
              class="w-full h-11 inline-flex items-center justify-center gap-2 px-4 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] active:scale-[0.99] text-white text-xs sm:text-sm font-bold shadow-sm shadow-[#4D9830]/20 hover:shadow-md hover:shadow-[#4D9830]/30 transition-all cursor-pointer">
              <i data-lucide="send" class="w-4 h-4"></i>
              <span>Simpan & Terapkan Keputusan</span>
            </button>

          </form>
        </div>
      @else
        <!-- Banner Informasi Akses Read-Only untuk Pengurus Non-Ketua -->
        <div class="rounded-[22px] bg-white border border-[#E4F0D6] p-5 sm:p-6 shadow-2xs space-y-3">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
              <i data-lucide="shield-alert" class="w-5 h-5"></i>
            </div>
            <div>
              <h3 class="text-sm font-bold text-[#1A2D10]">Akses Verifikasi Khusus Ketua</h3>
              <p class="text-xs text-[#6B7F5B] mt-1 leading-relaxed">
                Anda masuk dengan peran <strong>{{ $pengurus->jabatan ?? $pengurus->role ?? 'Pengurus' }}</strong>.
                Berdasarkan aturan tata kelola PawonTani, kewenangan untuk <em>menyetujui</em>, <em>meminta perbaikan</em>, atau <em>mencabut verifikasi</em> lahan anggota berada secara eksklusif pada <strong>Ketua Kelompok Tani aktif</strong>.
              </p>
              <div class="mt-3 p-3 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6] text-[11px] text-[#4A6030]">
                <i data-lucide="info" class="w-3.5 h-3.5 inline-block mr-1 text-[#4D9830] -mt-0.5"></i>
                Anda dapat melihat seluruh rincian dan riwayat verifikasi lahan ini dalam mode baca (<em>read-only</em>).
              </div>
            </div>
          </div>
        </div>
      @endif

    </div>

  </div>

</div>
@endsection
