@extends('Pengurus.Layout._layout')

@section('title', 'Kelola Anggota')

@section('content')
@php
  $anggota = [
    ['nama' => 'Pak Kamijo Hartono', 'nik' => '3201010101800001', 'telepon' => '082123456789', 'alamat' => 'Dusun Sukamaju RT 01/02', 'lahan' => 2, 'status' => 'Aktif'],
    ['nama' => 'Ibu Siti Murni', 'nik' => '3201010202780002', 'telepon' => '087234567890', 'alamat' => 'Dusun Sukamaju RT 02/02', 'lahan' => 1, 'status' => 'Aktif'],
    ['nama' => 'Pak Dwi Santoso', 'nik' => '3201010303820003', 'telepon' => '081345678901', 'alamat' => 'Dusun Cicaret RT 01/02', 'lahan' => 3, 'status' => 'Aktif'],
    ['nama' => 'Pak Agus Budiman', 'nik' => '3201010404760004', 'telepon' => '082456789012', 'alamat' => 'Dusun Cicaret RT 02/02', 'lahan' => 2, 'status' => 'Aktif'],
    ['nama' => 'Ibu Hartini Rahayu', 'nik' => '3201010505810005', 'telepon' => '089567890123', 'alamat' => 'Dusun Parung RT 01/03', 'lahan' => 1, 'status' => 'Aktif'],
    ['nama' => 'Pak Rudi Hermawan', 'nik' => '3201010606840006', 'telepon' => '083678901234', 'alamat' => 'Dusun Parung RT 02/03', 'lahan' => 2, 'status' => 'Aktif'],
  ];
@endphp

<div class="space-y-6">

  <!-- ==================== 1. TOP HEADER & ACTION BUTTONS ==================== -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Kelola Anggota
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Anggota Kelompok Tani Maju Bersama
      </p>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center gap-2.5 shrink-0">
      <!-- Export / Unduh Button -->
      <button type="button" onclick="alert('Fitur unduh data sedang disiapkan.')"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] hover:border-[#9AB880] transition-all shadow-2xs cursor-pointer">
        <i data-lucide="download" class="w-4 h-4 text-[#4D9830]"></i>
        <span>Unduh Data</span>
      </button>

      <!-- Tambah Anggota Button -->
      <button type="button" onclick="alert('Form tambah anggota sedang disiapkan.');"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] active:scale-[0.99] text-white text-xs sm:text-sm font-bold shadow-sm shadow-[#4D9830]/20 hover:shadow-md hover:shadow-[#4D9830]/30 transition-all cursor-pointer">
        <i data-lucide="plus" class="w-4 h-4 stroke-[2.5]"></i>
        <span>Tambah Anggota</span>
      </button>
    </div>
  </div>

  <!-- ==================== 2. SUMMARY METRICS CARDS ==================== -->
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

    <!-- Card 1: Total Anggota -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
        <i data-lucide="users" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Total Anggota</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ count($anggota) }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Tercatat di Database</span>
      </div>
    </div>

    <!-- Card 2: Anggota Aktif -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
        <i data-lucide="user-check" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Anggota Aktif</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ collect($anggota)->where('status', 'Aktif')->count() }}</span>
        <span class="text-[11px] text-emerald-600 font-semibold block mt-0.5">● Siap Bertugas</span>
      </div>
    </div>

    <!-- Card 3: Total Lahan -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[#F4A020] flex items-center justify-center shrink-0">
        <i data-lucide="map" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Total Lahan</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ collect($anggota)->sum('lahan') }} Bidang</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Lahan Terdaftar</span>
      </div>
    </div>

    <!-- Card 4: Rata-rata Lahan -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
        <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Rata-rata Lahan</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format(collect($anggota)->avg('lahan'), 1) }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Bidang per Anggota</span>
      </div>
    </div>

  </div>

  <!-- ==================== 3. FILTER & SEARCH TOOLBAR ==================== -->
  <div class="p-4 sm:p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs space-y-3">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">

      <!-- Search Box -->
      <div class="md:col-span-5 relative">
        <i data-lucide="search" class="w-4 h-4 text-[#9AB880] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
        <input id="anggota-search" type="search" placeholder="Cari nama, NIK, telepon..."
          class="w-full h-11 pl-10 pr-4 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-slate-800 placeholder-[#9AB880] text-xs sm:text-sm focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
      </div>

      <!-- Filter Status -->
      <div class="md:col-span-3">
        <select id="anggota-status"
          class="w-full h-11 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="">Semua Status</option>
          <option>Aktif</option>
          <option>Tidak Aktif</option>
        </select>
      </div>

      <!-- Result Count -->
      <div class="md:col-span-4 text-right">
        <span class="text-xs text-[#9AB880]">Menampilkan <strong id="anggota-count" class="text-[#1A2D10]">{{ count($anggota) }}</strong> dari {{ count($anggota) }} anggota</span>
      </div>

    </div>
  </div>

  <!-- ==================== 4. DATA TABLE ==================== -->
  <div class="bg-white rounded-[20px] border border-[#E4F0D6] shadow-2xs overflow-hidden">

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs sm:text-sm">
        <!-- Table Header -->
        <thead>
          <tr class="bg-[#F5F8F1] border-b border-[#E4F0D6] text-[#1A2D10] font-bold tracking-wider uppercase text-[11px]">
            <th class="py-3.5 px-4 text-center w-12">No</th>
            <th class="py-3.5 px-4">Nama Anggota</th>
            <th class="py-3.5 px-4">NIK</th>
            <th class="py-3.5 px-4">No. Telepon</th>
            <th class="py-3.5 px-4">Alamat</th>
            <th class="py-3.5 px-4 text-center">Jml Lahan</th>
            <th class="py-3.5 px-4 text-center">Status</th>
            <th class="py-3.5 px-4 text-center w-28">Aksi</th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="divide-y divide-[#E4F0D6]/60" id="anggota-body">
          @foreach($anggota as $index => $item)
            <tr class="hover:bg-[#F5F8F1]/50 transition-colors"
                data-search="{{ strtolower($item['nama'].' '.$item['nik'].' '.$item['telepon']) }}"
                data-status="{{ $item['status'] }}">
              <td class="py-3.5 px-4 text-center text-[#9AB880]">{{ $index + 1 }}</td>
              <td class="py-3.5 px-4 font-bold text-[#1A2D10]">{{ $item['nama'] }}</td>
              <td class="py-3.5 px-4 font-mono text-[11px] text-[#6B7F5B]">{{ $item['nik'] }}</td>
              <td class="py-3.5 px-4 text-[#4A6030]">{{ $item['telepon'] }}</td>
              <td class="py-3.5 px-4 max-w-40 truncate text-[#6B7F5B]">{{ $item['alamat'] }}</td>
              <td class="py-3.5 px-4 text-center">
                <span class="text-base font-extrabold text-[#4D9830]">{{ $item['lahan'] }}</span>
              </td>
              <td class="py-3.5 px-4 text-center">
                <span class="inline-flex items-center gap-1 rounded-full bg-[#DFF7E7] px-2.5 py-1 text-[10px] font-bold text-[#287442]">
                  <span class="w-1.5 h-1.5 rounded-full bg-[#287442]"></span>
                  {{ $item['status'] }}
                </span>
              </td>
              <td class="py-3.5 px-4">
                <div class="flex gap-1 justify-center">
                  <button onclick="alert('Detail anggota sedang disiapkan.')" class="rounded-lg bg-[#EAF5DE] px-2.5 py-1.5 text-[10px] font-bold text-[#4D9830] hover:bg-[#DFF7E7] transition-colors cursor-pointer">
                    <i data-lucide="eye" class="inline h-3 w-3"></i>
                  </button>
                  <button onclick="alert('Edit anggota sedang disiapkan.')" class="rounded-lg bg-[#FFF4B8] px-2.5 py-1.5 text-[10px] font-bold text-[#8A5A0A] hover:bg-[#FFEF99] transition-colors cursor-pointer">
                    <i data-lucide="edit-3" class="inline h-3 w-3"></i>
                  </button>
                  <button onclick="alert('Hapus anggota sedang disiapkan.')" class="rounded-lg bg-[#FFE1E1] px-2.5 py-1.5 text-[10px] font-bold text-[#C24141] hover:bg-[#FFD0D0] transition-colors cursor-pointer">
                    <i data-lucide="trash-2" class="inline h-3 w-3"></i>
                  </button>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <div id="anggota-empty" class="hidden px-4 py-12 text-center text-sm text-[#9AB880]">
        <i data-lucide="search-x" class="w-8 h-8 mx-auto mb-2 text-[#C5DFB0]"></i>
        <p class="font-semibold text-[#6B7F5B]">Tidak ada data yang cocok</p>
        <p class="text-[11px] text-[#9AB880]">Coba ubah filter atau kata kunci pencarian Anda.</p>
      </div>
    </div>

    <!-- Table Footer / Pagination -->
    <div class="flex items-center justify-between border-t border-[#E4F0D6] px-4 py-3.5 text-xs text-[#6B7F5B]">
      <span>Menampilkan 1–{{ count($anggota) }} dari {{ count($anggota) }} data</span>
      <div class="flex gap-1">
        <button class="h-7 w-7 rounded-lg bg-[#4D9830] font-bold text-white">1</button>
        <button class="h-7 w-7 rounded-lg border border-[#B8D99B] text-[#4A6030] hover:bg-[#F5F8F1]">2</button>
      </div>
    </div>
  </div>

</div>

<script>
  (() => {
    const search = document.getElementById('anggota-search');
    const status = document.getElementById('anggota-status');
    const rows = [...document.querySelectorAll('[data-search]')];
    const count = document.getElementById('anggota-count');
    const empty = document.getElementById('anggota-empty');

    const filter = () => {
      const q = search.value.toLowerCase().trim();
      const s = status.value;
      let visible = 0;
      rows.forEach((row) => {
        const match = (!q || row.dataset.search.includes(q)) && (!s || row.dataset.status === s);
        row.classList.toggle('hidden', !match);
        if (match) visible++;
      });
      count.textContent = visible;
      empty.classList.toggle('hidden', visible !== 0);
    };

    search.addEventListener('input', filter);
    status.addEventListener('change', filter);
  })();
</script>
@endsection
