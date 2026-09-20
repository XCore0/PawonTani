@extends('Admin.Layout._layout')

@section('title', $pageTitle)

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
  <div class="flex items-center gap-3"><a href="{{ route('admin.edukasi.artikel') }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#E4F0D6] bg-white text-[#4A6030] hover:bg-[#F5F8F1]"><i data-lucide="arrow-left" class="h-4 w-4"></i></a><div><h1 class="text-xl font-extrabold tracking-tight text-[#1A2D10] sm:text-2xl">{{ $pageTitle }}</h1><p class="mt-1 text-xs text-[#9AB880]">Lengkapi informasi artikel sebelum disimpan.</p></div></div>

  @if($errors->any())<div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

  <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-6">@csrf @if($formMethod !== 'POST') @method($formMethod) @endif
    <div class="rounded-[20px] border border-[#E4F0D6] bg-white p-5 shadow-sm sm:p-6">
      <div class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2"><label class="text-xs font-bold uppercase tracking-wider text-[#9AB880]">Judul *</label><input name="judul" value="{{ old('judul', $artikel->judul) }}" required class="mt-2 h-11 w-full rounded-xl border border-[#C5DFB0] px-3 text-sm outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"></div>
        <div><label class="text-xs font-bold uppercase tracking-wider text-[#9AB880]">Kategori *</label><input name="kategori" value="{{ old('kategori', $artikel->kategori) }}" required placeholder="Budidaya, Hama, Panen..." class="mt-2 h-11 w-full rounded-xl border border-[#C5DFB0] px-3 text-sm outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"></div>
        <div><label class="text-xs font-bold uppercase tracking-wider text-[#9AB880]">Komoditas</label><input name="komoditas" value="{{ old('komoditas', $artikel->komoditas) }}" placeholder="Padi, Jagung, Cabai..." class="mt-2 h-11 w-full rounded-xl border border-[#C5DFB0] px-3 text-sm outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"></div>
        <div><label class="text-xs font-bold uppercase tracking-wider text-[#9AB880]">Tanggal Publikasi</label><input type="date" name="tanggal" value="{{ old('tanggal', optional($artikel->tanggal)->format('Y-m-d')) }}" class="mt-2 h-11 w-full rounded-xl border border-[#C5DFB0] px-3 text-sm outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20"></div>
        <div><label class="text-xs font-bold uppercase tracking-wider text-[#9AB880]">Status *</label><select name="status" required class="mt-2 h-11 w-full rounded-xl border border-[#C5DFB0] bg-white px-3 text-sm outline-none focus:border-[#4D9830]"><option value="Draft" @selected(old('status', $artikel->status ?: 'Draft') === 'Draft')>Draft</option><option value="Publik" @selected(old('status', $artikel->status) === 'Publik')>Publik</option></select></div>
        <div class="md:col-span-2"><label class="text-xs font-bold uppercase tracking-wider text-[#9AB880]">Ringkasan *</label><textarea name="ringkasan" rows="4" required class="mt-2 w-full rounded-xl border border-[#C5DFB0] p-3 text-sm outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">{{ old('ringkasan', $artikel->ringkasan) }}</textarea></div>
        <div class="md:col-span-2"><label class="text-xs font-bold uppercase tracking-wider text-[#9AB880]">Isi Artikel *</label><textarea name="isi" rows="14" required placeholder="Tulis isi artikel dalam teks biasa. Paragraf dipertahankan saat ditampilkan." class="mt-2 w-full rounded-xl border border-[#C5DFB0] p-3 text-sm leading-7 outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">{{ old('isi', $artikel->isi) }}</textarea><p class="mt-1 text-[10px] text-[#9AB880]">Konten diperlakukan sebagai teks biasa untuk mencegah HTML/script berbahaya.</p></div>
        <div class="md:col-span-2"><label class="text-xs font-bold uppercase tracking-wider text-[#9AB880]">Gambar Artikel</label><input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp" class="mt-2 block w-full rounded-xl border border-[#C5DFB0] bg-white p-2 text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#EBF6E0] file:px-3 file:py-2 file:text-xs file:font-bold file:text-[#4D9830]"><p class="mt-1 text-[10px] text-[#9AB880]">JPG, JPEG, PNG, WEBP. Maksimal 4 MB.</p>@if($artikel->gambar)<div class="mt-3 flex items-center gap-3"><img src="{{ asset($artikel->gambar) }}" alt="Gambar artikel saat ini" class="h-20 w-28 rounded-xl object-cover"><span class="text-xs text-[#6B7F5B]">Gambar saat ini</span></div>@endif</div>
      </div>
    </div>
    <div class="flex justify-end gap-2"><a href="{{ route('admin.edukasi.artikel') }}" class="rounded-xl border border-[#C5DFB0] bg-white px-4 py-2.5 text-xs font-bold text-[#4A6030] hover:bg-[#F5F8F1]">Batal</a><button class="rounded-xl bg-[#4D9830] px-5 py-2.5 text-xs font-bold text-white hover:bg-[#3D8024]">Simpan Artikel</button></div>
  </form>
</div>
@endsection
