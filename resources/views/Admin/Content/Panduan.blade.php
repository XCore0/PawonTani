@extends('Admin.Layout._layout')

@section('title', 'Manajemen Panduan')

@section('content')
@php
  $panduanModalData = $panduan->getCollection()->mapWithKeys(function ($item) {
      return [(string) $item->id_panduan => [
          'id' => $item->id_panduan,
          'judul' => $item->judul,
          'kategori' => $item->kategori,
          'komoditas' => $item->komoditas,
          'ringkasan' => $item->ringkasan,
          'isi' => $item->isi,
          'tanggal' => $item->tanggal?->format('Y-m-d'),
          'status' => $item->status,
          'gambar' => $item->gambar ? asset($item->gambar) : null,
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
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
        <i data-lucide="book-open" class="h-5 w-5"></i>
      </div>
      <div>
        <h1 class="text-xl font-extrabold tracking-tight text-[#1A2D10] sm:text-2xl">Manajemen Panduan</h1>
        <p class="mt-1 text-xs font-medium text-[#9AB880]">Kelola panduan pertanian yang akan dibaca oleh pengguna.</p>
      </div>
    </div>

    <button type="button" onclick="openPanduanCreateModal()"
      class="inline-flex items-center justify-center gap-2 self-start rounded-xl bg-[#4D9830] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-[#3D8024]">
      <i data-lucide="plus" class="h-4 w-4"></i>
      Tambah Panduan
    </button>
  </div>

  <div class="flex flex-wrap gap-2">
    <div class="flex items-center gap-2 rounded-lg border border-[#E4F0D6] bg-[#F5F8F1] px-3.5 py-2.5 text-xs text-[#4A6030]">
      <strong class="text-lg leading-none text-[#4D9830]">{{ $counts['total'] }}</strong>
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

  <div class="overflow-hidden rounded-[20px] border border-[#E4F0D6] bg-white shadow-2xs">
    <form method="GET" action="{{ route('admin.edukasi.panduan') }}" class="flex flex-col gap-3 border-b border-[#E4F0D6] px-4 py-4 lg:flex-row lg:items-center">
      <div class="relative flex-1">
        <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#9AB880]"></i>
        <input type="search" name="search" value="{{ $search }}" placeholder="Cari judul, kategori, atau komoditas..."
          class="h-10 w-full rounded-xl border border-[#C5DFB0] bg-white pl-9 pr-3 text-xs text-slate-700 outline-none transition focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
      </div>

      <select name="status" class="h-10 rounded-xl border border-[#C5DFB0] bg-white px-3 text-xs text-slate-700 outline-none focus:border-[#4D9830]">
        <option value="">Semua Status</option>
        <option value="publik" @selected($status === 'publik')>Publik</option>
        <option value="draft" @selected($status === 'draft')>Draft</option>
      </select>

      <select name="kategori" class="h-10 rounded-xl border border-[#C5DFB0] bg-white px-3 text-xs text-slate-700 outline-none focus:border-[#4D9830]">
        <option value="">Semua Kategori</option>
        @foreach($categories as $category)
          <option value="{{ $category }}" @selected($kategori === $category)>{{ $category }}</option>
        @endforeach
      </select>

      <select name="komoditas" class="h-10 rounded-xl border border-[#C5DFB0] bg-white px-3 text-xs text-slate-700 outline-none focus:border-[#4D9830]">
        <option value="">Semua Komoditas</option>
        @foreach($commodities as $commodity)
          <option value="{{ $commodity }}" @selected($komoditas === $commodity)>{{ $commodity }}</option>
        @endforeach
      </select>

      <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#4D9830] px-4 text-xs font-bold text-white hover:bg-[#3D8024]">
        <i data-lucide="filter" class="h-4 w-4"></i>
        Filter
      </button>
      @if($search || $status || $kategori || $komoditas)
        <a href="{{ route('admin.edukasi.panduan') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-[#C5DFB0] px-4 text-xs font-bold text-[#4A6030] hover:bg-[#F5F8F1]">
          Reset
        </a>
      @endif
    </form>

    <div class="px-4 py-3 text-xs text-[#9AB880]">
      Menampilkan {{ $panduan->count() }} dari {{ $panduan->total() }} panduan
    </div>

    <div class="overflow-x-auto">
      <table class="w-full min-w-[900px] border-collapse text-left text-xs">
        <thead>
          <tr class="bg-[#F5F8F1] text-[10px] font-bold uppercase tracking-wider text-[#4A6030]">
            <th class="w-12 px-3 py-3">No</th>
            <th class="px-3 py-3">Judul</th>
            <th class="w-28 px-3 py-3">Gambar</th>
            <th class="w-28 px-3 py-3">Kategori</th>
            <th class="w-28 px-3 py-3">Komoditas</th>
            <th class="w-28 px-3 py-3">Tanggal</th>
            <th class="w-24 px-3 py-3">Status</th>
            <th class="w-48 px-3 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E4F0D6]/70 text-[#4A6030]">
          @forelse($panduan as $index => $item)
            <tr class="transition-colors hover:bg-[#F5F8F1]/60">
              <td class="px-3 py-3.5 text-[#9AB880]">{{ $panduan->firstItem() + $index }}</td>
              <td class="px-3 py-3.5">
                <div class="max-w-[310px]">
                  <p class="truncate font-bold text-[#1A2D10]" title="{{ $item->judul }}">{{ $item->judul }}</p>
                  <p class="mt-1 truncate text-[10px] text-[#9AB880]">{{ $item->ringkasan }}</p>
                </div>
              </td>
              <td class="px-3 py-3.5">
                @if($item->gambar)
                  <img src="{{ asset($item->gambar) }}" alt="{{ $item->judul }}" class="h-12 w-16 rounded-lg border border-[#E4F0D6] object-cover">
                @else
                  <div class="flex h-12 w-16 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                    <i data-lucide="book-open" class="h-4 w-4"></i>
                  </div>
                @endif
              </td>
              <td class="px-3 py-3.5">{{ $item->kategori }}</td>
              <td class="px-3 py-3.5">{{ $item->komoditas ?: '-' }}</td>
              <td class="whitespace-nowrap px-3 py-3.5 text-[#9AB880]">{{ $item->tanggal?->format('d M Y') }}</td>
              <td class="px-3 py-3.5">
                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold {{ $item->status === 'publik' ? 'bg-[#DFF7E7] text-[#287442]' : 'bg-[#FFF4B8] text-[#8A5A0A]' }}">
                  {{ ucfirst($item->status) }}
                </span>
              </td>
              <td class="px-3 py-3.5">
                <div class="flex items-center gap-1">
                  <a href="{{ route('admin.edukasi.panduan.show', $item) }}" class="inline-flex items-center gap-1 rounded-lg bg-[#EAF5DE] px-2.5 py-1.5 text-[10px] font-bold text-[#4D9830] hover:bg-[#DDF0CC]" data-admin-title="Detail Panduan">
                    <i data-lucide="eye" class="h-3 w-3"></i>Detail
                  </a>
                  <button type="button" onclick="openPanduanEditModal('{{ $item->id_panduan }}')" class="inline-flex items-center gap-1 rounded-lg bg-[#FFF4B8] px-2.5 py-1.5 text-[10px] font-bold text-[#8A5A0A] hover:bg-[#FFEFA0]">
                    <i data-lucide="edit-3" class="h-3 w-3"></i>Edit
                  </button>
                  <button type="button" onclick="openPanduanDeleteModal('{{ $item->id_panduan }}')" class="inline-flex items-center gap-1 rounded-lg bg-[#FFE1E1] px-2.5 py-1.5 text-[10px] font-bold text-[#C24141] hover:bg-[#FFD2D2]">
                    <i data-lucide="trash-2" class="h-3 w-3"></i>Hapus
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-4 py-14 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F5F8F1] text-[#4D9830]">
                  <i data-lucide="book-open" class="h-6 w-6"></i>
                </div>
                <p class="mt-3 text-sm font-bold text-[#1A2D10]">Belum ada panduan</p>
                <p class="mt-1 text-xs text-[#9AB880]">Tambahkan panduan pertama untuk mulai mengisi konten edukasi.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($panduan->hasPages())
      <div class="border-t border-[#E4F0D6] px-4 py-4">
        {{ $panduan->links() }}
      </div>
    @endif
  </div>
</div>

@include('Admin.Content._PanduanFormModal')

@include('Admin.Content._PanduanModalScript', ['panduanModalData' => $panduanModalData])

@endsection
