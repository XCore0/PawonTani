@extends('Edukasi.Panduan._layout')

@section('title', $panduan->judul)

@section('content')
<section class="mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-12">
  <nav class="mb-6 flex items-center gap-2 text-[11px] text-[#6B7F5B]" aria-label="Breadcrumb">
    <a href="{{ route('edukasi.panduan') }}" class="hover:text-[#4D9830]">Panduan</a>
    <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
    <span class="truncate text-[#9AB880]">{{ $panduan->judul }}</span>
  </nav>

  <article class="overflow-hidden rounded-[24px] border border-[#E4F0D6] bg-white shadow-sm">
    @if($panduan->gambar)
      <img src="{{ asset($panduan->gambar) }}" alt="{{ $panduan->judul }}" class="max-h-[460px] w-full object-cover">
    @endif

    <div class="p-5 sm:p-8">
      <div class="flex flex-wrap items-center gap-2">
        <span class="rounded-full bg-[#EBF6E0] px-3 py-1.5 text-[10px] font-bold text-[#4D9830]">{{ $panduan->kategori }}</span>
        <time class="text-[11px] text-[#9AB880]">{{ $panduan->tanggal?->format('d M Y') }}</time>
      </div>

      <h1 class="mt-4 text-2xl font-extrabold tracking-tight text-[#1A2D10] sm:text-4xl">{{ $panduan->judul }}</h1>
      <p class="mt-4 text-sm leading-7 text-[#4A6030]">{{ $panduan->ringkasan }}</p>

      <dl class="mt-6 grid gap-3 rounded-2xl bg-[#F5F8F1] p-4">
        <div>
          <dt class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880]">Komoditas</dt>
          <dd class="mt-1 text-sm font-semibold text-[#1A2D10]">{{ $panduan->komoditas ?: '-' }}</dd>
        </div>
      </dl>

      <div class="mt-8 border-t border-[#E4F0D6] pt-7">
        <div class="whitespace-pre-line text-sm leading-8 text-slate-700">{{ $panduan->isi }}</div>
      </div>
    </div>
  </article>

  @if($related->isNotEmpty())
    <section class="mt-10">
      <h2 class="text-lg font-extrabold text-[#1A2D10]">Panduan Terkait</h2>
      <div class="mt-4 grid gap-4 sm:grid-cols-3">
        @foreach($related as $item)
          <a href="{{ route('edukasi.panduan.show', $item->slug) }}" class="rounded-2xl border border-[#E4F0D6] bg-white p-4 hover:bg-[#F5F8F1]">
            <span class="text-[10px] font-bold text-[#4D9830]">{{ $item->kategori }}</span>
            <h3 class="mt-2 line-clamp-2 text-sm font-extrabold text-[#1A2D10]">{{ $item->judul }}</h3>
            <span class="mt-3 inline-flex items-center gap-1 text-[11px] font-bold text-[#4D9830]">Baca <i data-lucide="arrow-right" class="h-3 w-3"></i></span>
          </a>
        @endforeach
      </div>
    </section>
  @endif

  <a href="{{ route('edukasi.panduan') }}" class="mt-8 inline-flex items-center gap-2 rounded-xl border border-[#C5DFB0] bg-white px-4 py-2.5 text-xs font-bold text-[#4A6030] hover:bg-[#F5F8F1]">
    <i data-lucide="arrow-left" class="h-4 w-4"></i>
    Kembali ke Panduan
  </a>
</section>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) window.lucide.createIcons();
  });
</script>
@endpush
