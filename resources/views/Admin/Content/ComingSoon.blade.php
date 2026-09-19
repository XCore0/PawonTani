@extends('Admin.Layout._layout')

@section('title', $pageTitle)

@section('content')
<div class="admin-placeholder-content flex w-full items-center justify-center">
  <div class="w-full max-w-lg rounded-[24px] border border-[#E4F0D6] bg-white p-8 text-center shadow-2xs sm:p-10">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#EBF6E0] text-[#4D9830]">
      <i data-lucide="hard-hat" class="h-8 w-8 animate-pulse"></i>
    </div>
    <span class="mt-5 inline-flex rounded-full bg-amber-50 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-amber-700">
      Dalam Pengembangan
    </span>
    <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-[#1A2D10]">{{ $pageTitle }}</h1>
    <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-[#6B7F5B]">
      {{ $description }} Fitur ini sedang disiapkan dan akan segera tersedia.
    </p>
  </div>
</div>
@endsection
