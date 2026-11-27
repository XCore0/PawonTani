@extends('Admin.Layout._layout')

@section('title', 'Dokumen Anggota & Lahan (Ringkasan)')

@section('content')
<div class="space-y-6">

  <!-- ==================== FLASH ALERT NOTIFICATION ==================== -->
  @if(session('success'))
    <div id="flash-banner" class="flex items-center justify-between p-4 rounded-2xl bg-[#EBF6E0] border border-[#C5DFB0] text-[#1A2D10] text-xs sm:text-sm shadow-xs transition-all">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-[#4D9830] text-white flex items-center justify-center shrink-0">
          <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
        </div>
        <div>
          <span class="font-bold">Berhasil!</span>
          <span class="text-[#4A6030] ml-1">{{ session('success') }}</span>
        </div>
      </div>
      <button type="button" onclick="document.getElementById('flash-banner').remove()" class="p-1 text-[#4A6030] hover:text-[#1A2D10] rounded-lg hover:bg-[#dff0d0] transition-colors cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>
  @endif

  <!-- ==================== 1. TOP HEADER & ACTION BUTTONS ==================== -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Ringkasan Kelengkapan Dokumen Anggota & Lahan
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Monitoring statistik agregat kelengkapan berkas identitas dan bukti kepemilikan/penguasaan lahan petani di seluruh kelompok.
      </p>
    </div>

    <!-- Privacy Notice Badge -->
    <div class="flex items-center gap-2.5 shrink-0">
      <div class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-xs sm:text-sm font-semibold text-emerald-800">
        <i data-lucide="shield" class="w-4 h-4 text-emerald-600"></i>
        <span>Privasi Anggota: Berkas Dikelola Pemilik & Pengurus</span>
      </div>
    </div>
  </div>

  <!-- ==================== 2. SUMMARY METRICS CARDS ==================== -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <!-- Card 1: Total Anggota -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
        <i data-lucide="users" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Total Anggota</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalAnggota) }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Anggota Terdaftar</span>
      </div>
    </div>

    <!-- Card 2: KTP & KK Lengkap -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
        <i data-lucide="badge-check" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">KTP & KK Tersimpan</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalDokumenIdentitasLengkap) }}</span>
        <span class="text-[11px] text-emerald-600 font-semibold block mt-0.5">● Identitas Terpenuhi</span>
      </div>
    </div>

    <!-- Card 3: Perlu SPPT Lahan -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[#F4A020] flex items-center justify-center shrink-0">
        <i data-lucide="file-warning" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Belum Ada SPPT</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalSpptKurang) }}</span>
        <span class="text-[11px] text-amber-600 font-semibold block mt-0.5">● Perlu Kelengkapan Lahan</span>
      </div>
    </div>
  </div>

  <!-- ==================== 3. FILTER & SEARCH TOOLBAR ==================== -->
  <div class="p-4 sm:p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
      
      <!-- Search Box (Col 5) -->
      <div class="md:col-span-5 relative">
        <i data-lucide="search" class="w-4 h-4 text-[#9AB880] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
        <input type="text" name="q" value="{{ request('q') }}"
          placeholder="Cari nama, NIK, username, atau ID..."
          class="w-full h-11 pl-10 pr-4 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-slate-800 placeholder-[#9AB880] text-xs sm:text-sm focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
      </div>

      <!-- Filter Kelompok Tani (Col 4) -->
      <div class="md:col-span-4">
        <select name="kelompok" onchange="this.form.submit()"
          class="w-full h-11 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="">Semua Kelompok Tani</option>
          @foreach($kelompok as $item)
            <option value="{{ $item->id_kelompok }}" @selected(request('kelompok') === $item->id_kelompok)>
              {{ $item->nama_kelompok }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Filter Status (Col 2) -->
      <div class="md:col-span-2">
        <select name="status" onchange="this.form.submit()"
          class="w-full h-11 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="">Semua Status</option>
          <option value="Aktif" @selected(request('status') === 'Aktif')>Aktif</option>
          <option value="Keluar" @selected(request('status') === 'Keluar')>Keluar</option>
          <option value="Tidak Aktif" @selected(request('status') === 'Tidak Aktif')>Tidak Aktif</option>
        </select>
      </div>

      <!-- Reset / Action (Col 1) -->
      <div class="md:col-span-1 flex items-center justify-end">
        @if(request()->hasAny(['q', 'kelompok', 'status']))
          <a href="{{ route('admin.dokumen.erdkk') }}"
            class="w-full h-11 rounded-xl border border-[#C5DFB0] bg-white hover:bg-[#F5F8F1] text-[#4A6030] flex items-center justify-center transition-colors cursor-pointer"
            title="Reset Filter">
            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
          </a>
        @else
          <button type="submit"
            class="w-full h-11 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white flex items-center justify-center transition-all cursor-pointer"
            title="Cari">
            <i data-lucide="search" class="w-4 h-4"></i>
          </button>
        @endif
      </div>

    </form>
  </div>

  <!-- ==================== 4. DATA TABLE ==================== -->
  <div class="bg-white rounded-[20px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
    
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs sm:text-sm" id="erdkk-table">
        <!-- Table Header (Identik Pengurus) -->
        <thead>
          <tr class="bg-[#F5F8F1] border-b border-[#E4F0D6] text-[#1A2D10] font-bold tracking-wider uppercase text-[11px]">
            <th class="py-3.5 px-4 text-center w-12">No</th>
            <th class="py-3.5 px-4">Petani</th>
            <th class="py-3.5 px-4">Kelompok Tani</th>
            <th class="py-3.5 px-4 text-center w-24">KTP</th>
            <th class="py-3.5 px-4 text-center w-24">KK</th>
            <th class="py-3.5 px-4">Bukti SPPT per Lahan</th>
            <th class="py-3.5 px-4 text-center">Status</th>
            <th class="py-3.5 px-4 text-center w-28">Arsip ZIP</th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="divide-y divide-[#E4F0D6]/60 text-slate-700">
          @forelse($anggota as $index => $member)
            @php($ktp = $member->dokumenIdentitas->firstWhere('jenis', 'KTP'))
            @php($kk = $member->dokumenIdentitas->firstWhere('jenis', 'KK'))
            <tr class="hover:bg-[#F5F8F1]/50 transition-colors">
              <!-- NO -->
              <td class="py-4 px-4 text-center font-medium text-slate-500">
                {{ $anggota->firstItem() + $index }}
              </td>

              <!-- PETANI (Nama, Avatar & NIK) -->
              <td class="py-4 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-[#4D9830] to-[#72BE4A] text-white font-bold flex items-center justify-center text-xs shrink-0 border border-emerald-200">
                    {{ strtoupper(substr($member->nama, 0, 2)) }}
                  </div>
                  <div>
                    <span class="font-bold text-[#1A2D10] block">{{ $member->nama }}</span>
                    <span class="text-[11px] text-slate-500 block">
                      NIK: {{ $member->nik ?: 'Belum diisi' }}
                    </span>
                  </div>
                </div>
              </td>

              <!-- KELOMPOK TANI -->
              <td class="py-4 px-4">
                <span class="font-medium text-[#1A2D10] block">{{ $member->kelompokTani?->nama_kelompok ?? '-' }}</span>
                <span class="text-[11px] text-slate-400 block">Anggota Poktan</span>
              </td>

              <!-- KTP -->
              <td class="py-4 px-4 text-center">
                @if($ktp)
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i data-lucide="check" class="w-3 h-3"></i> Tersimpan
                  </span>
                @else
                  <span class="text-[11px] text-amber-600 italic">Belum ada</span>
                @endif
              </td>

              <!-- KK -->
              <td class="py-4 px-4 text-center">
                @if($kk)
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i data-lucide="check" class="w-3 h-3"></i> Tersimpan
                  </span>
                @else
                  <span class="text-[11px] text-amber-600 italic">Belum ada</span>
                @endif
              </td>

              <!-- SPPT LAHAN -->
              <td class="py-4 px-4 min-w-[220px]">
                @forelse($member->lahan as $land)
                  <div class="mb-2 last:mb-0 p-2 rounded-xl border border-[#E4F0D6] bg-[#F5F8F1]/40">
                    <div class="flex items-center justify-between text-xs mb-1">
                      <span class="font-bold text-[#1A2D10] flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-3 h-3 text-[#4D9830]"></i>
                        {{ $land->nama_lahan }}
                      </span>
                      <span class="text-slate-400 font-medium">{{ $land->luas }} Ha</span>
                    </div>

                    @if($land->dokumenSppt->isNotEmpty())
                      <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-medium">
                        <i data-lucide="file-check" class="w-3 h-3 text-emerald-600"></i> SPPT Tersimpan
                      </span>
                    @else
                      <span class="text-[11px] text-amber-600 italic block">SPPT belum ada</span>
                    @endif
                  </div>
                @empty
                  <span class="text-xs text-slate-400 italic">Belum ada lahan</span>
                @endforelse
              </td>

              <!-- STATUS -->
              <td class="py-4 px-4 text-center">
                @php($st = $member->anggota?->status_keanggotaan ?? '-')
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $st === 'Aktif' ? 'bg-[#EBF6E0] text-[#4D9830] border border-[#C5DFB0]' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                  {{ $st }}
                </span>
              </td>

              <!-- ARSIP PRIVASI -->
              <td class="py-4 px-4 text-center">
                <span class="text-[11px] text-slate-500 font-medium">Dikelola Pengurus</span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="py-12 text-center text-slate-400">
                <div class="flex flex-col items-center justify-center">
                  <div class="w-12 h-12 rounded-2xl bg-[#F5F8F1] flex items-center justify-center mb-3">
                    <i data-lucide="folder-x" class="w-6 h-6 text-[#9AB880]"></i>
                  </div>
                  <span class="font-bold text-slate-700 text-sm">Dokumen e-RDKK tidak ditemukan</span>
                  <span class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter kelompok</span>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination Footer -->
    <div class="p-4 sm:p-5 border-t border-[#E4F0D6]/60 bg-white">
      {{ $anggota->links() }}
    </div>
  </div>

</div>
@endsection
