@extends('Admin.Layout._layout')

@section('title', 'Manajemen Panduan')

@section('content')
@php
  $panduanModalData = $panduan->getCollection()->mapWithKeys(function ($item) {
      return [(string) $item->id_panduan => [
          'id' => $item->id_panduan,
          'judul' => $item->judul,
          'kategori' => $item->kategori,
          'komoditas' => $item->komoditas?->nama_komoditas ?? 'Semua Komoditas',
          'komoditas_id' => $item->komoditas_id,
          'ringkasan' => $item->ringkasan,
          'isi' => $item->isi,
          'tanggal' => $item->tanggal?->format('d M Y'),
          'created_at' => $item->created_at ? $item->created_at->format('d M Y') : '-',
          'status' => $item->status,
          'gambar' => $item->gambar ? asset($item->gambar) : null,
          'slug' => $item->slug,
          'author' => $item->author?->nama ?? '-',
          'update_url' => route('admin.edukasi.panduan.update', $item),
          'delete_url' => route('admin.edukasi.panduan.destroy', $item),
      ]];
  })->all();
@endphp

<div class="space-y-5">
  @if(session('success'))
    <div class="flex items-center gap-3 rounded-xl border border-[#C5DFB0] bg-[#EBF6E0] px-4 py-3 text-sm font-semibold text-[#3D8024]">
      <i data-lucide="circle-check" class="h-5 w-5 shrink-0"></i>
      <span>{{ session('success') }}</span>
    </div>
  @endif



  <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex items-start gap-3">
      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#EBF6E0] text-[#4D9830]">
        <i data-lucide="book-open" class="h-5 w-5"></i>
      </div>
      <div>
        <h1 class="text-xl font-extrabold tracking-tight text-[#1A2D10] sm:text-2xl">Manajemen Panduan</h1>
        <p class="mt-1 text-xs font-medium text-[#9AB880]">Kelola tips praktis, panduan cepat, dan trik budidaya pertanian.</p>
      </div>
    </div>

    <button type="button" id="btn-tambah-panduan"
      class="inline-flex items-center justify-center gap-2 self-start rounded-xl bg-[#4D9830] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-[#3D8024] cursor-pointer active:scale-95">
      <i data-lucide="plus" class="h-4 w-4 stroke-[2.5]"></i>
      <span>Tambah Panduan</span>
    </button>
  </div>

  <div class="flex flex-wrap gap-2">
    <div class="flex items-center gap-2 rounded-lg bg-[#FFF4D6] px-3.5 py-2.5 text-xs text-[#4A6030]">
      <strong class="text-lg leading-none text-[#D97706]">{{ $counts['total'] }}</strong>
      <span>Total Panduan</span>
    </div>
    <div class="flex items-center gap-2 rounded-lg bg-[#DFF7E7] px-3.5 py-2.5 text-xs text-[#287442]">
      <strong class="text-lg leading-none text-[#237A3B]">{{ $counts['publik'] }}</strong>
      <span>Publik</span>
    </div>
    <div class="flex items-center gap-2 rounded-lg bg-[#FFF4D6] px-3.5 py-2.5 text-xs text-[#8A5A0A]">
      <strong class="text-lg leading-none text-[#8A5A0A]">{{ $counts['draft'] }}</strong>
      <span>Draft</span>
    </div>
  </div>

  <div class="overflow-hidden rounded-2xl border border-[#C5DFB0] bg-white shadow-2xs">
    <!-- Filter Toolbar -->
    <form method="GET" action="{{ route('admin.edukasi.panduan') }}" class="flex flex-col gap-3 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between border-b border-[#F0F7E8]">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <div class="relative">
          <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#9AB880]"></i>
          <input type="search" name="search" value="{{ $search }}" placeholder="Cari judul panduan..."
            class="h-9 w-full rounded-lg border border-[#C5DFB0] bg-white pl-9 pr-3 text-xs text-slate-700 outline-none transition focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 sm:w-56">
        </div>

        <select name="kategori" onchange="this.form.submit()" class="h-9 rounded-lg border border-[#C5DFB0] bg-white px-3 text-xs text-slate-700 outline-none focus:border-[#4D9830] transition cursor-pointer">
          <option value="">Semua Kategori</option>
          @foreach($categories as $category)
            <option value="{{ $category }}" @selected($kategori === $category)>{{ $category }}</option>
          @endforeach
        </select>

        <select name="status" onchange="this.form.submit()" class="h-9 rounded-lg border border-[#C5DFB0] bg-white px-3 text-xs text-slate-700 outline-none focus:border-[#4D9830] transition cursor-pointer">
          <option value="">Semua Status</option>
          <option value="Publik" @selected($status === 'Publik')>Publik</option>
          <option value="Draft" @selected($status === 'Draft')>Draft</option>
        </select>
      </div>

      <span class="text-xs text-[#9AB880]">
        <strong class="font-bold text-[#1A2D10]">{{ $panduan->total() }}</strong> panduan
      </span>
    </form>

    <div class="overflow-x-auto">
      <table class="w-full min-w-[860px] border-collapse text-left text-xs" id="panduan-table">
        <thead>
          <tr class="bg-[#EBF6E0] text-[10px] font-bold uppercase tracking-wider text-[#4A6030]">
            <th class="w-12 px-3 py-3 text-center">No</th>
            <th class="px-3 py-3">Judul & Inti Panduan</th>
            <th class="w-36 px-3 py-3">Kategori</th>
            <th class="w-36 px-3 py-3">Komoditas</th>
            <th class="w-28 px-3 py-3">Gambar</th>
            <th class="w-36 px-3 py-3">Tanggal</th>
            <th class="w-20 px-3 py-3 text-center">Status</th>
            <th class="w-48 px-3 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E4F0D6]/70 text-[#4A6030]">
          @forelse($panduan as $index => $item)
            <tr class="transition-colors hover:bg-[#F5F8F1]/60 panduan-row"
                data-id="{{ $item->id_panduan }}"
                data-title="{{ strtolower($item->judul) }}"
                data-category="{{ $item->kategori }}"
                data-commodity="{{ strtolower($item->komoditas?->nama_komoditas ?? 'Semua Komoditas') }}"
                data-status="{{ $item->status }}"
                data-raw-title="{{ $item->judul }}"
                data-raw-excerpt="{{ $item->ringkasan }}"
                data-raw-content="{{ $item->isi }}"
                data-raw-image="{{ $item->gambar }}"
                data-image-url="{{ $item->gambar ? asset($item->gambar) : '' }}"
                data-raw-date="{{ $item->tanggal?->format('Y-m-d') }}">
              <td class="px-3 py-3.5 text-center text-[#9AB880] font-medium">{{ $panduan->firstItem() + $index }}</td>
              <td class="px-3 py-3.5">
                <div class="max-w-[340px]">
                  <p class="font-bold text-[#1A2D10] line-clamp-1 hover:text-[#4D9830] transition-colors cursor-pointer"
                     onclick="openPanduanDetailModal('{{ $item->id_panduan }}')"
                     title="{{ $item->judul }}">
                    {{ $item->judul }}
                  </p>
                  <p class="mt-1 line-clamp-1 text-[11px] text-[#6B7F5B] flex items-center gap-1" title="{{ $item->ringkasan }}">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-[#4D9830] shrink-0"></span>
                    <span>{{ $item->ringkasan }}</span>
                  </p>
                </div>
              </td>
              <td class="px-3 py-3.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-[#EBF6E0] text-[#4D9830] border border-[#C5DFB0]">
                  <i data-lucide="book-open" class="h-3 w-3 shrink-0"></i>
                  <span>{{ $item->kategori }}</span>
                </span>
              </td>
              <td class="px-3 py-3.5">
                <div class="flex items-center gap-1.5 text-[11px]">
                  <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#EBF6E0] text-[#4D9830]">
                    <i data-lucide="sprout" class="h-3.5 w-3.5"></i>
                  </span>
                  <span class="font-semibold text-[#1A2D10]">{{ $item->komoditas?->nama_komoditas ?? 'Semua Komoditas' }}</span>
                </div>
              </td>
              <td class="px-3 py-3.5">
                <div class="flex items-center gap-2">
                  @if($item->gambar)
                    <img src="{{ asset($item->gambar) }}" alt="{{ $item->judul }}" class="h-8 w-8 shrink-0 rounded-md object-cover border border-[#C5DFB0]">
                  @else
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-[#FFF4D6] text-[#D97706] border border-[#F6E0B0]">
                      <i data-lucide="image" class="h-4 w-4"></i>
                    </span>
                  @endif
                  <span class="max-w-16 truncate text-[10px] font-mono text-[#9AB880]" title="{{ basename($item->gambar ?? '') }}">
                    {{ $item->gambar ? basename($item->gambar) : '-' }}
                  </span>
                </div>
              </td>
              <td class="px-3 py-3.5">
                @if($item->tanggal)
                  <span class="inline-flex items-center gap-1.5 font-mono text-[11px] font-medium text-slate-700">
                    <i data-lucide="calendar" class="h-3 w-3 text-[#4D9830]"></i>
                    <span>{{ $item->tanggal->format('d M Y') }}</span>
                  </span>
                @else
                  <span class="font-mono text-[11px] text-[#9AB880]">-</span>
                @endif
              </td>
              <td class="px-3 py-3.5 text-center">
                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold {{ $item->status === 'Publik' ? 'bg-[#DFF7E7] text-[#287442]' : 'bg-[#FFF4B8] text-[#8A5A0A]' }}">
                  {{ ucfirst($item->status) }}
                </span>
              </td>
              <td class="px-3 py-3.5 text-center">
                <div class="flex items-center justify-center gap-1">
                  <button type="button" onclick="openPanduanDetailModal('{{ $item->id_panduan }}')"
                    class="inline-flex items-center gap-1 rounded-lg bg-[#EAF5DE] px-2.5 py-1.5 text-[10px] font-bold text-[#4D9830] hover:bg-[#DDF0CC] transition cursor-pointer"
                    title="Lihat Rincian Panduan">
                    <i data-lucide="eye" class="h-3 w-3"></i>
                    <span>Detail</span>
                  </button>
                  <button type="button" onclick="openPanduanEditModal('{{ $item->id_panduan }}')"
                    class="inline-flex items-center gap-1 rounded-lg bg-[#FFF4B8] px-2.5 py-1.5 text-[10px] font-bold text-[#8A5A0A] hover:bg-[#FFEFA0] transition cursor-pointer"
                    title="Ubah Data Panduan">
                    <i data-lucide="edit-3" class="h-3 w-3"></i>
                    <span>Edit</span>
                  </button>
                  <button type="button" onclick="openPanduanDeleteModal('{{ $item->id_panduan }}')"
                    class="inline-flex items-center gap-1 rounded-lg bg-[#FFE1E1] px-2.5 py-1.5 text-[10px] font-bold text-[#C24141] hover:bg-[#FFD2D2] transition cursor-pointer"
                    title="Hapus Panduan">
                    <i data-lucide="trash-2" class="h-3 w-3"></i>
                    <span>Hapus</span>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr id="panduan-empty-row">
              <td colspan="8" class="px-4 py-12 text-center text-xs text-[#9AB880]">
                <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-[#F5F8F1] text-[#9AB880]">
                  <i data-lucide="book-open" class="h-6 w-6"></i>
                </div>
                <p class="font-semibold text-slate-600">Belum ada panduan pertanian yang tersimpan di database.</p>
                <p class="mt-1 text-[11px] text-[#9AB880]">Klik tombol "+ Tambah Panduan" untuk membuat panduan pertama.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($panduan->hasPages())
      <div class="border-t border-[#F0F7E8] px-4 py-4">
        {{ $panduan->links() }}
      </div>
    @endif
  </div>
</div>

<!-- MODAL DETAIL PANDUAN -->
<div id="modal-detail-panduan" class="fixed inset-0 z-[85] hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 transition-opacity" aria-hidden="true">
  <div class="w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-[#E4F0D6] overflow-hidden flex flex-col max-h-[90vh]">
    <div class="flex items-center justify-between border-b border-[#E4F0D6] bg-[#EBF6E0] px-6 py-4">
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#EBF6E0] text-[#4D9830]">
          <i data-lucide="lightbulb" class="h-4 w-4"></i>
        </div>
        <div>
          <h3 class="text-base font-bold text-[#1A2D10]">Rincian Panduan</h3>
          <p class="text-xs text-[#9AB880]">Informasi lengkap panduan pertanian</p>
        </div>
      </div>
      <button type="button" onclick="closeDetailPanduanModal()" class="rounded-lg p-1 text-slate-400 transition-colors hover:bg-white hover:text-slate-700" aria-label="Tutup">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>

    <div class="overflow-y-auto p-5 space-y-4 text-xs">
      <div class="flex flex-wrap items-center gap-2">
        <span id="detail-panduan-kategori" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold bg-[#EBF6E0] text-[#4D9830]"></span>
        <span id="detail-panduan-status" class="rounded-full px-2.5 py-1 text-[10px] font-bold"></span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#F5F8F1] border border-[#E4F0D6] px-2.5 py-1 text-[10px] text-[#4A6030]">
          <i data-lucide="calendar" class="h-3 w-3 text-[#4D9830]"></i>
          <span class="font-medium text-[#6B7F5B]">Tanggal:</span>
          <span id="detail-panduan-tanggal" class="font-mono font-semibold text-[#1A2D10]">-</span>
        </span>
      </div>
      <h3 id="detail-panduan-judul" class="text-base sm:text-lg font-extrabold text-[#1A2D10] leading-snug"></h3>
      <div class="flex flex-wrap gap-4 p-3 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6] text-[11px]">
        <div><span class="text-[#9AB880] block text-[10px]">Komoditas Sasaran:</span><strong id="detail-panduan-komoditas" class="text-[#1A2D10] font-semibold"></strong></div>
        <div><span class="text-[#9AB880] block text-[10px]">Slug:</span><strong id="detail-panduan-slug" class="font-mono text-[#4A6030]"></strong></div>
        <div><span class="text-[#9AB880] block text-[10px]">Pembuat:</span><strong id="detail-panduan-author" class="text-[#1A2D10] font-semibold"></strong></div>
      </div>
      <div class="p-3.5 rounded-xl bg-[#FFF9E6] border border-[#FFE7A3]">
        <div class="flex items-center gap-1.5 font-bold text-[#8A5A0A] text-xs mb-1"><i data-lucide="sparkles" class="h-3.5 w-3.5 text-[#D97706]"></i><span>Inti Panduan Praktis:</span></div>
        <p id="detail-panduan-ringkasan" class="text-[#4A6030] text-xs leading-relaxed"></p>
      </div>
      <div><h4 class="font-bold text-[#1A2D10] text-xs mb-2 flex items-center gap-1.5"><i data-lucide="list-checks" class="h-4 w-4 text-[#4D9830]"></i><span>Langkah-langkah & Panduan Aksi:</span></h4><div id="detail-panduan-isi" class="whitespace-pre-line rounded-xl border border-[#E4F0D6] bg-white p-3.5 text-xs text-[#4A6030] leading-relaxed"></div></div>
    </div>

    <div class="flex items-center justify-end gap-2.5 border-t border-[#F0F7E8] bg-[#F5F8F1] px-5 py-3">
      <button type="button" onclick="closeDetailPanduanModal()" class="rounded-lg border border-[#C5DFB0] bg-white px-4 py-2.5 text-xs font-bold text-[#4A6030] hover:bg-[#F5F8F1] transition cursor-pointer">Tutup</button>
      <button type="button" id="detail-panduan-btn-edit" class="inline-flex items-center gap-1.5 rounded-xl bg-[#4D9830] px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-[#3D8024] transition cursor-pointer"><i data-lucide="edit-3" class="h-4 w-4"></i><span>Edit Panduan Ini</span></button>
    </div>
  </div>
</div>

<!-- Modal Form Tambah / Edit Panduan -->
<div id="modal-panduan-form" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 transition-opacity" aria-hidden="true">
  <div class="w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-[#E4F0D6] overflow-hidden flex flex-col max-h-[90vh]">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-[#F0F7E8] px-5 py-4 bg-[#F5F8F1]">
      <div class="flex items-center gap-2.5">
        <div id="panduan-modal-icon-box" class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EBF6E0] text-[#4D9830]">
          <i id="panduan-modal-icon" data-lucide="book-open" class="h-4 w-4"></i>
        </div>
        <div>
          <h3 id="modal-panduan-form-title" class="text-sm font-bold text-[#1A2D10]">Tambah Panduan Baru</h3>
          <p id="modal-panduan-form-subtitle" class="text-[11px] text-[#9AB880]">Simpan data panduan ke database</p>
        </div>
      </div>
      <button type="button" data-panduan-modal-close class="rounded-lg p-1 text-slate-400 hover:text-slate-700 hover:bg-slate-100 cursor-pointer">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>

    <!-- Form Body -->
    <form id="form-panduan-modal" method="POST" action="{{ route('admin.edukasi.panduan.store') }}" enctype="multipart/form-data" class="overflow-y-auto p-5 space-y-4 text-xs">
      @csrf
      <input type="hidden" name="_method" id="panduan-modal-method" value="POST">
      <input type="hidden" name="id_panduan" id="panduan-modal-id" value="">

      <div id="panduan-modal-errors" class="@if(!$errors->any()) hidden @endif p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs space-y-1">
        @if($errors->any())
          <div class="flex items-center gap-2 font-bold text-red-900">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
            <span>Gagal menyimpan data panduan. Periksa kesalahan berikut:</span>
          </div>
          <ul class="list-disc list-inside pl-6 text-red-700 space-y-0.5 text-[11px]">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        @endif
      </div>

      <div>
        <label class="block font-semibold text-[#1A2D10] mb-1">Judul Panduan <span class="text-red-500">*</span></label>
        <input id="panduan-modal-judul" name="judul" type="text" required minlength="5" maxlength="255" placeholder="Contoh: Panduan Budidaya Jagung Hibrida"
          class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Kategori Panduan <span class="text-red-500">*</span></label>
          <select id="panduan-modal-kategori" name="kategori" required
            class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
            @foreach($categories as $category)
              <option value="{{ $category }}">{{ $category }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block font-semibold text-[#1A2D10]">Komoditas Sasaran</label>
            <button type="button" id="btn-tambah-komoditas-panduan" class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#4D9830] hover:text-[#3d7a26] transition-colors cursor-pointer">
              <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
              <span>+ Tambah Baru</span>
            </button>
          </div>
          <select id="panduan-modal-komoditas" name="komoditas_id"
            class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
            <option value="">Semua Komoditas (Umum)</option>
            @php
              $categoryIcons = [
                'Tanaman Pangan' => '🌾',
                'Hortikultura & Sayuran' => '🌶️',
                'Buah-buahan' => '🍉',
                'Perkebunan & Rempah' => '☕',
              ];
            @endphp
            @foreach($commodities as $categoryName => $items)
              <optgroup label="{{ ($categoryIcons[$categoryName] ?? '🌱') . ' ' . $categoryName }}" data-kategori="{{ $categoryName }}">
                @foreach($items as $commodity)
                  <option value="{{ $commodity->id_komoditas }}">{{ $commodity->nama_komoditas }}</option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
        </div>
      </div>

      <input type="hidden" name="target" value="Semua Kelompok">

      <div>
        <div class="flex items-center justify-between gap-3 mb-1">
          <label class="block font-semibold text-[#1A2D10]">Ringkasan / Inti Panduan (1 Kalimat Actionable) <span class="text-red-500">*</span></label>
          <span id="panduan-ringkasan-count" class="text-[10px] text-[#9AB880]">0/1000</span>
        </div>
        <textarea id="panduan-modal-ringkasan" name="ringkasan" rows="2" required minlength="10" maxlength="1000" placeholder="Tuliskan rangkuman panduan yang langsung bisa dipraktikkan petani..."
          class="w-full rounded-lg border border-[#C5DFB0] p-2.5 text-xs text-slate-800 outline-none focus:border-[#4D9830]"></textarea>
      </div>

      <div>
        <div class="flex items-center justify-between gap-3 mb-1">
          <label class="block font-semibold text-[#1A2D10]">Langkah-langkah / Rincian Panduan <span class="text-red-500">*</span></label>
          <span id="panduan-isi-count" class="text-[10px] text-[#9AB880]">0 karakter</span>
        </div>
        <textarea id="panduan-modal-isi" name="isi" rows="4" required minlength="20" placeholder="1. Siapkan bahan...&#10;2. Ikuti langkah budidaya...&#10;3. Ulangi sesuai kebutuhan..."
          class="w-full rounded-lg border border-[#C5DFB0] p-2.5 text-xs text-slate-800 outline-none focus:border-[#4D9830]"></textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
        <label class="block font-semibold text-[#1A2D10] mb-1">Upload File Gambar Ilustrasi</label>
        <input id="panduan-modal-gambar" name="gambar" type="file" accept=".jpg,.jpeg,.png,.webp,.svg,image/*"
          class="w-full rounded-lg border border-[#C5DFB0] p-1.5 text-xs text-slate-700 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#EBF6E0] file:text-[#4D9830] hover:file:bg-[#dff0cc] cursor-pointer">
        <p class="mt-0.5 text-[10px] text-[#9AB880]">Format: JPG, JPEG, PNG, WEBP, SVG (Maks 2MB)</p>
        <div id="panduan-modal-current-image" class="mt-2 hidden items-center gap-2">
          <img id="panduan-modal-current-image-preview" src="" alt="Gambar panduan" class="h-12 w-16 rounded-lg object-cover border border-[#E4F0D6]">
          <span class="text-[10px] text-[#6B7F5B]">Gambar saat ini. Upload baru untuk mengganti.</span>
        </div>
        </div>
        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Status Publikasi <span class="text-red-500">*</span></label>
          <select id="panduan-modal-status" name="status" required class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
            <option value="Draft">Draft</option>
            <option value="Publik">Publik</option>
          </select>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#F0F7E8]">
        <button type="button" data-panduan-modal-close
          class="rounded-xl border border-[#C5DFB0] px-4 py-2 text-xs font-semibold text-[#4A6030] hover:bg-[#F5F8F1] transition cursor-pointer">
          Batal
        </button>
        <button type="submit" id="btn-submit-panduan-modal"
          class="inline-flex items-center gap-1.5 rounded-xl bg-[#4D9830] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#3D8024] transition cursor-pointer disabled:cursor-not-allowed disabled:opacity-60">
          <i data-lucide="check" class="h-3.5 w-3.5"></i>
          <span>Simpan Panduan</span>
        </button>
      </div>
    </form>
  </div>
</div>

<div id="modal-tambah-komoditas-panduan" class="fixed inset-0 z-[60] hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 transition-opacity">
  <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-[#C5DFB0] overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 bg-[#F5F8F1] border-b border-[#C5DFB0]">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center"><i data-lucide="sprout" class="w-4 h-4"></i></div>
        <div><h3 class="font-bold text-sm text-[#1A2D10]">Tambah Tanaman Baru</h3><p class="text-[11px] text-[#9AB880]">Daftarkan komoditas tanaman budidaya baru</p></div>
      </div>
      <button type="button" id="close-tambah-komoditas-panduan" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer"><i data-lucide="x" class="w-4 h-4"></i></button>
    </div>
    <form id="form-tambah-komoditas-panduan" class="p-5 space-y-4 text-xs">
      @csrf
      <div id="komoditas-panduan-error" class="hidden p-2.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs"></div>
      <div>
        <label class="block font-semibold text-[#1A2D10] mb-1">Nama Tanaman / Komoditas <span class="text-red-500">*</span></label>
        <input type="text" name="nama_komoditas" required
          pattern="[a-zA-ZÀ-öø-ÿ\-\/]+(\s[a-zA-ZÀ-öø-ÿ\-\/]+)*"
          title="Nama komoditas hanya boleh berisi huruf, tanda hubung (-), dan garis miring (/)."
          placeholder="Contoh: Padi Sawah, Cabai/Lombok, Kacang-kacangan"
          oninput="this.value = this.value.replace(/[^a-zA-ZÀ-öø-ÿ\s\-\/]/g, '')"
          class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
        <p class="mt-1 text-[10px] text-[#9AB880]">Hanya huruf, tanda hubung <span class="font-mono font-bold">-</span>, dan garis miring <span class="font-mono font-bold">/</span> yang diperbolehkan.</p>
      </div>
      <div>
        <label class="block font-semibold text-[#1A2D10] mb-1">Kelompok Tanaman <span class="text-red-500">*</span></label>
        <select name="kategori" required class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
          <option value="Tanaman Pangan">🌾 Tanaman Pangan</option>
          <option value="Hortikultura & Sayuran" selected>🌶️ Hortikultura & Sayuran</option>
          <option value="Buah-buahan">🍉 Buah-buahan</option>
          <option value="Perkebunan & Rempah">☕ Perkebunan & Rempah</option>
        </select>
      </div>
      <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
        <button type="button" id="cancel-tambah-komoditas-panduan" class="h-9 px-4 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium cursor-pointer">Batal</button>
        <button type="submit" id="submit-tambah-komoditas-panduan" class="h-9 px-4 rounded-lg bg-[#4D9830] hover:bg-[#3d7a26] text-white font-medium flex items-center gap-1.5 cursor-pointer"><i data-lucide="check" class="w-3.5 h-3.5"></i><span>Simpan Tanaman</span></button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Konfirmasi Hapus Panduan -->
<div id="modal-delete-panduan" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 transition-opacity" aria-hidden="true">
  <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-red-100 overflow-hidden">
    <form id="form-delete-panduan" method="POST">
      @csrf
      @method('DELETE')

      <div class="p-5 text-center">
        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#FFE1E1] text-[#C24141]">
          <i data-lucide="trash-2" class="h-6 w-6"></i>
        </div>
        <h3 id="modal-delete-panduan-title" class="text-base font-bold text-[#1A2D10]">Hapus Data Panduan?</h3>
        <p class="mt-1 text-xs text-slate-500">
          Apakah Anda yakin ingin menghapus panduan <span id="delete-panduan-nama" class="font-bold text-[#1A2D10]"></span>?
        </p>
      </div>
      <div class="flex items-center justify-center gap-2 border-t border-[#F0F7E8] bg-[#F5F8F1] px-5 py-3.5">
        <button type="button" data-panduan-delete-close
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

<input type="hidden" id="panduan-store-url" value="{{ route('admin.edukasi.panduan.store') }}">
<input type="hidden" id="panduan-index-url" value="{{ route('admin.edukasi.panduan') }}">
@if($errors->any())
<input type="hidden" id="panduan-old-id" value="{{ old('id_panduan', '') }}">
<input type="hidden" id="panduan-old-judul" value="{{ old('judul', '') }}">
<input type="hidden" id="panduan-old-kategori" value="{{ old('kategori', '') }}">
<input type="hidden" id="panduan-old-komoditas" value="{{ old('komoditas_id', '') }}">
<input type="hidden" id="panduan-old-status" value="{{ old('status', 'Draft') }}">
<input type="hidden" id="panduan-old-ringkasan" value="{{ old('ringkasan', '') }}">
<input type="hidden" id="panduan-old-isi" value="{{ old('isi', '') }}">
<input type="hidden" id="panduan-has-errors" value="1">
<input type="hidden" id="panduan-error-messages" value="{{ json_encode($errors->all()) }}">
@endif
<script>
(() => {
  const data = @json($panduanModalData);
  const modal = document.getElementById('modal-panduan-form');
  const form = document.getElementById('form-panduan-modal');
  const methodInput = document.getElementById('panduan-modal-method');
  const title = document.getElementById('modal-panduan-form-title');
  const subtitle = document.getElementById('modal-panduan-form-subtitle');
  const submitText = document.querySelector('#btn-submit-panduan-modal span');
  const iconBox = document.getElementById('panduan-modal-icon-box');
  const icon = document.getElementById('panduan-modal-icon');
  const errorBox = document.getElementById('panduan-modal-errors');
  const imageBox = document.getElementById('panduan-modal-current-image');
  const imagePreview = document.getElementById('panduan-modal-current-image-preview');
  const deleteModal = document.getElementById('modal-delete-panduan');
  const deleteForm = document.getElementById('form-delete-panduan');
  const deleteName = document.getElementById('delete-panduan-nama');

  if (!modal || !form || !deleteModal || !deleteForm) return;

  const fields = {
    judul: document.getElementById('panduan-modal-judul'),
    kategori: document.getElementById('panduan-modal-kategori'),
    komoditas: document.getElementById('panduan-modal-komoditas'),
    status: document.getElementById('panduan-modal-status'),
    ringkasan: document.getElementById('panduan-modal-ringkasan'),
    isi: document.getElementById('panduan-modal-isi'),
    gambar: document.getElementById('panduan-modal-gambar'),
    summaryCount: document.getElementById('panduan-ringkasan-count'),
    contentCount: document.getElementById('panduan-isi-count'),
  };

  function updateCounters() {
    if (fields.summaryCount && fields.ringkasan) {
      fields.summaryCount.textContent = `${fields.ringkasan.value.length}/1000`;
    }
    if (fields.contentCount && fields.isi) {
      fields.contentCount.textContent = `${fields.isi.value.length} karakter`;
    }
  }

  function setErrors(messages) {
    if (!messages || !messages.length) {
      errorBox.classList.add('hidden');
      errorBox.innerHTML = '';
      return;
    }
    errorBox.innerHTML = `
      <div class="flex items-center gap-2 font-bold text-red-900">
        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
        <span>Gagal menyimpan data panduan. Periksa kesalahan berikut:</span>
      </div>
      <ul class="list-disc list-inside pl-6 text-red-700 space-y-0.5 text-[11px]"></ul>
    `;
    const list = errorBox.querySelector('ul');
    messages.forEach(message => {
      const li = document.createElement('li');
      li.textContent = message;
      list.appendChild(li);
    });
    errorBox.classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
  }

  function resetFields(keepErrors = false) {
    form.reset();
    const idInput = document.getElementById('panduan-modal-id');
    if (idInput) idInput.value = '';
    methodInput.value = 'POST';
    form.action = document.getElementById('panduan-store-url').value;
    fields.status.value = 'Draft';
    imageBox.classList.add('hidden');
    imageBox.classList.remove('flex');
    imagePreview.src = '';
    if (!keepErrors) setErrors([]);
    updateCounters();
  }

  function openModal() {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
    if (window.lucide) window.lucide.createIcons();
    setTimeout(() => fields.judul.focus(), 50);
  }

  function closeModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
  }

  window.openPanduanCreateModal = function (isAutoRestore = false) {
    resetFields(isAutoRestore);
    title.textContent = 'Tambah Data Panduan';
    subtitle.textContent = 'Simpan data panduan ke database';
    iconBox.className = 'flex h-8 w-8 items-center justify-center rounded-lg bg-[#EBF6E0] text-[#4D9830]';
    icon.setAttribute('data-lucide', 'book-open');
    submitText.textContent = 'Simpan Panduan';
    openModal();
  };

  document.getElementById('btn-tambah-panduan')?.addEventListener('click', window.openPanduanCreateModal);

  window.openPanduanEditModal = function (id, itemOverride = null, isAutoRestore = false) {
    const item = itemOverride || data[String(id)] || {
      id: id,
      update_url: '{{ url("admin/edukasi/panduan") }}/' + id,
      judul: '',
      kategori: 'Budidaya Tanaman',
      komoditas_id: '',
      status: 'Draft',
      ringkasan: '',
      isi: '',
      gambar: null
    };

    form.reset();
    const idInput = document.getElementById('panduan-modal-id');
    if (idInput) idInput.value = id;
    methodInput.value = 'PUT';
    form.action = item.update_url;
    fields.judul.value = item.judul || '';
    fields.kategori.value = item.kategori || '';
    fields.komoditas.value = item.komoditas_id || '';
    fields.status.value = item.status || 'Draft';
    fields.ringkasan.value = item.ringkasan || '';
    fields.isi.value = item.isi || '';
    fields.gambar.value = '';
    if (!isAutoRestore) setErrors([]);
    updateCounters();

    title.textContent = 'Edit Data Panduan';
    subtitle.textContent = 'Perbarui data panduan yang tersimpan';
    iconBox.className = 'flex h-8 w-8 items-center justify-center rounded-lg bg-[#FFF4B8] text-[#8A5A0A]';
    icon.setAttribute('data-lucide', 'edit-3');
    submitText.textContent = 'Simpan Perubahan';

    if (item.gambar) {
      imagePreview.src = item.gambar;
      imageBox.classList.remove('hidden');
      imageBox.classList.add('flex');
    } else {
      imageBox.classList.add('hidden');
      imageBox.classList.remove('flex');
      imagePreview.src = '';
    }

    openModal();
  };

  window.openPanduanDeleteModal = function (id, itemOverride = null) {
    const item = itemOverride || data[String(id)];
    if (!item) return;

    deleteForm.action = item.delete_url;
    deleteName.textContent = item.judul || 'ini';
    deleteModal.classList.remove('hidden');
    deleteModal.classList.add('flex');
    deleteModal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
    if (window.lucide) window.lucide.createIcons();
  };

  // Detail Modal
  const detailModal = document.getElementById('modal-detail-panduan');
  let detailPanduanId = null;

  window.openPanduanDetailModal = function (id) {
    const item = data[String(id)];
    if (!item || !detailModal) return;

    detailPanduanId = id;
    // Populate data
    document.getElementById('detail-panduan-judul').textContent = item.judul || '';
    document.getElementById('detail-panduan-kategori').textContent = item.kategori || '';
    document.getElementById('detail-panduan-tanggal').textContent = item.tanggal || 'Draft';
    document.getElementById('detail-panduan-ringkasan').textContent = item.ringkasan || '';
    document.getElementById('detail-panduan-isi').textContent = item.isi || '';
    document.getElementById('detail-panduan-slug').textContent = item.slug || '-';
    document.getElementById('detail-panduan-author').textContent = item.author || '-';

    // Status badge
    const statusBadge = document.getElementById('detail-panduan-status');
    statusBadge.textContent = item.status === 'Publik' ? 'Publik' : 'Draft';
    statusBadge.className = 'rounded-full px-3 py-1 text-[10px] font-bold ' +
      (item.status === 'Publik' ? 'bg-[#DFF7E7] text-[#287442]' : 'bg-[#FFF4B8] text-[#8A5A0A]');

    // Komoditas
    document.getElementById('detail-panduan-komoditas').textContent = item.komoditas || 'Semua Komoditas';

    // Show modal
    detailModal.classList.remove('hidden');
    detailModal.classList.add('flex');
    detailModal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
    if (window.lucide) window.lucide.createIcons();
  };

  window.closeDetailPanduanModal = function () {
    if (!detailModal) return;
    detailModal.classList.add('hidden');
    detailModal.classList.remove('flex');
    detailModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
  };

  if (detailModal) {
    detailModal.addEventListener('click', (event) => {
      if (event.target === detailModal) window.closeDetailPanduanModal();
    });
  }

  document.getElementById('detail-panduan-btn-edit')?.addEventListener('click', () => {
    if (detailPanduanId) {
      window.closeDetailPanduanModal();
      window.openPanduanEditModal(detailPanduanId);
    }
  });

  function closeDeleteModal() {
    deleteModal.classList.add('hidden');
    deleteModal.classList.remove('flex');
    deleteModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
  }

  fields.ringkasan && fields.ringkasan.addEventListener('input', updateCounters);
  fields.isi && fields.isi.addEventListener('input', updateCounters);

  if (fields.gambar) {
    fields.gambar.addEventListener('change', function () {
      const file = this.files[0];
      if (!file) return;

      const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
      const fileExt = '.' + file.name.split('.').pop().toLowerCase();
      const allowedExts = ['.jpg', '.jpeg', '.png', '.webp', '.svg'];

      if (!allowedTypes.includes(file.type) && !allowedExts.includes(fileExt)) {
        alert('Format file tidak didukung! Format yang diperbolehkan hanya JPG, JPEG, PNG, WEBP, atau SVG.');
        this.value = '';
        return;
      }

      const maxSize = 2 * 1024 * 1024; // 2MB
      if (file.size > maxSize) {
        alert('Ukuran file gambar terlalu besar (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)! Maksimal ukuran yang diperbolehkan adalah 2 MB.');
        this.value = '';
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        imagePreview.src = e.target.result;
        imageBox.classList.remove('hidden');
        imageBox.classList.add('flex');
      };
      reader.readAsDataURL(file);
    });
  }

  document.querySelectorAll('[data-panduan-modal-close]').forEach(button => button.addEventListener('click', closeModal));
  modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
  document.querySelector('[data-panduan-delete-close]')?.addEventListener('click', closeDeleteModal);
  deleteModal.addEventListener('click', (e) => { if (e.target === deleteModal) closeDeleteModal(); });

  const commodityModal = document.getElementById('modal-tambah-komoditas-panduan');
  const commodityForm = document.getElementById('form-tambah-komoditas-panduan');
  const commodityError = document.getElementById('komoditas-panduan-error');
  const commoditySelect = document.getElementById('panduan-modal-komoditas');

  const closeCommodityModal = () => {
    commodityModal?.classList.add('hidden');
    commodityModal?.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  };

  document.getElementById('btn-tambah-komoditas-panduan')?.addEventListener('click', () => {
    commodityForm?.reset();
    commodityError?.classList.add('hidden');
    commodityModal?.classList.remove('hidden');
    commodityModal?.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    if (window.lucide) window.lucide.createIcons();
  });
  document.getElementById('close-tambah-komoditas-panduan')?.addEventListener('click', closeCommodityModal);
  document.getElementById('cancel-tambah-komoditas-panduan')?.addEventListener('click', closeCommodityModal);
  commodityModal?.addEventListener('click', (event) => {
    if (event.target === commodityModal) closeCommodityModal();
  });

  commodityForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const submitButton = document.getElementById('submit-tambah-komoditas-panduan');
    commodityError?.classList.add('hidden');
    submitButton.disabled = true;

    try {
      const response = await fetch('{{ route("admin.edukasi.komoditas.store") }}', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: new FormData(commodityForm),
      });
      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.message || (result.errors ? Object.values(result.errors).flat().join(', ') : 'Gagal menyimpan komoditas.'));
      }

      const newName = result.data.nama_komoditas;
      const newId = result.data.id_komoditas;
      const category = result.data.kategori;
      let optgroup = Array.from(commoditySelect.querySelectorAll('optgroup')).find((group) => (group.dataset.kategori || group.label || '').includes(category));
      if (!optgroup) {
        optgroup = document.createElement('optgroup');
        optgroup.label = category;
        optgroup.dataset.kategori = category;
        commoditySelect.appendChild(optgroup);
      }
      const option = document.createElement('option');
      option.value = newId;
      option.textContent = newName;
      optgroup.appendChild(option);
      commoditySelect.value = newId;
      commodityForm.reset();
      closeCommodityModal();
    } catch (error) {
      commodityError.textContent = error.message || 'Terjadi kesalahan koneksi.';
      commodityError.classList.remove('hidden');
    } finally {
      submitButton.disabled = false;
      if (window.lucide) window.lucide.createIcons();
    }
  });

  if (form) {
    form.addEventListener('submit', function() {
      const submitBtn = document.getElementById('btn-submit-panduan-modal');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
      }
    });
  }

  // Auto-restore old input on validation error redirect
  if (document.getElementById('panduan-has-errors')) {
    const oldId = document.getElementById('panduan-old-id')?.value;
    if (oldId) {
      window.openPanduanEditModal(oldId, null, true);
    } else {
      window.openPanduanCreateModal(true);
    }

    if (fields.judul) fields.judul.value = document.getElementById('panduan-old-judul')?.value || '';
    if (fields.kategori) fields.kategori.value = document.getElementById('panduan-old-kategori')?.value || 'Budidaya Tanaman';
    if (fields.komoditas) fields.komoditas.value = document.getElementById('panduan-old-komoditas')?.value || '';
    if (fields.status) fields.status.value = document.getElementById('panduan-old-status')?.value || 'Draft';
    if (fields.ringkasan) fields.ringkasan.value = document.getElementById('panduan-old-ringkasan')?.value || '';
    if (fields.isi) fields.isi.value = document.getElementById('panduan-old-isi')?.value || '';

    const errMessages = document.getElementById('panduan-error-messages')?.value;
    if (errMessages) {
      try {
        setErrors(JSON.parse(errMessages));
      } catch (e) {}
    }
  }

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    if (!modal.classList.contains('hidden')) closeModal();
    if (!deleteModal.classList.contains('hidden')) closeDeleteModal();
    if (detailModal && !detailModal.classList.contains('hidden')) window.closeDetailPanduanModal();
    if (commodityModal && !commodityModal.classList.contains('hidden')) closeCommodityModal();
  });

  if (window.lucide) window.lucide.createIcons();
})();
</script>

@endsection
