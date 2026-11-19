<nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs sm:text-sm">
  
  <!-- Info Ringkasan Data (Kiri) -->
  <div class="text-xs text-slate-500 font-medium">
    @if ($paginator->total() > 0)
      <span>Menampilkan</span>
      <span class="font-bold text-[#1A2D10]">{{ $paginator->firstItem() ?? 1 }}</span>
      <span>sampai</span>
      <span class="font-bold text-[#1A2D10]">{{ $paginator->lastItem() ?? $paginator->total() }}</span>
      <span>dari</span>
      <span class="font-bold text-[#1A2D10]">{{ number_format($paginator->total()) }}</span>
      <span>data</span>
    @else
      <span>Menampilkan</span>
      <span class="font-bold text-[#1A2D10]">0</span>
      <span>data</span>
    @endif
  </div>

  <!-- Tombol Navigasi Halaman (Kanan) -->
  <div class="flex items-center gap-1.5 self-end sm:self-center">
    
    <!-- Tombol Sebelumnya (Previous) -->
    @if ($paginator->onFirstPage())
      <span aria-disabled="true" aria-label="Sebelumnya"
        class="h-9 px-3 rounded-xl border border-[#E4F0D6] bg-slate-50 text-slate-300 font-medium flex items-center justify-center gap-1 cursor-not-allowed select-none">
        <i data-lucide="chevron-left" class="w-4 h-4"></i>
        <span class="hidden sm:inline">Sebelumnya</span>
      </span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya"
        class="h-9 px-3 rounded-xl border border-[#C5DFB0] bg-white text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] font-semibold flex items-center justify-center gap-1 transition-colors">
        <i data-lucide="chevron-left" class="w-4 h-4 text-[#4D9830]"></i>
        <span class="hidden sm:inline">Sebelumnya</span>
      </a>
    @endif

    <!-- Nomor Halaman -->
    <div class="hidden sm:flex items-center gap-1">
      @if ($paginator->hasPages())
        @foreach ($elements as $element)
          {{-- Separator Titik Tiga "..." --}}
          @if (is_string($element))
            <span aria-disabled="true" class="h-9 w-9 flex items-center justify-center text-slate-400 font-bold">
              {{ $element }}
            </span>
          @endif

          {{-- Array of Link Halaman --}}
          @if (is_array($element))
            @foreach ($element as $page => $url)
              @if ($page == $paginator->currentPage())
                <span aria-current="page"
                  class="h-9 min-w-[36px] px-2.5 rounded-xl bg-[#4D9830] text-white font-extrabold flex items-center justify-center shadow-xs shadow-[#4D9830]/20 select-none">
                  {{ $page }}
                </span>
              @else
                <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}"
                  class="h-9 min-w-[36px] px-2.5 rounded-xl border border-[#C5DFB0] bg-white text-slate-700 hover:bg-[#EBF6E0] hover:text-[#3D8024] hover:border-[#4D9830] font-semibold flex items-center justify-center transition-colors">
                  {{ $page }}
                </a>
              @endif
            @endforeach
          @endif
        @endforeach
      @else
        {{-- Hanya 1 halaman --}}
        <span aria-current="page"
          class="h-9 min-w-[36px] px-2.5 rounded-xl bg-[#4D9830] text-white font-extrabold flex items-center justify-center shadow-xs shadow-[#4D9830]/20 select-none">
          1
        </span>
      @endif
    </div>

    <!-- Tombol Berikutnya (Next) -->
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya"
        class="h-9 px-3 rounded-xl border border-[#C5DFB0] bg-white text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] font-semibold flex items-center justify-center gap-1 transition-colors">
        <span class="hidden sm:inline">Berikutnya</span>
        <i data-lucide="chevron-right" class="w-4 h-4 text-[#4D9830]"></i>
      </a>
    @else
      <span aria-disabled="true" aria-label="Berikutnya"
        class="h-9 px-3 rounded-xl border border-[#E4F0D6] bg-slate-50 text-slate-300 font-medium flex items-center justify-center gap-1 cursor-not-allowed select-none">
        <span class="hidden sm:inline">Berikutnya</span>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
      </span>
    @endif

  </div>

</nav>
