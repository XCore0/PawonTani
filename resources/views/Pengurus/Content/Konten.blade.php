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
      $allContent->push(['type' => 'Tips', 'id' => $t->id_tips, 'judul' => $t->judul, 'ringkasan' => $t->ringkasan, 'isi' => $t->isi, 'tanggal' => $t->tanggal?->locale('id')->isoFormat('D MMM YYYY'), 'kategori' => $t->kategori, 'komoditas' => $t->komoditas?->nama_komoditas, 'gambar' => $t->gambar]);
    }
    foreach ($artikels as $a) {
      $allContent->push(['type' => 'Artikel', 'id' => $a->id_artikel, 'judul' => $a->judul, 'ringkasan' => $a->ringkasan, 'isi' => $a->isi, 'tanggal' => $a->tanggal?->locale('id')->isoFormat('D MMM YYYY'), 'kategori' => $a->kategori, 'komoditas' => $a->komoditas?->nama_komoditas, 'gambar' => $a->gambar]);
    }
    foreach ($panduans as $p) {
      $allContent->push(['type' => 'Panduan', 'id' => $p->id_panduan, 'judul' => $p->judul, 'ringkasan' => $p->ringkasan, 'isi' => $p->isi, 'tanggal' => $p->tanggal?->locale('id')->isoFormat('D MMM YYYY'), 'kategori' => $p->kategori, 'komoditas' => $p->komoditas?->nama_komoditas, 'gambar' => $p->gambar]);
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
      <!-- Colored Header / Gambar -->
      @if(!empty($item['gambar']))
        <div class="h-36 overflow-hidden">
          <img
            src="{{ Str::startsWith($item['gambar'], ['http://', 'https://']) ? $item['gambar'] : asset('storage/' . $item['gambar']) }}"
            alt="{{ $item['judul'] }}"
            class="w-full h-full object-cover"
            onerror="this.parentElement.innerHTML='<div class=\'h-full flex items-center justify-center\' style=\'background: {{ addslashes($headerColor[$item['type']]) }}\'><svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' class=\'opacity-30\'><path d=\'M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z\'/><circle cx=\'12\' cy=\'13\' r=\'3\'/></svg></div>'"
          >
        </div>
      @else
        <div class="h-36 flex items-center justify-center" style="background: {{ $headerColor[$item['type']] }};">
          @if($item['type'] === 'Artikel')
            <i data-lucide="newspaper" class="w-10 h-10 text-[#4D9830]/40"></i>
          @elseif($item['type'] === 'Tips')
            <i data-lucide="lightbulb" class="w-10 h-10 text-[#D97706]/40"></i>
          @else
            <i data-lucide="book-open" class="w-10 h-10 text-[#2563EB]/40"></i>
          @endif
        </div>
      @endif
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
        <button type="button" data-konten-index="{{ $idx }}" class="konten-open-btn w-full mt-2 h-10 rounded-xl border-2 border-[#C5DFB0] bg-[#F0F9E8] text-[#4D9830] text-sm font-semibold hover:bg-[#E8F5E0] transition-colors cursor-pointer flex items-center justify-center gap-1.5">
          Baca Selengkapnya
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

@push('scripts')
<script type="application/json" id="konten-data">@json($allContent)</script>
<script>
(() => {
  const dataEl = document.getElementById('konten-data');
  if (!dataEl) return;
  const kontenData = JSON.parse(dataEl.textContent);

  // ── Hapus modal lama jika ada (saat AJAX re-load) ──
  const existingModal = document.getElementById('konten-modal');
  if (existingModal) existingModal.remove();

  // ── Buat modal HTML dan inject langsung ke document.body ──
  const modalEl = document.createElement('div');
  modalEl.id = 'konten-modal';
  modalEl.style.cssText = 'position:fixed;inset:0;z-index:99999;display:none;';
  modalEl.innerHTML = `
    <div id="konten-backdrop" style="display:flex;width:100%;height:100%;align-items:center;justify-content:center;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);padding:16px;">
      <div style="width:100%;max-width:560px;background:#fff;border-radius:20px;border:1px solid #E4F0D6;box-shadow:0 25px 60px rgba(0,0,0,0.2);display:flex;flex-direction:column;max-height:90vh;overflow:hidden;">

        <!-- Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:#F5F8F1;border-bottom:1px solid #E4F0D6;gap:12px;">
          <div style="display:flex;align-items:center;gap:10px;">
            <div id="km-icon-wrap" style="width:36px;height:36px;border-radius:10px;background:#EBF6E0;display:flex;align-items:center;justify-content:center;color:#4D9830;flex-shrink:0;">
              <i id="km-icon" data-lucide="file-text" style="width:16px;height:16px;"></i>
            </div>
            <div>
              <p style="font-weight:800;font-size:14px;color:#1A2D10;margin:0;" id="km-title-label">Detail Konten</p>
              <p style="font-size:11px;color:#9AB880;margin:0;" id="km-subtitle"></p>
            </div>
          </div>
          <button id="km-close" type="button" style="padding:6px;border-radius:8px;border:none;background:transparent;cursor:pointer;color:#94a3b8;display:flex;align-items:center;" aria-label="Tutup">
            <i data-lucide="x" style="width:18px;height:18px;"></i>
          </button>
        </div>

        <!-- Gambar (hidden by default) -->
        <div id="km-img-wrap" style="display:none;overflow:hidden;max-height:200px;">
          <img id="km-img" src="" alt="" style="width:100%;height:200px;object-fit:cover;display:block;">
        </div>

        <!-- Body -->
        <div style="overflow-y:auto;flex:1;padding:20px;display:flex;flex-direction:column;gap:14px;">

          <!-- Badge row -->
          <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
            <span id="km-badge-type" style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:999px;"></span>
            <span id="km-badge-kategori" style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:999px;background:#F0F7E8;color:#4A6030;display:none;"></span>
            <span id="km-badge-komoditas" style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:999px;background:#FFF4D6;color:#8A5A0A;display:none;"></span>
            <span id="km-tanggal" style="font-size:11px;color:#9AB880;margin-left:auto;"></span>
          </div>

          <!-- Judul -->
          <h2 id="km-judul" style="font-size:17px;font-weight:900;color:#1A2D10;margin:0;line-height:1.35;"></h2>

          <!-- Ringkasan -->
          <div id="km-ringkasan-wrap" style="background:#F9FCF5;border:1px solid #E4F0D6;border-radius:12px;padding:14px;display:none;">
            <p id="km-ringkasan" style="font-size:13px;color:#4A6030;line-height:1.6;font-style:italic;margin:0;"></p>
          </div>

          <!-- Divider -->
          <hr style="border:none;border-top:1px solid #E4F0D6;margin:0;">

          <!-- Isi -->
          <div id="km-isi" style="font-size:13px;color:#1A2D10;line-height:1.75;white-space:pre-wrap;"></div>
        </div>

        <!-- Footer -->
        <div style="padding:12px 20px;background:#F5F8F1;border-top:1px solid #E4F0D6;display:flex;justify-content:flex-end;">
          <button id="km-close-footer" type="button" style="padding:8px 18px;border-radius:12px;border:1px solid #C5DFB0;background:#fff;font-size:12px;font-weight:700;color:#4A6030;cursor:pointer;">Tutup</button>
        </div>

      </div>
    </div>
  `;
  document.body.appendChild(modalEl);

  // ── Referensi elemen modal ──
  const modal       = modalEl;
  const backdrop    = document.getElementById('konten-backdrop');
  const closeBtn    = document.getElementById('km-close');
  const closeBtnFtr = document.getElementById('km-close-footer');

  const badgeStyle = {
    'Artikel': 'background:#DBEAFE;color:#1E40AF;',
    'Tips'   : 'background:#D1FAE5;color:#065F46;',
    'Panduan': 'background:#FEF3C7;color:#92400E;',
  };
  const iconMap = { 'Artikel': 'newspaper', 'Tips': 'lightbulb', 'Panduan': 'book-open' };

  function openModal(idx) {
    const item = kontenData[idx];
    if (!item) return;

    // Icon
    const iconEl = document.getElementById('km-icon');
    if (iconEl) iconEl.setAttribute('data-lucide', iconMap[item.type] || 'file-text');

    document.getElementById('km-subtitle').textContent = item.type + ' · ' + (item.kategori || 'Umum');

    // Badge
    const badgeType = document.getElementById('km-badge-type');
    badgeType.textContent = item.type;
    badgeType.style.cssText = 'font-size:11px;font-weight:700;padding:3px 10px;border-radius:999px;' + (badgeStyle[item.type] || '');

    const badgeKat = document.getElementById('km-badge-kategori');
    if (item.kategori) { badgeKat.textContent = item.kategori; badgeKat.style.display = 'inline-block'; }
    else { badgeKat.style.display = 'none'; }

    const badgeKom = document.getElementById('km-badge-komoditas');
    if (item.komoditas) { badgeKom.textContent = item.komoditas; badgeKom.style.display = 'inline-block'; }
    else { badgeKom.style.display = 'none'; }

    document.getElementById('km-tanggal').textContent = item.tanggal || '-';
    document.getElementById('km-judul').textContent = item.judul || '';

    // Gambar
    const imgWrap = document.getElementById('km-img-wrap');
    const img = document.getElementById('km-img');
    if (item.gambar) {
      const url = (item.gambar.startsWith('http://') || item.gambar.startsWith('https://'))
        ? item.gambar : '/storage/' + item.gambar;
      img.src = url;
      img.alt = item.judul || '';
      img.onerror = () => { imgWrap.style.display = 'none'; };
      imgWrap.style.display = 'block';
    } else {
      imgWrap.style.display = 'none';
    }

    // Ringkasan
    const ringWrap = document.getElementById('km-ringkasan-wrap');
    if (item.ringkasan) {
      document.getElementById('km-ringkasan').textContent = item.ringkasan;
      ringWrap.style.display = 'block';
    } else {
      ringWrap.style.display = 'none';
    }

    document.getElementById('km-isi').textContent = item.isi || 'Konten lengkap belum tersedia.';

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    if (window.lucide) window.lucide.createIcons();
  }

  function closeModal() {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }

  // Event listeners
  document.querySelectorAll('.konten-open-btn').forEach((btn) => {
    btn.addEventListener('click', () => openModal(Number(btn.dataset.kontenIndex)));
  });
  closeBtn?.addEventListener('click', closeModal);
  closeBtnFtr?.addEventListener('click', closeModal);
  backdrop?.addEventListener('click', (e) => { if (e.target === backdrop) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  // ── Search & Filter ──
  const searchInput = document.getElementById('search-konten');
  const filterBtns  = document.querySelectorAll('.filter-btn');
  const cards       = document.querySelectorAll('.konten-card');
  let activeFilter  = 'semua';

  function applyFilters() {
    const query = (searchInput?.value || '').toLowerCase().trim();
    cards.forEach(card => {
      const matchType   = activeFilter === 'semua' || card.dataset.type === activeFilter;
      const matchSearch = !query || card.dataset.title.includes(query) || card.dataset.kategori.includes(query);
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
  if (window.lucide) window.lucide.createIcons();
})();
</script>
@endpush
@endsection
