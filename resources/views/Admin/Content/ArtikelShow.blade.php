@extends('Admin.Layout._layout')

@section('title', 'Detail Artikel')

@section('content')
<div class="mx-auto max-w-5xl space-y-5">
  @if(session('success'))<div class="rounded-2xl border border-[#C5DFB0] bg-[#EBF6E0] px-4 py-3 text-sm text-[#2F6D20]">{{ session('success') }}</div>@endif
  <div class="flex flex-wrap items-center justify-between gap-3"><a href="{{ route('admin.edukasi.artikel') }}" class="inline-flex items-center gap-2 rounded-xl border border-[#E4F0D6] bg-white px-3 py-2 text-xs font-bold text-[#4A6030] hover:bg-[#F5F8F1]"><i data-lucide="arrow-left" class="h-4 w-4"></i>Kembali</a><a href="{{ route('admin.edukasi.artikel.edit', $artikel) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#4D9830] px-4 py-2.5 text-xs font-bold text-white hover:bg-[#3D8024]"><i data-lucide="edit-3" class="h-4 w-4"></i>Edit Artikel</a></div>
  <article class="overflow-hidden rounded-[24px] border border-[#E4F0D6] bg-white shadow-sm">
    @if($artikel->gambar)<img src="{{ asset($artikel->gambar) }}" alt="{{ $artikel->judul }}" class="h-64 w-full object-cover sm:h-80">@else<div class="flex h-56 w-full items-center justify-center bg-[#F5F8F1] text-[#9AB880]"><i data-lucide="image-off" class="h-10 w-10"></i></div>@endif
    <div class="p-5 sm:p-8"><div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-bold text-blue-700">{{ $artikel->kategori }}</span><span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $artikel->status === 'Publik' ? 'bg-[#DFF7E7] text-[#287442]' : 'bg-[#FFF4B8] text-[#8A5A0A]' }}">{{ $artikel->status }}</span></div><h1 class="mt-4 text-2xl font-extrabold tracking-tight text-[#1A2D10] sm:text-4xl">{{ $artikel->judul }}</h1><div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-[#9AB880]"><span>{{ optional($artikel->tanggal)->translatedFormat('d F Y') ?: 'Tanggal belum ditentukan' }}</span>@if($artikel->komoditas)<span>Komoditas: {{ $artikel->komoditas }}</span>@endif</div><div class="mt-7 rounded-2xl bg-[#F5F8F1] p-4 text-sm font-semibold leading-7 text-[#4A6030]">{{ $artikel->ringkasan }}</div><div class="prose prose-sm mt-7 max-w-none text-slate-700"><div class="whitespace-pre-line leading-8">{{ $artikel->isi }}</div></div></div>
  </article>
</div>
@endsection
