@extends('Admin.Layout._layout')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

  <!-- Welcome Banner -->
  <div class="p-6 sm:p-8 rounded-[22px] bg-gradient-to-r from-[#4D9830] to-[#72BE4A] text-white shadow-xs relative overflow-hidden">
    <div class="absolute -right-8 -bottom-8 w-48 h-48 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
    <div class="relative z-10 max-w-2xl space-y-2">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-semibold backdrop-blur-xs">
        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-yellow-300"></i>
        <span>Selamat Datang, Ahmad Fauzi, S.P.</span>
      </span>
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
        Panel Administrasi PawonTani
      </h1>
      <p class="text-xs sm:text-sm text-emerald-50 leading-relaxed font-medium">
        Pusat kendali dan monitoring data kelompok tani, struktur pengurus, komoditas, dan aktivitas pertanian terpadu.
      </p>
      <div class="pt-2">
        <a href="{{ route('admin.pengurus') }}"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-[#1A2D10] text-xs sm:text-sm font-bold shadow-xs hover:bg-[#F5F8F1] transition-all">
          <span>Kelola Pengurus Poktan</span>
          <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
      </div>
    </div>
  </div>

  <!-- Quick Metrics -->
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
        <i data-lucide="users" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Kelompok Tani</span>
        <span class="text-2xl font-extrabold text-[#1A2D10]">8</span>
        <span class="text-[11px] text-[#4A6030] block font-medium">100% Aktif</span>
      </div>
    </div>

    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[#F4A020] flex items-center justify-center shrink-0">
        <i data-lucide="user-check" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Total Pengurus</span>
        <span class="text-2xl font-extrabold text-[#1A2D10]">28</span>
        <span class="text-[11px] text-[#4A6030] block font-medium">Terverifikasi</span>
      </div>
    </div>

    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
        <i data-lucide="sprout" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Total Luas Lahan</span>
        <span class="text-2xl font-extrabold text-[#1A2D10]">142.5 Ha</span>
        <span class="text-[11px] text-[#4A6030] block font-medium">Padi & Jagung</span>
      </div>
    </div>

    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
        <i data-lucide="check-circle" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Verifikasi Lapangan</span>
        <span class="text-2xl font-extrabold text-[#1A2D10]">24 / 24</span>
        <span class="text-[11px] text-emerald-600 font-semibold block">Tuntas Semester Ini</span>
      </div>
    </div>
  </div>

</div>
@endsection
