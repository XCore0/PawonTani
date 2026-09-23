@extends('Admin.Layout._layout')

@section('title', 'Kelompok Tani')

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
        Kelompok Tani
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Kelola data kelompok tani yang terdaftar
      </p>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center gap-2.5 shrink-0">
      <!-- Cetak / Export Button (matching SVG: bg #EBF6E0, border #C5DFB0, text #4D9830) -->
      <button type="button" onclick="exportData()"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#EBF6E0] text-xs sm:text-sm font-semibold text-[#4D9830] hover:bg-[#e0f1d2] hover:border-[#9AB880] transition-all shadow-2xs cursor-pointer">
        <i data-lucide="printer" class="w-4 h-4 text-[#4D9830]"></i>
        <span>Cetak / Export</span>
      </button>

      <!-- + Tambah Kelompok Button (matching SVG: bg #4D9830, text white, plus icon) -->
      <button type="button" onclick="openModalTambah()"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] active:scale-[0.99] text-white text-xs sm:text-sm font-bold shadow-sm shadow-[#4D9830]/20 hover:shadow-md hover:shadow-[#4D9830]/30 transition-all cursor-pointer">
        <i data-lucide="plus" class="w-4 h-4 stroke-[2.5]"></i>
        <span>Tambah Kelompok</span>
      </button>
    </div>
  </div>

  <!-- ==================== 2. MAIN CARD: FILTER TOOLBAR & TABLE ==================== -->
  <div class="rounded-2xl bg-white border border-[#E4F0D6] shadow-[0_2px_12px_rgba(26,45,16,0.04)] overflow-hidden">

    <!-- Search & Filter Toolbar (matching SVG layout inside the card top) -->
    <div class="p-4 sm:p-5 border-b border-[#F0F7E8] flex flex-col md:flex-row md:items-center justify-between gap-3.5">
      <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">

        <!-- Search Box -->
        <div class="relative w-full sm:w-[280px]">
          <i data-lucide="search" class="w-4 h-4 text-[#9AB880] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
          <input type="text" id="search-input" onkeyup="filterTable()"
            placeholder="Cari nama, alamat, atau ID..."
            class="w-full h-10.5 pl-10 pr-4 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] text-xs sm:text-sm focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
        </div>

        <!-- Filter Status Dropdown -->
        <div class="w-full sm:w-auto">
          <select id="filter-status" onchange="filterTable()"
            class="w-full sm:w-[160px] h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-[#1A2D10] font-medium focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
            <option value="">Semua Status</option>
            <option value="Aktif">Aktif</option>
            <option value="Tidak Aktif">Tidak Aktif</option>
          </select>
        </div>

        <!-- Reset Button -->
        <button type="button" onclick="resetFilters()"
          class="h-10.5 px-3 rounded-xl border border-[#C5DFB0] bg-white hover:bg-[#F5F8F1] text-[#4A6030] hover:text-[#1A2D10] flex items-center justify-center transition-colors cursor-pointer"
          title="Reset Pencarian">
          <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
        </button>
      </div>

      <!-- Counter Info (matching SVG right-side text "X kelompok tani") -->
      <div class="text-xs sm:text-sm text-[#9AB880] font-medium shrink-0 self-end md:self-center">
        <span id="counter-text" class="font-semibold text-[#4A6030]">{{ $kelompokList->count() }}</span> kelompok tani
      </div>
    </div>

    <!-- Data Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs sm:text-sm" id="kelompok-table">
        <!-- Table Header (matching SVG: bg #EBF6E0, text #4A6030, uppercase) -->
        <thead>
          <tr class="bg-[#EBF6E0] border-b border-[#E4F0D6] text-[#4A6030] font-bold tracking-wider uppercase text-[11px]">
            <th class="py-3.5 px-4 text-center w-12">No</th>
            <th class="py-3.5 px-4">Nama Kelompok</th>
            <th class="py-3.5 px-4">Alamat</th>
            <th class="py-3.5 px-4 text-center sm:text-left">Status</th>
            <th class="py-3.5 px-4 text-center w-28">Aksi</th>
          </tr>
        </thead>

        <!-- Table Body (Loaded from PostgreSQL Database) -->
        <tbody class="divide-y divide-[#F0F7E8]" id="table-body">
          @forelse($kelompokList as $index => $item)
            <tr class="table-row-item hover:bg-[#F5F8F1]/60 transition-colors"
                data-id="{{ $item->id_kelompok }}"
                data-nama="{{ $item->nama_kelompok }}"
                data-alamat="{{ $item->alamat }}"
                data-status="{{ $item->status }}"
                data-created="{{ $item->created_at ? $item->created_at->translatedFormat('d F Y, H:i') : '-' }}">
              
              <!-- NO -->
              <td class="py-3.5 px-4 text-center font-medium text-[#9AB880] row-number">
                {{ $loop->iteration }}
              </td>

              <!-- NAMA KELOMPOK -->
              <td class="py-3.5 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center font-bold text-xs shrink-0">
                    <i data-lucide="users" class="w-4 h-4"></i>
                  </div>
                  <div>
                    <span class="font-bold text-[#1A2D10] block item-nama">{{ $item->nama_kelompok }}</span>
                  </div>
                </div>
              </td>

              <!-- ALAMAT -->
              <td class="py-3.5 px-4 text-[#4A6030] item-alamat max-w-xs truncate" title="{{ $item->alamat }}">
                {{ $item->alamat }}
              </td>

              <!-- STATUS -->
              <td class="py-3.5 px-4 item-status">
                @if($item->status === 'Aktif')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#EBF6E0] text-[#4D9830] border border-[#C5DFB0]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#4D9830]"></span>
                    Aktif
                  </span>
                @else
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    Tidak Aktif
                  </span>
                @endif
              </td>

              <!-- AKSI -->
              <td class="py-3.5 px-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <!-- Detail Button -->
                  <button type="button" onclick="viewDetail(this)" title="Lihat Detail"
                    class="p-1.5 rounded-lg text-[#4D9830] hover:bg-[#EBF6E0] transition-colors cursor-pointer">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                  </button>

                  <!-- Edit Button -->
                  <button type="button" onclick="openModalEdit(this, '{{ $item->id_kelompok }}')" title="Edit Data"
                    class="p-1.5 rounded-lg text-[#4D9830] hover:bg-[#EBF6E0] transition-colors cursor-pointer">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                  </button>

                  <!-- Delete Button -->
                  <button type="button" onclick="confirmDelete('{{ $item->id_kelompok }}', '{{ addslashes($item->nama_kelompok) }}')" title="Hapus Data"
                    class="p-1.5 rounded-lg text-[#DC2626] hover:bg-red-50 transition-colors cursor-pointer">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr id="initial-empty-row">
              <td colspan="6" class="py-12 px-4 text-center text-[#9AB880]">
                <div class="w-12 h-12 mx-auto rounded-full bg-[#F5F8F1] text-[#9AB880] flex items-center justify-center mb-2">
                  <i data-lucide="inbox" class="w-6 h-6"></i>
                </div>
                <p class="font-bold text-[#1A2D10]">Belum ada data kelompok tani</p>
                <p class="text-xs mt-0.5">Klik tombol "+ Tambah Kelompok" di atas untuk menambahkan data baru.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Empty State Message when searching finds 0 results -->
    <div id="empty-state" class="hidden py-12 px-4 text-center">
      <div class="w-14 h-14 mx-auto rounded-full bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center mb-3">
        <i data-lucide="search-x" class="w-7 h-7"></i>
      </div>
      <h3 class="text-sm font-bold text-[#1A2D10]">Tidak ada data kelompok tani ditemukan</h3>
      <p class="text-xs text-[#9AB880] mt-1">Coba gunakan kata kunci pencarian atau filter status yang lain.</p>
      <button type="button" onclick="resetFilters()"
        class="mt-4 px-4 py-2 rounded-xl bg-[#EBF6E0] text-xs font-semibold text-[#4D9830] hover:bg-[#e0f1d2] transition-colors cursor-pointer">
        Reset Pencarian
      </button>
    </div>

    <!-- Table Pagination Footer -->
    <div class="p-4 sm:p-5 border-t border-[#F0F7E8] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs sm:text-sm text-[#4A6030]">
      <span id="pagination-info" class="font-medium text-[#9AB880]">
        Menampilkan <span class="font-bold text-[#1A2D10]">1</span> - <span id="showing-count" class="font-bold text-[#1A2D10]">{{ $kelompokList->count() }}</span> dari <span id="total-count" class="font-bold text-[#1A2D10]">{{ $kelompokList->count() }}</span> kelompok tani
      </span>

      <!-- Pagination Buttons -->
      <div class="flex items-center gap-1.5 self-end sm:self-auto">
        <button type="button" disabled
          class="px-3 py-1.5 rounded-lg border border-[#E4F0D6] bg-white text-slate-400 text-xs font-medium cursor-not-allowed">
          Sebelumnya
        </button>
        <button type="button"
          class="w-8 h-8 rounded-lg bg-[#4D9830] text-white text-xs font-bold flex items-center justify-center shadow-xs">
          1
        </button>
        <button type="button" disabled
          class="px-3 py-1.5 rounded-lg border border-[#E4F0D6] bg-white text-slate-400 text-xs font-medium cursor-not-allowed">
          Selanjutnya
        </button>
      </div>
    </div>

  </div>

</div>

<!-- ==================== 3. MODAL TAMBAH KELOMPOK TANI ==================== -->
<div id="modal-tambah" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs transition-opacity duration-300">
  <div class="w-full max-w-lg rounded-2xl bg-white border border-[#E4F0D6] shadow-xl overflow-hidden transform transition-transform duration-300 scale-95" id="modal-tambah-container">
    
    <!-- Modal Header -->
    <div class="p-5 border-b border-[#F0F7E8] flex items-center justify-between bg-[#F5F8F1]">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center font-bold">
          <i data-lucide="users" class="w-5 h-5"></i>
        </div>
        <div>
          <h3 class="text-base font-bold text-[#1A2D10]">Tambah Kelompok Tani</h3>
          <p class="text-xs text-[#9AB880]">Input data kelompok tani ke database</p>
        </div>
      </div>
      <button type="button" onclick="closeModalTambah()" class="p-1 text-[#9AB880] hover:text-slate-700 rounded-lg hover:bg-white transition-colors cursor-pointer">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- Modal Form Body (matching DB fields: nama_kelompok, alamat, status) -->
    <form action="{{ route('admin.kelompok.store') }}" method="POST" id="form-tambah-kelompok" class="p-5 space-y-4">
      @csrf

      @if($errors->any() && !old('_edit_mode'))
        <div id="kelompok-tambah-errors" class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs space-y-1">
          <div class="flex items-center gap-2 font-bold text-red-900">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
            <span>Gagal menyimpan data. Periksa kesalahan berikut:</span>
          </div>
          <ul class="list-disc list-inside pl-6 text-red-700 space-y-0.5 text-[11px]">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <!-- Nama Kelompok -->
      <div>
        <label for="input-nama" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">
          Nama Kelompok Tani <span class="text-red-500">*</span>
        </label>
        <input type="text" name="nama_kelompok" id="input-nama" required placeholder="Contoh: Kelompok Tani Maju Bersama"
          class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
      </div>

      <!-- Alamat Lengkap -->
      <div>
        <label for="input-alamat" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">
          Alamat Lengkap <span class="text-red-500">*</span>
        </label>
        <textarea name="alamat" id="input-alamat" required rows="3" placeholder="Contoh: Desa Sukamaju, RT 02 / RW 04, Kec. Ciawi, Bogor"
          class="w-full p-3.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all resize-none"></textarea>
      </div>

      <!-- Status -->
      <div>
        <label for="input-status" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">
          Status <span class="text-red-500">*</span>
        </label>
        <select name="status" id="input-status" required
          class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="Aktif" selected>Aktif</option>
          <option value="Tidak Aktif">Tidak Aktif</option>
        </select>
      </div>

      <!-- Modal Footer Action -->
      <div class="pt-4 border-t border-[#F0F7E8] flex items-center justify-end gap-2.5">
        <button type="button" onclick="closeModalTambah()"
          class="px-4 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs font-semibold text-[#4A6030] hover:bg-[#F5F8F1] transition-colors cursor-pointer">
          Batal
        </button>
        <button type="submit"
          class="px-4 py-2.5 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white text-xs font-bold shadow-sm transition-all cursor-pointer">
          Simpan Data
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ==================== 4. MODAL EDIT KELOMPOK TANI ==================== -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs transition-opacity duration-300">
  <div class="w-full max-w-lg rounded-2xl bg-white border border-[#E4F0D6] shadow-xl overflow-hidden transform transition-transform duration-300 scale-95" id="modal-edit-container">
    
    <!-- Modal Header -->
    <div class="p-5 border-b border-[#F0F7E8] flex items-center justify-between bg-[#F5F8F1]">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center font-bold">
          <i data-lucide="edit-3" class="w-5 h-5"></i>
        </div>
        <div>
          <h3 class="text-base font-bold text-[#1A2D10]">Edit Kelompok Tani</h3>
          <p class="text-xs text-[#9AB880]" id="edit-header-sub">Perbarui informasi kelompok tani</p>
        </div>
      </div>
      <button type="button" onclick="closeModalEdit()" class="p-1 text-[#9AB880] hover:text-slate-700 rounded-lg hover:bg-white transition-colors cursor-pointer">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- Modal Form Body -->
    <form id="form-edit-kelompok" method="POST" class="p-5 space-y-4">
      @csrf
      @method('PUT')
      <input type="hidden" name="_edit_mode" value="1">
      <input type="hidden" name="id_kelompok" id="edit-id-input" value="">

      @if($errors->any() && old('_edit_mode'))
        <div id="kelompok-edit-errors" class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs space-y-1">
          <div class="flex items-center gap-2 font-bold text-red-900">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
            <span>Gagal menyimpan data. Periksa kesalahan berikut:</span>
          </div>
          <ul class="list-disc list-inside pl-6 text-red-700 space-y-0.5 text-[11px]">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <!-- Nama Kelompok -->
      <div>
        <label for="edit-nama" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">
          Nama Kelompok Tani <span class="text-red-500">*</span>
        </label>
        <input type="text" name="nama_kelompok" id="edit-nama" required
          class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
      </div>

      <!-- Alamat Lengkap -->
      <div>
        <label for="edit-alamat" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">
          Alamat Lengkap <span class="text-red-500">*</span>
        </label>
        <textarea name="alamat" id="edit-alamat" required rows="3"
          class="w-full p-3.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all resize-none"></textarea>
      </div>

      <!-- Status -->
      <div>
        <label for="edit-status" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">
          Status <span class="text-red-500">*</span>
        </label>
        <select name="status" id="edit-status" required
          class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="Aktif">Aktif</option>
          <option value="Tidak Aktif">Tidak Aktif</option>
        </select>
      </div>

      <!-- Modal Footer Action -->
      <div class="pt-4 border-t border-[#F0F7E8] flex items-center justify-end gap-2.5">
        <button type="button" onclick="closeModalEdit()"
          class="px-4 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs font-semibold text-[#4A6030] hover:bg-[#F5F8F1] transition-colors cursor-pointer">
          Batal
        </button>
        <button type="submit"
          class="px-4 py-2.5 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white text-xs font-bold shadow-sm transition-all cursor-pointer">
          Perbarui Data
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ==================== 5. MODAL DETAIL KELOMPOK TANI ==================== -->
<div id="modal-detail" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs transition-opacity duration-300">
  <div class="w-full max-w-md rounded-2xl bg-white border border-[#E4F0D6] shadow-xl overflow-hidden transform transition-transform duration-300 scale-95" id="modal-detail-container">
    <div class="p-5 border-b border-[#F0F7E8] flex items-center justify-between bg-[#F5F8F1]">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center font-bold">
          <i data-lucide="info" class="w-5 h-5"></i>
        </div>
        <div>
          <h3 class="text-base font-bold text-[#1A2D10]">Detail Kelompok Tani</h3>
          <p class="text-xs text-[#9AB880]">Informasi lengkap kelompok binaan</p>
        </div>
      </div>
      <button type="button" onclick="closeModalDetail()" class="p-1 text-[#9AB880] hover:text-slate-700 rounded-lg hover:bg-white transition-colors cursor-pointer">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <div class="p-5 space-y-4 text-xs sm:text-sm">
      <div class="p-4 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6]">
        <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Nama Kelompok Tani</span>
        <h4 id="detail-nama" class="text-base font-bold text-[#1A2D10] mt-1">-</h4>
        <div class="mt-2.5">
          <span id="detail-status-badge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#EBF6E0] text-[#4D9830] border border-[#C5DFB0]">
            <span class="w-1.5 h-1.5 rounded-full bg-[#4D9830]"></span>
            Aktif
          </span>
        </div>
      </div>

      <div class="p-3.5 rounded-xl border border-[#E4F0D6] bg-white space-y-1">
        <span class="text-[11px] text-[#9AB880] block font-semibold uppercase tracking-wider">Alamat / Lokasi</span>
        <p id="detail-alamat" class="text-[#1A2D10] font-medium leading-relaxed">-</p>
      </div>

      <div class="p-3.5 rounded-xl border border-[#E4F0D6] bg-white space-y-1">
        <span class="text-[11px] text-[#9AB880] block font-semibold uppercase tracking-wider">Waktu Pendaftaran</span>
        <p id="detail-created" class="text-[#4A6030] font-medium">-</p>
      </div>

      <div class="pt-2 flex justify-end">
        <button type="button" onclick="closeModalDetail()"
          class="px-4 py-2 rounded-xl bg-[#4D9830] text-white text-xs font-bold hover:bg-[#3D8024] transition-colors cursor-pointer">
          Tutup
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ==================== 6. MODAL KONFIRMASI HAPUS ==================== -->
<div id="modal-delete" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs transition-opacity duration-300">
  <div class="w-full max-w-sm rounded-2xl bg-white border border-red-100 shadow-xl overflow-hidden p-6 text-center space-y-4 transform transition-transform duration-300 scale-95" id="modal-delete-container">
    <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
      <i data-lucide="alert-triangle" class="w-6 h-6"></i>
    </div>
    <div>
      <h3 class="text-base font-bold text-[#1A2D10]">Hapus Kelompok Tani?</h3>
      <p class="text-xs text-slate-500 mt-1">
        Anda akan menghapus kelompok <strong id="delete-item-name" class="text-red-600">...</strong>. Tindakan ini tidak dapat dibatalkan.
      </p>
    </div>
    <form id="form-delete-kelompok" method="POST" class="pt-2 flex items-center justify-center gap-2.5">
      @csrf
      @method('DELETE')
      <button type="button" onclick="closeModalDelete()"
        class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors cursor-pointer">
        Batal
      </button>
      <button type="submit"
        class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-sm transition-all cursor-pointer">
        Ya, Hapus
      </button>
    </form>
  </div>
</div>

@if($errors->any())
<input type="hidden" id="kelompok-has-errors" value="1">
<input type="hidden" id="kelompok-error-mode" value="{{ old('_edit_mode') ? 'edit' : 'tambah' }}">
<input type="hidden" id="kelompok-old-id" value="{{ old('id_kelompok', '') }}">
<input type="hidden" id="kelompok-old-nama" value="{{ old('nama_kelompok', '') }}">
<input type="hidden" id="kelompok-old-alamat" value="{{ old('alamat', '') }}">
<input type="hidden" id="kelompok-old-status" value="{{ old('status', 'Aktif') }}">
@endif

<!-- ==================== 7. TOAST NOTIFICATION ==================== -->
<div id="toast" class="fixed bottom-5 right-5 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none flex items-center gap-2.5 px-4 py-3 rounded-xl bg-[#1A2D10] text-white text-xs sm:text-sm font-semibold shadow-lg">
  <i data-lucide="check-circle" class="w-4 h-4 text-[#4D9830]"></i>
  <span id="toast-message">Operasi berhasil!</span>
</div>

<!-- ==================== 8. PAGE JAVASCRIPT ==================== -->
<script>
  // Filter Table function
  function filterTable() {
    const searchVal = document.getElementById('search-input').value.toLowerCase().trim();
    const statusVal = document.getElementById('filter-status').value;
    const rows = document.querySelectorAll('#table-body .table-row-item');
    let visibleCount = 0;

    rows.forEach(row => {
      const nama = (row.getAttribute('data-nama') || '').toLowerCase();
      const alamat = (row.getAttribute('data-alamat') || '').toLowerCase();
      const id = (row.getAttribute('data-id') || '').toLowerCase();
      const status = row.getAttribute('data-status') || '';

      const matchSearch = !searchVal || nama.includes(searchVal) || alamat.includes(searchVal) || id.includes(searchVal);
      const matchStatus = !statusVal || status === statusVal;

      if (matchSearch && matchStatus) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    // Update Counter & Pagination Info
    document.getElementById('counter-text').textContent = visibleCount;
    document.getElementById('showing-count').textContent = visibleCount;

    // Show/hide empty state
    const emptyState = document.getElementById('empty-state');
    if (visibleCount === 0 && rows.length > 0) {
      emptyState.classList.remove('hidden');
    } else {
      emptyState.classList.add('hidden');
    }

    // Re-index visible rows
    let currentNo = 1;
    rows.forEach(row => {
      if (row.style.display !== 'none') {
        const numCol = row.querySelector('.row-number');
        if (numCol) numCol.textContent = currentNo++;
      }
    });
  }

  // Reset Filters
  function resetFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('filter-status').value = '';
    filterTable();
  }

  // Modal Tambah
  function openModalTambah() {
    const errorEl = document.getElementById('kelompok-tambah-errors');
    if (errorEl) errorEl.classList.add('hidden');
    const modal = document.getElementById('modal-tambah');
    const container = document.getElementById('modal-tambah-container');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
      container.classList.remove('scale-95');
      container.classList.add('scale-100');
    }, 10);
    lucide.createIcons();
  }

  function closeModalTambah() {
    const modal = document.getElementById('modal-tambah');
    const container = document.getElementById('modal-tambah-container');
    container.classList.remove('scale-100');
    container.classList.add('scale-95');
    setTimeout(() => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }, 150);
  }

  // Modal Edit
  function openModalEdit(btn, explicitId = null) {
    const errorEl = document.getElementById('kelompok-edit-errors');
    if (errorEl) errorEl.classList.add('hidden');

    const row = btn ? btn.closest('tr') : null;
    const id = explicitId || (row ? row.getAttribute('data-id') : null);
    const nama = row ? (row.getAttribute('data-nama') || '') : '';
    const alamat = row ? (row.getAttribute('data-alamat') || '') : '';
    const status = row ? (row.getAttribute('data-status') || 'Aktif') : 'Aktif';

    const editBadge = document.getElementById('edit-id-badge');
    if (editBadge) editBadge.textContent = id || '';

    const editIdInput = document.getElementById('edit-id-input');
    if (editIdInput) editIdInput.value = id || '';

    const inputNama = document.getElementById('edit-nama');
    if (inputNama) inputNama.value = nama;

    const inputAlamat = document.getElementById('edit-alamat');
    if (inputAlamat) inputAlamat.value = alamat;

    const inputStatus = document.getElementById('edit-status');
    if (inputStatus) inputStatus.value = status;

    const form = document.getElementById('form-edit-kelompok');
    if (form && id) {
      form.action = "{{ url('admin/kelompok') }}/" + encodeURIComponent(id);
    }

    const modal = document.getElementById('modal-edit');
    const container = document.getElementById('modal-edit-container');
    if (modal && container) {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      setTimeout(() => {
        container.classList.remove('scale-95');
        container.classList.add('scale-100');
      }, 10);
    }
    if (window.lucide) lucide.createIcons();
  }

  function closeModalEdit() {
    const modal = document.getElementById('modal-edit');
    const container = document.getElementById('modal-edit-container');
    if (container) {
      container.classList.remove('scale-100');
      container.classList.add('scale-95');
    }
    setTimeout(() => {
      if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    }, 150);
  }

  // Modal Detail
  function viewDetail(btn) {
    const row = btn.closest('tr');
    const id = row.getAttribute('data-id') || '-';
    const nama = row.getAttribute('data-nama') || '-';
    const alamat = row.getAttribute('data-alamat') || '-';
    const status = row.getAttribute('data-status') || 'Aktif';
    const created = row.getAttribute('data-created') || '-';

    const detailNama = document.getElementById('detail-nama');
    if (detailNama) detailNama.textContent = nama;

    const detailAlamat = document.getElementById('detail-alamat');
    if (detailAlamat) detailAlamat.textContent = alamat;

    const detailCreated = document.getElementById('detail-created');
    if (detailCreated) detailCreated.textContent = created;

    const badge = document.getElementById('detail-status-badge');
    if (badge) {
      if (status === 'Aktif') {
        badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#EBF6E0] text-[#4D9830] border border-[#C5DFB0]';
        badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-[#4D9830]"></span>Aktif';
      } else {
        badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200';
        badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Tidak Aktif';
      }
    }

    const modal = document.getElementById('modal-detail');
    const container = document.getElementById('modal-detail-container');
    if (modal && container) {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      setTimeout(() => {
        container.classList.remove('scale-95');
        container.classList.add('scale-100');
      }, 10);
    }
    if (window.lucide) lucide.createIcons();
  }

  function closeModalDetail() {
    const modal = document.getElementById('modal-detail');
    const container = document.getElementById('modal-detail-container');
    container.classList.remove('scale-100');
    container.classList.add('scale-95');
    setTimeout(() => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }, 150);
  }

  // Modal Konfirmasi Hapus
  function confirmDelete(id, nama) {
    document.getElementById('delete-item-name').textContent = nama + ' (' + id + ')';
    const form = document.getElementById('form-delete-kelompok');
    form.action = "{{ url('admin/kelompok') }}/" + encodeURIComponent(id);

    const modal = document.getElementById('modal-delete');
    const container = document.getElementById('modal-delete-container');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
      container.classList.remove('scale-95');
      container.classList.add('scale-100');
    }, 10);
    lucide.createIcons();
  }

  function closeModalDelete() {
    const modal = document.getElementById('modal-delete');
    const container = document.getElementById('modal-delete-container');
    container.classList.remove('scale-100');
    container.classList.add('scale-95');
    setTimeout(() => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }, 150);
  }

  // Export / Print
  function exportData() {
    window.print();
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) {
      lucide.createIcons();
    }

    // Auto-reopen modal on validation error
    if (document.getElementById('kelompok-has-errors')) {
      const mode = document.getElementById('kelompok-error-mode')?.value;
      const oldNama = document.getElementById('kelompok-old-nama')?.value || '';
      const oldAlamat = document.getElementById('kelompok-old-alamat')?.value || '';
      const oldStatus = document.getElementById('kelompok-old-status')?.value || 'Aktif';

      if (mode === 'edit') {
        const oldId = document.getElementById('kelompok-old-id')?.value || '';
        // Prefill edit modal then open it
        const editNamaEl = document.getElementById('edit-nama');
        const editAlamatEl = document.getElementById('edit-alamat');
        const editStatusEl = document.getElementById('edit-status');
        const editIdInput = document.getElementById('edit-id-input');
        const editBadge = document.getElementById('edit-id-badge');
        const editForm = document.getElementById('form-edit-kelompok');
        if (editNamaEl) editNamaEl.value = oldNama;
        if (editAlamatEl) editAlamatEl.value = oldAlamat;
        if (editStatusEl) editStatusEl.value = oldStatus;
        if (editIdInput) editIdInput.value = oldId;
        if (editBadge) editBadge.textContent = oldId;
        if (editForm && oldId) {
          editForm.action = "{{ url('admin/kelompok') }}/" + encodeURIComponent(oldId);
        }
        const modal = document.getElementById('modal-edit');
        const container = document.getElementById('modal-edit-container');
        if (modal && container) {
          modal.classList.remove('hidden');
          modal.classList.add('flex');
          setTimeout(() => {
            container.classList.remove('scale-95');
            container.classList.add('scale-100');
          }, 10);
        }
      } else {
        // Prefill tambah modal then open it
        const inputNama = document.getElementById('input-nama');
        const inputAlamat = document.getElementById('input-alamat');
        const inputStatus = document.getElementById('input-status');
        if (inputNama) inputNama.value = oldNama;
        if (inputAlamat) inputAlamat.value = oldAlamat;
        if (inputStatus) inputStatus.value = oldStatus;
        const modal = document.getElementById('modal-tambah');
        const container = document.getElementById('modal-tambah-container');
        if (modal && container) {
          modal.classList.remove('hidden');
          modal.classList.add('flex');
          setTimeout(() => {
            container.classList.remove('scale-95');
            container.classList.add('scale-100');
          }, 10);
        }
      }
      if (window.lucide) lucide.createIcons();
    }

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
