@extends('Pengurus.Layout._layout')

@section('title', 'Edukasi Pertanian')

@section('content')
<div class="space-y-6">

  <!-- ==================== HEADER ==================== -->
  <div>
    <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
      Edukasi Pertanian
    </h1>
    <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
      Konten edukasi dari PPL — baca dan tingkatkan pengetahuan pertanian Anda
    </p>
  </div>

  <!-- ==================== SEARCH + FILTER ==================== -->
  <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
    <div class="relative w-full sm:w-72">
      <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9AB880]"></i>
      <input type="text" id="search-konten" placeholder="Cari artikel, tips, panduan..." class="w-full h-10 rounded-xl border border-[#D1DFC4] bg-white pl-9 pr-4 text-sm text-[#1A2D10] placeholder:text-[#C5DFB0] focus:outline-none focus:ring-2 focus:ring-[#4D9830]/30 focus:border-[#4D9830]">
    </div>
    <div class="flex items-center gap-2" id="filter-pills">
      <button type="button" data-filter="semua" class="filter-btn active h-9 px-4 rounded-full text-sm font-semibold border transition-colors cursor-pointer bg-[#4D9830] text-white border-[#4D9830]">Semua</button>
      <button type="button" data-filter="Artikel" class="filter-btn h-9 px-4 rounded-full text-sm font-semibold border transition-colors cursor-pointer bg-white text-[#4A6030] border-[#D1DFC4] hover:bg-[#F5F8F1]">Artikel</button>
      <button type="button" data-filter="Tips" class="filter-btn h-9 px-4 rounded-full text-sm font-semibold border transition-colors cursor-pointer bg-white text-[#4A6030] border-[#D1DFC4] hover:bg-[#F5F8F1]">Tips</button>
      <button type="button" data-filter="Panduan" class="filter-btn h-9 px-4 rounded-full text-sm font-semibold border transition-colors cursor-pointer bg-white text-[#4A6030] border-[#D1DFC4] hover:bg-[#F5F8F1]">Panduan</button>
    </div>
  </div>

  <!-- ==================== CONTENT CARDS ==================== -->
  @php
    $allContent = collect();
    foreach ($tips as $t) {
      $allContent->push(['type' => 'Tips', 'id' => $t->id_tips, 'judul' => $t->judul, 'ringkasan' => $t->ringkasan, 'isi' => $t->isi, 'tanggal' => $t->tanggal?->locale('id')->isoFormat('D MMM YYYY'), 'kategori' => $t->kategori, 'komoditas' => $t->komoditas]);
    }
    foreach ($artikels as $a) {
      $allContent->push(['type' => 'Artikel', 'id' => $a->id_artikel, 'judul' => $a->judul, 'ringkasan' => $a->ringkasan, 'isi' => $a->isi, 'tanggal' => $a->tanggal?->locale('id')->isoFormat('D MMM YYYY'), 'kategori' => $a->kategori, 'komoditas' => $a->komoditas]);
    }
    foreach ($panduans as $p) {
      $allContent->push(['type' => 'Panduan', 'id' => $p->id_panduan, 'judul' => $p->judul, 'ringkasan' => $p->ringkasan, 'isi' => $p->isi, 'tanggal' => $p->tanggal?->locale('id')->isoFormat('D MMM YYYY'), 'kategori' => $p->kategori, 'komoditas' => $p->komoditas]);
    }
    $allContent = $allContent->sortByDesc(fn($c) => $c['tanggal'] ?? '')->values();

    $headerColor = [
      'Artikel' => 'linear-gradient(135deg, #D4EDBA 0%, #C5DFB0 100%)',
      'Tips'    => 'linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%)',
      'Panduan' => 'linear-gradient(135deg, #DBEAFE 0%, #BFDBFE 100%)',
    ];
    $badgeColor = [
      'Artikel' => 'bg-[#DBEAFE] text-[#1E40AF]',
      'Tips'    => 'bg-[#D1FAE5] text-[#065F46]',
      'Panduan' => 'bg-[#FEF3C7] text-[#92400E]',
    ];
  @endphp

  @forelse($allContent as $idx => $item)
  @if($loop->first)
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="konten-grid">
  @endif
    <div class="rounded-2xl overflow-hidden border border-[#E4F0D6] shadow-sm konten-card" data-type="{{ $item['type'] }}" data-title="{{ strtolower($item['judul']) }}" data-kategori="{{ strtolower($item['kategori'] ?? '') }}">
      <!-- Colored Header -->
      <div class="h-28 flex items-center justify-center" style="background: {{ $headerColor[$item['type']] }};">
        @if($item['type'] === 'Artikel')
          <i data-lucide="newspaper" class="w-10 h-10 text-[#4D9830]/40"></i>
        @elseif($item['type'] === 'Tips')
          <i data-lucide="lightbulb" class="w-10 h-10 text-[#D97706]/40"></i>
        @else
          <i data-lucide="book-open" class="w-10 h-10 text-[#2563EB]/40"></i>
        @endif
      </div>
      <!-- Body -->
      <div class="bg-white p-5 space-y-3">
        <div class="flex items-center gap-2">
          <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $badgeColor[$item['type']] }}">{{ $item['type'] }}</span>
          <span class="text-xs text-[#9AB880]">{{ $item['tanggal'] ?? '-' }}</span>
        </div>
        <h3 class="text-sm font-bold text-[#1A2D10] leading-snug">{{ $item['judul'] }}</h3>
        @if($item['ringkasan'])
        <p class="text-xs text-[#4A6030] leading-relaxed line-clamp-3">{{ \Illuminate\Support\Str::limit($item['ringkasan'], 120) }}</p>
        @endif
        <button type="button" onclick="openKontenModal({{ $idx }})" class="w-full mt-2 h-10 rounded-xl border-2 border-[#C5DFB0] bg-[#F0F9E8] text-[#4D9830] text-sm font-semibold hover:bg-[#E8F5E0] transition-colors cursor-pointer flex items-center justify-center gap-1.5">
          <span></span> Baca Selengkapnya
        </button>
      </div>
    </div>
  @if($loop->last)
  </div>
  @endif
  @empty
  <div class="flex flex-col items-center justify-center py-32">
    <i data-lucide="book-open" class="w-12 h-12 mb-3 text-[#C5DFB0]"></i>
    <p class="text-sm font-medium text-[#1A2D10] mb-1">Belum ada konten edukasi</p>
    <p class="text-xs text-[#9AB880] text-center max-w-sm">Konten akan muncul setelah administrator menambahkan artikel, tips, atau panduan.</p>
  </div>
  @endforelse

</div>

<!-- ==================== MODAL POPUP ==================== -->
<div id="konten-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 transition-opacity">
  <div class="w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-[#E4F0D6] overflow-hidden flex flex-col max-h-[90vh]">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-[#F0F7E8] px-5 py-4 bg-[#F5F8F1]">
      <div class="flex items-center gap-2.5">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EBF6E0] text-[#4D9830]">
          <i id="modal-icon" data-lucide="file-text" class="h-4 w-4"></i>
        </div>
        <div>
          <p class="text-sm font-bold text-[#1A2D10]">Detail Konten</p>
          <p id="modal-type-label" class="text-[10px] text-[#9AB880] font-medium"></p>
        </div>
      </div>
      <button type="button" onclick="closeKontenModal()" class="rounded-lg p-1 text-slate-400 hover:text-slate-700 hover:bg-slate-100 cursor-pointer">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>

    <!-- Body (scrollable) -->
    <div class="overflow-y-auto flex-1 p-5 space-y-4">
      <!-- Meta Row -->
      <div class="flex flex-wrap items-center gap-2">
        <span id="modal-type" class="text-[11px] font-bold px-2.5 py-1 rounded-full"></span>
        <span id="modal-kategori" class="hidden text-xs px-2.5 py-1 rounded-full bg-[#F0F7E8] text-[#4A6030] font-medium"></span>
        <span id="modal-komoditas" class="hidden text-xs px-2.5 py-1 rounded-full bg-[#FFF4D6] text-[#8A5A0A] font-medium"></span>
        <span id="modal-tanggal" class="text-xs text-[#9AB880] ml-auto"></span>
      </div>
      <!-- Title -->
      <h2 id="modal-judul" class="text-base sm:text-lg font-extrabold text-[#1A2D10] leading-tight"></h2>
      <!-- Ringkasan -->
      <div id="modal-ringkasan-wrap" class="hidden rounded-xl bg-[#F9FCF5] border border-[#E4F0D6] p-4">
        <p id="modal-ringkasan" class="text-sm text-[#4A6030] leading-relaxed italic"></p>
      </div>
      <!-- Divider -->
      <hr class="border-[#E4F0D6]">
      <!-- Full Content -->
      <div id="modal-isi" class="text-sm text-[#1A2D10] leading-relaxed whitespace-pre-wrap"></div>
    </div>

    <!-- Footer -->
    <div class="flex items-center justify-end border-t border-[#F0F7E8] px-5 py-3 bg-[#F5F8F1]">
      <button type="button" onclick="closeKontenModal()" class="rounded-xl border border-[#C5DFB0] bg-white px-4 py-2 text-xs font-semibold text-[#4A6030] hover:bg-[#EBF6E0] transition cursor-pointer">
        Tutup
      </button>
    </div>
  </div>
</div>

@push('scripts')
<script type="application/json" id="konten-data">@json($allContent)</script>
<script>
(() => {
  const kontenData = JSON.parse(document.getElementById('konten-data').textContent);
  const modal = document.getElementById('konten-modal');
  const searchInput = document.getElementById('search-konten');
  const filterBtns = document.querySelectorAll('.filter-btn');
  const cards = document.querySelectorAll('.konten-card');
  let activeFilter = 'semua';

  const badgeColors = {
    'Artikel': 'bg-[#DBEAFE] text-[#1E40AF]',
    'Tips': 'bg-[#D1FAE5] text-[#065F46]',
    'Panduan': 'bg-[#FEF3C7] text-[#92400E]',
  };
  const iconNames = { 'Artikel': 'newspaper', 'Tips': 'lightbulb', 'Panduan': 'book-open' };

  window.openKontenModal = function(idx) {
    const item = kontenData[idx];
    if (!item) return;

    document.getElementById('modal-icon').setAttribute('data-lucide', iconNames[item.type] || 'file-text');
    document.getElementById('modal-type-label').textContent = item.type + ' · ' + (item.kategori || 'Umum');
    document.getElementById('modal-type').textContent = item.type;
    document.getElementById('modal-type').className = 'text-[11px] font-bold px-2.5 py-1 rounded-full ' + (badgeColors[item.type] || '');
    document.getElementById('modal-tanggal').textContent = item.tanggal || '-';

    const katEl = document.getElementById('modal-kategori');
    if (item.kategori) { katEl.textContent = item.kategori; katEl.classList.remove('hidden'); }
    else { katEl.classList.add('hidden'); }

    const komEl = document.getElementById('modal-komoditas');
    if (item.komoditas) { komEl.textContent = item.komoditas; komEl.classList.remove('hidden'); }
    else { komEl.classList.add('hidden'); }

    document.getElementById('modal-judul').textContent = item.judul || '';

    const ringWrap = document.getElementById('modal-ringkasan-wrap');
    if (item.ringkasan) {
      document.getElementById('modal-ringkasan').textContent = item.ringkasan;
      ringWrap.classList.remove('hidden');
    } else {
      ringWrap.classList.add('hidden');
    }

    document.getElementById('modal-isi').textContent = item.isi || 'Konten lengkap belum tersedia.';

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';

    if (window.lucide) window.lucide.createIcons();
  };

  window.closeKontenModal = function() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
  };

  // Close on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeKontenModal();
  });

  function applyFilters() {
    const query = (searchInput?.value || '').toLowerCase().trim();
    cards.forEach(card => {
      const type = card.dataset.type || '';
      const title = card.dataset.title || '';
      const kategori = card.dataset.kategori || '';
      const matchType = activeFilter === 'semua' || type === activeFilter;
      const matchSearch = !query || title.includes(query) || kategori.includes(query);
      card.style.display = (matchType && matchSearch) ? '' : 'none';
    });
  }

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => {
        b.classList.remove('bg-[#4D9830]', 'text-white', 'border-[#4D9830]');
        b.classList.add('bg-white', 'text-[#4A6030]', 'border-[#D1DFC4]');
      });
      btn.classList.remove('bg-white', 'text-[#4A6030]', 'border-[#D1DFC4]');
      btn.classList.add('bg-[#4D9830]', 'text-white', 'border-[#4D9830]');
      activeFilter = btn.dataset.filter;
      applyFilters();
    });
  });

  if (searchInput) searchInput.addEventListener('input', applyFilters);
})();
</script>
@endpush
@endsection
