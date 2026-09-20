@extends('Pengurus.Layout._layout')

@section('title', 'Kelola Anggota')

@section('content')
<div class="space-y-6">

  @if(session('success'))
    <div id="flash-banner" class="flex items-center justify-between p-4 rounded-2xl bg-[#EBF6E0] border border-[#C5DFB0] text-[#1A2D10] text-xs sm:text-sm shadow-xs">
      <div class="flex items-center gap-3"><div class="w-8 h-8 rounded-xl bg-[#4D9830] text-white flex items-center justify-center shrink-0"><i data-lucide="check" class="w-4 h-4 stroke-[3]"></i></div><div><span class="font-bold">Berhasil!</span><span class="text-[#4A6030] ml-1">{{ session('success') }}</span></div></div>
      <button type="button" onclick="document.getElementById('flash-banner').remove()" class="p-1 text-[#4A6030] hover:text-[#1A2D10] rounded-lg hover:bg-[#dff0d0] cursor-pointer" aria-label="Tutup"><i data-lucide="x" class="w-4 h-4"></i></button>
    </div>
  @endif

  @if($errors->any())
    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm space-y-1"><div class="flex items-center gap-2 font-bold text-red-900"><i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i><span>Terjadi kesalahan saat menyimpan anggota:</span></div><ul class="list-disc list-inside pl-6 text-red-700 space-y-0.5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
  @endif

  <!-- ==================== 1. TOP HEADER & ACTION BUTTONS ==================== -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Kelola Anggota
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Anggota {{ $kelompok->nama_kelompok }}
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
      <button type="button" onclick="openModalTambahAnggota()"
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
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">-</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Data lahan menyusul</span>
      </div>
    </div>

    <!-- Card 4: Rata-rata Lahan -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
        <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Rata-rata Lahan</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ $kelompok->nama_kelompok }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Kelompok otomatis dari pengurus</span>
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
            <th class="py-3.5 px-4 text-center">Kelompok Tani</th>
            <th class="py-3.5 px-4 text-center">Status</th>
            <th class="py-3.5 px-4 text-center w-28">Aksi</th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="divide-y divide-[#E4F0D6]/60" id="anggota-body">
          @forelse($anggota as $index => $item)
            <tr class="hover:bg-[#F5F8F1]/50 transition-colors"
                data-search="{{ strtolower($item->nama.' '.($item->nik ?? '').' '.($item->no_telepon ?? '')) }}"
                data-status="{{ $item->status }}">
              <td class="py-3.5 px-4 text-center text-[#9AB880]">{{ $index + 1 }}</td>
              <td class="py-3.5 px-4 font-bold text-[#1A2D10]">{{ $item->nama }}<span class="block text-[10px] font-medium text-[#9AB880]">{{ $item->username }}</span></td>
              <td class="py-3.5 px-4 font-mono text-[11px] text-[#6B7F5B]">{{ $item->nik ?? '-' }}</td>
              <td class="py-3.5 px-4 text-[#4A6030]">{{ $item->no_telepon ?? '-' }}</td>
              <td class="py-3.5 px-4 max-w-40 truncate text-[#6B7F5B]">{{ $item->alamat ?? '-' }}</td>
              <td class="py-3.5 px-4 text-center"><span class="text-xs font-bold text-[#4D9830]">{{ $kelompok->nama_kelompok }}</span></td>
              <td class="py-3.5 px-4 text-center">
                <span class="inline-flex items-center gap-1 rounded-full bg-[#DFF7E7] px-2.5 py-1 text-[10px] font-bold text-[#287442]">
                  <span class="w-1.5 h-1.5 rounded-full bg-[#287442]"></span>
                  {{ $item->status }}
                </span>
              </td>
              <td class="py-3.5 px-4">
                <div class="flex gap-1 justify-center">
                  <button type="button" onclick="openDetailAnggota(this)" data-nama="{{ $item->nama }}" data-nik="{{ $item->nik ?? '-' }}" data-telepon="{{ $item->no_telepon ?? '-' }}" data-alamat="{{ $item->alamat ?? '-' }}" class="rounded-lg bg-[#EAF5DE] px-2.5 py-1.5 text-[10px] font-bold text-[#4D9830] hover:bg-[#DFF7E7] transition-colors cursor-pointer" title="Detail">
                    <i data-lucide="eye" class="inline h-3 w-3"></i>
                  </button>
                  <button type="button" onclick="openEditAnggota(this)" data-id="{{ $item->id_pengguna }}" data-nama="{{ $item->nama }}" data-nik="{{ $item->nik }}" data-username="{{ $item->username }}" data-email="{{ $item->email }}" data-telepon="{{ $item->no_telepon }}" data-alamat="{{ $item->alamat }}" data-status="{{ $item->status }}" class="rounded-lg bg-[#FFF4B8] px-2.5 py-1.5 text-[10px] font-bold text-[#8A5A0A] hover:bg-[#FFEF99] transition-colors cursor-pointer" title="Edit">
                    <i data-lucide="edit-3" class="inline h-3 w-3"></i>
                  </button>
                  <button type="button" onclick="confirmDeleteAnggota('{{ $item->id_pengguna }}', '{{ addslashes($item->nama) }}')" class="rounded-lg bg-[#FFE1E1] px-2.5 py-1.5 text-[10px] font-bold text-[#C24141] hover:bg-[#FFD0D0] transition-colors cursor-pointer" title="Hapus">
                    <i data-lucide="trash-2" class="inline h-3 w-3"></i>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="py-12 text-center text-sm text-[#9AB880]">Belum ada anggota di kelompok tani ini.</td></tr>
          @endforelse
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

<!-- ==================== 5. MODAL TAMBAH ANGGOTA ==================== -->
<div id="modal-tambah-anggota" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden items-center justify-center p-4">
  <div class="bg-white w-full max-w-xl rounded-[22px] border border-[#E4F0D6] shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
    <div class="px-6 py-4 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center"><i data-lucide="user-plus" class="w-4 h-4"></i></div>
        <div><h3 class="text-base font-bold text-[#1A2D10]">Tambah Data Anggota</h3><p class="text-xs text-[#9AB880]"></p></div>
      </div>
      <button type="button" onclick="closeModalTambahAnggota()" class="p-1 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-white cursor-pointer" aria-label="Tutup"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>

    <form action="{{ route('pengurus.anggota.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 text-xs sm:text-sm max-h-[80vh] overflow-y-auto">
      @csrf
      <input type="hidden" name="role" value="Anggota">

      <div>
        <label for="anggota-nama" class="block font-semibold text-[#1A2D10] mb-1">Nama Lengkap Anggota <span class="text-red-500">*</span></label>
        <input type="text" name="nama" id="anggota-nama" required pattern="[A-Za-zÀ-ÿ .'-]+" title="Nama hanya boleh berisi huruf, spasi, titik, apostrof, atau tanda hubung." placeholder="Contoh: Bpk. Sutrisno" value="{{ old('nama') }}" class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Kelompok Tani</label>
          <div class="flex items-center gap-2 h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1] text-[#4D9830] font-semibold"><i data-lucide="lock-keyhole" class="w-4 h-4"></i>{{ $kelompok->nama_kelompok }}</div>
          <p class="text-[10px] text-[#9AB880] mt-1">Otomatis mengikuti kelompok pengurus.</p>
        </div>
        <div>
          <label for="anggota-status" class="block font-semibold text-[#1A2D10] mb-1">Status Anggota <span class="text-red-500">*</span></label>
          <select name="status" id="anggota-status" required class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 cursor-pointer">
            <option value="Aktif" {{ old('status', 'Aktif') === 'Aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="Tidak Aktif" {{ old('status') === 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div><label for="anggota-nik" class="block font-semibold text-[#1A2D10] mb-1">NIK (16 Digit)</label><input type="text" name="nik" id="anggota-nik" inputmode="numeric" pattern="[0-9]{16}" minlength="16" maxlength="16" title="NIK harus tepat 16 digit angka." placeholder="3302xxxxxxxxxxxx" value="{{ old('nik') }}" class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"></div>
        <div><label for="anggota-telepon" class="block font-semibold text-[#1A2D10] mb-1">No. WhatsApp / HP</label><input type="tel" name="no_telepon" id="anggota-telepon" inputmode="numeric" pattern="[0-9]+" maxlength="15" title="Nomor HP hanya boleh berisi angka." placeholder="08xxxxxxxxxx" value="{{ old('no_telepon') }}" class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"></div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div><label for="anggota-username" class="block font-semibold text-[#1A2D10] mb-1">Username Login <span class="text-red-500">*</span></label><input type="text" name="username" id="anggota-username" required placeholder="sutrisno123" value="{{ old('username') }}" class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"></div>
        <div><label for="anggota-password" class="block font-semibold text-[#1A2D10] mb-1">Password Login <span class="text-red-500">*</span></label><input type="password" name="password" id="anggota-password" required placeholder="Minimal 6 karakter" class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"></div>
      </div>

      <div><label for="anggota-email" class="block font-semibold text-[#1A2D10] mb-1">Alamat Email</label><input type="email" name="email" id="anggota-email" pattern="[^@\s]+@[^@\s]+\.[^@\s]+" title="Email harus memiliki format seperti nama@domain.com." placeholder="anggota@pawontani.id" value="{{ old('email') }}" class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"></div>
      <div><label for="anggota-alamat" class="block font-semibold text-[#1A2D10] mb-1">Alamat Tempat Tinggal</label><textarea name="alamat" id="anggota-alamat" rows="2" placeholder="Dusun, RT/RW, Desa, Kecamatan..." class="w-full p-3 rounded-xl border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 resize-none">{{ old('alamat') }}</textarea></div>
      <div><label for="anggota-foto" class="block font-semibold text-[#1A2D10] mb-1">Foto Profil (Opsional)</label><input type="file" name="foto_profil" id="anggota-foto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#EBF6E0] file:text-[#4D9830] border border-[#C5DFB0] rounded-xl p-1 bg-white cursor-pointer"><p class="text-[11px] text-[#9AB880] mt-1">Format: JPG, PNG, WEBP (Maksimal 2MB).</p></div>

      <div class="pt-3 border-t border-[#E4F0D6] flex items-center justify-end gap-2.5"><button type="button" onclick="closeModalTambahAnggota()" class="px-4 py-2.5 rounded-xl border border-[#C5DFB0] text-[#4A6030] hover:bg-[#F5F8F1] font-semibold cursor-pointer">Batal</button><button type="submit" class="px-5 py-2.5 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white font-bold shadow-sm shadow-[#4D9830]/20 cursor-pointer">Simpan Anggota</button></div>
    </form>
  </div>
</div>

<div id="modal-detail-anggota" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden items-center justify-center p-4">
  <div class="bg-white w-full max-w-md rounded-[22px] border border-[#E4F0D6] shadow-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]"><h3 class="font-bold text-[#1A2D10]">Detail Anggota</h3><button type="button" onclick="closeModal('modal-detail-anggota')" class="p-1 text-slate-400 cursor-pointer" aria-label="Tutup"><i data-lucide="x" class="w-5 h-5"></i></button></div>
    <div class="p-6 space-y-3 text-sm"><div><span class="text-xs text-[#9AB880]">Nama</span><p id="detail-nama" class="font-bold text-[#1A2D10]"></p></div><div><span class="text-xs text-[#9AB880]">NIK</span><p id="detail-nik"></p></div><div><span class="text-xs text-[#9AB880]">Telepon</span><p id="detail-telepon"></p></div><div><span class="text-xs text-[#9AB880]">Alamat</span><p id="detail-alamat"></p></div><div><span class="text-xs text-[#9AB880]">Kelompok Tani</span><p class="font-semibold text-[#4D9830]">{{ $kelompok->nama_kelompok }}</p></div></div>
  </div>
</div>

<div id="modal-edit-anggota" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden items-center justify-center p-4">
  <div class="bg-white w-full max-w-xl rounded-[22px] border border-[#E4F0D6] shadow-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]"><div><h3 class="font-bold text-[#1A2D10]">Edit Data Anggota</h3><p class="text-xs text-[#9AB880]"></p></div><button type="button" onclick="closeModal('modal-edit-anggota')" class="p-1 text-slate-400 cursor-pointer" aria-label="Tutup"><i data-lucide="x" class="w-5 h-5"></i></button></div>
    <form id="form-edit-anggota" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 text-xs sm:text-sm max-h-[80vh] overflow-y-auto">
      @csrf @method('PUT')
      <div><label class="block font-semibold text-[#1A2D10] mb-1">Nama Lengkap <span class="text-red-500">*</span></label><input type="text" name="nama" id="edit-anggota-nama" required pattern="[A-Za-zÀ-ÿ .'-]+" title="Nama hanya boleh berisi huruf, spasi, titik, apostrof, atau tanda hubung." class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0]"></div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5"><div><label class="block font-semibold text-[#1A2D10] mb-1">NIK</label><input type="text" name="nik" id="edit-anggota-nik" inputmode="numeric" pattern="[0-9]{16}" minlength="16" maxlength="16" title="NIK harus tepat 16 digit angka." class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0]"></div><div><label class="block font-semibold text-[#1A2D10] mb-1">No. Telepon</label><input type="tel" name="no_telepon" id="edit-anggota-telepon" inputmode="numeric" pattern="[0-9]+" maxlength="15" title="Nomor HP hanya boleh berisi angka." class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0]"></div></div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5"><div><label class="block font-semibold text-[#1A2D10] mb-1">Username <span class="text-red-500">*</span></label><input type="text" name="username" id="edit-anggota-username" required class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0]"></div><div><label class="block font-semibold text-[#1A2D10] mb-1">Status <span class="text-red-500">*</span></label><select name="status" id="edit-anggota-status" required class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0]"><option>Aktif</option><option>Tidak Aktif</option></select></div></div>
      <div><label class="block font-semibold text-[#1A2D10] mb-1">Email</label><input type="email" name="email" id="edit-anggota-email" class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0]"></div><div><label class="block font-semibold text-[#1A2D10] mb-1">Alamat</label><textarea name="alamat" id="edit-anggota-alamat" rows="2" class="w-full p-3 rounded-xl border border-[#C5DFB0] resize-none"></textarea></div><div><label class="block font-semibold text-[#1A2D10] mb-1">Password Baru</label><input type="password" name="password" class="w-full h-10.5 px-3.5 rounded-xl border border-[#C5DFB0]" placeholder="Kosongkan jika tidak diubah"></div>
      <div class="flex justify-end gap-2 pt-3 border-t border-[#E4F0D6]"><button type="button" onclick="closeModal('modal-edit-anggota')" class="px-4 py-2.5 rounded-xl border border-[#C5DFB0] font-semibold cursor-pointer">Batal</button><button type="submit" class="px-5 py-2.5 rounded-xl bg-[#4D9830] text-white font-bold cursor-pointer">Simpan Perubahan</button></div>
    </form>
  </div>
</div>

<form id="form-delete-anggota" method="POST" class="hidden">@csrf @method('DELETE')</form>

<script>
  const anggotaModal = document.getElementById('modal-tambah-anggota');
  const openModalTambahAnggota = () => { anggotaModal.classList.remove('hidden'); anggotaModal.classList.add('flex'); };
  const closeModalTambahAnggota = () => { anggotaModal.classList.add('hidden'); anggotaModal.classList.remove('flex'); };
  anggotaModal?.addEventListener('click', (event) => { if (event.target === anggotaModal) closeModalTambahAnggota(); });

  const sanitizeInput = (selector, sanitizer, maxLength = null) => {
    document.querySelectorAll(selector).forEach((input) => {
      input.addEventListener('input', () => {
        input.value = sanitizer(input.value).slice(0, maxLength || input.value.length);
      });
    });
  };
  sanitizeInput('#anggota-nama, #edit-anggota-nama', (value) => value.replace(/[^\p{L}\s.'-]/gu, ''));
  sanitizeInput('#anggota-nik, #edit-anggota-nik', (value) => value.replace(/\D/g, ''), 16);
  sanitizeInput('#anggota-telepon, #edit-anggota-telepon', (value) => value.replace(/\D/g, ''), 15);
  const closeModal = (id) => { const modal = document.getElementById(id); modal.classList.add('hidden'); modal.classList.remove('flex'); };
  const openModal = (id) => { const modal = document.getElementById(id); modal.classList.remove('hidden'); modal.classList.add('flex'); };
  const openDetailAnggota = (button) => { ['nama', 'nik', 'telepon', 'alamat'].forEach((field) => { document.getElementById(`detail-${field}`).textContent = button.dataset[field]; }); openModal('modal-detail-anggota'); };
  const openEditAnggota = (button) => { const form = document.getElementById('form-edit-anggota'); form.action = `{{ url('/pengurus/anggota') }}/${encodeURIComponent(button.dataset.id)}`; [['nama', 'nama'], ['nik', 'nik'], ['username', 'username'], ['email', 'email'], ['telepon', 'telepon'], ['alamat', 'alamat'], ['status', 'status']].forEach(([field, input]) => { document.getElementById(`edit-anggota-${input}`).value = button.dataset[field] || ''; }); openModal('modal-edit-anggota'); };
  const confirmDeleteAnggota = (id, nama) => { if (confirm(`Hapus anggota ${nama}?`)) { const form = document.getElementById('form-delete-anggota'); form.action = `{{ url('/pengurus/anggota') }}/${encodeURIComponent(id)}`; form.submit(); } };
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
