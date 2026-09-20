@extends('Admin.Layout._layout')

@section('title', $panduan->exists ? 'Edit Panduan' : 'Tambah Panduan')

@section('content')
@php($isModal = request()->boolean('modal'))
<div class="mx-auto max-w-5xl space-y-5">
  @unless($isModal)
    <div class="flex items-start gap-3">
      <a href="{{ route('admin.edukasi.panduan') }}" class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-xl border border-[#C5DFB0] bg-white text-[#4A6030] hover:bg-[#F5F8F1]" data-admin-title="Manajemen Panduan">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
      </a>
      <div>
        <h1 class="text-xl font-extrabold tracking-tight text-[#1A2D10] sm:text-2xl">
          {{ $panduan->exists ? 'Edit Panduan' : 'Tambah Panduan' }}
        </h1>
        <p class="mt-1 text-xs font-medium text-[#9AB880]">Isi informasi panduan pertanian secara lengkap.</p>
      </div>
    </div>
  @endunless

  @if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      <p class="font-bold">Periksa kembali data yang diisi.</p>
      <ul class="mt-1 list-inside list-disc text-xs">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form id="{{ $isModal ? 'panduan-create-form' : 'panduan-edit-form' }}" method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if($formMethod !== 'POST')
      @method($formMethod)
    @endif

    <div class="{{ $isModal ? '' : 'rounded-[20px] border border-[#E4F0D6] bg-white p-5 shadow-2xs sm:p-6' }}">
      <div class="mb-5">
        <h2 class="text-sm font-extrabold text-[#1A2D10]">Informasi Utama</h2>
        <p class="mt-1 text-xs text-[#9AB880]">Jenis konten otomatis ditetapkan sebagai Panduan.</p>
      </div>

      <div class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
          <label for="judul" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#6B7F5B]">Judul</label>
          <input id="judul" name="judul" type="text" value="{{ old('judul', $panduan->judul) }}" required maxlength="255"
            class="w-full rounded-xl border border-[#C5DFB0] px-3.5 py-3 text-sm text-slate-800 outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"
            placeholder="Contoh: Panduan Budidaya Jagung Hibrida">
        </div>

        <div>
          <label for="kategori" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#6B7F5B]">Kategori</label>
          <input id="kategori" name="kategori" type="text" value="{{ old('kategori', $panduan->kategori) }}" required maxlength="100"
            class="w-full rounded-xl border border-[#C5DFB0] px-3.5 py-3 text-sm text-slate-800 outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"
            placeholder="Contoh: Budidaya">
        </div>

        <div>
          <label for="komoditas" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#6B7F5B]">Komoditas</label>
          <input id="komoditas" name="komoditas" type="text" value="{{ old('komoditas', $panduan->komoditas) }}" maxlength="100"
            class="w-full rounded-xl border border-[#C5DFB0] px-3.5 py-3 text-sm text-slate-800 outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"
            placeholder="Contoh: Jagung">
        </div>

        <div>
          <label for="tanggal" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#6B7F5B]">Tanggal</label>
          <input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', optional($panduan->tanggal)->format('Y-m-d')) }}" required
            class="w-full rounded-xl border border-[#C5DFB0] px-3.5 py-3 text-sm text-slate-800 outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
        </div>

        <div>
          <label for="status" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#6B7F5B]">Status</label>
          <select id="status" name="status" required
            class="w-full rounded-xl border border-[#C5DFB0] bg-white px-3.5 py-3 text-sm text-slate-800 outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
            <option value="draft" @selected(old('status', $panduan->status) === 'draft')>Draft</option>
            <option value="publik" @selected(old('status', $panduan->status) === 'publik')>Publik</option>
          </select>
        </div>

        <div class="md:col-span-2">
          <label for="ringkasan" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#6B7F5B]">Ringkasan</label>
          <textarea id="ringkasan" name="ringkasan" rows="4" required
            class="w-full rounded-xl border border-[#C5DFB0] px-3.5 py-3 text-sm text-slate-800 outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"
            placeholder="Tuliskan ringkasan singkat panduan.">{{ old('ringkasan', $panduan->ringkasan) }}</textarea>
        </div>

        <div class="md:col-span-2">
          <label for="isi" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#6B7F5B]">Isi Panduan</label>
          <textarea id="isi" name="isi" rows="12" required
            class="w-full rounded-xl border border-[#C5DFB0] px-3.5 py-3 text-sm leading-7 text-slate-800 outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"
            placeholder="Tuliskan langkah-langkah atau materi panduan secara lengkap.">{{ old('isi', $panduan->isi) }}</textarea>
        </div>

        <div class="md:col-span-2">
          <label for="gambar" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#6B7F5B]">Gambar</label>
          <input id="gambar" name="gambar" type="file" accept=".jpg,.jpeg,.png,.webp"
            class="block w-full rounded-xl border border-[#C5DFB0] bg-white px-3 py-2.5 text-xs text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#EBF6E0] file:px-3 file:py-2 file:text-xs file:font-bold file:text-[#4D9830]">
          <p class="mt-1.5 text-[11px] text-[#9AB880]">JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.</p>

          @if($panduan->gambar)
            <div class="mt-3 flex items-center gap-3">
              <img src="{{ asset($panduan->gambar) }}" alt="{{ $panduan->judul }}" class="h-20 w-28 rounded-xl border border-[#E4F0D6] object-cover">
              <span class="text-xs text-[#6B7F5B]">Gambar saat ini. Upload gambar baru untuk menggantinya.</span>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="flex flex-col-reverse gap-2 border-t border-[#E4F0D6] pt-4 sm:flex-row sm:justify-end {{ $isModal ? '' : '' }}">
      @unless($isModal)
        <a href="{{ route('admin.edukasi.panduan') }}" class="inline-flex items-center justify-center rounded-xl border border-[#C5DFB0] bg-white px-5 py-2.5 text-xs font-bold text-[#4A6030] hover:bg-[#F5F8F1]" data-admin-title="Manajemen Panduan">Batal</a>
      @else
        <button type="button" data-panduan-modal-close class="inline-flex items-center justify-center rounded-xl border border-[#C5DFB0] bg-white px-5 py-2.5 text-xs font-bold text-[#4A6030] hover:bg-[#F5F8F1]">Batal</button>
      @endunless
      <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#4D9830] px-5 py-2.5 text-xs font-bold text-white hover:bg-[#3D8024]">
        <i data-lucide="save" class="h-4 w-4"></i>
        {{ $panduan->exists ? 'Simpan Perubahan' : 'Simpan Panduan' }}
      </button>
    </div>
  </form>
</div>
@endsection
