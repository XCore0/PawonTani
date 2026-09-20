@extends('Edukasi.Panduan._layout')

@section('title', 'Panduan Pertanian')

@section('content')
<section class="bg-gradient-to-br from-[#4D9830] to-[#72BE4A]">
  <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20">
    <div class="max-w-2xl">
      <span class="inline-flex rounded-full bg-white/20 px-3 py-1.5 text-[11px] font-bold text-white backdrop-blur">KONTEN & EDUKASI</span>
      <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-5xl">Panduan Pertanian</h1>
      <p class="mt-4 max-w-xl text-sm leading-7 text-white/90 sm:text-base">
        Panduan langkah demi langkah untuk membantu kegiatan pertanian menjadi lebih terarah dan mudah dipraktikkan.
      </p>
    </div>
  </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
  <div class="mb-6">
    <h2 class="text-xl font-extrabold tracking-tight text-[#1A2D10]">Daftar Panduan</h2>
    <p class="mt-1 text-xs text-[#6B7F5B]">Hanya panduan dengan status publik yang ditampilkan.</p>
  </div>

  @if($panduan->count())
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
      @foreach($panduan as $item)
        <article class="group overflow-hidden rounded-[20px] border border-[#E4F0D6] bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
          @if($item->gambar)
            <img src="{{ asset($item->gambar) }}" alt="{{ $item->judul }}" class="h-44 w-full object-cover">
          @else
            <div class="flex h-44 items-center justify-center bg-[#EBF6E0] text-[#4D9830]">
              <i data-lucide="book-open" class="h-10 w-10"></i>
            </div>
          @endif

          <div class="p-5">
            <div class="flex items-center justify-between gap-2">
              <span class="rounded-full bg-[#EBF6E0] px-2.5 py-1 text-[10px] font-bold text-[#4D9830]">{{ $item->kategori }}</span>
              <time class="text-[10px] text-[#9AB880]">{{ $item->tanggal?->format('d M Y') }}</time>
            </div>
            <h3 class="mt-3 line-clamp-2 text-base font-extrabold text-[#1A2D10]">{{ $item->judul }}</h3>
            <p class="mt-2 line-clamp-3 text-xs leading-6 text-[#6B7F5B]">{{ $item->ringkasan }}</p>
            <a href="{{ route('edukasi.panduan.show', $item->slug) }}" class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-[#4D9830] hover:text-[#3D8024]">
              Baca Selengkapnya
              <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
            </a>
          </div>
        </article>
      @endforeach
    </div>

    @if($panduan->hasPages())
      <div class="mt-8">
        {{ $panduan->links() }}
      </div>
    @endif
  @else
    <div class="rounded-[20px] border border-[#E4F0D6] bg-white px-6 py-14 text-center">
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#EBF6E0] text-[#4D9830]">
        <i data-lucide="book-open" class="h-7 w-7"></i>
      </div>
      <h2 class="mt-4 text-base font-extrabold text-[#1A2D10]">Belum ada panduan publik</h2>
      <p class="mt-1 text-xs text-[#6B7F5B]">Panduan yang sudah dipublikasikan akan muncul di halaman ini.</p>
    </div>
  @endif
</section>
@endsection
