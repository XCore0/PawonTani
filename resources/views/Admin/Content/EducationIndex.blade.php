@php
  $typeConfig = [
    'Tips' => ['icon' => 'lightbulb', 'accent' => 'bg-[#EBF6E0] text-[#4D9830]', 'placeholder' => 'Cari judul tips...'],
    'Artikel' => ['icon' => 'newspaper', 'accent' => 'bg-blue-50 text-blue-700', 'placeholder' => 'Cari judul artikel...'],
    'Panduan' => ['icon' => 'book-open', 'accent' => 'bg-amber-50 text-amber-700', 'placeholder' => 'Cari judul panduan...'],
  ][$contentType];
@endphp

<div class="space-y-5" data-education-page>
  <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex items-start gap-3">
      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $typeConfig['accent'] }}">
        <i data-lucide="{{ $typeConfig['icon'] }}" class="h-5 w-5"></i>
      </div>
      <div>
        <h1 class="text-xl font-extrabold tracking-tight text-[#1A2D10] sm:text-2xl">Manajemen {{ $contentType }}</h1>
        <p class="mt-1 text-xs font-medium text-[#9AB880]">Kelola konten {{ strtolower($contentType) }} edukasi pertanian.</p>
      </div>
    </div>
    <button type="button" onclick="alert('Form tambah {{ $contentType }} sedang disiapkan.');"
      class="inline-flex items-center justify-center gap-2 self-start rounded-xl bg-[#4D9830] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-[#3D8024]">
      <i data-lucide="plus" class="h-4 w-4"></i>
      Tambah {{ $contentType }}
    </button>
  </div>

  <div class="flex flex-wrap gap-2">
    <div class="flex items-center gap-2 rounded-lg bg-[#FFF4D6] px-3.5 py-2.5 text-xs text-[#4A6030]">
      <strong class="text-lg leading-none text-[#D97706]">3</strong>
      <span>Total {{ $contentType }}</span>
    </div>
    <div class="flex items-center gap-2 rounded-lg bg-[#DFF7E7] px-3.5 py-2.5 text-xs text-[#287442]">
      <strong class="text-lg leading-none text-[#237A3B]">2</strong>
      <span>Dipublikasi</span>
    </div>
    <div class="flex items-center gap-2 rounded-lg bg-[#FFF4D6] px-3.5 py-2.5 text-xs text-[#8A5A0A]">
      <strong class="text-lg leading-none text-[#8A5A0A]">1</strong>
      <span>Draft</span>
    </div>
  </div>

  <div class="overflow-hidden rounded-2xl border border-[#C5DFB0] bg-white shadow-2xs">
    <div class="flex flex-col gap-3 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <div class="relative">
          <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#9AB880]"></i>
          <input type="search" data-education-search placeholder="{{ $typeConfig['placeholder'] }}"
            class="h-9 w-full rounded-lg border border-[#C5DFB0] bg-white pl-9 pr-3 text-xs text-slate-700 outline-none transition focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 sm:w-48">
        </div>
        <select data-education-status class="h-9 rounded-lg border border-[#C5DFB0] bg-white px-3 text-xs text-slate-700 outline-none focus:border-[#4D9830]">
          <option value="">Semua Status</option>
          <option value="Publik">Publik</option>
          <option value="Draft">Draft</option>
        </select>
      </div>
      <span class="text-xs text-[#9AB880]"><strong data-education-count>3</strong> {{ strtolower($contentType) }}</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] border-collapse text-left text-xs" data-education-table>
        <thead>
          <tr class="bg-[#EBF6E0] text-[10px] font-bold uppercase tracking-wider text-[#4A6030]">
            <th class="w-12 px-3 py-3">No</th>
            <th class="px-3 py-3">Judul</th>
            <th class="w-28 px-3 py-3">Gambar</th>
            <th class="w-24 px-3 py-3">Tanggal</th>
            <th class="w-20 px-3 py-3">Status</th>
            <th class="w-48 px-3 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E4F0D6]/70 text-[#4A6030]">
          @foreach($items as $index => $item)
            <tr class="transition-colors hover:bg-[#F5F8F1]/60" data-education-row data-title="{{ strtolower($item['title']) }}" data-status="{{ $item['status'] }}">
              <td class="px-3 py-3.5 text-[#9AB880]">{{ $index + 1 }}</td>
              <td class="px-3 py-3.5">
                <div class="max-w-[310px]">
                  <p class="truncate font-bold text-[#1A2D10]" title="{{ $item['title'] }}">{{ $item['title'] }}</p>
                  <p class="mt-1 truncate text-[10px] text-[#9AB880]">Target: {{ $item['target'] }}</p>
                </div>
              </td>
              <td class="px-3 py-3.5">
                <div class="flex items-center gap-2">
                  <span class="flex h-8 w-9 shrink-0 items-center justify-center rounded-md {{ $typeConfig['accent'] }} border border-current/10">
                    <i data-lucide="{{ $typeConfig['icon'] }}" class="h-4 w-4"></i>
                  </span>
                  <span class="max-w-14 truncate text-[10px] text-[#9AB880]">{{ $item['image'] }}</span>
                </div>
              </td>
              <td class="whitespace-nowrap px-3 py-3.5 text-[#9AB880]">{{ $item['date'] }}</td>
              <td class="px-3 py-3.5">
                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold {{ $item['status'] === 'Publik' ? 'bg-[#DFF7E7] text-[#287442]' : 'bg-[#FFF4B8] text-[#8A5A0A]' }}">{{ $item['status'] }}</span>
              </td>
              <td class="px-3 py-3.5">
                <div class="flex items-center gap-1">
                  <button type="button" onclick="alert('Detail {{ $contentType }} sedang disiapkan.');" class="inline-flex items-center gap-1 rounded-lg bg-[#EAF5DE] px-2.5 py-1.5 text-[10px] font-bold text-[#4D9830] hover:bg-[#DDF0CC]"><i data-lucide="eye" class="h-3 w-3"></i>Detail</button>
                  <button type="button" onclick="alert('Edit {{ $contentType }} sedang disiapkan.');" class="inline-flex items-center gap-1 rounded-lg bg-[#FFF4B8] px-2.5 py-1.5 text-[10px] font-bold text-[#8A5A0A] hover:bg-[#FFEFA0]"><i data-lucide="edit-3" class="h-3 w-3"></i>Edit</button>
                  <button type="button" onclick="alert('Hapus {{ $contentType }} sedang disiapkan.');" class="inline-flex items-center gap-1 rounded-lg bg-[#FFE1E1] px-2.5 py-1.5 text-[10px] font-bold text-[#C24141] hover:bg-[#FFD2D2]"><i data-lucide="trash-2" class="h-3 w-3"></i>Hapus</button>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <div data-education-empty class="hidden px-4 py-12 text-center text-sm text-[#9AB880]">Tidak ada {{ strtolower($contentType) }} yang cocok.</div>
    </div>
  </div>
</div>

<script>
  (() => {
    const page = document.querySelector('[data-education-page]');
    if (!page) return;
    const search = page.querySelector('[data-education-search]');
    const status = page.querySelector('[data-education-status]');
    const rows = [...page.querySelectorAll('[data-education-row]')];
    const count = page.querySelector('[data-education-count]');
    const empty = page.querySelector('[data-education-empty]');

    const filter = () => {
      const query = search.value.toLowerCase().trim();
      const selectedStatus = status.value;
      let visible = 0;
      rows.forEach((row) => {
        const matches = (!query || row.dataset.title.includes(query)) && (!selectedStatus || row.dataset.status === selectedStatus);
        row.classList.toggle('hidden', !matches);
        if (matches) visible++;
      });
      count.textContent = visible;
      empty.classList.toggle('hidden', visible !== 0);
    };

    search.addEventListener('input', filter);
    status.addEventListener('change', filter);
  })();
</script>
