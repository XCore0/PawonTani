@extends('Pengurus.Layout._layout')

@section('title', 'Monitoring Pertanian')

@section('content')
<div class="space-y-6">

  {{-- ==================== 1. TOP HEADER ==================== --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Monitoring Pertanian
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Pantau catatan aktivitas tanam, pemupukan, penyemprotan, dan kondisi kesehatan tanaman anggota {{ $kelompok?->nama_kelompok ?? 'Kelompok Tani' }}.
      </p>
    </div>

    <div class="flex items-center gap-2.5 shrink-0">
      <a href="{{ route('pengurus.monitoring') }}"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] hover:border-[#9AB880] transition-all shadow-2xs cursor-pointer"
        title="Muat Ulang Halaman">
        <i data-lucide="refresh-cw" class="w-4 h-4 text-[#4D9830]"></i>
        <span>Segarkan</span>
      </a>
    </div>
  </div>

  {{-- ==================== 2. SUMMARY METRICS CARDS ==================== --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

    <!-- Card 1: Total Aktivitas -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
        <i data-lucide="clipboard-list" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Total Aktivitas</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ $stats['total'] ?? 0 }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Kegiatan tercatat di sistem</span>
      </div>
    </div>

    <!-- Card 2: Bulan Ini -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#E0F2FE] text-[#0284C7] flex items-center justify-center shrink-0">
        <i data-lucide="calendar" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Bulan Ini</span>
        <span class="text-2xl font-extrabold text-[#0284C7] tracking-tight">{{ $stats['bulan_ini'] ?? 0 }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">{{ now()->translatedFormat('F Y') }}</span>
      </div>
    </div>

    <!-- Card 3: Tanaman Sehat -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#DCFCE7] text-[#16A34A] flex items-center justify-center shrink-0">
        <i data-lucide="check-circle-2" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Tanaman Sehat</span>
        <span class="text-2xl font-extrabold text-[#16A34A] tracking-tight">{{ $stats['sehat'] ?? 0 }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Kondisi pertumbuhan baik</span>
      </div>
    </div>

    <!-- Card 4: Perlu Perhatian / Hama -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#FEF3C7] text-[#D97706] flex items-center justify-center shrink-0">
        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Perlu Perhatian</span>
        <span class="text-2xl font-extrabold text-[#D97706] tracking-tight">{{ $stats['perlu_perhatian'] ?? 0 }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Gejala hama / butuh penanganan</span>
      </div>
    </div>

  </div>

  {{-- ==================== 3. FILTER & SEARCH BAR ==================== --}}
  <div class="p-4 sm:p-5 rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs space-y-4">
    <form method="GET" action="{{ route('pengurus.monitoring') }}" class="space-y-3">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

        <!-- Search Input -->
        <div class="relative lg:col-span-1">
          <i data-lucide="search" class="w-4 h-4 text-[#9AB880] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
          <input type="text" name="q" value="{{ request('q') }}"
            placeholder="Cari aktivitas, catatan, petani..."
            class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-[#E4F0D6] bg-[#F5F8F1]/40 text-xs sm:text-sm text-[#1A2D10] placeholder-[#9AB880] focus:outline-none focus:border-[#4D9830] focus:bg-white transition-all">
        </div>

        <!-- Filter Kategori -->
        <div>
          <select name="kategori"
            class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4F0D6] bg-[#F5F8F1]/40 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830] focus:bg-white transition-all">
            <option value="">Semua Kategori</option>
            <option value="pemupukan" {{ request('kategori') == 'pemupukan' ? 'selected' : '' }}>Pemupukan</option>
            <option value="penyiraman" {{ request('kategori') == 'penyiraman' ? 'selected' : '' }}>Penyiraman</option>
            <option value="pengairan" {{ request('kategori') == 'pengairan' ? 'selected' : '' }}>Pengairan</option>
            <option value="penyemprotan" {{ request('kategori') == 'penyemprotan' ? 'selected' : '' }}>Penyemprotan</option>
            <option value="penyiangan" {{ request('kategori') == 'penyiangan' ? 'selected' : '' }}>Penyiangan</option>
            <option value="pemangkasan" {{ request('kategori') == 'pemangkasan' ? 'selected' : '' }}>Pemangkasan</option>
          </select>
        </div>

        <!-- Filter Kondisi -->
        <div>
          <select name="kondisi"
            class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4F0D6] bg-[#F5F8F1]/40 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830] focus:bg-white transition-all">
            <option value="">Semua Kondisi Tanaman</option>
            <option value="sehat" {{ request('kondisi') == 'sehat' ? 'selected' : '' }}>✔ Sehat</option>
            <option value="perlu_perhatian" {{ request('kondisi') == 'perlu_perhatian' ? 'selected' : '' }}>▲ Perlu Perhatian</option>
            <option value="terserang_hama" {{ request('kondisi') == 'terserang_hama' ? 'selected' : '' }}>⚠ Terserang Hama</option>
          </select>
        </div>

        <!-- Filter Lahan -->
        <div>
          <select name="id_lahan"
            class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4F0D6] bg-[#F5F8F1]/40 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830] focus:bg-white transition-all">
            <option value="">Semua Petak Lahan</option>
            @foreach($daftarLahan as $lahanOption)
              <option value="{{ $lahanOption->id_lahan }}" {{ request('id_lahan') == $lahanOption->id_lahan ? 'selected' : '' }}>
                {{ $lahanOption->nama_lahan }}
              </option>
            @endforeach
          </select>
        </div>

      </div>

      <div class="flex items-center justify-between pt-1">
        <span class="text-xs text-[#9AB880] font-medium">
          Ditemukan <strong>{{ $daftarAktivitas->total() }}</strong> catatan aktivitas
        </span>
        <div class="flex items-center gap-2">
          @if(request()->hasAny(['q', 'kategori', 'kondisi', 'id_lahan']))
            <a href="{{ route('pengurus.monitoring') }}"
              class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
              Reset Filter
            </a>
          @endif
          <button type="submit"
            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-[#4D9830] hover:bg-[#3D7826] text-white text-xs font-bold transition-colors cursor-pointer shadow-2xs">
            <i data-lucide="filter" class="w-3.5 h-3.5"></i>
            <span>Terapkan</span>
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- ==================== 4. TABEL AKTIVITAS ==================== --}}
  <div class="rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-[#E4F0D6] bg-[#F5F8F1]/60 text-[11px] font-bold text-[#6B7F5B] uppercase tracking-wider">
            <th class="py-3.5 px-4 text-center w-12">No</th>
            <th class="py-3.5 px-4 min-w-[130px]">Waktu & Tanggal</th>
            <th class="py-3.5 px-4 min-w-[180px]">Petani & Lahan</th>
            <th class="py-3.5 px-4 min-w-[170px]">Aktivitas</th>
            <th class="py-3.5 px-4 min-w-[140px]">Kondisi Tanaman</th>
            <th class="py-3.5 px-4 min-w-[200px]">Catatan / Foto</th>
            <th class="py-3.5 px-4 text-center w-20">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#F0F7E8] text-xs">
          @forelse($daftarAktivitas as $index => $item)
            @php
              // Badge Kondisi
              $isSehat = in_array(strtolower($item->kondisi_tanaman), ['sehat']);
              $isPerhatian = in_array(strtolower($item->kondisi_tanaman), ['perluperhatian', 'perlu_perhatian']);
              $isHama = in_array(strtolower($item->kondisi_tanaman), ['terseranghama', 'terserang_hama']);

              // Badge Kategori
              $kategoriColors = [
                'pemupukan' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => 'sprout'],
                'penyiraman' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon' => 'droplets'],
                'pengairan' => ['bg' => 'bg-cyan-50 text-cyan-700 border-cyan-200', 'icon' => 'waves'],
                'penyemprotan' => ['bg' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => 'shield-check'],
                'penyiangan' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'icon' => 'scissors'],
                'pemangkasan' => ['bg' => 'bg-orange-50 text-orange-700 border-orange-200', 'icon' => 'scissors'],
              ];
              $katInfo = $kategoriColors[strtolower($item->kategori)] ?? ['bg' => 'bg-gray-50 text-gray-700 border-gray-200', 'icon' => 'leaf'];
            @endphp
            <tr class="hover:bg-[#F5F8F1]/50 transition-colors">
              <!-- No -->
              <td class="py-3.5 px-4 text-center font-mono text-[#9AB880]">
                {{ $daftarAktivitas->firstItem() + $index }}
              </td>

              <!-- Tanggal & Waktu -->
              <td class="py-3.5 px-4">
                <div class="font-bold text-[#1A2D10]">
                  {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') : '-' }}
                </div>
                <div class="text-[11px] text-[#9AB880] mt-0.5 flex items-center gap-1">
                  <i data-lucide="clock" class="w-3 h-3"></i>
                  <span>{{ $item->waktu ?? '-' }}</span>
                </div>
                @if($item->hari_setelah_tanam)
                  <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#EBF6E0] text-[#4D9830]">
                    {{ $item->hari_setelah_tanam }} HST
                  </span>
                @endif
              </td>

              <!-- Petani & Lahan -->
              <td class="py-3.5 px-4">
                <div class="font-bold text-[#1A2D10]">
                  {{ $item->pengguna?->nama ?? 'Anggota Petani' }}
                </div>
                <div class="text-[11px] text-[#4A6030] mt-0.5 flex items-center gap-1">
                  <i data-lucide="map-pin" class="w-3 h-3 text-[#9AB880] shrink-0"></i>
                  <span class="truncate max-w-[150px]">{{ $item->lahan?->nama_lahan ?? 'Petak Lahan' }}</span>
                </div>
                @if($item->lahan?->komoditas)
                  <span class="inline-block mt-1 text-[10px] font-semibold text-[#6B7F5B]">
                    🌱 {{ $item->lahan->komoditas->nama_komoditas }}
                  </span>
                @endif
              </td>

              <!-- Aktivitas -->
              <td class="py-3.5 px-4">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $katInfo['bg'] }}">
                  <i data-lucide="{{ $katInfo['icon'] }}" class="w-3 h-3"></i>
                  <span>{{ ucfirst($item->kategori) }}</span>
                </span>
                <div class="font-semibold text-[#1A2D10] mt-1">
                  {{ $item->judul ?? ucfirst($item->kategori) }}
                </div>
              </td>

              <!-- Kondisi Tanaman -->
              <td class="py-3.5 px-4">
                @if($isSehat)
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-green-50 text-green-700 border border-green-200">
                    <i data-lucide="check" class="w-3 h-3"></i>
                    <span>Sehat</span>
                  </span>
                @elseif($isPerhatian)
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    <i data-lucide="alert-circle" class="w-3 h-3"></i>
                    <span>Perlu Perhatian</span>
                  </span>
                @elseif($isHama)
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                    <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                    <span>Terserang Hama</span>
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-50 text-slate-700 border border-slate-200">
                    <span>{{ $item->kondisi_tanaman }}</span>
                  </span>
                @endif
              </td>

              <!-- Catatan & Foto -->
              <td class="py-3.5 px-4">
                <p class="text-[#4A6030] line-clamp-2 italic mb-1.5">
                  "{{ $item->catatan ?: 'Tidak ada catatan tambahan.' }}"
                </p>
                @if(!empty($item->foto_aktivitas))
                  @php
                    $imgSrc = str_starts_with($item->foto_aktivitas, 'http')
                        ? $item->foto_aktivitas
                        : asset('storage/' . $item->foto_aktivitas);
                  @endphp
                  <a href="{{ $imgSrc }}" target="_blank"
                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#4D9830] hover:underline">
                    <i data-lucide="image" class="w-3.5 h-3.5"></i>
                    <span>Lihat Dokumentasi</span>
                  </a>
                @endif
              </td>

              <!-- Aksi -->
              <td class="py-3.5 px-4 text-center">
                <button type="button"
                  onclick="openModalAktivitas('modal-aktivitas-{{ $item->id_aktivitas }}')"
                  class="p-2 rounded-xl text-[#4D9830] hover:bg-[#EBF6E0] inline-flex items-center justify-center transition-colors"
                  title="Lihat Detail Aktivitas & Lahan">
                  <i data-lucide="eye" class="w-4 h-4"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="py-12 text-center">
                <div class="w-16 h-16 rounded-full bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center mx-auto mb-3">
                  <i data-lucide="leaf" class="w-8 h-8"></i>
                </div>
                <h3 class="text-sm font-bold text-[#1A2D10]">Belum Ada Catatan Aktivitas Pertanian</h3>
                <p class="text-xs text-[#9AB880] mt-1 max-w-sm mx-auto">
                  Anggota kelompok tani dapat mencatat aktivitas tanam harian, jadwal pemupukan, dan foto kondisi lahan melalui aplikasi mobile PawonTani.
                </p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    @if($daftarAktivitas->hasPages())
      <div class="p-4 border-t border-[#E4F0D6] bg-white">
        {{ $daftarAktivitas->links() }}
      </div>
    @endif
  </div>

</div>

<!-- Modal Detail Aktivitas Pertanian -->
@foreach($daftarAktivitas as $item)
  @php
    $katInfo = $kategoriBadges[$item->kategori] ?? [
        'bg' => 'bg-gray-100 text-gray-700 border-gray-200',
        'icon' => 'activity',
    ];
    $imgSrcModal = $item->foto_aktivitas
        ? (str_starts_with($item->foto_aktivitas, 'http')
            ? $item->foto_aktivitas
            : asset('storage/' . $item->foto_aktivitas))
        : null;
  @endphp
  <div id="modal-aktivitas-{{ $item->id_aktivitas }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50 p-4 backdrop-blur-sm flex items-center justify-center">
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-[#DCE9D0] animate-in fade-in zoom-in duration-200">
      <div class="flex items-center justify-between border-b border-[#E4F0D6] pb-3 mb-4">
        <div class="flex items-center gap-2">
          <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#E8F2DF] text-[#4D9830]">
            <i data-lucide="clipboard-list" class="h-5 w-5"></i>
          </div>
          <div>
            <h3 class="text-sm font-bold text-[#1A2D10]">Detail Aktivitas Pertanian</h3>
            <p class="text-[11px] text-[#78965F]">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') : '-' }} · {{ $item->waktu ?? '-' }}</p>
          </div>
        </div>
        <button type="button" onclick="closeModalAktivitas('modal-aktivitas-{{ $item->id_aktivitas }}')" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
          <i data-lucide="x" class="h-5 w-5"></i>
        </button>
      </div>

      <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-1">
        <!-- Informasi Petani & Lahan -->
        <div class="rounded-xl bg-[#F7FAF4] border border-[#DCE9D0] p-3.5 space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[11px] text-[#78965F]">Petani</span>
            <span class="text-xs font-bold text-[#1A2D10]">{{ $item->pengguna?->nama ?? '-' }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-[11px] text-[#78965F]">Lahan</span>
            <span class="text-xs font-semibold text-[#1A2D10]">{{ $item->lahan?->nama_lahan ?? '-' }}</span>
          </div>
          @if($item->lahan?->komoditas)
          <div class="flex items-center justify-between">
            <span class="text-[11px] text-[#78965F]">Komoditas</span>
            <span class="text-xs font-semibold text-[#4D9830]">{{ $item->lahan->komoditas->nama_komoditas }}</span>
          </div>
          @endif
          @if($item->hari_setelah_tanam)
          <div class="flex items-center justify-between">
            <span class="text-[11px] text-[#78965F]">Hari Setelah Tanam (HST)</span>
            <span class="text-xs font-bold text-[#4D9830]">{{ $item->hari_setelah_tanam }} HST</span>
          </div>
          @endif
        </div>

        <!-- Detail Aktivitas -->
        <div class="space-y-2">
          <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold border {{ $katInfo['bg'] }}">
              <i data-lucide="{{ $katInfo['icon'] }}" class="w-3.5 h-3.5"></i>
              <span>{{ ucfirst($item->kategori) }}</span>
            </span>
            <span class="text-xs font-bold text-[#1A2D10]">{{ $item->judul ?? ucfirst($item->kategori) }}</span>
          </div>
          @if($item->deskripsi)
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
              <p class="text-xs text-slate-700 leading-relaxed">{{ $item->deskripsi }}</p>
            </div>
          @endif
        </div>

        <!-- Kondisi Tanaman -->
        <div class="rounded-xl border border-slate-200 p-3 flex items-center justify-between">
          <span class="text-xs font-medium text-slate-600">Kondisi Tanaman:</span>
          @php
            $kondisi = strtolower($item->kondisi_tanaman ?? '');
          @endphp
          @if(str_contains($kondisi, 'sehat') || str_contains($kondisi, 'baik'))
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">
              <i data-lucide="check" class="w-3.5 h-3.5"></i> Sehat
            </span>
          @elseif(str_contains($kondisi, 'perhatian') || str_contains($kondisi, 'hama') || str_contains($kondisi, 'kering'))
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
              <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Perlu Perhatian
            </span>
          @else
            <span class="text-xs text-slate-500 font-semibold">{{ $item->kondisi_tanaman ?: '-' }}</span>
          @endif
        </div>

        <!-- Foto Dokumentasi -->
        @if($imgSrcModal)
          <div>
            <label class="block text-xs font-bold text-[#1A2D10] mb-1.5">Foto Dokumentasi</label>
            <div class="overflow-hidden rounded-xl border border-[#DCE9D0] bg-[#F7FAF4]">
              <img src="{{ $imgSrcModal }}" alt="Dokumentasi Aktivitas" class="w-full max-h-56 object-cover hover:scale-105 transition-transform duration-300">
            </div>
          </div>
        @endif
      </div>

      <div class="mt-5 flex items-center justify-between border-t border-[#E4F0D6] pt-3">
        @if($item->id_lahan)
          <a href="{{ route('pengurus.lahan.show', $item->id_lahan) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#4D9830] hover:underline">
            <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Halaman Lahan
          </a>
        @else
          <div></div>
        @endif
        <button type="button" onclick="closeModalAktivitas('modal-aktivitas-{{ $item->id_aktivitas }}')" class="rounded-xl bg-[#4D9830] px-4 py-2 text-xs font-bold text-white hover:bg-[#3D8024] transition">
          Tutup
        </button>
      </div>
    </div>
  </div>
@endforeach

<script>
  function openModalAktivitas(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('hidden');
  }
  function closeModalAktivitas(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('hidden');
  }
</script>
</div>
@endsection

