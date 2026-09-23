@extends('Admin.Layout._layout')

@section('title', 'Pengurus')

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

  @if($errors->any())
    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm space-y-1">
      <div class="flex items-center gap-2 font-bold text-red-900">
        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
        <span>Terjadi kesalahan saat memproses data pengurus:</span>
      </div>
      <ul class="list-disc list-inside pl-6 text-red-700 space-y-0.5">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- ==================== 1. TOP HEADER & ACTION BUTTONS ==================== -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Pengurus
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Kelola dan pantau struktur pengurus kelompok tani di seluruh wilayah binaan.
      </p>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center gap-2.5 shrink-0">
      <!-- Export / Unduh Button -->
      <button type="button" onclick="exportData()"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] hover:border-[#9AB880] transition-all shadow-2xs cursor-pointer">
        <i data-lucide="download" class="w-4 h-4 text-[#4D9830]"></i>
        <span>Unduh Data</span>
      </button>

      <!-- Tambah Pengurus Button -->
      <button type="button" onclick="openModalTambah()"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] active:scale-[0.99] text-white text-xs sm:text-sm font-bold shadow-sm shadow-[#4D9830]/20 hover:shadow-md hover:shadow-[#4D9830]/30 transition-all cursor-pointer">
        <i data-lucide="plus" class="w-4 h-4 stroke-[2.5]"></i>
        <span>Tambah Pengurus</span>
      </button>
    </div>
  </div>

  <!-- ==================== 2. SUMMARY METRICS CARDS ==================== -->
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

    <!-- Card 1: Total Pengurus -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
        <i data-lucide="users" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Total Pengurus</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ $totalPengurus ?? $pengurusList->count() }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Tercatat di Database</span>
      </div>
    </div>

    <!-- Card 2: Pengurus Inti (Ketua) -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[#F4A020] flex items-center justify-center shrink-0">
        <i data-lucide="shield-check" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Ketua Kelompok</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ $ketuaCount ?? 0 }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Pimpinan Poktan</span>
      </div>
    </div>

    <!-- Card 3: Kelompok Tani Binaan -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
        <i data-lucide="sprout" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Kelompok Tani</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ $kelompokList->count() }} Poktan</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Wilayah Binaan</span>
      </div>
    </div>

    <!-- Card 4: Status Aktif -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
        <i data-lucide="user-check" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Status Aktif</span>
        <span class="text-xl font-extrabold text-[#1A2D10] tracking-tight">{{ $aktifCount ?? 0 }} Orang</span>
        <span class="text-[11px] text-emerald-600 font-semibold block mt-0.5">● Siap Bertugas</span>
      </div>
    </div>

  </div>

  <!-- ==================== 3. FILTER & SEARCH TOOLBAR ==================== -->
  <div class="p-4 sm:p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs space-y-3">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">

      <!-- Search Box (Col 4) -->
      <div class="md:col-span-4 relative">
        <i data-lucide="search" class="w-4 h-4 text-[#9AB880] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
        <input type="text" id="search-input" onkeyup="filterTable()"
          placeholder="Cari nama, NIK, username, atau ID..."
          class="w-full h-11 pl-10 pr-4 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-slate-800 placeholder-[#9AB880] text-xs sm:text-sm focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
      </div>

      <!-- Filter Jabatan (Col 3) -->
      <div class="md:col-span-3">
        <select id="filter-jabatan" onchange="filterTable()"
          class="w-full h-11 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="">Semua Jabatan</option>
          <option value="Ketua">Ketua Kelompok</option>
          <option value="Sekretaris">Sekretaris</option>
          <option value="Bendahara">Bendahara</option>
          <option value="Seksi Pengairan">Seksi Pengairan</option>
          <option value="Seksi Saprotan">Seksi Saprotan</option>
          <option value="Anggota Pengurus">Anggota Pengurus</option>
        </select>
      </div>

      <!-- Filter Kelompok Tani (Col 3) -->
      <div class="md:col-span-3">
        <select id="filter-poktan" onchange="filterTable()"
          class="w-full h-11 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="">Semua Kelompok Tani</option>
          @foreach($kelompokList as $k)
            <option value="{{ $k->id_kelompok }}">{{ $k->nama_kelompok }}</option>
          @endforeach
        </select>
      </div>

      <!-- Filter Status (Col 2) -->
      <div class="md:col-span-2">
        <select id="filter-status" onchange="filterTable()"
          class="w-full h-11 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="">Semua Status</option>
          <option value="Aktif">Aktif</option>
          <option value="Tidak Aktif">Tidak Aktif</option>
        </select>
      </div>

    </div>
  </div>

  <!-- ==================== 4. DATA TABLE ==================== -->
  <div class="bg-white rounded-[20px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
    
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs sm:text-sm" id="pengurus-table">
        <!-- Table Header -->
        <thead>
          <tr class="bg-[#F5F8F1] border-b border-[#E4F0D6] text-[#1A2D10] font-bold tracking-wider uppercase text-[11px]">
            <th class="py-3.5 px-4 text-center w-12">No</th>
            <th class="py-3.5 px-4">Pengurus</th>
            <th class="py-3.5 px-4">Jabatan</th>
            <th class="py-3.5 px-4">Kelompok Tani</th>
            <th class="py-3.5 px-4">Kontak</th>
            <th class="py-3.5 px-4 text-center">Status</th>
            <th class="py-3.5 px-4 text-center w-28">Aksi</th>
          </tr>
        </thead>

        <!-- Table Body (Loaded from database table `pengguna`) -->
        <tbody class="divide-y divide-[#E4F0D6]/60 text-slate-700" id="table-body">
          @forelse($pengurusList as $index => $p)
            <tr class="hover:bg-[#F5F8F1]/50 transition-colors pengurus-row"
                data-id="{{ $p->id_pengguna }}"
                data-nama="{{ $p->nama }}"
                data-jabatan="{{ $p->jabatan ?? '-' }}"
                data-poktan-id="{{ $p->id_kelompok ?? '' }}"
                data-poktan-nama="{{ $p->kelompokTani?->nama_kelompok ?? '-' }}"
                data-poktan-alamat="{{ $p->kelompokTani?->alamat ?? '-' }}"
                data-nik="{{ $p->nik ?? '-' }}"
                data-username="{{ $p->username }}"
                data-email="{{ $p->email ?? '-' }}"
                data-kontak="{{ $p->no_telepon ?? '-' }}"
                data-alamat="{{ $p->alamat ?? '-' }}"
                data-status="{{ $p->status }}"
                data-foto="{{ $p->foto_profil ? asset($p->foto_profil) : '' }}"
                data-created="{{ $p->created_at ? $p->created_at->translatedFormat('d F Y') : '-' }}">
              
              <!-- NO -->
              <td class="py-4 px-4 text-center font-medium text-slate-500 row-number">
                {{ $loop->iteration }}
              </td>

              <!-- PENGURUS (Nama, Avatar & ID/Username) -->
              <td class="py-4 px-4">
                <div class="flex items-center gap-3">
                  @if($p->foto_profil)
                    <img src="{{ asset($p->foto_profil) }}" alt="{{ $p->nama }}"
                      loading="lazy" decoding="async"
                      class="w-10 h-10 rounded-full object-cover border border-[#C5DFB0] shadow-2xs shrink-0">
                  @else
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-[#4D9830] to-[#72BE4A] text-white font-bold flex items-center justify-center text-xs shrink-0 border border-emerald-200">
                      {{ strtoupper(substr($p->nama, 0, 2)) }}
                    </div>
                  @endif
                  <div>
                    <div class="font-bold text-[#1A2D10] text-sm item-nama">{{ $p->nama }}</div>
                    <div class="flex items-center gap-1.5 text-[11px] text-[#9AB880] mt-0.5">
                      <span>@<span>{{ $p->username }}</span></span>
                    </div>
                  </div>
                </div>
              </td>

              <!-- JABATAN -->
              <td class="py-4 px-4 item-jabatan">
                @php
                  $jabatan = $p->jabatan ?? 'Pengurus';
                  $badgeClasses = match($jabatan) {
                    'Ketua' => 'bg-[#EBF6E0] text-[#4D9830] border-[#C5DFB0]',
                    'Sekretaris' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'Bendahara' => 'bg-amber-50 text-amber-700 border-amber-200',
                    default => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                  };
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $badgeClasses }}">
                  {{ $jabatan }}
                </span>
              </td>

              <!-- KELOMPOK TANI (Relasi ke tabel kelompok_tani) -->
              <td class="py-4 px-4 item-poktan">
                @if($p->kelompokTani)
                  <div class="font-bold text-[#1A2D10] flex items-center gap-1.5">
                    <i data-lucide="sprout" class="w-3.5 h-3.5 text-[#4D9830] shrink-0"></i>
                    <span>{{ $p->kelompokTani->nama_kelompok }}</span>
                  </div>
                  <div class="text-[11px] text-[#9AB880] truncate max-w-xs mt-0.5">
                    {{ $p->kelompokTani->alamat }}
                  </div>
                @else
                  <span class="text-slate-400 italic">Belum terikat kelompok</span>
                @endif
              </td>

              <!-- KONTAK (No Telp & Email) -->
              <td class="py-4 px-4">
                <div class="space-y-0.5">
                  @if($p->no_telepon)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $p->no_telepon) }}" target="_blank"
                      class="inline-flex items-center gap-1.5 font-medium text-emerald-700 hover:text-emerald-900 hover:underline">
                      <i data-lucide="phone" class="w-3.5 h-3.5 text-emerald-600"></i>
                      <span>{{ $p->no_telepon }}</span>
                    </a>
                  @else
                    <span class="text-slate-400">-</span>
                  @endif

                  @if($p->email)
                    <div class="text-[11px] text-slate-500 flex items-center gap-1">
                      <i data-lucide="mail" class="w-3 h-3 text-slate-400"></i>
                      <span>{{ $p->email }}</span>
                    </div>
                  @endif
                </div>
              </td>

              <!-- STATUS -->
              <td class="py-4 px-4 text-center item-status">
                @if($p->status === 'Aktif')
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Aktif
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    Tidak Aktif
                  </span>
                @endif
              </td>

              <!-- AKSI -->
              <td class="py-4 px-4 text-center">
                <div class="flex items-center justify-center gap-1">
                  <!-- Detail Button -->
                  <button type="button" onclick="showDetail(this)" title="Lihat Detail"
                    class="p-1.5 rounded-lg text-[#4D9830] hover:bg-[#EBF6E0] transition-colors cursor-pointer">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                  </button>

                  <!-- Edit Button -->
                  <button type="button" onclick="openModalEdit(this, '{{ $p->id_pengguna }}')" title="Edit Data Pengurus"
                    class="p-1.5 rounded-lg text-[#4D9830] hover:bg-[#EBF6E0] transition-colors cursor-pointer">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                  </button>

                  <!-- Delete Button -->
                  <button type="button" onclick="confirmDelete('{{ $p->id_pengguna }}', '{{ addslashes($p->nama) }}')" title="Hapus Data Pengurus"
                    class="p-1.5 rounded-lg text-[#DC2626] hover:bg-red-50 transition-colors cursor-pointer">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr id="empty-row-initial">
              <td colspan="7" class="py-12 px-4 text-center text-slate-400">
                <div class="w-12 h-12 mx-auto rounded-full bg-[#F5F8F1] text-[#9AB880] flex items-center justify-center mb-2">
                  <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <p class="font-bold text-[#1A2D10]">Belum ada data pengurus</p>
                <p class="text-xs text-[#9AB880] mt-0.5">Klik tombol "+ Tambah Pengurus" di atas untuk menambahkan pengurus baru.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Empty State when filtering finds 0 results -->
    <div id="empty-state" class="hidden py-12 px-4 text-center">
      <div class="w-12 h-12 mx-auto rounded-full bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center mb-2">
        <i data-lucide="search-x" class="w-6 h-6"></i>
      </div>
      <h3 class="text-sm font-bold text-[#1A2D10]">Tidak ada pengurus ditemukan</h3>
      <p class="text-xs text-[#9AB880] mt-0.5">Coba sesuaikan kata kunci pencarian, jabatan, atau filter kelompok tani.</p>
      <button type="button" onclick="resetFilters()"
        class="mt-3 px-3.5 py-1.5 rounded-xl bg-[#EBF6E0] text-xs font-semibold text-[#4D9830] hover:bg-[#dff0d0] transition-colors cursor-pointer">
        Reset Pencarian
      </button>
    </div>

    <!-- Table Pagination Footer -->
    <div class="p-4 sm:p-5 border-t border-[#E4F0D6] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs sm:text-sm text-slate-600">
      <div class="font-medium text-[#9AB880]">
        Menampilkan <span class="font-bold text-[#1A2D10]">1</span> - <span id="showing-count" class="font-bold text-[#1A2D10]">{{ $pengurusList->count() }}</span> dari <span class="font-bold text-[#1A2D10]">{{ $totalPengurus ?? $pengurusList->count() }}</span> pengurus
      </div>
      <div class="flex items-center gap-1.5 self-end sm:self-auto">
        <button type="button" disabled class="px-3 py-1.5 rounded-lg border border-[#E4F0D6] bg-white text-slate-400 text-xs font-medium cursor-not-allowed">
          Sebelumnya
        </button>
        <button type="button" class="w-8 h-8 rounded-lg bg-[#4D9830] text-white text-xs font-bold flex items-center justify-center shadow-xs">
          1
        </button>
        <button type="button" disabled class="px-3 py-1.5 rounded-lg border border-[#E4F0D6] bg-white text-slate-400 text-xs font-medium cursor-not-allowed">
          Selanjutnya
        </button>
      </div>
    </div>

  </div>

</div>

<!-- ==================== 5. MODAL TAMBAH PENGURUS (CREATE ONLY) ==================== -->
<div id="modal-tambah" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
  <div class="bg-white w-full max-w-xl rounded-[22px] border border-[#E4F0D6] shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
    
    <!-- Modal Header -->
    <div class="px-6 py-4 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center">
          <i data-lucide="user-plus" class="w-4 h-4"></i>
        </div>
        <div>
          <h3 class="text-base font-bold text-[#1A2D10]">Tambah Data Pengurus</h3>
          <p class="text-xs text-[#9AB880]">Simpan data pengurus ke database pengguna</p>
        </div>
      </div>
      <button onclick="closeModalTambah()" class="p-1 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-white transition-colors cursor-pointer" aria-label="Tutup">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- Modal Form (Submits to POST /admin/pengurus) -->
    <form action="{{ route('admin.pengurus.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 text-xs sm:text-sm max-h-[80vh] overflow-y-auto">
      @csrf

      <!-- Role locked to Pengurus -->
      <input type="hidden" name="role" value="Pengurus">

      <!-- Nama Lengkap -->
      <div>
        <label for="form-nama" class="block font-semibold text-[#1A2D10] mb-1">
          Nama Lengkap Pengurus <span class="text-red-500">*</span>
        </label>
        <input type="text" name="nama" id="form-nama" required pattern="[\p{L} .-]+" title="Nama hanya boleh berisi huruf, spasi, titik, dan strip." placeholder="Contoh: Bpk. Sutrisno, S.P." value="{{ old('nama') }}"
          class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
      </div>

      <!-- Grid 2 Cols: Jabatan & Kelompok Tani -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <!-- Jabatan Pengurus -->
        <div>
          <label for="form-jabatan" class="block font-semibold text-[#1A2D10] mb-1">
            Jabatan Pengurus <span class="text-red-500">*</span>
          </label>
          <select name="jabatan" id="form-jabatan" required
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
            <option value="">Pilih Jabatan</option>
            <option value="Ketua" {{ old('jabatan') === 'Ketua' ? 'selected' : '' }}>Ketua Kelompok</option>
            <option value="Sekretaris" {{ old('jabatan') === 'Sekretaris' ? 'selected' : '' }}>Sekretaris</option>
            <option value="Bendahara" {{ old('jabatan') === 'Bendahara' ? 'selected' : '' }}>Bendahara</option>
            <option value="Seksi Pengairan" {{ old('jabatan') === 'Seksi Pengairan' ? 'selected' : '' }}>Seksi Pengairan</option>
            <option value="Seksi Saprotan" {{ old('jabatan') === 'Seksi Saprotan' ? 'selected' : '' }}>Seksi Saprotan</option>
            <option value="Anggota Pengurus" {{ old('jabatan') === 'Anggota Pengurus' ? 'selected' : '' }}>Anggota Pengurus</option>
          </select>
        </div>

        <!-- Kelompok Tani Asal -->
        <div>
          <label for="form-kelompok" class="block font-semibold text-[#1A2D10] mb-1">
            Kelompok Tani <span class="text-red-500">*</span>
          </label>
          <select name="id_kelompok" id="form-kelompok" required
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
            <option value="">Pilih Kelompok Tani</option>
            @foreach($kelompokList as $k)
              <option value="{{ $k->id_kelompok }}" {{ old('id_kelompok') === $k->id_kelompok ? 'selected' : '' }}>
                {{ $k->nama_kelompok }}
              </option>
            @endforeach
          </select>
        </div>
      </div>

      <!-- Grid 2 Cols: NIK & No. Telepon -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label for="form-nik" class="block font-semibold text-[#1A2D10] mb-1">
            NIK (16 Digit)
          </label>
          <input type="text" name="nik" id="form-nik" required inputmode="numeric" pattern="[0-9]{16}" minlength="16" maxlength="16" title="NIK harus tepat 16 digit angka." placeholder="3302xxxxxxxxxxxx" value="{{ old('nik') }}"
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
        </div>
        <div>
          <label for="form-telepon" class="block font-semibold text-[#1A2D10] mb-1">
            No. WhatsApp / HP
          </label>
          <input type="tel" name="no_telepon" id="form-telepon" required inputmode="numeric" pattern="[0-9]{11,15}" minlength="11" maxlength="15" title="Nomor WhatsApp / HP harus 11 sampai 15 digit angka." placeholder="08xxxxxxxxxx" value="{{ old('no_telepon') }}"
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
        </div>
      </div>

      <!-- Grid 2 Cols: Username & Password -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label for="form-username" class="block font-semibold text-[#1A2D10] mb-1">
            Username Login <span class="text-red-500">*</span>
          </label>
          <input type="text" name="username" id="form-username" required placeholder="sutrisno123" value="{{ old('username') }}"
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
          <span id="form-username-feedback" class="text-[11px] mt-1 hidden"></span>
        </div>
        <div>
          <label for="form-password" class="block font-semibold text-[#1A2D10] mb-1">
            Password Login <span class="text-red-500">*</span>
          </label>
          <input type="password" name="password" id="form-password" required placeholder="Minimal 6 karakter"
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
        </div>
      </div>

      <!-- Grid 2 Cols: Email & Status -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label for="form-email" class="block font-semibold text-[#1A2D10] mb-1">
            Alamat Email
          </label>
          <input type="email" name="email" id="form-email" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" title="Format email tidak valid. Contoh: nama@gmail.com (tidak boleh ada angka atau karakter lain setelah ekstensi domain seperti .com)" placeholder="pengurus@pawontani.id" value="{{ old('email') }}"
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
        </div>
        <div>
          <label for="form-status" class="block font-semibold text-[#1A2D10] mb-1">
            Status Pengurus <span class="text-red-500">*</span>
          </label>
          <select name="status" id="form-status" required
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
            <option value="Aktif" {{ old('status', 'Aktif') === 'Aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="Tidak Aktif" {{ old('status') === 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
          </select>
        </div>
      </div>

      <!-- Alamat Lengkap -->
      <div>
        <label for="form-alamat" class="block font-semibold text-[#1A2D10] mb-1">Alamat Tempat Tinggal</label>
        <textarea name="alamat" id="form-alamat" rows="2" required placeholder="Dusun, RT/RW, Desa, Kecamatan..."
          class="w-full p-3 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all resize-none">{{ old('alamat') }}</textarea>
      </div>

      <!-- Foto Profil Upload -->
      <div>
        <label for="form-foto" class="block font-semibold text-[#1A2D10] mb-1">Foto Profil (Opsional)</label>
        <input type="file" name="foto_profil" id="form-foto" accept="image/*"
          class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#EBF6E0] file:text-[#4D9830] hover:file:bg-[#dff0d0] file:cursor-pointer border border-[#C5DFB0] rounded-xl p-1 bg-white cursor-pointer">
        <p class="text-[11px] text-[#9AB880] mt-1">Format: JPG, PNG, WEBP (Maksimal 2MB).</p>
      </div>

      <!-- Modal Footer Buttons -->
      <div class="pt-3 border-t border-[#E4F0D6] flex items-center justify-end gap-2.5">
        <button type="button" onclick="closeModalTambah()"
          class="px-4 py-2.5 rounded-xl border border-[#C5DFB0] text-[#4A6030] hover:bg-[#F5F8F1] font-semibold transition-colors cursor-pointer">
          Batal
        </button>
        <button type="submit"
          class="px-5 py-2.5 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white font-bold transition-all shadow-sm shadow-[#4D9830]/20 cursor-pointer">
          Simpan Pengurus
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ==================== 6. MODAL DETAIL PENGURUS ==================== -->
<div id="modal-detail" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
  <div class="bg-white w-full max-w-md rounded-[22px] border border-[#E4F0D6] shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
    
    <div class="px-6 py-4 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]">
      <h3 class="text-base font-bold text-[#1A2D10] flex items-center gap-2">
        <i data-lucide="user" class="w-4 h-4 text-[#4D9830]"></i>
        <span>Detail Profil Pengurus</span>
      </h3>
      <button onclick="closeModalDetail()" class="p-1 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-white transition-colors cursor-pointer">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <div class="p-6 space-y-4 text-xs sm:text-sm">
      <div class="flex items-center gap-3.5 pb-4 border-b border-[#E4F0D6]">
        <div id="detail-avatar-container" class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#4D9830] to-[#72BE4A] text-white font-bold text-base flex items-center justify-center shadow-xs overflow-hidden shrink-0">
          <span id="detail-initial">P</span>
        </div>
        <div>
          <h4 id="detail-nama" class="text-base font-bold text-[#1A2D10]">-</h4>
          <div class="flex items-center gap-2 mt-0.5">
            <span id="detail-jabatan-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#EBF6E0] text-[#4D9830] border border-[#C5DFB0]">
              Ketua
            </span>
            <span id="detail-status-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
              Aktif
            </span>
          </div>
        </div>
      </div>

      <div class="space-y-2.5">
        <div class="flex justify-between py-1 border-b border-slate-100">
          <span class="text-[#9AB880]">Kelompok Tani</span>
          <span id="detail-poktan" class="font-semibold text-[#1A2D10]">-</span>
        </div>
        <div class="flex justify-between py-1 border-b border-slate-100">
          <span class="text-[#9AB880]">Username</span>
          <span id="detail-username" class="font-semibold text-slate-800">-</span>
        </div>
        <div class="flex justify-between py-1 border-b border-slate-100">
          <span class="text-[#9AB880]">NIK</span>
          <span id="detail-nik" class="font-semibold text-slate-800 font-mono">-</span>
        </div>
        <div class="flex justify-between py-1 border-b border-slate-100">
          <span class="text-[#9AB880]">No. Telepon</span>
          <span id="detail-telepon" class="font-semibold text-slate-800">-</span>
        </div>
        <div class="flex justify-between py-1 border-b border-slate-100">
          <span class="text-[#9AB880]">Email</span>
          <span id="detail-email" class="font-semibold text-slate-800">-</span>
        </div>
        <div class="py-1">
          <span class="text-[#9AB880] block mb-1">Alamat Tempat Tinggal</span>
          <p id="detail-alamat" class="font-medium text-slate-700 leading-relaxed bg-[#F5F8F1] p-2.5 rounded-xl border border-[#E4F0D6]">
            -
          </p>
        </div>
      </div>

      <div class="pt-2 flex items-center justify-end gap-2.5">
        <button type="button" onclick="closeModalDetail()"
          class="px-4 py-2 rounded-xl border border-[#C5DFB0] text-[#4A6030] hover:bg-[#F5F8F1] font-semibold transition-colors cursor-pointer">
          Tutup
        </button>
        <button type="button" onclick="editFromDetail()"
          class="px-4 py-2 rounded-xl bg-[#4D9830] text-white font-bold hover:bg-[#3D8024] transition-colors cursor-pointer flex items-center gap-1.5">
          <i data-lucide="edit-3" class="w-4 h-4"></i>
          <span>Edit Pengurus</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ==================== 7. MODAL EDIT PENGURUS ==================== -->
<div id="modal-edit" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
  <div class="bg-white w-full max-w-xl rounded-[22px] border border-[#E4F0D6] shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
    
    <!-- Modal Header -->
    <div class="px-6 py-4 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center">
          <i data-lucide="edit-3" class="w-4 h-4"></i>
        </div>
        <div>
          <h3 class="text-base font-bold text-[#1A2D10]">Edit Data Pengurus</h3>
          <p class="text-xs text-[#9AB880]">Perbarui informasi dan penugasan kelompok tani</p>
        </div>
      </div>
      <button type="button" onclick="closeModalEdit()" class="p-1 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-white transition-colors cursor-pointer" aria-label="Tutup">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- Modal Form (Submits to PUT /admin/pengurus/{id_pengguna}) -->
    <form id="form-edit-pengurus" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 text-xs sm:text-sm max-h-[80vh] overflow-y-auto">
      @csrf
      @method('PUT')

      <!-- Nama Lengkap -->
      <div>
        <label for="edit-nama" class="block font-semibold text-[#1A2D10] mb-1">
          Nama Lengkap Pengurus <span class="text-red-500">*</span>
        </label>
        <input type="text" name="nama" id="edit-nama" required pattern="[\p{L} .-]+" title="Nama hanya boleh berisi huruf, spasi, titik, dan strip." placeholder="Contoh: Bpk. Sutrisno, S.P."
          class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
      </div>

      <!-- Grid 2 Cols: Jabatan & Kelompok Tani -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <!-- Jabatan Pengurus -->
        <div>
          <label for="edit-jabatan" class="block font-semibold text-[#1A2D10] mb-1">
            Jabatan Pengurus <span class="text-red-500">*</span>
          </label>
          <select name="jabatan" id="edit-jabatan" required
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
            <option value="">Pilih Jabatan</option>
            <option value="Ketua">Ketua Kelompok</option>
            <option value="Sekretaris">Sekretaris</option>
            <option value="Bendahara">Bendahara</option>
            <option value="Seksi Pengairan">Seksi Pengairan</option>
            <option value="Seksi Saprotan">Seksi Saprotan</option>
            <option value="Anggota Pengurus">Anggota Pengurus</option>
          </select>
        </div>

        <!-- Kelompok Tani Asal -->
        <div>
          <label for="edit-kelompok" class="block font-semibold text-[#1A2D10] mb-1">
            Kelompok Tani <span class="text-red-500">*</span>
          </label>
          <select name="id_kelompok" id="edit-kelompok" required
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
            <option value="">Pilih Kelompok Tani</option>
            @foreach($kelompokList as $k)
              <option value="{{ $k->id_kelompok }}">{{ $k->nama_kelompok }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <!-- Grid 2 Cols: NIK & No. Telepon -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label for="edit-nik" class="block font-semibold text-[#1A2D10] mb-1">
            NIK (16 Digit)
          </label>
          <input type="text" name="nik" id="edit-nik" required inputmode="numeric" pattern="[0-9]{16}" minlength="16" maxlength="16" title="NIK harus tepat 16 digit angka." placeholder="3302xxxxxxxxxxxx"
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all font-mono">
        </div>
        <div>
          <label for="edit-telepon" class="block font-semibold text-[#1A2D10] mb-1">
            No. WhatsApp / HP
          </label>
          <input type="tel" name="no_telepon" id="edit-telepon" required inputmode="numeric" pattern="[0-9]{11,15}" minlength="11" maxlength="15" title="Nomor WhatsApp / HP harus 11 sampai 15 digit angka."
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
        </div>
      </div>

      <!-- Grid 2 Cols: Username & Password -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label for="edit-username" class="block font-semibold text-[#1A2D10] mb-1">
            Username Login <span class="text-red-500">*</span>
          </label>
          <input type="text" name="username" id="edit-username" required placeholder="sutrisno123"
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
          <span id="edit-username-feedback" class="text-[11px] mt-1 hidden"></span>
        </div>
        <div>
          <label for="edit-password" class="block font-semibold text-[#1A2D10] mb-1">
            Password Baru <span class="text-xs font-normal text-slate-400">(Opsional)</span>
          </label>
          <input type="password" name="password" id="edit-password" placeholder="Kosongkan jika tidak diubah"
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
        </div>
      </div>

      <!-- Grid 2 Cols: Email & Status -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label for="edit-email" class="block font-semibold text-[#1A2D10] mb-1">
            Alamat Email
          </label>
          <input type="email" name="email" id="edit-email" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" title="Format email tidak valid. Contoh: nama@gmail.com (tidak boleh ada angka atau karakter lain setelah ekstensi domain seperti .com)" placeholder="pengurus@pawontani.id"
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
        </div>
        <div>
          <label for="edit-status" class="block font-semibold text-[#1A2D10] mb-1">
            Status Pengurus <span class="text-red-500">*</span>
          </label>
          <select name="status" id="edit-status" required
            class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
            <option value="Aktif">Aktif</option>
            <option value="Tidak Aktif">Tidak Aktif</option>
          </select>
        </div>
      </div>

      <!-- Alamat Lengkap -->
      <div>
        <label for="edit-alamat" class="block font-semibold text-[#1A2D10] mb-1">Alamat Tempat Tinggal</label>
        <textarea name="alamat" id="edit-alamat" rows="2" required placeholder="Dusun, RT/RW, Desa, Kecamatan..."
          class="w-full p-3 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all resize-none"></textarea>
      </div>

      <!-- Foto Profil Upload -->
      <div>
        <label for="edit-foto" class="block font-semibold text-[#1A2D10] mb-1">Foto Profil (Ganti jika diperlukan)</label>
        <div class="flex items-center gap-3">
          <div id="edit-foto-preview-box" class="w-11 h-11 rounded-xl bg-[#F5F8F1] border border-[#C5DFB0] overflow-hidden flex items-center justify-center shrink-0">
            <i data-lucide="user" class="w-5 h-5 text-[#9AB880]" id="edit-foto-icon"></i>
            <img id="edit-foto-img" src="" alt="Foto Profil" class="w-full h-full object-cover hidden">
          </div>
          <div class="flex-1">
            <input type="file" name="foto_profil" id="edit-foto" accept="image/*"
              class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#EBF6E0] file:text-[#4D9830] hover:file:bg-[#dff0d0] file:cursor-pointer border border-[#C5DFB0] rounded-xl p-1 bg-white cursor-pointer">
          </div>
        </div>
        <p class="text-[11px] text-[#9AB880] mt-1">Kosongkan jika tidak ingin mengubah foto profil.</p>
      </div>

      <!-- Modal Footer Buttons -->
      <div class="pt-3 border-t border-[#E4F0D6] flex items-center justify-end gap-2.5">
        <button type="button" onclick="closeModalEdit()"
          class="px-4 py-2.5 rounded-xl border border-[#C5DFB0] text-[#4A6030] hover:bg-[#F5F8F1] font-semibold transition-colors cursor-pointer">
          Batal
        </button>
        <button type="submit"
          class="px-5 py-2.5 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white font-bold transition-all shadow-sm shadow-[#4D9830]/20 cursor-pointer flex items-center gap-1.5">
          <i data-lucide="check" class="w-4 h-4"></i>
          <span>Perbarui Pengurus</span>
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ==================== 8. MODAL KONFIRMASI HAPUS PENGURUS ==================== -->
<div id="modal-delete" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
  <div class="w-full max-w-md rounded-[22px] bg-white border border-red-100 shadow-2xl overflow-hidden p-6 text-center space-y-4 animate-in fade-in zoom-in-95 duration-200" id="modal-delete-container">
    <div class="w-14 h-14 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center mx-auto border border-red-100 shadow-2xs">
      <i data-lucide="alert-triangle" class="w-7 h-7"></i>
    </div>
    
    <div>
      <h3 class="text-lg font-bold text-[#1A2D10]">Hapus Data Pengurus?</h3>
      <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">
        Apakah Anda yakin ingin menghapus data pengurus <span id="delete-pengurus-nama" class="font-bold text-[#1A2D10]"></span>?
      </p>
      <div class="mt-3 p-3 rounded-xl bg-red-50/70 border border-red-100 text-[11px] text-red-700 flex items-start gap-2 text-left">
        <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5 text-red-500"></i>
        <span>Tindakan ini tidak dapat dibatalkan. Akun login dan data penugasan pengurus ini akan dihapus secara permanen dari sistem.</span>
      </div>
    </div>

    <form id="form-delete-pengurus" method="POST" class="pt-2 flex items-center justify-center gap-3">
      @csrf
      @method('DELETE')
      <button type="button" onclick="closeModalDelete()"
        class="w-1/2 py-2.5 px-4 rounded-xl border border-slate-200 bg-white text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors cursor-pointer">
        Batal
      </button>
      <button type="submit"
        class="w-1/2 py-2.5 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-bold shadow-sm shadow-red-500/20 transition-all cursor-pointer flex items-center justify-center gap-1.5">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
        <span>Ya, Hapus</span>
      </button>
    </form>
  </div>
</div>

<!-- ==================== 9. PAGE JAVASCRIPT ==================== -->
<script>
  let currentDetailBtn = null;
  let currentDetailId = null;

  // Filter Table
  function filterTable() {
    const searchVal = document.getElementById('search-input').value.toLowerCase().trim();
    const jabatanVal = document.getElementById('filter-jabatan').value;
    const poktanVal = document.getElementById('filter-poktan').value;
    const statusVal = document.getElementById('filter-status').value;
    const rows = document.querySelectorAll('#table-body .pengurus-row');
    let visibleCount = 0;

    rows.forEach(row => {
      const nama = (row.getAttribute('data-nama') || '').toLowerCase();
      const nik = (row.getAttribute('data-nik') || '').toLowerCase();
      const username = (row.getAttribute('data-username') || '').toLowerCase();
      const email = (row.getAttribute('data-email') || '').toLowerCase();
      const kontak = (row.getAttribute('data-kontak') || '').toLowerCase();
      const id = (row.getAttribute('data-id') || '').toLowerCase();
      const jabatan = row.getAttribute('data-jabatan') || '';
      const poktanId = row.getAttribute('data-poktan-id') || '';
      const poktanNama = (row.getAttribute('data-poktan-nama') || '').toLowerCase();
      const status = row.getAttribute('data-status') || '';

      const matchSearch = !searchVal || 
        nama.includes(searchVal) || 
        nik.includes(searchVal) || 
        username.includes(searchVal) || 
        email.includes(searchVal) || 
        kontak.includes(searchVal) ||
        id.includes(searchVal) ||
        poktanNama.includes(searchVal);

      const matchJabatan = !jabatanVal || jabatan === jabatanVal;
      const matchPoktan = !poktanVal || poktanId === poktanVal;
      const matchStatus = !statusVal || status === statusVal;

      if (matchSearch && matchJabatan && matchPoktan && matchStatus) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    const showingCount = document.getElementById('showing-count');
    if (showingCount) showingCount.textContent = visibleCount;

    const emptyState = document.getElementById('empty-state');
    if (emptyState) {
      if (visibleCount === 0 && rows.length > 0) {
        emptyState.classList.remove('hidden');
      } else {
        emptyState.classList.add('hidden');
      }
    }

    // Re-index numbering
    let currentNo = 1;
    rows.forEach(row => {
      if (row.style.display !== 'none') {
        const numCol = row.querySelector('.row-number');
        if (numCol) numCol.textContent = currentNo++;
      }
    });
  }

  function resetFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('filter-jabatan').value = '';
    document.getElementById('filter-poktan').value = '';
    document.getElementById('filter-status').value = '';
    filterTable();
  }

  // Modal Tambah
  function openModalTambah() {
    const modal = document.getElementById('modal-tambah');
    modal.classList.remove('hidden');
    if (window.lucide) lucide.createIcons();
  }

  function closeModalTambah() {
    const modal = document.getElementById('modal-tambah');
    modal.classList.add('hidden');
  }

  // Modal Detail
  function showDetail(btn) {
    currentDetailBtn = btn;
    const row = btn.closest('tr');
    const nama = row.getAttribute('data-nama') || '-';
    const id = row.getAttribute('data-id') || '-';
    currentDetailId = id;
    const jabatan = row.getAttribute('data-jabatan') || '-';
    const poktan = row.getAttribute('data-poktan-nama') || '-';
    const nik = row.getAttribute('data-nik') || '-';
    const username = row.getAttribute('data-username') || '-';
    const email = row.getAttribute('data-email') || '-';
    const kontak = row.getAttribute('data-kontak') || '-';
    const alamat = row.getAttribute('data-alamat') || '-';
    const status = row.getAttribute('data-status') || 'Aktif';
    const foto = row.getAttribute('data-foto');

    document.getElementById('detail-nama').textContent = nama;
    document.getElementById('detail-poktan').textContent = poktan;
    document.getElementById('detail-username').textContent = '@' + username;
    document.getElementById('detail-nik').textContent = nik;
    document.getElementById('detail-telepon').textContent = kontak;
    document.getElementById('detail-email').textContent = email;
    document.getElementById('detail-alamat').textContent = alamat;

    const jabatanBadge = document.getElementById('detail-jabatan-badge');
    if (jabatanBadge) {
      jabatanBadge.textContent = jabatan;
    }

    const avatarContainer = document.getElementById('detail-avatar-container');
    if (foto && foto.trim() !== '') {
      avatarContainer.innerHTML = `<img src="${foto}" alt="${nama}" class="w-full h-full object-cover">`;
    } else {
      avatarContainer.innerHTML = `<span id="detail-initial">${nama.substring(0, 2).toUpperCase()}</span>`;
    }

    const badge = document.getElementById('detail-status-badge');
    if (badge) {
      if (status === 'Aktif') {
        badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
        badge.textContent = 'Aktif';
      } else {
        badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200';
        badge.textContent = 'Tidak Aktif';
      }
    }

    const modal = document.getElementById('modal-detail');
    modal.classList.remove('hidden');
    if (window.lucide) lucide.createIcons();
  }

  function closeModalDetail() {
    const modal = document.getElementById('modal-detail');
    modal.classList.add('hidden');
  }

  function editFromDetail() {
    closeModalDetail();
    if (currentDetailBtn && currentDetailId) {
      openModalEdit(currentDetailBtn, currentDetailId);
    }
  }

  // Modal Edit
  function openModalEdit(btn, id) {
    const row = btn.closest('tr');
    if (!row) return;

    const nama = row.getAttribute('data-nama') || '';
    const jabatan = row.getAttribute('data-jabatan') || '';
    const poktanId = row.getAttribute('data-poktan-id') || '';
    let nik = row.getAttribute('data-nik') || '';
    if (nik === '-') nik = '';
    const username = row.getAttribute('data-username') || '';
    let email = row.getAttribute('data-email') || '';
    if (email === '-') email = '';
    let kontak = row.getAttribute('data-kontak') || '';
    if (kontak === '-') kontak = '';
    let alamat = row.getAttribute('data-alamat') || '';
    if (alamat === '-') alamat = '';
    const status = row.getAttribute('data-status') || 'Aktif';
    const foto = row.getAttribute('data-foto') || '';

    // Set Form Action
    const form = document.getElementById('form-edit-pengurus');
    form.action = "{{ url('/admin/pengurus') }}/" + encodeURIComponent(id);

    // Populate Form Inputs
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-jabatan').value = jabatan;
    document.getElementById('edit-kelompok').value = poktanId;
    document.getElementById('edit-nik').value = nik;
    document.getElementById('edit-telepon').value = kontak;
    document.getElementById('edit-username').value = username;
    document.getElementById('edit-password').value = '';
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-status').value = status;
    document.getElementById('edit-alamat').value = alamat;

    // Foto Preview
    const fotoImg = document.getElementById('edit-foto-img');
    const fotoIcon = document.getElementById('edit-foto-icon');
    if (foto && foto.trim() !== '') {
      fotoImg.src = foto;
      fotoImg.classList.remove('hidden');
      fotoIcon.classList.add('hidden');
    } else {
      fotoImg.src = '';
      fotoImg.classList.add('hidden');
      fotoIcon.classList.remove('hidden');
    }

    const modal = document.getElementById('modal-edit');
    modal.classList.remove('hidden');
    if (window.lucide) lucide.createIcons();
  }

  function closeModalEdit() {
    const modal = document.getElementById('modal-edit');
    modal.classList.add('hidden');
  }

  // Modal Delete
  function confirmDelete(id, nama) {
    document.getElementById('delete-pengurus-nama').textContent = nama;

    const form = document.getElementById('form-delete-pengurus');
    form.action = "{{ url('/admin/pengurus') }}/" + encodeURIComponent(id);

    const modal = document.getElementById('modal-delete');
    modal.classList.remove('hidden');
    if (window.lucide) lucide.createIcons();
  }

  function closeModalDelete() {
    const modal = document.getElementById('modal-delete');
    modal.classList.add('hidden');
  }

  // Keyboard shortcut (Escape to close all modals)
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeModalTambah();
      closeModalDetail();
      closeModalEdit();
      closeModalDelete();
    }
  });

  // Click outside backdrop to close
  ['modal-tambah', 'modal-detail', 'modal-edit', 'modal-delete'].forEach(modalId => {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          modal.classList.add('hidden');
        }
      });
    }
  });

  function exportData() {
    window.print();
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) {
      lucide.createIcons();
    }

    // Real-time username availability check
    const checkUrl = "{{ route('ajax.check-username') }}";
    let usernameTimer = null;

    function setupUsernameCheck(inputId, feedbackId, getExcludeId) {
      const input = document.getElementById(inputId);
      const feedback = document.getElementById(feedbackId);
      if (!input || !feedback) return;

      input.addEventListener('input', function () {
        clearTimeout(usernameTimer);
        const val = this.value.trim().toLowerCase();
        if (val.length < 2) {
          feedback.classList.add('hidden');
          feedback.textContent = '';
          input.style.borderColor = '';
          return;
        }
        usernameTimer = setTimeout(() => {
          const url = checkUrl + '?username=' + encodeURIComponent(val) + (getExcludeId ? '&exclude_id=' + encodeURIComponent(getExcludeId()) : '');
          fetch(url)
            .then(r => r.json())
            .then(data => {
              if (data.available) {
                feedback.textContent = '✓ Username tersedia';
                feedback.className = 'text-[11px] mt-1 text-emerald-600 font-medium';
                input.style.borderColor = '#4D9830';
              } else {
                feedback.textContent = '✗ Username sudah digunakan';
                feedback.className = 'text-[11px] mt-1 text-red-600 font-medium';
                input.style.borderColor = '#DC2626';
              }
            })
            .catch(() => {});
        }, 400);
      });
    }

    // Create form username check
    setupUsernameCheck('form-username', 'form-username-feedback', null);

    // Edit form username check (exclude current pengurus)
    setupUsernameCheck('edit-username', 'edit-username-feedback', () => {
      const form = document.getElementById('form-edit-pengurus');
      const parts = form ? form.action.split('/') : [];
      return parts[parts.length - 1] || '';
    });

    // Prevent double form submission
    document.querySelectorAll('form').forEach(form => {
      form.addEventListener('submit', function (e) {
        if (this.dataset.submitted === 'true') {
          e.preventDefault();
          return false;
        }
        this.dataset.submitted = 'true';
        const btn = this.querySelector('button[type="submit"]');
        if (btn) {
          btn.disabled = true;
          btn.classList.add('opacity-60', 'cursor-not-allowed');
        }
      });
    });
  });
</script>
@endsection
