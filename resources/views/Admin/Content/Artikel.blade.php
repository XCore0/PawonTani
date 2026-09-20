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
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#EBF6E0] text-[#4D9830]"><i data-lucide="newspaper" class="h-5 w-5"></i></div>
      <div>
        <h1 class="text-xl font-extrabold tracking-tight text-[#1A2D10] sm:text-2xl">Manajemen Artikel</h1>
        <p class="mt-1 text-xs font-medium text-[#9AB880]">Kelola artikel edukasi pertanian yang tampil di halaman publik.</p>
      </div>
    </div>
    <button type="button" onclick="openArtikelModal()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#4D9830] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#3D8024]">
      <i data-lucide="plus" class="h-4 w-4"></i>Tambah Artikel
    </button>
  </div>

  <div class="grid gap-3 sm:grid-cols-3">
    @foreach([
      ['label'=>'Total Artikel','value'=>$stats['total'],'tone'=>'bg-[#EBF6E0] text-[#4D9830]','icon'=>'newspaper'],
      ['label'=>'Artikel Publik','value'=>$stats['publik'],'tone'=>'bg-[#DFF7E7] text-[#287442]','icon'=>'globe-2'],
      ['label'=>'Artikel Draft','value'=>$stats['draft'],'tone'=>'bg-[#FFF4D6] text-[#8A5A0A]','icon'=>'file-edit'],
    ] as $stat)
      <div class="rounded-[18px] border border-[#E4F0D6] bg-white p-4 shadow-sm">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-2xl {{ $stat['tone'] }}"><i data-lucide="{{ $stat['icon'] }}" class="h-4 w-4"></i></div>
          <div><p class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880]">{{ $stat['label'] }}</p><p class="mt-0.5 text-2xl font-extrabold text-[#1A2D10]">{{ $stat['value'] }}</p></div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="overflow-hidden rounded-[20px] border border-[#E4F0D6] bg-white shadow-sm">
    <form method="GET" action="{{ route('admin.edukasi.artikel') }}" class="flex flex-col gap-3 border-b border-[#E4F0D6] p-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-col gap-2 sm:flex-row">
        <div class="relative">
          <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#9AB880]"></i>
          <input name="search" value="{{ request('search') }}" type="search" placeholder="Cari judul, kategori, komoditas..." class="h-10 w-full rounded-xl border border-[#C5DFB0] pl-9 pr-3 text-xs outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 sm:w-72">
        </div>
        <select name="status" class="h-10 rounded-xl border border-[#C5DFB0] bg-white px-3 text-xs outline-none focus:border-[#4D9830]">
          <option value="">Semua Status</option>
          <option value="Publik" @selected(request('status') === 'Publik')>Publik</option>
          <option value="Draft" @selected(request('status') === 'Draft')>Draft</option>
        </select>
        <button class="h-10 rounded-xl border border-[#C5DFB0] px-4 text-xs font-bold text-[#4A6030] hover:bg-[#F5F8F1]">Terapkan</button>
      </div>
      <span class="text-xs text-[#9AB880]">{{ $artikelList->total() }} artikel ditemukan</span>
    </form>

    @if($artikelList->count())
      <div class="overflow-x-auto">
        <table class="w-full min-w-[850px] border-collapse text-left text-xs">
          <thead>
            <tr class="bg-[#F5F8F1] text-[11px] font-bold uppercase tracking-wider text-[#4A6030]">
              <th class="px-4 py-3">Artikel</th><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E4F0D6]/60">
          @foreach($artikelList as $artikel)
            <tr class="transition hover:bg-[#F5F8F1]/50">
              <td class="px-4 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="h-12 w-16 shrink-0 overflow-hidden rounded-xl border border-[#E4F0D6] bg-[#F5F8F1]">
                    @if($artikel->gambar)<img src="{{ asset($artikel->gambar) }}" alt="" class="h-full w-full object-cover">@else<div class="flex h-full w-full items-center justify-center text-[#9AB880]"><i data-lucide="image-off" class="h-4 w-4"></i></div>@endif
                  </div>
                  <div class="min-w-0"><p class="max-w-[360px] truncate font-bold text-[#1A2D10]">{{ $artikel->judul }}</p><p class="mt-1 max-w-[360px] truncate text-[10px] text-[#9AB880]">{{ $artikel->komoditas ?: 'Komoditas tidak ditentukan' }}</p></div>
                </div>
              </td>
              <td class="px-4 py-3.5 text-[#4A6030]">{{ $artikel->kategori }}</td>
              <td class="whitespace-nowrap px-4 py-3.5 text-[#9AB880]">{{ optional($artikel->tanggal)->format('d M Y') ?: '-' }}</td>
              <td class="px-4 py-3.5"><span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold {{ $artikel->status === 'Publik' ? 'bg-[#DFF7E7] text-[#287442]' : 'bg-[#FFF4B8] text-[#8A5A0A]' }}">{{ $artikel->status }}</span></td>
              <td class="px-4 py-3.5">
                <div class="flex items-center gap-1.5">
                  <a href="{{ route('admin.edukasi.artikel.show', $artikel) }}" class="inline-flex items-center gap-1 rounded-lg bg-[#EAF5DE] px-2.5 py-1.5 text-[10px] font-bold text-[#4D9830] hover:bg-[#DDF0CC]"><i data-lucide="eye" class="h-3 w-3"></i>Detail</a>
                  <button type="button" class="js-edit-artikel inline-flex items-center gap-1 rounded-lg bg-[#FFF4B8] px-2.5 py-1.5 text-[10px] font-bold text-[#8A5A0A] hover:bg-[#FFEFA0]"
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
                    <i data-lucide="edit-3" class="h-3 w-3"></i>Edit
                  </button>
                  <button type="button" class="js-delete-artikel inline-flex items-center gap-1 rounded-lg bg-[#FFE1E1] px-2.5 py-1.5 text-[10px] font-bold text-[#C24141] hover:bg-[#FFD2D2]"
                    data-delete-action="{{ route('admin.edukasi.artikel.destroy', $artikel) }}" data-delete-title="{{ $artikel->judul }}">
                    <i data-lucide="trash-2" class="h-3 w-3"></i>Hapus
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
      <div class="px-6 py-14 text-center"><div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#EBF6E0] text-[#4D9830]"><i data-lucide="newspaper" class="h-6 w-6"></i></div><h2 class="mt-4 text-sm font-extrabold text-[#1A2D10]">Belum ada artikel.</h2><p class="mt-1 text-xs text-[#9AB880]">Tambahkan artikel edukasi pertama untuk mulai mengisi halaman publik.</p><button type="button" onclick="openArtikelModal()" class="mt-4 inline-flex rounded-xl bg-[#4D9830] px-4 py-2.5 text-xs font-bold text-white hover:bg-[#3D8024]">Tambah Artikel</button></div>
    @endif
  </div>
</div>

<!-- MODAL TAMBAH / EDIT ARTIKEL: mengikuti pola modal Tambah Pengurus -->
<div id="modal-artikel" class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-900/50 p-3 backdrop-blur-xs sm:p-4">
  <div id="modal-artikel-container" class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-[22px] border border-[#E4F0D6] bg-white shadow-2xl animate-in fade-in zoom-in-95 duration-200">
    <div class="flex shrink-0 items-center justify-between border-b border-[#E4F0D6] bg-[#F5F8F1] px-5 py-4 sm:px-6">
      <div class="flex items-center gap-2.5">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#EBF6E0] text-[#4D9830]"><i id="artikel-modal-icon" data-lucide="file-plus-2" class="h-4 w-4"></i></div>
        <div>
          <h3 id="artikel-modal-title" class="text-base font-bold text-[#1A2D10]">Tambah Artikel</h3>
          <p id="artikel-modal-subtitle" class="text-xs text-[#9AB880]">Simpan artikel edukasi ke database</p>
        </div>
      </div>
      <button type="button" onclick="closeArtikelModal()" class="rounded-lg p-1 text-slate-400 transition-colors hover:bg-white hover:text-slate-700" aria-label="Tutup"><i data-lucide="x" class="h-5 w-5"></i></button>
    </div>

    <form id="form-artikel-modal" action="{{ route('admin.edukasi.artikel.store') }}" method="POST" enctype="multipart/form-data" class="min-h-0 overflow-y-auto p-5 text-xs sm:p-6 sm:text-sm">
      @csrf
      <input type="hidden" name="_method" id="artikel-form-method" value="POST">
      <input type="hidden" name="id_artikel" id="artikel-form-id" value="">

      <div class="space-y-4">
        <div>
          <label for="artikel-judul" class="mb-1 block font-semibold text-[#1A2D10]">Judul Artikel <span class="text-red-500">*</span></label>
          <input type="text" name="judul" id="artikel-judul" required maxlength="255" placeholder="Contoh: Teknik Budidaya Padi yang Efisien" class="h-11 w-full rounded-xl border border-[#C5DFB0] bg-white px-3.5 text-slate-800 placeholder-[#9AB880] outline-none transition-all focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
        </div>

        <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
          <div>
            <label for="artikel-kategori" class="mb-1 block font-semibold text-[#1A2D10]">Kategori <span class="text-red-500">*</span></label>
            <select name="kategori" id="artikel-kategori" required class="h-11 w-full cursor-pointer rounded-xl border border-[#C5DFB0] bg-white px-3.5 text-slate-800 outline-none transition-all focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
              <option value="">Pilih Kategori</option>
              @foreach(($kategoriOptions ?? collect()) as $kategori)
                <option value="{{ $kategori }}">{{ $kategori }}</option>
              @endforeach
              <option value="__custom__">Kategori lainnya...</option>
            </select>
            <input type="text" id="artikel-kategori-custom" placeholder="Ketik kategori baru" class="mt-2 hidden h-10 w-full rounded-xl border border-[#C5DFB0] px-3.5 text-sm outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
          </div>
          <div>
            <label for="artikel-komoditas" class="mb-1 block font-semibold text-[#1A2D10]">Komoditas</label>
            <select name="komoditas" id="artikel-komoditas" class="h-11 w-full cursor-pointer rounded-xl border border-[#C5DFB0] bg-white px-3.5 text-slate-800 outline-none transition-all focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
              <option value="">Pilih Komoditas</option>
              @foreach(($komoditasOptions ?? collect()) as $komoditas)
                <option value="{{ $komoditas }}">{{ $komoditas }}</option>
              @endforeach
              <option value="__custom__">Komoditas lainnya...</option>
            </select>
            <input type="text" id="artikel-komoditas-custom" placeholder="Ketik komoditas baru" class="mt-2 hidden h-10 w-full rounded-xl border border-[#C5DFB0] px-3.5 text-sm outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
          </div>
        </div>

        <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
          <div>
            <label for="artikel-tanggal" class="mb-1 block font-semibold text-[#1A2D10]">Tanggal Publikasi</label>
            <input type="date" name="tanggal" id="artikel-tanggal" class="h-11 w-full rounded-xl border border-[#C5DFB0] bg-white px-3.5 text-slate-800 outline-none transition-all focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
          </div>
        </div>

        <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
          <div>
            <label for="artikel-status" class="mb-1 block font-semibold text-[#1A2D10]">Status <span class="text-red-500">*</span></label>
            <select name="status" id="artikel-status" required class="h-11 w-full cursor-pointer rounded-xl border border-[#C5DFB0] bg-white px-3.5 text-slate-800 outline-none transition-all focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
              <option value="Draft">Draft</option>
              <option value="Publik">Publik</option>
            </select>
          </div>
          <div class="flex items-end">
            <div class="w-full rounded-xl border border-[#E4F0D6] bg-[#F5F8F1] px-3.5 py-2.5 text-[11px] leading-5 text-[#6B7F5B]">
              <span class="font-bold text-[#4A6030]">Catatan:</span> hanya artikel berstatus <b>Publik</b> yang tampil di halaman frontend.
            </div>
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between gap-3"><label for="artikel-ringkasan" class="mb-1 block font-semibold text-[#1A2D10]">Ringkasan <span class="text-red-500">*</span></label><span id="artikel-ringkasan-count" class="text-[10px] text-[#9AB880]">0/1000</span></div>
          <textarea name="ringkasan" id="artikel-ringkasan" rows="3" required maxlength="1000" placeholder="Tulis ringkasan singkat artikel..." class="w-full resize-none rounded-xl border border-[#C5DFB0] p-3 text-sm leading-6 text-slate-800 placeholder-[#9AB880] outline-none transition-all focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"></textarea>
        </div>

        <div>
          <div class="flex items-center justify-between gap-3"><label for="artikel-isi" class="mb-1 block font-semibold text-[#1A2D10]">Isi Artikel <span class="text-red-500">*</span></label><span id="artikel-isi-count" class="text-[10px] text-[#9AB880]">0 karakter</span></div>
          <textarea name="isi" id="artikel-isi" rows="10" required placeholder="Tulis isi artikel di sini...&#10;&#10;Gunakan paragraf dan baris baru agar artikel nyaman dibaca." class="w-full resize-y rounded-xl border border-[#C5DFB0] p-3 text-sm leading-7 text-slate-800 placeholder-[#9AB880] outline-none transition-all focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"></textarea>
          <p class="mt-1 text-[10px] text-[#9AB880]">Isi artikel disimpan sebagai teks biasa dan ditampilkan dengan aman.</p>
        </div>

        <div>
          <label for="artikel-gambar" class="mb-1 block font-semibold text-[#1A2D10]">Gambar Artikel</label>
          <label for="artikel-gambar" class="flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-[#C5DFB0] bg-[#F5F8F1]/50 p-3.5 transition hover:border-[#4D9830] hover:bg-[#F5F8F1]">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#EBF6E0] text-[#4D9830]"><i data-lucide="upload-cloud" class="h-5 w-5"></i></span>
            <span class="min-w-0"><span class="block text-xs font-bold text-[#4A6030]">Klik untuk mengunggah gambar</span><span id="artikel-file-label" class="mt-0.5 block truncate text-[10px] text-[#9AB880]">JPG, JPEG, PNG, WEBP (Maks. 4 MB)</span></span>
          </label>
          <input type="file" name="gambar" id="artikel-gambar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="sr-only">
          <div id="artikel-current-image" class="mt-2 hidden items-center gap-3 rounded-xl border border-[#E4F0D6] bg-[#F5F8F1] p-2.5"><img id="artikel-current-image-preview" src="" alt="Gambar artikel" class="h-14 w-20 rounded-lg object-cover"><div class="min-w-0"><p class="text-[10px] font-bold text-[#4A6030]">Gambar saat ini</p><p class="truncate text-[10px] text-[#9AB880]">Biarkan kosong jika tidak ingin mengganti gambar.</p></div></div>
        </div>
      </div>

      <div class="mt-5 flex items-center justify-end gap-2.5 border-t border-[#E4F0D6] pt-4">
        <button type="button" onclick="closeArtikelModal()" class="rounded-xl border border-[#C5DFB0] px-4 py-2.5 font-semibold text-[#4A6030] transition-colors hover:bg-[#F5F8F1]">Batal</button>
        <button id="artikel-submit-button" type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-[#4D9830] px-5 py-2.5 font-bold text-white shadow-sm shadow-[#4D9830]/20 transition-all hover:bg-[#3D8024]"><i data-lucide="check" class="h-4 w-4"></i><span>Simpan Artikel</span></button>
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
    fileLabel: document.getElementById('artikel-file-label'),
    currentImage: document.getElementById('artikel-current-image'),
    currentImagePreview: document.getElementById('artikel-current-image-preview'),
    title: document.getElementById('artikel-modal-title'),
    subtitle: document.getElementById('artikel-modal-subtitle'),
    submit: document.getElementById('artikel-submit-button'),
    summaryCount: document.getElementById('artikel-ringkasan-count'),
    contentCount: document.getElementById('artikel-isi-count'),
  };

  const storeAction = @js(route('admin.edukasi.artikel.store'));
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
    fields.fileLabel.textContent = 'JPG, JPEG, PNG, WEBP (Maks. 4 MB)';
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
  fields.gambar.addEventListener('change', function () {
    const file = this.files?.[0];
    fields.fileLabel.textContent = file ? `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)` : 'JPG, JPEG, PNG, WEBP (Maks. 4 MB)';
  });

  modal.addEventListener('click', function (event) {
    if (event.target === modal) window.closeArtikelModal();
  });

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    if (!modal.classList.contains('hidden')) window.closeArtikelModal();
    if (deleteModal && !deleteModal.classList.contains('hidden')) window.closeDeleteArtikelModal();
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

  @if($errors->any())
    const oldArtikelId = @js(old('id_artikel'));
    if (oldArtikelId) {
      const editButton = Array.from(document.querySelectorAll('[onclick*="openArtikelModal"]')).find(button => button.getAttribute('onclick')?.includes(`"id":${oldArtikelId}`));
      if (editButton) editButton.click();
    } else {
      window.openArtikelModal();
    }
    fields.judul.value = @js(old('judul', ''));
    setFieldValue(fields.kategori, @js(old('kategori', '')));
    setFieldValue(fields.komoditas, @js(old('komoditas', '')));
    fields.tanggal.value = @js(old('tanggal', ''));
    fields.status.value = @js(old('status', 'Draft'));
    fields.ringkasan.value = @js(old('ringkasan', ''));
    fields.isi.value = @js(old('isi', ''));
    updateCounters();
  @endif
})();
</script>
@endsection
