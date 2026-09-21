@extends('Admin.Layout._layout')

@section('title', 'Manajemen Artikel')

@section('content')
<div class="space-y-6" data-article-admin-page>
  @if(session('success'))
    <div id="article-flash" class="flex items-start justify-between gap-4 rounded-2xl border border-[#C5DFB0] bg-[#EBF6E0] px-4 py-3 text-sm text-[#2F6D20]">
      <div class="flex items-start gap-2"><i data-lucide="check-circle-2" class="mt-0.5 h-4 w-4 shrink-0"></i><span>{{ session('success') }}</span></div>
      <button type="button" onclick="document.getElementById('article-flash')?.remove()" class="rounded-lg p-1 text-[#4A6030] hover:bg-white"><i data-lucide="x" class="h-4 w-4"></i></button>
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      <p class="font-bold">Periksa kembali data artikel.</p>
      <ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex items-start gap-3">
      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#EBF6E0] text-[#4D9830]">
        <i data-lucide="newspaper" class="h-5 w-5"></i>
      </div>
      <div>
        <h1 class="text-xl font-extrabold tracking-tight text-[#1A2D10] sm:text-2xl">Manajemen Artikel</h1>
        <p class="mt-1 text-xs font-medium text-[#9AB880]">Kelola tips praktis, panduan cepat, dan trik budidaya pertanian.</p>
      </div>
    </div>

    <button type="button" onclick="openArtikelModal()"
      class="inline-flex items-center justify-center gap-2 self-start rounded-xl bg-[#4D9830] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-[#3D8024] cursor-pointer active:scale-95">
      <i data-lucide="plus" class="h-4 w-4 stroke-[2.5]"></i>
      <span>Tambah Artikel</span>
    </button>
  </div>

  <div class="flex flex-wrap gap-2">
    <div class="flex items-center gap-2 rounded-lg bg-[#FFF4D6] px-3.5 py-2.5 text-xs text-[#4A6030]">
      <strong class="text-lg leading-none text-[#D97706]">{{ $stats['total'] }}</strong>
      <span>Total Artikel</span>
    </div>
    <div class="flex items-center gap-2 rounded-lg bg-[#DFF7E7] px-3.5 py-2.5 text-xs text-[#287442]">
      <strong class="text-lg leading-none text-[#237A3B]">{{ $stats['publik'] }}</strong>
      <span>Publik</span>
    </div>
    <div class="flex items-center gap-2 rounded-lg bg-[#FFF4D6] px-3.5 py-2.5 text-xs text-[#8A5A0A]">
      <strong class="text-lg leading-none text-[#8A5A0A]">{{ $stats['draft'] }}</strong>
      <span>Draft</span>
    </div>
  </div>

  <div class="overflow-hidden rounded-[20px] border border-[#E4F0D6] bg-white shadow-sm">
    <!-- Filter Toolbar -->
    <div class="flex flex-col gap-3 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between border-b border-[#F0F7E8]">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <div class="relative">
          <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#9AB880]"></i>
          <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari judul artikel..."
            class="h-9 w-full rounded-lg border border-[#C5DFB0] bg-white pl-9 pr-3 text-xs text-slate-700 outline-none transition focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 sm:w-56">
        </div>

        <select name="kategori" class="h-9 rounded-lg border border-[#C5DFB0] bg-white px-3 text-xs text-slate-700 outline-none focus:border-[#4D9830] transition cursor-pointer">
          <option value="">Semua Kategori</option>
          @foreach(($kategoriOptions ?? collect()) as $kategori)
            <option value="{{ $kategori }}" @selected(request('kategori') === $kategori)>{{ $kategori }}</option>
          @endforeach
        </select>

        <select name="status" class="h-9 rounded-lg border border-[#C5DFB0] bg-white px-3 text-xs text-slate-700 outline-none focus:border-[#4D9830] transition cursor-pointer">
          <option value="">Semua Status</option>
          <option value="Publik" @selected(request('status') === 'Publik')>Publik</option>
          <option value="Draft" @selected(request('status') === 'Draft')>Draft</option>
        </select>
      </div>

      <span class="text-xs text-[#9AB880]">
        <strong class="font-bold text-[#1A2D10]">{{ $artikelList->total() }}</strong> artikel
      </span>
    </div>

    @if($artikelList->count())
      <div class="overflow-x-auto">
        <table class="w-full min-w-[860px] border-collapse text-left text-xs" id="artikel-table">
          <thead>
            <tr class="bg-[#EBF6E0] text-[10px] font-bold uppercase tracking-wider text-[#4A6030]">
              <th class="w-12 px-3 py-3 text-center">No</th>
              <th class="px-3 py-3">Judul & Inti Artikel</th>
              <th class="w-36 px-3 py-3">Kategori</th>
              <th class="w-36 px-3 py-3">Komoditas</th>
              <th class="w-28 px-3 py-3">Gambar</th>
              <th class="w-28 px-3 py-3">Tanggal</th>
              <th class="w-20 px-3 py-3 text-center">Status</th>
              <th class="w-48 px-3 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E4F0D6]/70 text-[#4A6030]">
          @foreach($artikelList as $index => $artikel)
            <tr class="transition-colors hover:bg-[#F5F8F1]/60 artikel-row"
                data-id="{{ $artikel->id_artikel }}"
                data-title="{{ strtolower($artikel->judul) }}"
                data-category="{{ $artikel->kategori }}"
                data-commodity="{{ strtolower($artikel->komoditas) }}"
                data-status="{{ $artikel->status }}"
                data-raw-title="{{ $artikel->judul }}"
                data-raw-excerpt="{{ $artikel->ringkasan }}"
                data-raw-content="{{ $artikel->isi }}"
                data-raw-image="{{ $artikel->gambar }}"
                data-image-url="{{ $artikel->gambar ? asset($artikel->gambar) : '' }}"
                data-raw-date="{{ optional($artikel->tanggal)->format('Y-m-d') }}">
              <td class="px-3 py-3.5 text-center text-[#9AB880] font-medium">{{ $artikelList->firstItem() + $index }}</td>
              <td class="px-3 py-3.5">
                <div class="max-w-[340px]">
                  <p class="font-bold text-[#1A2D10] line-clamp-1 hover:text-[#4D9830] transition-colors cursor-pointer"
                     onclick="openDetailArtikelModal(this.closest('tr'))"
                     title="{{ $artikel->judul }}">
                    {{ $artikel->judul }}
                  </p>
                  <p class="mt-1 line-clamp-1 text-[11px] text-[#6B7F5B] flex items-center gap-1" title="{{ $artikel->ringkasan }}">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-[#4D9830] shrink-0"></span>
                    <span>{{ $artikel->ringkasan }}</span>
                  </p>
                </div>
              </td>
              <td class="px-3 py-3.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-[#EBF6E0] text-[#4D9830] border border-[#C5DFB0]">
                  <i data-lucide="newspaper" class="h-3 w-3 shrink-0"></i>
                  <span>{{ $artikel->kategori }}</span>
                </span>
              </td>
              <td class="px-3 py-3.5">
                <div class="flex items-center gap-1.5 text-[11px]">
                  <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#EBF6E0] text-[#4D9830]">
                    <i data-lucide="sprout" class="h-3.5 w-3.5"></i>
                  </span>
                  <span class="font-semibold text-[#1A2D10]">{{ $artikel->komoditas ?: '-' }}</span>
                </div>
              </td>
              <td class="px-3 py-3.5">
                <div class="flex items-center gap-2">
                  @if($artikel->gambar)
                    <img src="{{ asset($artikel->gambar) }}" alt="{{ $artikel->judul }}" class="h-8 w-8 shrink-0 rounded-md object-cover border border-[#C5DFB0]">
                  @else
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-[#FFF4D6] text-[#D97706] border border-[#F6E0B0]">
                      <i data-lucide="image" class="h-4 w-4"></i>
                    </span>
                  @endif
                  <span class="max-w-16 truncate text-[10px] font-mono text-[#9AB880]" title="{{ basename($artikel->gambar ?? '') }}">
                    {{ $artikel->gambar ? basename($artikel->gambar) : '-' }}
                  </span>
                </div>
              </td>
              <td class="whitespace-nowrap px-3 py-3.5 text-[#9AB880] font-mono text-[11px]">
                {{ optional($artikel->tanggal)->format('Y-m-d') ?: '-' }}
              </td>
              <td class="px-3 py-3.5 text-center">
                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold {{ $artikel->status === 'Publik' ? 'bg-[#DFF7E7] text-[#287442]' : 'bg-[#FFF4B8] text-[#8A5A0A]' }}">
                  {{ $artikel->status }}
                </span>
              </td>
              <td class="px-3 py-3.5 text-center">
                <div class="flex items-center justify-center gap-1">
                  <button type="button" onclick="openDetailArtikelModal(this.closest('tr'))"
                    class="inline-flex items-center gap-1 rounded-lg bg-[#EAF5DE] px-2.5 py-1.5 text-[10px] font-bold text-[#4D9830] hover:bg-[#DDF0CC] transition cursor-pointer"
                    title="Lihat Rincian Artikel">
                    <i data-lucide="eye" class="h-3 w-3"></i>
                    <span>Detail</span>
                  </button>
                  <button type="button" class="js-edit-artikel inline-flex items-center gap-1 rounded-lg bg-[#FFF4B8] px-2.5 py-1.5 text-[10px] font-bold text-[#8A5A0A] hover:bg-[#FFEFA0] transition cursor-pointer"
                    title="Ubah Data Artikel"
                    data-artikel="{{ json_encode([
                      'id' => $artikel->id_artikel,
                      'judul' => $artikel->judul,
                      'kategori' => $artikel->kategori,
                      'komoditas' => $artikel->komoditas,
                      'ringkasan' => $artikel->ringkasan,
                      'isi' => $artikel->isi,
                      'tanggal' => optional($artikel->tanggal)->format('Y-m-d'),
                      'status' => $artikel->status,
                      'gambar' => $artikel->gambar ? asset($artikel->gambar) : null,
                      'action' => route('admin.edukasi.artikel.update', $artikel),
                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}">
                    <i data-lucide="edit-3" class="h-3 w-3"></i>
                    <span>Edit</span>
                  </button>
                  <button type="button" class="js-delete-artikel inline-flex items-center gap-1 rounded-lg bg-[#FFE1E1] px-2.5 py-1.5 text-[10px] font-bold text-[#C24141] hover:bg-[#FFD2D2] transition cursor-pointer"
                    title="Hapus Artikel"
                    data-delete-action="{{ route('admin.edukasi.artikel.destroy', $artikel) }}" data-delete-title="{{ $artikel->judul }}">
                    <i data-lucide="trash-2" class="h-3 w-3"></i>
                    <span>Hapus</span>
                  </button>
                </div>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      <div class="border-t border-[#E4F0D6] px-4 py-3">{{ $artikelList->links() }}</div>
    @else
      <tr id="artikel-empty-row">
        <td colspan="8" class="px-4 py-12 text-center text-xs text-[#9AB880]">
          <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-[#F5F8F1] text-[#9AB880]">
            <i data-lucide="newspaper" class="h-6 w-6"></i>
          </div>
          <p class="font-semibold text-slate-600">Belum ada artikel pertanian yang tersimpan di database.</p>
          <p class="mt-1 text-[11px] text-[#9AB880]">Klik tombol "+ Tambah Artikel" untuk membuat artikel pertama.</p>
        </td>
      </tr>
    @endif
  </div>
</div>

<!-- MODAL TAMBAH / EDIT ARTIKEL: mengikuti pola modal Tips -->
<div id="modal-artikel" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 transition-opacity">
  <div id="modal-artikel-container" class="w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-[#E4F0D6] overflow-hidden flex flex-col max-h-[90vh]">
    <div class="flex items-center justify-between border-b border-[#F0F7E8] px-5 py-4 bg-[#F5F8F1]">
      <div class="flex items-center gap-2.5">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EBF6E0] text-[#4D9830]">
          <i id="artikel-modal-icon" data-lucide="file-plus-2" class="h-4 w-4"></i>
        </div>
        <div>
          <h3 id="artikel-modal-title" class="text-sm font-bold text-[#1A2D10]">Tambah Artikel</h3>
          <p id="artikel-modal-subtitle" class="text-[11px] text-[#9AB880]">Simpan artikel edukasi ke database</p>
        </div>
      </div>
      <button type="button" onclick="closeArtikelModal()" class="rounded-lg p-1 text-slate-400 hover:text-slate-700 hover:bg-slate-100 cursor-pointer">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>

    <form id="form-artikel-modal" action="{{ route('admin.edukasi.artikel.store') }}" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-5 space-y-4 text-xs">
      @csrf
      <input type="hidden" name="_method" id="artikel-form-method" value="POST">
      <input type="hidden" name="id_artikel" id="artikel-form-id" value="">

      <div>
        <label class="block font-semibold text-[#1A2D10] mb-1">Judul Artikel <span class="text-red-500">*</span></label>
        <input type="text" name="judul" id="artikel-judul" required maxlength="255" placeholder="Contoh: 5 Tips Mengatasi Hama Wereng Alami"
          class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Kategori <span class="text-red-500">*</span></label>
          <select name="kategori" id="artikel-kategori" required class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
            <option value="">Pilih Kategori</option>
            @foreach(($kategoriOptions ?? collect()) as $kategori)
              <option value="{{ $kategori }}">{{ $kategori }}</option>
            @endforeach
            <option value="__custom__">Kategori lainnya...</option>
          </select>
          <input type="text" id="artikel-kategori-custom" placeholder="Ketik kategori baru" class="mt-2 hidden h-9 w-full rounded-lg border border-[#C5DFB0] px-3 text-xs outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
        </div>
        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Komoditas</label>
          <select name="komoditas" id="artikel-komoditas" class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
            <option value="">Pilih Komoditas</option>
            @foreach(($komoditasOptions ?? collect()) as $komoditas)
              <option value="{{ $komoditas }}">{{ $komoditas }}</option>
            @endforeach
            <option value="__custom__">Komoditas lainnya...</option>
          </select>
          <input type="text" id="artikel-komoditas-custom" placeholder="Ketik komoditas baru" class="mt-2 hidden h-9 w-full rounded-lg border border-[#C5DFB0] px-3 text-xs outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Tanggal Publikasi</label>
          <input type="date" name="tanggal" id="artikel-tanggal" class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
        </div>
        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Status <span class="text-red-500">*</span></label>
          <select name="status" id="artikel-status" required class="w-full h-9 rounded-lg border border-[#C5DFB0] px-3 text-xs text-slate-800 outline-none focus:border-[#4D9830]">
            <option value="Draft">Draft</option>
            <option value="Publik">Publik</option>
          </select>
        </div>
      </div>

      <div>
        <div class="flex items-center justify-between gap-3 mb-1">
          <label class="block font-semibold text-[#1A2D10]">Ringkasan <span class="text-red-500">*</span></label>
          <span id="artikel-ringkasan-count" class="text-[10px] text-[#9AB880]">0/1000</span>
        </div>
        <textarea name="ringkasan" id="artikel-ringkasan" rows="2" required maxlength="1000" placeholder="Tuliskan rangkuman artikel yang langsung bisa dipraktikkan petani..."
          class="w-full rounded-lg border border-[#C5DFB0] p-2.5 text-xs text-slate-800 outline-none focus:border-[#4D9830]"></textarea>
      </div>

      <div>
        <div class="flex items-center justify-between gap-3 mb-1">
          <label class="block font-semibold text-[#1A2D10]">Isi Artikel <span class="text-red-500">*</span></label>
          <span id="artikel-isi-count" class="text-[10px] text-[#9AB880]">0 karakter</span>
        </div>
        <textarea name="isi" id="artikel-isi" rows="4" required placeholder="1. Siapkan bahan...&#10;2. Aplikasikan di sore hari...&#10;3. Ulangi tiap 5 hari..."
          class="w-full rounded-lg border border-[#C5DFB0] p-2.5 text-xs text-slate-800 outline-none focus:border-[#4D9830]"></textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-[#1A2D10] mb-1">Upload File Gambar Ilustrasi</label>
          <input type="file" name="gambar" id="artikel-gambar" accept=".jpg,.jpeg,.png,.webp,image/*"
            class="w-full rounded-lg border border-[#C5DFB0] p-1.5 text-xs text-slate-700 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#EBF6E0] file:text-[#4D9830] hover:file:bg-[#dff0cc] cursor-pointer">
          <p class="mt-0.5 text-[10px] text-[#9AB880]">Format: JPG, JPEG, PNG, WEBP (Maks 2MB)</p>
          <div id="artikel-current-image" class="mt-2 hidden items-center gap-2">
            <img id="artikel-current-image-preview" src="" alt="Gambar artikel" class="h-12 w-16 rounded-lg object-cover border border-[#E4F0D6]">
            <span class="text-[10px] text-[#6B7F5B]">Gambar saat ini. Upload baru untuk mengganti.</span>
          </div>
        </div>
        <div class="flex items-end">
          <div class="w-full rounded-lg border border-[#E4F0D6] bg-[#F5F8F1] px-3 py-2 text-[11px] leading-5 text-[#6B7F5B]">
            <span class="font-bold text-[#4A6030]">Catatan:</span> hanya artikel berstatus <b>Publik</b> yang tampil di halaman frontend.
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#F0F7E8]">
        <button type="button" onclick="closeArtikelModal()"
          class="rounded-xl border border-[#C5DFB0] px-4 py-2 text-xs font-semibold text-[#4A6030] hover:bg-[#F5F8F1] transition cursor-pointer">
          Batal
        </button>
        <button id="artikel-submit-button" type="submit"
          class="inline-flex items-center gap-1.5 rounded-xl bg-[#4D9830] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#3D8024] transition cursor-pointer">
          <i data-lucide="check" class="h-3.5 w-3.5"></i>
          <span>Simpan Artikel</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL KONFIRMASI HAPUS ARTIKEL -->
<div id="modal-delete-artikel" class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs">
  <div id="modal-delete-artikel-container" class="w-full max-w-md overflow-hidden rounded-[22px] border border-[#E4F0D6] bg-white shadow-2xl animate-in fade-in zoom-in-95 duration-200">
    <div class="border-b border-[#E4F0D6] bg-[#F5F8F1] px-5 py-4 sm:px-6">
      <div class="flex items-start gap-3">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-red-100 bg-red-50 text-red-600">
          <i data-lucide="alert-triangle" class="h-5 w-5"></i>
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="text-base font-bold text-[#1A2D10]">Hapus Artikel?</h3>
          <p class="mt-0.5 text-xs text-[#9AB880]">Konfirmasi penghapusan artikel dari sistem.</p>
        </div>
        <button type="button" onclick="closeDeleteArtikelModal()" class="rounded-lg p-1 text-slate-400 transition hover:bg-white hover:text-slate-700" aria-label="Tutup">
          <i data-lucide="x" class="h-5 w-5"></i>
        </button>
      </div>
    </div>

    <div class="px-5 py-5 sm:px-6">
      <p class="text-sm leading-6 text-slate-600">Apakah kamu yakin ingin menghapus artikel:</p>
      <div class="mt-3 rounded-xl border border-[#E4F0D6] bg-[#F5F8F1] px-3.5 py-3">
        <p id="delete-artikel-title" class="line-clamp-2 text-sm font-bold text-[#1A2D10]"></p>
      </div>
      <div class="mt-3 flex items-start gap-2 rounded-xl border border-red-100 bg-red-50/70 px-3 py-2.5 text-[11px] leading-5 text-red-700">
        <i data-lucide="info" class="mt-0.5 h-4 w-4 shrink-0 text-red-500"></i>
        <span>Tindakan ini tidak dapat dibatalkan. Data artikel dan gambar yang dikelola aplikasi akan dihapus.</span>
      </div>

      <form id="form-delete-artikel" method="POST" class="mt-5 flex items-center justify-end gap-2.5">
        @csrf
        @method('DELETE')
        <button type="button" onclick="closeDeleteArtikelModal()" class="rounded-xl border border-[#C5DFB0] bg-white px-4 py-2.5 text-xs font-semibold text-[#4A6030] transition hover:bg-[#F5F8F1]">Batal</button>
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-red-500/20 transition hover:bg-red-700">
          <i data-lucide="trash-2" class="h-4 w-4"></i>Ya, Hapus
        </button>
      </form>
    </div>
  </div>
</div>

<!-- MODAL DETAIL ARTIKEL -->
<div id="modal-detail-artikel" class="fixed inset-0 z-[85] hidden items-center justify-center bg-slate-900/50 p-3 backdrop-blur-xs sm:p-4">
  <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-[22px] border border-[#E4F0D6] bg-white shadow-2xl animate-in fade-in zoom-in-95 duration-200">
    <div class="flex shrink-0 items-center justify-between border-b border-[#E4F0D6] bg-[#F5F8F1] px-5 py-4 sm:px-6">
      <div class="flex items-center gap-2.5">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#EBF6E0] text-[#4D9830]"><i data-lucide="file-text" class="h-4 w-4"></i></div>
        <div>
          <h3 class="text-base font-bold text-[#1A2D10]">Detail Artikel</h3>
          <p class="text-xs text-[#9AB880]">Informasi lengkap artikel</p>
        </div>
      </div>
      <button type="button" onclick="closeDetailArtikelModal()" class="rounded-lg p-1 text-slate-400 transition-colors hover:bg-white hover:text-slate-700" aria-label="Tutup"><i data-lucide="x" class="h-5 w-5"></i></button>
    </div>

    <div class="min-h-0 overflow-y-auto p-5 sm:p-6">
      <!-- Gambar -->
      <div id="detail-artikel-image-container" class="mb-5 overflow-hidden rounded-2xl border border-[#E4F0D6] bg-[#F5F8F1]">
        <img id="detail-artikel-image" src="" alt="" class="h-56 w-full object-cover sm:h-64">
      </div>

      <!-- Header info -->
      <div class="mb-4 flex flex-wrap items-center gap-2">
        <span id="detail-artikel-kategori" class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-bold text-blue-700"></span>
        <span id="detail-artikel-status" class="rounded-full px-3 py-1 text-[10px] font-bold"></span>
      </div>

      <h2 id="detail-artikel-judul" class="text-2xl font-extrabold tracking-tight text-[#1A2D10] sm:text-3xl"></h2>

      <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-[#9AB880]">
        <span id="detail-artikel-tanggal"></span>
        <span id="detail-artikel-komoditas-wrapper">Komoditas: <strong id="detail-artikel-komoditas" class="text-[#4A6030]"></strong></span>
      </div>

      <!-- Ringkasan -->
      <div class="mt-6 rounded-2xl bg-[#F5F8F1] p-4">
        <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-[#9AB880]">Ringkasan</p>
        <p id="detail-artikel-ringkasan" class="text-sm font-semibold leading-7 text-[#4A6030]"></p>
      </div>

      <!-- Isi -->
      <div class="mt-5">
        <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-[#9AB880]">Isi Artikel</p>
        <div id="detail-artikel-isi" class="whitespace-pre-line text-sm leading-8 text-slate-700"></div>
      </div>
    </div>

    <div class="flex items-center justify-end gap-2.5 border-t border-[#E4F0D6] bg-[#F5F8F1] px-5 py-3 sm:px-6">
      <button type="button" onclick="closeDetailArtikelModal()" class="rounded-xl border border-[#C5DFB0] bg-white px-4 py-2.5 text-xs font-bold text-[#4A6030] hover:bg-[#F5F8F1]">Tutup</button>
    </div>
  </div>
</div>

<input type="hidden" id="artikel-store-url" value="{{ route('admin.edukasi.artikel.store') }}">
@if($errors->any())
<input type="hidden" id="artikel-old-id" value="{{ old('id_artikel') }}">
<input type="hidden" id="artikel-old-judul" value="{{ old('judul', '') }}">
<input type="hidden" id="artikel-old-kategori" value="{{ old('kategori', '') }}">
<input type="hidden" id="artikel-old-komoditas" value="{{ old('komoditas', '') }}">
<input type="hidden" id="artikel-old-tanggal" value="{{ old('tanggal', '') }}">
<input type="hidden" id="artikel-old-status" value="{{ old('status', 'Draft') }}">
<input type="hidden" id="artikel-old-ringkasan" value="{{ old('ringkasan', '') }}">
<input type="hidden" id="artikel-old-isi" value="{{ old('isi', '') }}">
<input type="hidden" id="artikel-has-errors" value="1">
@endif

<script>
(function () {
  const modal = document.getElementById('modal-artikel');
  const form = document.getElementById('form-artikel-modal');
  const deleteModal = document.getElementById('modal-delete-artikel');
  const deleteForm = document.getElementById('form-delete-artikel');
  const deleteTitle = document.getElementById('delete-artikel-title');
  if (!modal || !form) return;

  const fields = {
    id: document.getElementById('artikel-form-id'),
    method: document.getElementById('artikel-form-method'),
    judul: document.getElementById('artikel-judul'),
    kategori: document.getElementById('artikel-kategori'),
    komoditas: document.getElementById('artikel-komoditas'),
    kategoriCustom: document.getElementById('artikel-kategori-custom'),
    komoditasCustom: document.getElementById('artikel-komoditas-custom'),
    tanggal: document.getElementById('artikel-tanggal'),
    status: document.getElementById('artikel-status'),
    ringkasan: document.getElementById('artikel-ringkasan'),
    isi: document.getElementById('artikel-isi'),
    gambar: document.getElementById('artikel-gambar'),
    currentImage: document.getElementById('artikel-current-image'),
    currentImagePreview: document.getElementById('artikel-current-image-preview'),
    title: document.getElementById('artikel-modal-title'),
    subtitle: document.getElementById('artikel-modal-subtitle'),
    submit: document.getElementById('artikel-submit-button'),
    summaryCount: document.getElementById('artikel-ringkasan-count'),
    contentCount: document.getElementById('artikel-isi-count'),
  };

  const storeAction = document.getElementById('artikel-store-url').value;
  const defaultDate = new Date().toISOString().slice(0, 10);

  function setFieldValue(select, value) {
    if (!select) return;
    const normalized = value ?? '';
    const optionExists = Array.from(select.options).some(option => option.value === normalized);
    if (normalized && !optionExists) {
      const option = document.createElement('option');
      option.value = normalized;
      option.textContent = normalized;
      select.appendChild(option);
    }
    select.value = normalized;
  }

  function toggleCustomField(select, customInput) {
    const custom = select.value === '__custom__';
    customInput.classList.toggle('hidden', !custom);
    if (!custom) customInput.value = '';
    if (custom) customInput.focus();
  }

  function updateCounters() {
    fields.summaryCount.textContent = `${fields.ringkasan.value.length}/1000`;
    fields.contentCount.textContent = `${fields.isi.value.length} karakter`;
  }

  function resetImageState() {
    fields.gambar.value = '';
    fields.currentImage.classList.add('hidden');
    fields.currentImage.classList.remove('flex');
    fields.currentImagePreview.src = '';
  }

  window.openArtikelModal = function (artikel = null) {
    const editing = Boolean(artikel && artikel.id);
    form.reset();
    fields.id.value = editing ? artikel.id : '';
    fields.method.value = editing ? 'PUT' : 'POST';
    form.action = editing ? artikel.action : storeAction;

    fields.title.textContent = editing ? 'Edit Artikel' : 'Tambah Artikel';
    fields.subtitle.textContent = editing ? 'Perbarui informasi artikel yang tersimpan di database' : 'Simpan data artikel ke database';
    fields.submit.querySelector('span').textContent = editing ? 'Perbarui Artikel' : 'Simpan Artikel';

    if (editing) {
      fields.judul.value = artikel.judul ?? '';
      setFieldValue(fields.kategori, artikel.kategori);
      setFieldValue(fields.komoditas, artikel.komoditas);
      fields.tanggal.value = artikel.tanggal ?? '';
      fields.status.value = artikel.status ?? 'Draft';
      fields.ringkasan.value = artikel.ringkasan ?? '';
      fields.isi.value = artikel.isi ?? '';
      resetImageState();
      if (artikel.gambar) {
        fields.currentImage.classList.remove('hidden');
        fields.currentImage.classList.add('flex');
        fields.currentImagePreview.src = artikel.gambar;
      }
    } else {
      fields.status.value = 'Draft';
      fields.tanggal.value = defaultDate;
      resetImageState();
    }

    toggleCustomField(fields.kategori, fields.kategoriCustom);
    toggleCustomField(fields.komoditas, fields.komoditasCustom);
    updateCounters();
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    requestAnimationFrame(() => fields.judul.focus());
    if (window.lucide) window.lucide.createIcons();
  };

  window.closeArtikelModal = function () {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  };

  window.closeDeleteArtikelModal = function () {
    if (!deleteModal) return;
    deleteModal.classList.add('hidden');
    deleteModal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  };

  // Detail Modal
  const detailModal = document.getElementById('modal-detail-artikel');

  window.openDetailArtikelModal = function (row) {
    if (!row || !detailModal) return;

    const data = row.dataset;
    const imageContainer = document.getElementById('detail-artikel-image-container');
    const image = document.getElementById('detail-artikel-image');

    // Populate data
    document.getElementById('detail-artikel-judul').textContent = data.judul || '';
    document.getElementById('detail-artikel-kategori').textContent = data.kategori || '';
    document.getElementById('detail-artikel-tanggal').textContent = data.tanggal || 'Tanggal belum ditentukan';
    document.getElementById('detail-artikel-ringkasan').textContent = data.ringkasan || '';
    document.getElementById('detail-artikel-isi').textContent = data.isi || '';

    // Status badge
    const statusBadge = document.getElementById('detail-artikel-status');
    statusBadge.textContent = data.status || 'Draft';
    statusBadge.className = 'rounded-full px-3 py-1 text-[10px] font-bold ' +
      (data.status === 'Publik' ? 'bg-[#DFF7E7] text-[#287442]' : 'bg-[#FFF4B8] text-[#8A5A0A]');

    // Komoditas
    const komoditasWrapper = document.getElementById('detail-artikel-komoditas-wrapper');
    const komoditas = document.getElementById('detail-artikel-komoditas');
    if (data.komoditas) {
      komoditas.textContent = data.komoditas;
      komoditasWrapper.style.display = '';
    } else {
      komoditasWrapper.style.display = 'none';
    }

    // Image
    if (data.gambar) {
      image.src = data.gambar;
      image.alt = data.judul || '';
      imageContainer.style.display = '';
    } else {
      imageContainer.style.display = 'none';
    }

    // Show modal
    detailModal.classList.remove('hidden');
    detailModal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    if (window.lucide) window.lucide.createIcons();
  };

  window.closeDetailArtikelModal = function () {
    if (!detailModal) return;
    detailModal.classList.add('hidden');
    detailModal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  };

  if (detailModal) {
    detailModal.addEventListener('click', (event) => {
      if (event.target === detailModal) window.closeDetailArtikelModal();
    });
  }

  function openDeleteArtikelModal(button) {
    if (!deleteModal || !deleteForm || !deleteTitle) return;
    deleteForm.action = button.dataset.deleteAction || '';
    deleteTitle.textContent = button.dataset.deleteTitle || 'artikel ini';
    deleteModal.classList.remove('hidden');
    deleteModal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    if (window.lucide) window.lucide.createIcons();
  }

  document.querySelectorAll('.js-edit-artikel').forEach((button) => {
    button.addEventListener('click', () => {
      try {
        const artikel = JSON.parse(button.dataset.artikel || '{}');
        window.openArtikelModal(artikel);
      } catch (error) {
        console.error('Data edit artikel tidak valid:', error);
      }
    });
  });

  document.querySelectorAll('.js-delete-artikel').forEach((button) => {
    button.addEventListener('click', () => openDeleteArtikelModal(button));
  });

  if (deleteModal) {
    deleteModal.addEventListener('click', (event) => {
      if (event.target === deleteModal) window.closeDeleteArtikelModal();
    });
  }

  fields.kategori.addEventListener('change', () => toggleCustomField(fields.kategori, fields.kategoriCustom));
  fields.komoditas.addEventListener('change', () => toggleCustomField(fields.komoditas, fields.komoditasCustom));

  fields.summaryCount && fields.ringkasan.addEventListener('input', updateCounters);
  fields.contentCount && fields.isi.addEventListener('input', updateCounters);

  modal.addEventListener('click', function (event) {
    if (event.target === modal) window.closeArtikelModal();
  });

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    if (!modal.classList.contains('hidden')) window.closeArtikelModal();
    if (deleteModal && !deleteModal.classList.contains('hidden')) window.closeDeleteArtikelModal();
    if (detailModal && !detailModal.classList.contains('hidden')) window.closeDetailArtikelModal();
  });

  form.addEventListener('submit', function () {
    if (fields.kategori.value === '__custom__') {
      const value = fields.kategoriCustom.value.trim();
      if (value) {
        const option = new Option(value, value, true, true);
        fields.kategori.add(option);
      }
    }
    if (fields.komoditas.value === '__custom__') {
      const value = fields.komoditasCustom.value.trim();
      if (value) {
        const option = new Option(value, value, true, true);
        fields.komoditas.add(option);
      } else {
        fields.komoditas.value = '';
      }
    }
    fields.submit.disabled = true;
    fields.submit.classList.add('opacity-70', 'cursor-not-allowed');
  });

  if (document.getElementById('artikel-has-errors')) {
    const oldArtikelId = document.getElementById('artikel-old-id').value;
    if (oldArtikelId) {
      const editButton = Array.from(document.querySelectorAll('[onclick*="openArtikelModal"]')).find(button => button.getAttribute('onclick')?.includes(`"id":${oldArtikelId}`));
      if (editButton) editButton.click();
    } else {
      window.openArtikelModal();
    }
    fields.judul.value = document.getElementById('artikel-old-judul').value;
    setFieldValue(fields.kategori, document.getElementById('artikel-old-kategori').value);
    setFieldValue(fields.komoditas, document.getElementById('artikel-old-komoditas').value);
    fields.tanggal.value = document.getElementById('artikel-old-tanggal').value;
    fields.status.value = document.getElementById('artikel-old-status').value;
    fields.ringkasan.value = document.getElementById('artikel-old-ringkasan').value;
    fields.isi.value = document.getElementById('artikel-old-isi').value;
    updateCounters();
  }
})();
</script>
@endsection
