@extends('Pengurus.Layout._layout')

@section('title', 'Detail & Verifikasi Hasil Panen')

@section('content')
<div class="space-y-6">

  {{-- ==================== FLASH NOTIFICATIONS ==================== --}}
  @if(session('success'))
    <div id="flash-banner" class="flex items-center justify-between p-4 rounded-2xl bg-[#EBF6E0] border border-[#C5DFB0] text-[#1A2D10] text-xs sm:text-sm shadow-xs">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-[#4D9830] text-white flex items-center justify-center shrink-0">
          <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
        </div>
        <div>
          <span class="font-bold">Berhasil!</span>
          <span class="text-[#4A6030] ml-1">{{ session('success') }}</span>
        </div>
      </div>
      <button type="button" onclick="document.getElementById('flash-banner').remove()" class="p-1 text-[#4A6030] hover:text-[#1A2D10] rounded-lg cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>
  @endif

  @if(isset($errors) && $errors->any())
    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm space-y-1">
      <div class="flex items-center gap-2 font-bold text-red-900">
        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
        <span>Terjadi kesalahan:</span>
      </div>
      <ul class="list-disc list-inside pl-6 text-red-700">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- ==================== TOP BREADCRUMB & HEADER ==================== --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3">
      <a href="{{ route('pengurus.panen') }}" class="w-10 h-10 rounded-xl border border-[#C5DFB0] bg-white text-[#4A6030] hover:bg-[#F5F8F1] flex items-center justify-center transition-colors shadow-2xs">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
      </a>
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-xl sm:text-2xl font-extrabold text-[#1A2D10] tracking-tight">
            {{ $panen->kode_panen }}
          </h1>
          @php
            $badge = match($panen->status) {
              'disetujui' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Disetujui'],
              'ditolak' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Ditolak'],
              default => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Menunggu Verifikasi'],
            };
          @endphp
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badge['bg'] }}">
            {{ $badge['label'] }}
          </span>
        </div>
        <p class="text-xs sm:text-sm text-[#9AB880] mt-0.5">
          Dicatat pada {{ $panen->created_at ? $panen->created_at->translatedFormat('d F Y, H:i') : '-' }} WIB
        </p>
      </div>
    </div>
  </div>

  {{-- ==================== MAIN CONTENT GRID ==================== --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- KIRI: DETAIL HASIL PANEN --}}
    <div class="lg:col-span-2 space-y-6">

      {{-- Card 1: Informasi Utama Hasil Panen --}}
      <div class="p-6 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs space-y-5">
        <h2 class="text-base font-bold text-[#1A2D10] flex items-center gap-2">
          <i data-lucide="wheat" class="w-5 h-5 text-[#4D9830]"></i>
          <span>Rincian Komoditas & Hasil Panen</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
          <div class="p-4 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6]">
            <span class="text-[11px] text-[#9AB880] font-semibold uppercase block">Komoditas & Varietas</span>
            <span class="text-base font-bold text-[#1A2D10] block mt-0.5">{{ $panen->varietas ?? $panen->komoditas_nama }}</span>
            <span class="text-xs text-[#4A6030] font-medium">{{ $panen->jenis_panen ?? 'Gabah Kering Panen (GKP)' }}</span>
          </div>

          <div class="p-4 rounded-xl bg-[#EBF6E0] border border-[#C5DFB0]">
            <span class="text-[11px] text-[#4A6030] font-semibold uppercase block">Total Berat Panen</span>
            <span class="text-xl font-extrabold text-[#1A2D10] block mt-0.5">{{ number_format((float)$panen->berat_kg, 0, ',', '.') }} kg</span>
            <span class="text-xs text-[#4D9830] font-bold">≈ {{ round(((float)$panen->berat_kg)/1000.0, 3) }} Ton ({{ round(((float)$panen->berat_kg)/100.0, 2) }} Kuintal)</span>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs sm:text-sm">
          <div class="p-3.5 rounded-xl border border-[#E4F0D6]">
            <span class="text-[11px] text-[#9AB880] font-medium block">Tanggal Panen</span>
            <span class="font-bold text-[#1A2D10] mt-0.5 block">{{ $panen->tanggal_panen ? $panen->tanggal_panen->translatedFormat('l, d F Y') : '-' }}</span>
            <span class="text-xs text-[#4A6030]">{{ $panen->waktu_panen ?? 'Pagi Hari' }}</span>
          </div>

          <div class="p-3.5 rounded-xl border border-[#E4F0D6]">
            <span class="text-[11px] text-[#9AB880] font-medium block">Musim Tanam</span>
            <span class="font-bold text-[#1A2D10] mt-0.5 block">{{ $panen->musim_tanam ?? 'Musim Rendeng' }}</span>
          </div>

          <div class="p-3.5 rounded-xl border border-[#E4F0D6]">
            <span class="text-[11px] text-[#9AB880] font-medium block">Metode Pemanenan</span>
            <span class="font-bold text-[#1A2D10] mt-0.5 block">{{ $panen->metode_panen ?? 'Mesin Combine Harvester' }}</span>
          </div>
        </div>

        {{-- Biaya & Nilai --}}
        <div class="border-t border-[#E4F0D6] pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <span class="text-[11px] text-[#9AB880] block font-medium">Estimasi Biaya Panen / Tebas</span>
            <span class="font-bold text-[#1A2D10] text-sm">Rp {{ number_format((float)$panen->biaya_panen, 0, ',', '.') }}</span>
            <p class="text-[11px] text-[#4A6030] mt-0.5">{{ $panen->keterangan_tenaga_kerja ?? 'Tenaga kerja mandiri/kelompok' }}</p>
          </div>
          <div>
            <span class="text-[11px] text-[#9AB880] block font-medium">Harga Jual / Perkiraan Pasar</span>
            <span class="font-bold text-[#4D9830] text-sm">Rp {{ number_format((float)$panen->harga_per_kg, 0, ',', '.') }} / kg</span>
            <p class="text-[11px] text-[#4A6030] mt-0.5">Estimasi bruto: Rp {{ number_format(((float)$panen->berat_kg) * ((float)$panen->harga_per_kg), 0, ',', '.') }}</p>
          </div>
        </div>

        @if($panen->catatan)
          <div class="border-t border-[#E4F0D6] pt-4">
            <span class="text-[11px] text-[#9AB880] block font-semibold uppercase">Catatan Lapangan dari Petani</span>
            <p class="text-xs sm:text-sm text-[#1A2D10] mt-1 bg-[#F5F8F1] p-3 rounded-xl italic">
              "{{ $panen->catatan }}"
            </p>
          </div>
        @endif
      </div>

      {{-- Card 2: Foto Bukti Hasil Panen --}}
      <div class="p-6 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs space-y-4">
        <h2 class="text-base font-bold text-[#1A2D10] flex items-center gap-2">
          <i data-lucide="image" class="w-5 h-5 text-[#4D9830]"></i>
          <span>Dokumentasi Foto Hasil Panen</span>
        </h2>

        @if($panen->foto_panen)
          <div class="rounded-2xl overflow-hidden border border-[#E4F0D6] max-w-lg">
            <img src="{{ url('media/' . ltrim($panen->foto_panen, '/')) }}"
                 alt="Foto Bukti Hasil Panen"
                 class="w-full h-auto object-cover hover:scale-105 transition-transform duration-300">
          </div>
        @else
          <div class="p-8 text-center rounded-2xl bg-[#F5F8F1] border border-dashed border-[#C5DFB0]">
            <i data-lucide="image-off" class="w-8 h-8 text-[#9AB880] mx-auto mb-2"></i>
            <span class="text-xs text-[#9AB880]">Petani tidak melampirkan foto hasil panen saat input di aplikasi.</span>
          </div>
        @endif
      </div>

    </div>

    {{-- KANAN: PETANI & FORM VERIFIKASI PENGURUS --}}
    <div class="space-y-6">

      {{-- Identitas Petani & Lahan --}}
      <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs space-y-4">
        <h3 class="text-sm font-bold text-[#1A2D10] uppercase tracking-wider flex items-center gap-2">
          <i data-lucide="user" class="w-4 h-4 text-[#4D9830]"></i>
          <span>Petani & Petak Lahan</span>
        </h3>

        <div class="flex items-center gap-3 p-3 rounded-xl bg-[#F5F8F1]">
          <div class="w-11 h-11 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center font-bold text-sm">
            {{ strtoupper(substr($panen->pengguna?->nama ?? 'P', 0, 2)) }}
          </div>
          <div>
            <div class="font-bold text-[#1A2D10] text-sm">{{ $panen->pengguna?->nama ?? 'Anggota Poktan' }}</div>
            <div class="text-[11px] text-[#4A6030]">No. HP: {{ $panen->pengguna?->no_telepon ?? '-' }}</div>
            <div class="text-[11px] text-[#9AB880] font-medium">{{ $panen->kelompokTani?->nama_kelompok }}</div>
          </div>
        </div>

        <div class="p-3 rounded-xl border border-[#E4F0D6] space-y-1 text-xs">
          <div class="font-bold text-[#1A2D10]">{{ $panen->lahan?->nama_lahan ?? 'Lahan Garapan' }}</div>
          <div class="text-[#4A6030]">{{ $panen->lahan?->alamat_lahan ?? '-' }}</div>
          @if($panen->lahan?->luas)
            <div class="text-[#9AB880] font-medium">Luas: {{ number_format($panen->lahan->luas, 2) }} Hektar</div>
          @endif
        </div>
      </div>

      {{-- Kotak Aksi Verifikasi --}}
      <div class="p-6 rounded-[20px] bg-white border-2 border-[#C5DFB0] shadow-xs space-y-5">
        <div>
          <h3 class="text-base font-extrabold text-[#1A2D10]">Verifikasi Pengurus</h3>
          <p class="text-xs text-[#9AB880] mt-0.5">
            Tinjau kesesuaian timbangan, varietas, dan catatan panen anggota poktan.
          </p>
        </div>

        @if($panen->diverifikasi_oleh)
          <div class="p-3.5 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6] text-xs space-y-1">
            <span class="text-[11px] text-[#9AB880] block font-medium">Riwayat Verifikasi Terakhir</span>
            <div class="font-bold text-[#1A2D10]">{{ $panen->diverifikasi_oleh }}</div>
            <div class="text-[11px] text-[#4A6030]">{{ $panen->tanggal_verifikasi ? $panen->tanggal_verifikasi->translatedFormat('d M Y, H:i') : '-' }} WIB</div>
            @if($panen->catatan_verifikasi)
              <div class="text-xs text-[#1A2D10] mt-1 italic">"{{ $panen->catatan_verifikasi }}"</div>
            @endif
          </div>
        @endif

        <form method="POST" action="{{ route('pengurus.panen.verifikasi', $panen->id_panen) }}" class="space-y-4">
          @csrf

          <div>
            <label class="block text-xs font-bold text-[#1A2D10] uppercase mb-1.5">Pilih Status Verifikasi</label>
            <div class="grid grid-cols-2 gap-2">
              <label class="flex items-center gap-2 p-3 rounded-xl border border-emerald-200 bg-emerald-50/50 cursor-pointer hover:bg-emerald-50 transition-colors">
                <input type="radio" name="status" value="disetujui" {{ $panen->status === 'disetujui' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                <span class="text-xs font-bold text-emerald-800">Setujui</span>
              </label>

              <label class="flex items-center gap-2 p-3 rounded-xl border border-rose-200 bg-rose-50/50 cursor-pointer hover:bg-rose-50 transition-colors">
                <input type="radio" name="status" value="ditolak" {{ $panen->status === 'ditolak' ? 'checked' : '' }} class="text-rose-600 focus:ring-rose-500">
                <span class="text-xs font-bold text-rose-800">Tolak</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-[#1A2D10] uppercase mb-1.5">Catatan Verifikasi (Opsional)</label>
            <textarea name="catatan_verifikasi"
                      rows="3"
                      placeholder="Contoh: Berat sesuai pengecekan timbangan poktan."
                      class="w-full p-3 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-[#1A2D10] placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:bg-white transition-all">{{ old('catatan_verifikasi', $panen->catatan_verifikasi) }}</textarea>
          </div>

          <button type="submit" class="w-full py-3 rounded-xl bg-[#4D9830] text-white text-xs sm:text-sm font-bold hover:bg-[#3D7826] transition-colors shadow-xs flex items-center justify-center gap-2 cursor-pointer">
            <i data-lucide="check-check" class="w-4 h-4"></i>
            <span>Simpan Keputusan Verifikasi</span>
          </button>
        </form>
      </div>

    </div>

  </div>

</div>
@endsection
