@extends('Admin.Layout._layout')

@section('title', 'Notifikasi')

@section('content')
@php
  $notifications = [
    ['icon' => 'file-check-2', 'title' => 'Data kelompok tani perlu diverifikasi', 'text' => 'Ada data baru yang menunggu pemeriksaan lapangan.', 'time' => '10 menit lalu', 'href' => route('admin.verifikasi'), 'tone' => 'blue'],
    ['icon' => 'user-plus', 'title' => 'Pengurus baru ditambahkan', 'text' => 'Satu data pengurus baru berhasil masuk ke sistem.', 'time' => '1 jam lalu', 'href' => route('admin.pengurus'), 'tone' => 'green'],
    ['icon' => 'sprout', 'title' => 'Pembaruan data kelompok tani', 'text' => 'Informasi kelompok tani Sri Rejeki telah diperbarui.', 'time' => '3 jam lalu', 'href' => route('admin.kelompok'), 'tone' => 'amber'],
    ['icon' => 'book-open', 'title' => 'Materi edukasi baru tersedia', 'text' => 'Tips pertanian terbaru sedang disiapkan untuk Anda.', 'time' => 'Kemarin', 'href' => route('admin.edukasi.tips'), 'tone' => 'violet'],
    ['icon' => 'info', 'title' => 'Pembaruan sistem', 'text' => 'Sistem PawonTani berjalan dengan normal.', 'time' => '2 hari lalu', 'href' => route('admin.dashboard'), 'tone' => 'slate'],
  ];
@endphp

<div class="space-y-6">
  <div>
    <h1 class="text-2xl font-extrabold tracking-tight text-[#1A2D10]">Notifikasi</h1>
    <p class="mt-1 text-sm font-medium text-[#9AB880]">Informasi terbaru terkait aktivitas administrasi PawonTani.</p>
  </div>

  <div class="overflow-hidden rounded-[20px] border border-[#E4F0D6] bg-white shadow-2xs">
    <div class="flex items-center justify-between border-b border-[#E4F0D6] px-5 py-4">
      <h2 class="text-sm font-bold text-[#1A2D10]">Semua Notifikasi</h2>
      <span class="rounded-full bg-[#EBF6E0] px-2.5 py-1 text-[11px] font-bold text-[#4D9830]">{{ count($notifications) }} terbaru</span>
    </div>
    <div class="divide-y divide-[#E4F0D6]/70">
      @foreach($notifications as $notification)
        <a href="{{ $notification['href'] }}" class="flex items-start gap-3.5 px-5 py-4 transition-colors hover:bg-[#F5F8F1]/60">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#EBF6E0] text-[#4D9830]">
            <i data-lucide="{{ $notification['icon'] }}" class="h-4 w-4"></i>
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
              <h3 class="text-sm font-bold text-[#1A2D10]">{{ $notification['title'] }}</h3>
              <span class="text-[11px] font-medium text-[#9AB880]">{{ $notification['time'] }}</span>
            </div>
            <p class="mt-1 text-xs leading-relaxed text-[#6B7F5B]">{{ $notification['text'] }}</p>
          </div>
          <i data-lucide="chevron-right" class="mt-3 h-4 w-4 shrink-0 text-[#9AB880]"></i>
        </a>
      @endforeach
    </div>
  </div>
</div>
@endsection
