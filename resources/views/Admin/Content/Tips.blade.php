@extends('Admin.Layout._layout')

@section('title', 'Manajemen Tips')

@php
  $total = $totalCount ?? count($tips);
  $publik = $publikCount ?? count($tips->where('status', 'Publik'));
  $draft = $draftCount ?? count($tips->where('status', 'Draft'));

  $categoryColorMap = [
    'Hama & Penyakit' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200/60', 'icon' => 'shield-alert'],
    'Irigasi & Air' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-200/60', 'icon' => 'droplet'],
    'Nutrisi & Pupuk' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200/60', 'icon' => 'sprout'],
    'Perawatan Tanaman' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200/60', 'icon' => 'leaf'],
    'Pasca Panen' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200/60', 'icon' => 'package'],
  ];
@endphp

@section('content')
<div class="space-y-5" data-tips-page>

  <!-- ==================== FLASH NOTIFICATIONS ==================== -->
  @if(session('success'))
    <div id="flash-banner" class="flex items-center justify-between p-4 rounded-2xl bg-[#EBF6E0] border border-[#C5DFB0] text-[#1A2D10] text-xs sm:text-sm shadow-xs transition-all animate-fadeIn">
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
        <span>Terjadi kesalahan saat memproses data:</span>
      </div>
      <ul class="list-disc list-inside pl-6 text-red-700 space-y-0.5 text-xs">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- ==================== 1. TOP HEADER & ACTION BUTTON ==================== -->
  <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex items-start gap-3">
      <!-- Icon Box (matching screenshot: soft green rounded-lg) -->
      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#EBF6E0] text-[#4D9830]">
        <i data-lucide="lightbulb" class="h-5 w-5"></i>
      </div>
      <div>
        <h1 class="text-xl font-extrabold tracking-tight text-[#1A2D10] sm:text-2xl">Manajemen Tips</h1>
        <p class="mt-1 text-xs font-medium text-[#9AB880]">Kelola tips praktis, panduan cepat, dan trik budidaya pertanian.</p>
      </div>
    </div>

    <!-- Tambah Tips Button (matching screenshot: green rounded-xl) -->
    <button type="button" onclick="openModalTambahTips()"
      class="inline-flex items-center justify-center gap-2 self-start rounded-xl bg-[#4D9830] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-[#3D8024] cursor-pointer active:scale-95">
      <i data-lucide="plus" class="h-4 w-4 stroke-[2.5]"></i>
      <span>Tambah Tips</span>
    </button>
  </div>

  <!-- ==================== 2. STAT CARDS / SUMMARY BADGES ==================== -->
  <div class="flex flex-wrap gap-2">
    <!-- Total Tips -->
    <div class="flex items-center gap-2 rounded-lg bg-[#FFF4D6] px-3.5 py-2.5 text-xs text-[#4A6030]">
      <strong id="stat-total-tips" class="text-lg leading-none text-[#D97706]">{{ $total }}</strong>
      <span>Total Tips</span>
    </div>

    <!-- Dipublikasi -->
    <div class="flex items-center gap-2 rounded-lg bg-[#DFF7E7] px-3.5 py-2.5 text-xs text-[#287442]">
      <strong id="stat-public-tips" class="text-lg leading-none text-[#237A3B]">{{ $publik }}</strong>
      <span>Dipublikasi</span>
    </div>

    <!-- Draft -->
    <div class="flex items-center gap-2 rounded-lg bg-[#FFF4D6] px-3.5 py-2.5 text-xs text-[#8A5A0A]">
      <strong id="stat-draft-tips" class="text-lg leading-none text-[#8A5A0A]">{{ $draft }}</strong>
      <span>Draft</span>
    </div>
  </div>

  <!-- ==================== 3. MAIN CARD: FILTER & TIPS TABLE ==================== -->
  <div class="overflow-hidden rounded-2xl border border-[#C5DFB0] bg-white shadow-2xs">
    
    <!-- Filter Toolbar -->
    <div class="flex flex-col gap-3 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between border-b border-[#F0F7E8]">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <!-- Search Input with icon -->
        <div class="relative">
          <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#9AB880]"></i>
          <input type="search" id="tips-search" placeholder="Cari judul tips..."
            class="h-9 w-full rounded-lg border border-[#C5DFB0] bg-white pl-9 pr-3 text-xs text-slate-700 outline-none transition focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 sm:w-56">
        </div>

        <!-- Filter Kategori Tips -->
        <select id="tips-filter-category"
          class="h-9 rounded-lg border border-[#C5DFB0] bg-white px-3 text-xs text-slate-700 outline-none focus:border-[#4D9830] transition cursor-pointer">
          <option value="">Semua Kategori</option>
          <option value="Budidaya Tanaman">Budidaya Tanaman</option>
          <option value="Hama & Penyakit">Hama & Penyakit</option>
          <option value="Irigasi & Air">Irigasi & Air</option>
          <option value="Nutrisi & Pupuk">Nutrisi & Pupuk</option>
          <option value="Perawatan Tanaman">Perawatan Tanaman</option>
          <option value="Panen & Pasca Panen">Panen & Pasca Panen</option>
        </select>

        <!-- Filter Status -->
        <select id="tips-filter-status"
          class="h-9 rounded-lg border border-[#C5DFB0] bg-white px-3 text-xs text-slate-700 outline-none focus:border-[#4D9830] transition cursor-pointer">
          <option value="">Semua Status</option>
          <option value="Publik">Publik</option>
          <option value="Draft">Draft</option>
        </select>
      </div>

      <!-- Live Count -->
      <span class="text-xs text-[#9AB880]">
        <strong id="tips-count" class="font-bold text-[#1A2D10]">{{ count($tips) }}</strong> tips
      </span>
    </div>

    <!-- Table Section specifically structured for agricultural tips -->
    <div class="overflow-x-auto">
      <table class="w-full min-w-[860px] border-collapse text-left text-xs" id="tips-table">
        <thead>
          <tr class="bg-[#EBF6E0] text-[10px] font-bold uppercase tracking-wider text-[#4A6030]">
            <th class="w-12 px-3 py-3 text-center">No</th>
            <th class="px-3 py-3">Judul & Inti Tips</th>
            <th class="w-36 px-3 py-3">Kategori</th>
            <th class="w-36 px-3 py-3">Komoditas</th>
            <th class="w-28 px-3 py-3">Gambar</th>
            <th class="w-24 px-3 py-3">Tanggal</th>
            <th class="w-20 px-3 py-3 text-center">Status</th>
            <th class="w-48 px-3 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody id="tips-tbody" class="divide-y divide-[#E4F0D6]/70 text-[#4A6030]">
          @forelse($tips as $index => $tip)
            @php
              $idTips = $tip->id_tips ?? $tip->id;
              $judul = $tip->judul ?? $tip->title;
              $kategori = $tip->kategori ?? $tip->category ?? 'Perawatan Tanaman';
              $komoditas = $tip->komoditas?->nama_komoditas ?? 'Semua Komoditas';
              $target = $tip->target ?? 'Semua Kelompok';
              $ringkasan = $tip->ringkasan ?? $tip->excerpt ?? 'Tips dan trik praktis untuk membantu produktivitas pertanian secara efisien.';
              $isi = $tip->isi ?? $tip->content ?? '';
              $gambar = $tip->gambar ?? $tip->image ?? 'tips-pertanian.jpg';
              $tanggal = $tip->date ?? (isset($tip->created_at) ? $tip->created_at->format('Y-m-d') : date('Y-m-d'));
              $status = $tip->status ?? 'Publik';

              $catStyle = $categoryColorMap[$kategori] ?? ['bg' => 'bg-[#EBF6E0]', 'text' => 'text-[#4D9830]', 'border' => 'border-[#C5DFB0]', 'icon' => 'lightbulb'];
              
              // Check if image file actually exists locally
              $hasLocalImage = false;
              $imageUrl = asset('images/Logo2.png');
              if (!empty($gambar)) {
                if (file_exists(public_path($gambar))) {
                  $hasLocalImage = true;
                  $imageUrl = asset($gambar);
                } elseif (file_exists(public_path('uploads/tips/' . $gambar))) {
                  $hasLocalImage = true;
                  $imageUrl = asset('uploads/tips/' . $gambar);
                }
              }
            @endphp
            <tr class="transition-colors hover:bg-[#F5F8F1]/60 tips-row"
                data-id="{{ $idTips }}"
                data-title="{{ strtolower($judul) }}"
                data-category="{{ $kategori }}"
                data-komoditas-id="{{ $tip->komoditas_id ?? '' }}"
                data-commodity="{{ strtolower($komoditas) }}"
                data-target="{{ $target }}"
                data-status="{{ $status }}"
                data-raw-title="{{ $judul }}"
                data-raw-excerpt="{{ $ringkasan }}"
                data-raw-content="{{ $isi }}"
                data-raw-image="{{ $gambar }}"
                data-image-url="{{ $imageUrl }}"
                data-raw-date="{{ $tanggal }}">
              
              <!-- No -->
              <td class="px-3 py-3.5 text-center text-[#9AB880] font-medium row-number">
                {{ $index + 1 }}
              </td>

              <!-- Judul & Inti Tips -->
              <td class="px-3 py-3.5">
                <div class="max-w-[340px]">
                  <p class="font-bold text-[#1A2D10] line-clamp-1 hover:text-[#4D9830] transition-colors cursor-pointer"
                     onclick="openModalDetailTips(this.closest('tr'))"
                     title="{{ $judul }}">
                    {{ $judul }}
                  </p>
                  <!-- Ringkasan Inti Tips -->
                  <p class="mt-1 line-clamp-1 text-[11px] text-[#6B7F5B] flex items-center gap-1" title="{{ $ringkasan }}">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-[#4D9830] shrink-0"></span>
                    <span>{{ $ringkasan }}</span>
                  </p>
                </div>
              </td>

              <!-- Kategori Tips -->
              <td class="px-3 py-3.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $catStyle['bg'] }} {{ $catStyle['text'] }} border {{ $catStyle['border'] }}">
                  <i data-lucide="{{ $catStyle['icon'] }}" class="h-3 w-3 shrink-0"></i>
                  <span>{{ $kategori }}</span>
                </span>
              </td>

              <!-- Komoditas -->
              <td class="px-3 py-3.5">
                <div class="flex items-center gap-1.5 text-[11px]">
                  <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#EBF6E0] text-[#4D9830]">
                    <i data-lucide="sprout" class="h-3.5 w-3.5"></i>
                  </span>
                  <span class="font-semibold text-[#1A2D10]">{{ $komoditas }}</span>
                </div>
              </td>

              <!-- Gambar (thumbnail or badge matching screenshot) -->
              <td class="px-3 py-3.5">
                <div class="flex items-center gap-2">
                  @if($hasLocalImage)
                    <img src="{{ $imageUrl }}" alt="{{ $judul }}" class="h-8 w-8 shrink-0 rounded-md object-cover border border-[#C5DFB0]">
                  @else
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-[#FFF4D6] text-[#D97706] border border-[#F6E0B0]">
                      <i data-lucide="image" class="h-4 w-4"></i>
                    </span>
                  @endif
                  <span class="max-w-16 truncate text-[10px] font-mono text-[#9AB880]" title="{{ basename($gambar) }}">
                    {{ basename($gambar) }}
                  </span>
                </div>
              </td>

              <!-- Tanggal -->
              <td class="whitespace-nowrap px-3 py-3.5 text-[#9AB880] font-mono text-[11px]">
                {{ $tanggal }}
              </td>

              <!-- Status -->
              <td class="px-3 py-3.5 text-center">
                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold {{ $status === 'Publik' ? 'bg-[#DFF7E7] text-[#287442]' : 'bg-[#FFF4B8] text-[#8A5A0A]' }}">
                  {{ $status }}
                </span>
              </td>

              <!-- Aksi -->
              <td class="px-3 py-3.5 text-center">
                <div class="flex items-center justify-center gap-1">
                  <!-- Detail Button -->
                  <button type="button"
                    onclick="openModalDetailTips(this.closest('tr'))"
                    class="inline-flex items-center gap-1 rounded-lg bg-[#EAF5DE] px-2.5 py-1.5 text-[10px] font-bold text-[#4D9830] hover:bg-[#DDF0CC] transition cursor-pointer"
                    title="Lihat Rincian Tips">
                    <i data-lucide="eye" class="h-3 w-3"></i>
                    <span>Detail</span>
                  </button>

                  <!-- Edit Button -->
                  <button type="button"
                    onclick="openModalEditTips(this.closest('tr'))"
                    class="inline-flex items-center gap-1 rounded-lg bg-[#FFF4B8] px-2.5 py-1.5 text-[10px] font-bold text-[#8A5A0A] hover:bg-[#FFEFA0] transition cursor-pointer"
                    title="Ubah Data Tips">
                    <i data-lucide="edit-3" class="h-3 w-3"></i>
                    <span>Edit</span>
                  </button>

                  <!-- Hapus Button -->
                  <button type="button"
                    onclick="openModalHapusTips(this.closest('tr'))"
                    class="inline-flex items-center gap-1 rounded-lg bg-[#FFE1E1] px-2.5 py-1.5 text-[10px] font-bold text-[#C24141] hover:bg-[#FFD2D2] transition cursor-pointer"
                    title="Hapus Tips">
                    <i data-lucide="trash-2" class="h-3 w-3"></i>
                    <span>Hapus</span>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr id="tips-empty-row">
              <td colspan="8" class="px-4 py-12 text-center text-xs text-[#9AB880]">
                <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-[#F5F8F1] text-[#9AB880]">
                  <i data-lucide="book-open" class="h-6 w-6"></i>
                </div>
                <p class="font-semibold text-slate-600">Belum ada tips pertanian yang tersimpan di database.</p>
                <p class="mt-1 text-[11px] text-[#9AB880]">Klik tombol "+ Tambah Tips" untuk membuat tips pertama.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>

      <!-- Empty State for JS Filter -->
      <div id="tips-empty" class="hidden px-4 py-12 text-center text-xs text-[#9AB880]">
        <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-[#F5F8F1] text-[#9AB880]">
          <i data-lucide="search-x" class="h-6 w-6"></i>
        </div>
        <p class="font-semibold text-slate-600">Tidak ada tips yang cocok dengan filter atau pencarian.</p>
        <p class="mt-1 text-[11px] text-[#9AB880]">Coba atur ulang kata kunci pencarian atau ubah filter status & kategori.</p>
        <button type="button" onclick="resetFilterTips()" class="mt-3 inline-flex items-center gap-1.5 rounded-lg border border-[#C5DFB0] bg-white px-3 py-1.5 text-xs font-semibold text-[#4D9830] hover:bg-[#EBF6E0] transition cursor-pointer">
          <i data-lucide="rotate-ccw" class="h-3.5 w-3.5"></i>
          <span>Reset Filter</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ==================== 4. MODAL TAMBAH TIPS (DATABASE STORE FORM) ==================== -->
<div id="modal-tambah-tips" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 transition-opacity">
  <div class="w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-[#E4F0D6] overflow-hidden flex flex-col max-h-[90vh]">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-[#F0F7E8] px-5 py-4 bg-[#F5F8F1]">
      <div class="flex items-center gap-2.5">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EBF6E0] text-[#4D9830]">
          <i data-lucide="lightbulb" class="h-4 w-4"></i>
        </div>
        <div>
          <h2 class="text-sm font-bold text-[#1A2D10]">Tambah Tips Pertanian Baru</h2>
          <p class="text-[11px] text-[#9AB880]">Buat tips praktis dan trik budidaya untuk petani</p>
        </div>
      </div>
      <button type="button" onclick="closeModal('modal-tambah-tips')" class="rounded-lg p-1 text-slate-400 hover:text-slate-700 hover:bg-slate-100 cursor-pointer">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>

    <!-- Form Body -->
    <form id="form-tambah-tips" action="{{ route('admin.edukasi.tips.store') }}" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-5 space-y-4 text-xs">
      @csrf

      <div>
        <label class="block font-semibold text-[#1A2D10] mb-1">Judul Tips <span class="text-red-500">*</span></label>
        <input type="text" name="judul" required placeholder="Contoh: 5 Tips Mengatasi Hama Wereng Alami"
          class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Kategori Tips <span class="text-red-500">*</span></label>
          <select name="kategori" required class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
            <option value="Budidaya Tanaman">Budidaya Tanaman</option>
            <option value="Hama & Penyakit">Hama & Penyakit</option>
            <option value="Irigasi & Air">Irigasi & Air</option>
            <option value="Nutrisi & Pupuk">Nutrisi & Pupuk</option>
            <option value="Perawatan Tanaman">Perawatan Tanaman</option>
            <option value="Panen & Pasca Panen">Panen & Pasca Panen</option>
          </select>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block font-semibold text-[#1A2D10]">Komoditas Sasaran <span class="text-red-500">*</span></label>
            <button type="button" onclick="openModalTambahKomoditas()" class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#4D9830] hover:text-[#3d7a26] transition-colors cursor-pointer">
              <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
              <span>+ Tambah Baru</span>
            </button>
          </div>
          <select id="tambah-commodity" name="komoditas_id" class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
            <option value="">Semua Komoditas (Umum)</option>
            @php
              $categoryIcons = [
                'Tanaman Pangan' => '🌾',
                'Hortikultura & Sayuran' => '🌶️',
                'Buah-buahan' => '🍉',
                'Perkebunan & Rempah' => '☕',
              ];
            @endphp
            @if(isset($commodities) && $commodities->isNotEmpty())
              @foreach($commodities as $catName => $items)
                <optgroup label="{{ ($categoryIcons[$catName] ?? '🌱') . ' ' . $catName }}" data-kategori="{{ $catName }}">
                  @foreach($items as $item)
                    <option value="{{ $item->id_komoditas }}">{{ $item->nama_komoditas }}</option>
                  @endforeach
                </optgroup>
              @endforeach
            @endif
          </select>
        </div>
      </div>

      <input type="hidden" name="target" value="Semua Kelompok">

      <div>
        <label class="block font-semibold text-[#1A2D10] mb-1">Ringkasan / Inti Tips (1 Kalimat Actionable) <span class="text-red-500">*</span></label>
        <textarea name="ringkasan" rows="2" required placeholder="Tuliskan rangkuman tips yang langsung bisa dipraktikkan petani..."
          class="w-full rounded-lg border border-[#C5DFB0] p-2.5 text-xs text-slate-800 outline-none focus:border-[#4D9830]"></textarea>
      </div>

      <div>
        <label class="block font-semibold text-[#1A2D10] mb-1">Langkah-langkah / Rincian Tips</label>
        <textarea name="isi" rows="4" required placeholder="1. Siapkan bahan...&#10;2. Aplikasikan di sore hari...&#10;3. Ulangi tiap 5 hari..."
          class="w-full rounded-lg border border-[#C5DFB0] p-2.5 text-xs text-slate-800 outline-none focus:border-[#4D9830]"></textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Upload File Gambar Ilustrasi</label>
          <input type="file" name="gambar_file" accept=".jpg,.jpeg,.png,.webp,.svg,image/*"
            class="w-full rounded-lg border border-[#C5DFB0] p-1.5 text-xs text-slate-700 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#EBF6E0] file:text-[#4D9830] hover:file:bg-[#dff0cc] cursor-pointer">
          <p class="mt-0.5 text-[10px] text-[#9AB880]">Format: JPG, JPEG, PNG, WEBP, SVG (Maks 2MB)</p>
        </div>

        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Status Publikasi</label>
          <select name="status" class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
            <option value="Publik">Publik</option>
            <option value="Draft">Draft</option>
          </select>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#F0F7E8]">
        <button type="button" onclick="closeModal('modal-tambah-tips')"
          class="rounded-xl border border-[#C5DFB0] px-4 py-2 text-xs font-semibold text-[#4A6030] hover:bg-[#F5F8F1] transition cursor-pointer">
          Batal
        </button>
        <button type="submit"
          class="inline-flex items-center gap-1.5 rounded-xl bg-[#4D9830] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#3D8024] transition cursor-pointer">
          <i data-lucide="check" class="h-3.5 w-3.5"></i>
          <span>Simpan Tips</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ==================== 5. MODAL DETAIL TIPS ==================== -->
<div id="modal-detail-tips" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 transition-opacity">
  <div class="w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-[#E4F0D6] overflow-hidden flex flex-col max-h-[90vh]">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-[#F0F7E8] px-5 py-4 bg-[#F5F8F1]">
      <div class="flex items-center gap-2">
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#EBF6E0] text-[#4D9830]">
          <i data-lucide="lightbulb" class="h-4 w-4"></i>
        </span>
        <span class="text-xs font-bold text-[#1A2D10]">Rincian Tips Pertanian</span>
      </div>
      <button type="button" onclick="closeModal('modal-detail-tips')" class="rounded-lg p-1 text-slate-400 hover:text-slate-700 hover:bg-slate-100 cursor-pointer">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="overflow-y-auto p-5 space-y-4 text-xs">
      <!-- Badges row -->
      <div class="flex flex-wrap items-center gap-2">
        <span id="detail-category-badge" class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-bold bg-[#EBF6E0] text-[#4D9830]">
          Hama & Penyakit
        </span>
        <span id="detail-status-badge" class="rounded-full px-2.5 py-0.5 text-[10px] font-bold bg-[#DFF7E7] text-[#287442]">
          Publik
        </span>
        <span class="text-[11px] text-[#9AB880] flex items-center gap-1 font-mono">
          <i data-lucide="calendar" class="h-3.5 w-3.5"></i>
          <span id="detail-date">2024-11-12</span>
        </span>
      </div>

      <!-- Title -->
      <h3 id="detail-title" class="text-base sm:text-lg font-extrabold text-[#1A2D10] leading-snug">
        Judul Tips
      </h3>

      <!-- Target & Komoditas Info -->
      <div class="flex flex-wrap gap-4 p-3 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6] text-[11px]">
        <div>
          <span class="text-[#9AB880] block text-[10px]">Komoditas Sasaran:</span>
          <strong id="detail-commodity" class="text-[#1A2D10] font-semibold">Padi Sawah</strong>
        </div>
        <div>
          <span class="text-[#9AB880] block text-[10px]">Berkas Gambar:</span>
          <strong id="detail-image" class="font-mono text-[#4A6030]">wereng.jpg</strong>
        </div>
      </div>

      <!-- Practical Excerpt / Callout -->
      <div class="p-3.5 rounded-xl bg-[#FFF9E6] border border-[#FFE7A3]">
        <div class="flex items-center gap-1.5 font-bold text-[#8A5A0A] text-xs mb-1">
          <i data-lucide="sparkles" class="h-3.5 w-3.5 text-[#D97706]"></i>
          <span>Inti Tips Praktis:</span>
        </div>
        <p id="detail-excerpt" class="text-[#4A6030] text-xs leading-relaxed"></p>
      </div>

      <!-- Steps / Practical Steps -->
      <div>
        <h4 class="font-bold text-[#1A2D10] text-xs mb-2 flex items-center gap-1.5">
          <i data-lucide="list-checks" class="h-4 w-4 text-[#4D9830]"></i>
          <span>Langkah-langkah & Panduan Aksi:</span>
        </h4>
        <div id="detail-content" class="whitespace-pre-line rounded-xl border border-[#E4F0D6] bg-white p-3.5 text-xs text-[#4A6030] leading-relaxed"></div>
      </div>
    </div>

    <!-- Footer -->
    <div class="flex items-center justify-end gap-2 border-t border-[#F0F7E8] px-5 py-3 bg-[#F5F8F1]">
      <button type="button" onclick="closeModal('modal-detail-tips')"
        class="rounded-xl border border-[#C5DFB0] bg-white px-4 py-2 text-xs font-semibold text-[#4A6030] hover:bg-[#EBF6E0] transition cursor-pointer">
        Tutup
      </button>
      <button type="button" id="detail-btn-edit"
        class="inline-flex items-center gap-1.5 rounded-xl bg-[#4D9830] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#3D8024] transition cursor-pointer">
        <i data-lucide="edit-3" class="h-3.5 w-3.5"></i>
        <span>Edit Tips Ini</span>
      </button>
    </div>
  </div>
</div>

<!-- ==================== 6. MODAL EDIT TIPS (DATABASE UPDATE FORM) ==================== -->
<div id="modal-edit-tips" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 transition-opacity">
  <div class="w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-[#E4F0D6] overflow-hidden flex flex-col max-h-[90vh]">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-[#F0F7E8] px-5 py-4 bg-[#F5F8F1]">
      <div class="flex items-center gap-2.5">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FFF4B8] text-[#8A5A0A]">
          <i data-lucide="edit-3" class="h-4 w-4"></i>
        </div>
        <div>
          <h2 class="text-sm font-bold text-[#1A2D10]">Edit Tips Pertanian</h2>
          <p class="text-[11px] text-[#9AB880]">Perbarui informasi dan materi tips pertanian</p>
        </div>
      </div>
      <button type="button" onclick="closeModal('modal-edit-tips')" class="rounded-lg p-1 text-slate-400 hover:text-slate-700 hover:bg-slate-100 cursor-pointer">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>

    <!-- Form Body -->
    <form id="form-edit-tips" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-5 space-y-4 text-xs">
      @csrf
      @method('PUT')

      <input type="hidden" id="edit-row-id" name="id_tips">

      <div>
        <label class="block font-semibold text-[#1A2D10] mb-1">Judul Tips <span class="text-red-500">*</span></label>
        <input type="text" id="edit-title" name="judul" required
          class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Kategori Tips <span class="text-red-500">*</span></label>
          <select id="edit-category" name="kategori" required class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
            <option value="Budidaya Tanaman">Budidaya Tanaman</option>
            <option value="Hama & Penyakit">Hama & Penyakit</option>
            <option value="Irigasi & Air">Irigasi & Air</option>
            <option value="Nutrisi & Pupuk">Nutrisi & Pupuk</option>
            <option value="Perawatan Tanaman">Perawatan Tanaman</option>
            <option value="Panen & Pasca Panen">Panen & Pasca Panen</option>
          </select>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block font-semibold text-[#1A2D10]">Komoditas Sasaran <span class="text-red-500">*</span></label>
            <button type="button" onclick="openModalTambahKomoditas()" class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#4D9830] hover:text-[#3d7a26] transition-colors cursor-pointer">
              <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
              <span>+ Tambah Baru</span>
            </button>
          </div>
          <select id="edit-commodity" name="komoditas_id" class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
            <option value="">Semua Komoditas (Umum)</option>
            @if(isset($commodities) && $commodities->isNotEmpty())
              @foreach($commodities as $catName => $items)
                <optgroup label="{{ ($categoryIcons[$catName] ?? '🌱') . ' ' . $catName }}" data-kategori="{{ $catName }}">
                  @foreach($items as $item)
                    <option value="{{ $item->id_komoditas }}">{{ $item->nama_komoditas }}</option>
                  @endforeach
                </optgroup>
              @endforeach
            @endif
          </select>
        </div>
      </div>

      <input type="hidden" id="edit-target" name="target" value="Semua Kelompok">

      <div>
        <label class="block font-semibold text-[#1A2D10] mb-1">Ringkasan / Inti Tips <span class="text-red-500">*</span></label>
        <textarea id="edit-excerpt" name="ringkasan" rows="2" required
          class="w-full rounded-lg border border-[#C5DFB0] p-2.5 text-xs text-slate-800 outline-none focus:border-[#4D9830]"></textarea>
      </div>

      <div>
        <label class="block font-semibold text-[#1A2D10] mb-1">Langkah-langkah / Rincian Tips</label>
        <textarea id="edit-content" name="isi" rows="4" required
          class="w-full rounded-lg border border-[#C5DFB0] p-2.5 text-xs text-slate-800 outline-none focus:border-[#4D9830]"></textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Upload File Gambar Baru</label>
          <input type="file" name="gambar_file" accept=".jpg,.jpeg,.png,.webp,.svg,image/*"
            class="w-full rounded-lg border border-[#C5DFB0] p-1.5 text-xs text-slate-700 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#FFF4B8] file:text-[#8A5A0A] hover:file:bg-[#ffeaa0] cursor-pointer">
          <p class="mt-0.5 text-[10px] text-[#9AB880]">Format: JPG, JPEG, PNG, WEBP, SVG (Maks 2MB). Biarkan kosong jika tidak ingin mengubah.</p>
        </div>

        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Status Publikasi</label>
          <select id="edit-status" name="status" class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
            <option value="Publik">Publik</option>
            <option value="Draft">Draft</option>
          </select>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#F0F7E8]">
        <button type="button" onclick="closeModal('modal-edit-tips')"
          class="rounded-xl border border-[#C5DFB0] px-4 py-2 text-xs font-semibold text-[#4A6030] hover:bg-[#F5F8F1] transition cursor-pointer">
          Batal
        </button>
        <button type="submit"
          class="inline-flex items-center gap-1.5 rounded-xl bg-[#4D9830] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#3D8024] transition cursor-pointer">
          <i data-lucide="check" class="h-3.5 w-3.5"></i>
          <span>Simpan Perubahan</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ==================== 7. MODAL HAPUS TIPS (DATABASE DELETE FORM) ==================== -->
<div id="modal-hapus-tips" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 transition-opacity">
  <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-red-100 overflow-hidden">
    <form id="form-hapus-tips" method="POST">
      @csrf
      @method('DELETE')

      <div class="p-5 text-center">
        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#FFE1E1] text-[#C24141]">
          <i data-lucide="trash-2" class="h-6 w-6"></i>
        </div>
        <h3 class="text-base font-bold text-[#1A2D10]">Hapus Tips Pertanian?</h3>
        <p class="mt-1 text-xs text-slate-500">
          Apakah Anda yakin ingin menghapus tips <span id="hapus-tips-title" class="font-bold text-[#1A2D10]"></span>? Tindakan ini akan menghapus data secara permanen dari database.
        </p>
      </div>
      <div class="flex items-center justify-center gap-2 border-t border-[#F0F7E8] bg-[#F5F8F1] px-5 py-3.5">
        <button type="button" onclick="closeModal('modal-hapus-tips')"
          class="rounded-xl border border-[#C5DFB0] bg-white px-4 py-2 text-xs font-semibold text-[#4A6030] hover:bg-slate-100 transition cursor-pointer">
          Batal
        </button>
        <button type="submit"
          class="inline-flex items-center gap-1.5 rounded-xl bg-[#C24141] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-700 transition cursor-pointer">
          <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
          <span>Ya, Hapus</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ==================== 8. MODAL TAMBAH KOMODITAS BARU (ADMIN PPL) ==================== -->
<div id="modal-tambah-komoditas" class="fixed inset-0 z-[60] hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 transition-opacity">
  <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-[#C5DFB0] overflow-hidden animate-in fade-in zoom-in-95 duration-200">
    <div class="flex items-center justify-between px-5 py-4 bg-[#F5F8F1] border-b border-[#C5DFB0]">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center">
          <i data-lucide="sprout" class="w-4 h-4"></i>
        </div>
        <div>
          <h3 class="font-bold text-sm text-[#1A2D10]">Tambah Tanaman Baru</h3>
          <p class="text-[11px] text-[#9AB880]">Daftarkan komoditas tanaman budidaya baru</p>
        </div>
      </div>
      <button type="button" onclick="closeModal('modal-tambah-komoditas')" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>

    <form id="form-tambah-komoditas" class="p-5 space-y-4 text-xs">
      @csrf
      <div id="komoditas-error-alert" class="hidden p-2.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs">
      </div>

      <div>
        <label class="block font-semibold text-[#1A2D10] mb-1">Nama Tanaman / Komoditas <span class="text-red-500">*</span></label>
        <input type="text" id="input-nama-komoditas" name="nama_komoditas" required placeholder="Contoh: Buncis, Selada, Vanili, dll"
          class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
      </div>

      <div>
        <label class="block font-semibold text-[#1A2D10] mb-1">Kelompok Tanaman <span class="text-red-500">*</span></label>
        <select id="input-kategori-komoditas" name="kategori" required class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
          <option value="Tanaman Pangan">🌾 Tanaman Pangan</option>
          <option value="Hortikultura & Sayuran" selected>🌶️ Hortikultura & Sayuran</option>
          <option value="Buah-buahan">🍉 Buah-buahan</option>
          <option value="Perkebunan & Rempah">☕ Perkebunan & Rempah</option>
        </select>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
        <button type="button" onclick="closeModal('modal-tambah-komoditas')"
          class="h-9 px-4 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium transition-colors cursor-pointer">
          Batal
        </button>
        <button type="submit" id="btn-submit-komoditas"
          class="h-9 px-4 rounded-lg bg-[#4D9830] hover:bg-[#3d7a26] text-white font-medium flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer">
          <i data-lucide="check" class="w-3.5 h-3.5"></i>
          <span>Simpan Tanaman</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ==================== 9. CLIENT-SIDE JAVASCRIPT ==================== -->
<script>
  (() => {
    const searchInput = document.getElementById('tips-search');
    const categoryFilter = document.getElementById('tips-filter-category');
    const statusFilter = document.getElementById('tips-filter-status');
    const tbody = document.getElementById('tips-tbody');
    const countEl = document.getElementById('tips-count');
    const emptyEl = document.getElementById('tips-empty');

    function refreshIcons() {
      if (window.lucide) {
        window.lucide.createIcons();
      }
    }

    // Instant client-side search & filtering
    function applyFilters() {
      const query = (searchInput.value || '').toLowerCase().trim();
      const selectedCategory = categoryFilter.value;
      const selectedStatus = statusFilter.value;

      const rows = tbody.querySelectorAll('.tips-row');
      let visibleCount = 0;

      rows.forEach(row => {
        const title = row.dataset.title || '';
        const excerpt = (row.dataset.rawExcerpt || '').toLowerCase();
        const commodity = (row.dataset.commodity || '').toLowerCase();
        const category = row.dataset.category || '';
        const status = row.dataset.status || '';

        const matchesQuery = !query || title.includes(query) || excerpt.includes(query) || commodity.includes(query);
        const matchesCategory = !selectedCategory || category === selectedCategory;
        const matchesStatus = !selectedStatus || status === selectedStatus;

        if (matchesQuery && matchesCategory && matchesStatus) {
          row.classList.remove('hidden');
          visibleCount++;
        } else {
          row.classList.add('hidden');
        }
      });

      if (countEl) countEl.textContent = visibleCount;
      if (emptyEl) {
        emptyEl.classList.toggle('hidden', visibleCount !== 0);
      }
    }

    window.resetFilterTips = function() {
      if (searchInput) searchInput.value = '';
      if (categoryFilter) categoryFilter.value = '';
      if (statusFilter) statusFilter.value = '';
      applyFilters();
    };

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (categoryFilter) categoryFilter.addEventListener('change', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);

    // Modal controls
    window.openModal = function(id) {
      const m = document.getElementById(id);
      if (m) {
        m.classList.remove('hidden');
        refreshIcons();
      }
    };

    window.closeModal = function(id) {
      const m = document.getElementById(id);
      if (m) m.classList.add('hidden');
    };

    // Close on backdrop click
    ['modal-tambah-tips', 'modal-detail-tips', 'modal-edit-tips', 'modal-hapus-tips', 'modal-tambah-komoditas'].forEach(id => {
      const modal = document.getElementById(id);
      if (modal) {
        modal.addEventListener('click', (e) => {
          if (e.target === modal) closeModal(id);
        });
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        ['modal-tambah-tips', 'modal-detail-tips', 'modal-edit-tips', 'modal-hapus-tips', 'modal-tambah-komoditas'].forEach(id => closeModal(id));
      }
    });

    // Open Detail Modal
    window.openModalDetailTips = function(row) {
      if (!row) return;
      document.getElementById('detail-title').textContent = row.dataset.rawTitle || '';
      document.getElementById('detail-category-badge').textContent = row.dataset.category || '';
      document.getElementById('detail-status-badge').textContent = row.dataset.status || '';
      document.getElementById('detail-date').textContent = row.dataset.rawDate || '';
      document.getElementById('detail-commodity').textContent = row.dataset.commodity || '';
      document.getElementById('detail-image').textContent = row.dataset.rawImage || '';
      document.getElementById('detail-excerpt').textContent = row.dataset.rawExcerpt || '';
      document.getElementById('detail-content').textContent = row.dataset.rawContent || 'Belum ada langkah rincian.';

      const editBtn = document.getElementById('detail-btn-edit');
      if (editBtn) {
        editBtn.onclick = () => {
          closeModal('modal-detail-tips');
          openModalEditTips(row);
        };
      }

      openModal('modal-detail-tips');
    };

    // Open Tambah Modal
    window.openModalTambahTips = function() {
      const form = document.getElementById('form-tambah-tips');
      if (form) {
        form.reset();
        const targetInput = form.querySelector('[name="target"]');
        if (targetInput) targetInput.value = 'Semua Kelompok';
      }
      openModal('modal-tambah-tips');
    };

    // Open Edit Modal & setup PUT action
    window.openModalEditTips = function(row) {
      if (!row) return;
      const rowId = row.dataset.id;
      const form = document.getElementById('form-edit-tips');
      if (form) {
        form.action = '{{ url("admin/edukasi/tips") }}/' + rowId;
      }

      document.getElementById('edit-row-id').value = rowId;
      document.getElementById('edit-title').value = row.dataset.rawTitle || '';
      document.getElementById('edit-category').value = row.dataset.category || 'Perawatan Tanaman';
      const editCommEl = document.getElementById('edit-commodity');
      if (editCommEl) {
        editCommEl.value = row.dataset.komoditasId || '';
      }
      document.getElementById('edit-target').value = 'Semua Kelompok';
      document.getElementById('edit-excerpt').value = row.dataset.rawExcerpt || '';
      document.getElementById('edit-content').value = row.dataset.rawContent || '';
      document.getElementById('edit-status').value = row.dataset.status || 'Publik';

      openModal('modal-edit-tips');
    };

    // Open Hapus Modal & setup DELETE action
    window.openModalHapusTips = function(row) {
      if (!row) return;
      const rowId = row.dataset.id;
      const form = document.getElementById('form-hapus-tips');
      if (form) {
        form.action = '{{ url("admin/edukasi/tips") }}/' + rowId;
      }

      document.getElementById('hapus-tips-title').textContent = `"${row.dataset.rawTitle}"`;
      openModal('modal-hapus-tips');
    };

    // Open Tambah Komoditas Modal
    window.openModalTambahKomoditas = function() {
      const form = document.getElementById('form-tambah-komoditas');
      if (form) form.reset();
      const errEl = document.getElementById('komoditas-error-alert');
      if (errEl) {
        errEl.classList.add('hidden');
        errEl.textContent = '';
      }
      openModal('modal-tambah-komoditas');
    };

    // Handle AJAX Submit Tambah Komoditas
    const formKomoditas = document.getElementById('form-tambah-komoditas');
    if (formKomoditas) {
      formKomoditas.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-komoditas');
        const errEl = document.getElementById('komoditas-error-alert');
        errEl.classList.add('hidden');
        errEl.textContent = '';
        btn.disabled = true;
        btn.innerHTML = '<span>Menyimpan...</span>';

        const formData = new FormData(formKomoditas);

        try {
          const response = await fetch('{{ route("admin.edukasi.komoditas.store") }}', {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
            },
            body: formData,
          });

          const result = await response.json();

          if (response.ok && result.success) {
            const newName = result.data.nama_komoditas;
            const newId = result.data.id_komoditas;
            const category = result.data.kategori;

            // Add option to both select elements (tambah & edit)
            ['tambah-commodity', 'edit-commodity'].forEach(selectId => {
              const selectEl = document.getElementById(selectId);
              if (selectEl) {
                let targetOptgroup = Array.from(selectEl.querySelectorAll('optgroup')).find(
                  og => (og.getAttribute('data-kategori') || og.label || '').includes(category)
                );
                if (!targetOptgroup) {
                  targetOptgroup = document.createElement('optgroup');
                  targetOptgroup.label = category;
                  targetOptgroup.setAttribute('data-kategori', category);
                  selectEl.appendChild(targetOptgroup);
                }
                const opt = document.createElement('option');
                opt.value = newId;
                opt.textContent = newName;
                targetOptgroup.appendChild(opt);

                // Auto select the newly created crop
                selectEl.value = newName;
              }
            });

            closeModal('modal-tambah-komoditas');
            formKomoditas.reset();
          } else {
            const errMsg = result.message || (result.errors ? Object.values(result.errors).flat().join(', ') : 'Gagal menyimpan komoditas.');
            errEl.textContent = errMsg;
            errEl.classList.remove('hidden');
          }
        } catch (err) {
          errEl.textContent = 'Terjadi kesalahan koneksi. Silakan coba lagi.';
          errEl.classList.remove('hidden');
        } finally {
          btn.disabled = false;
          btn.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5"></i><span>Simpan Tanaman</span>';
          refreshIcons();
        }
      });
    }

    refreshIcons();
  })();
</script>
@endsection
