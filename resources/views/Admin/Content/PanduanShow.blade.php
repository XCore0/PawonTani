@extends('Admin.Layout._layout')

@section('title', 'Detail Panduan')

@section('content')
@php
  $panduanModalData = [
    (string) $panduan->id_panduan => [
      'id' => $panduan->id_panduan,
      'judul' => $panduan->judul,
      'kategori' => $panduan->kategori,
      'komoditas' => $panduan->komoditas,
      'ringkasan' => $panduan->ringkasan,
      'isi' => $panduan->isi,
      'tanggal' => $panduan->tanggal?->format('Y-m-d'),
      'status' => $panduan->status,
      'gambar' => $panduan->gambar ? asset($panduan->gambar) : null,
      'update_url' => route('admin.edukasi.panduan.update', $panduan),
      'delete_url' => route('admin.edukasi.panduan.destroy', $panduan),
    ],
  ];
@endphp

<div class="mx-auto max-w-5xl space-y-5">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex items-start gap-3">
      <a href="{{ route('admin.edukasi.panduan') }}" class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-xl border border-[#C5DFB0] bg-white text-[#4A6030] hover:bg-[#F5F8F1]" data-admin-title="Manajemen Panduan">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
      </a>
      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-[#9AB880]">Detail Panduan</p>
        <h1 class="mt-1 text-xl font-extrabold tracking-tight text-[#1A2D10] sm:text-2xl">{{ $panduan->judul }}</h1>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <button type="button" onclick="openPanduanEditModal('{{ $panduan->id_panduan }}')"
        class="inline-flex items-center gap-2 rounded-xl bg-[#4D9830] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#3D8024]">
        <i data-lucide="edit-3" class="h-4 w-4"></i>Edit
      </button>
      <button type="button" onclick="openPanduanDeleteModal('{{ $panduan->id_panduan }}')"
        class="inline-flex items-center gap-2 rounded-xl bg-[#FFE1E1] px-4 py-2.5 text-xs font-bold text-[#C24141] transition hover:bg-[#FFD2D2]">
        <i data-lucide="trash-2" class="h-4 w-4"></i>Hapus
      </button>
    </div>
  </div>

  <article class="overflow-hidden rounded-[20px] border border-[#E4F0D6] bg-white shadow-2xs">
    @if($panduan->gambar)
      <img src="{{ asset($panduan->gambar) }}" alt="{{ $panduan->judul }}" class="h-64 w-full object-cover sm:h-80">
    @endif

    <div class="space-y-6 p-5 sm:p-7">
      <div class="flex flex-wrap items-center gap-2">
        <span class="rounded-full bg-amber-50 px-3 py-1.5 text-[11px] font-bold text-amber-700">{{ $panduan->kategori }}</span>
        <span class="rounded-full px-3 py-1.5 text-[11px] font-bold {{ $panduan->status === 'publik' ? 'bg-[#DFF7E7] text-[#287442]' : 'bg-[#FFF4B8] text-[#8A5A0A]' }}">{{ ucfirst($panduan->status) }}</span>
        <span class="text-xs text-[#9AB880]">{{ $panduan->tanggal?->format('d M Y') }}</span>
      </div>

      <div>
        <p class="text-sm leading-7 text-[#4A6030]">{{ $panduan->ringkasan }}</p>
      </div>

      <dl class="grid gap-4 rounded-2xl bg-[#F5F8F1] p-4 sm:grid-cols-3">
        <div>
          <dt class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880]">Komoditas</dt>
          <dd class="mt-1 text-sm font-semibold text-[#1A2D10]">{{ $panduan->komoditas ?: '-' }}</dd>
        </div>
        <div>
          <dt class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880]">Slug</dt>
          <dd class="mt-1 break-all text-sm font-semibold text-[#1A2D10]">{{ $panduan->slug }}</dd>
        </div>
        <div>
          <dt class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880]">Pembuat</dt>
          <dd class="mt-1 text-sm font-semibold text-[#1A2D10]">{{ $panduan->author?->nama ?: '-' }}</dd>
        </div>
      </dl>

      <div class="border-t border-[#E4F0D6] pt-6">
        <h2 class="text-sm font-extrabold text-[#1A2D10]">Isi Panduan</h2>
        <div class="mt-4 whitespace-pre-line text-sm leading-8 text-slate-700">{{ $panduan->isi }}</div>
      </div>
    </div>
  </article>
</div>

@include('Admin.Content._PanduanFormModal')
@include('Admin.Content._PanduanModalScript', ['panduanModalData' => $panduanModalData])
@endsection
