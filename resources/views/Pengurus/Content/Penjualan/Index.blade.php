@extends('Pengurus.Layout._layout')

@section('title', 'Penjualan Hasil Pertanian')

@section('content')
<div class="space-y-6">
  @if(session('success'))
    <div class="flex items-center gap-3 p-4 rounded-2xl bg-[#EBF6E0] border border-[#C5DFB0] text-xs sm:text-sm text-[#1A2D10]">
      <span class="w-8 h-8 rounded-xl bg-[#4D9830] text-white flex items-center justify-center shrink-0"><i data-lucide="check" class="w-4 h-4"></i></span>
      <span class="font-semibold">{{ session('success') }}</span>
    </div>
  @endif

  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">Penjualan Hasil Pertanian</h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">Catat transaksi penjualan hasil panen {{ $pengurus->kelompokTani?->nama_kelompok ?? 'kelompok tani' }}.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2.5 shrink-0">
      <a href="{{ route('pengurus.monitoring') }}"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] hover:border-[#9AB880] transition-all shadow-2xs">
        <i data-lucide="activity" class="w-4 h-4 text-[#4D9830]"></i>
        Monitoring
      </a>
      <a href="{{ route('pengurus.panen') }}"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] hover:border-[#9AB880] transition-all shadow-2xs">
        <i data-lucide="wheat" class="w-4 h-4 text-[#4D9830]"></i>
        Data Panen
      </a>
      <a href="{{ route('pengurus.laporan') }}"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] hover:border-[#9AB880] transition-all shadow-2xs">
        <i data-lucide="file-text" class="w-4 h-4 text-[#4D9830]"></i>
        Laporan
      </a>
      <a href="{{ route('pengurus.laporan.cetak', ['tanggal_mulai' => $tanggalMulai->toDateString(), 'tanggal_selesai' => $tanggalSelesai->toDateString()]) }}" target="_blank" rel="noopener"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] hover:border-[#9AB880] transition-all shadow-2xs">
        <i data-lucide="printer" class="w-4 h-4 text-[#4D9830]"></i>
        Cetak / PDF
      </a>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs flex items-center gap-4"><div class="w-11 h-11 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center"><i data-lucide="receipt" class="w-5 h-5"></i></div><div><span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider">Transaksi</span><strong class="block text-2xl font-extrabold text-[#1A2D10]">{{ number_format($stats['transaksi'],0,',','.') }}</strong><span class="text-[10px] text-[#6B7F5B]">Pada periode laporan</span></div></div>
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs flex items-center gap-4"><div class="w-11 h-11 rounded-2xl bg-[#E0F2FE] text-[#0284C7] flex items-center justify-center"><i data-lucide="scale" class="w-5 h-5"></i></div><div><span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider">Berat Terjual</span><strong class="block text-2xl font-extrabold text-[#0284C7]">{{ number_format($stats['berat_kg'],2,',','.') }} kg</strong><span class="text-[10px] text-[#6B7F5B]">Total transaksi dengan satuan kg</span></div></div>
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs flex items-center gap-4"><div class="w-11 h-11 rounded-2xl bg-[#DCFCE7] text-[#16A34A] flex items-center justify-center"><i data-lucide="wallet" class="w-5 h-5"></i></div><div><span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider">Nilai Transaksi</span><strong class="block text-2xl font-extrabold text-[#16A34A]">Rp{{ number_format($stats['total_penjualan'],0,',','.') }}</strong><span class="text-[10px] text-[#6B7F5B]">Tidak termasuk transaksi dibatalkan</span></div></div>
  </div>

  <section class="p-4 sm:p-5 rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs space-y-4">
    <div class="flex items-start gap-3">
      <div class="w-9 h-9 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0"><i data-lucide="plus-circle" class="w-5 h-5"></i></div>
      <div><h2 class="text-sm font-bold text-[#1A2D10]">Catat Transaksi Penjualan</h2><p class="text-[11px] text-[#9AB880] mt-0.5">Pilih hasil panen yang sudah disetujui. Sistem akan membatasi berat penjualan sesuai sisa panen.</p></div>
    </div>

    @if($errors->any())
      <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 space-y-1">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
      </div>
    @endif

    @if($panenTersedia->isEmpty())
      <div class="p-4 rounded-xl border border-[#FDE68A] bg-[#FFFBEB] text-xs text-[#92400E]">
        <p class="font-bold">Belum ada hasil panen yang dapat dijual.</p>
        <p class="mt-1 leading-relaxed">Pastikan anggota sudah mencatat hasil panen dan Pengurus menyetujui catatan tersebut pada menu Panen. Penjualan hanya dapat dicatat dari panen yang disetujui dan masih memiliki sisa berat.</p>
        <a href="{{ route('pengurus.panen') }}" class="inline-flex items-center gap-1.5 mt-2 font-bold text-[#4D7827] hover:underline">Buka Data Panen <span aria-hidden="true">→</span></a>
      </div>
    @else
      <form method="POST" action="{{ route('pengurus.penjualan.store') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5" id="form-penjualan">
        @csrf
        <div class="md:col-span-2 xl:col-span-3">
          <label for="id_panen" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">Hasil Panen <span class="text-red-500">*</span></label>
          <select name="id_panen" id="id_panen" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20">
            <option value="">Pilih hasil panen yang akan dijual</option>
            @foreach($panenTersedia as $item)
              @php($sisa = max(0, (float) $item->berat_kg - (float) ($item->total_terjual_kg ?? 0)))
              <option value="{{ $item->id_panen }}" data-sisa="{{ number_format($sisa,2,'.','') }}" data-harga="{{ number_format((float)$item->harga_per_kg,2,'.','') }}" {{ old('id_panen') == $item->id_panen ? 'selected' : '' }}>
                {{ $item->kode_panen }} · {{ $item->komoditas_nama }} · {{ number_format($sisa,2,',','.') }} kg tersedia · {{ $item->pengguna?->nama ?? 'Anggota' }}
              </option>
            @endforeach
          </select>
          <p class="text-[10px] text-[#9AB880] mt-1" id="sisa-panen-info">Pilih hasil panen untuk melihat sisa berat yang tersedia.</p>
        </div>
        <div>
          <label for="tanggal_penjualan" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">Tanggal Penjualan <span class="text-red-500">*</span></label>
          <input type="date" name="tanggal_penjualan" id="tanggal_penjualan" max="{{ now()->toDateString() }}" value="{{ old('tanggal_penjualan', now()->toDateString()) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830]">
        </div>
        <div>
          <label for="nama_pembeli" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">Nama Pembeli</label>
          <input type="text" name="nama_pembeli" id="nama_pembeli" maxlength="120" value="{{ old('nama_pembeli') }}" placeholder="Nama pembeli (opsional)" class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830]">
        </div>
        <div>
          <label for="berat_kg" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">Berat Terjual (kg) <span class="text-red-500">*</span></label>
          <input type="number" name="berat_kg" id="berat_kg" min="0.01" step="0.01" max="{{ old('berat_kg') }}" value="{{ old('berat_kg') }}" required placeholder="Contoh: 125.50" class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830]">
        </div>
        <div>
          <label for="harga_per_kg" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">Harga per Kg (Rp) <span class="text-red-500">*</span></label>
          <input type="number" name="harga_per_kg" id="harga_per_kg" min="1" step="0.01" value="{{ old('harga_per_kg') }}" required placeholder="Contoh: 6500" class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830]">
        </div>
        <div>
          <label class="block text-xs font-semibold text-[#1A2D10] mb-1.5">Perkiraan Total</label>
          <div class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4F0D6] bg-[#F5F8F1] text-sm font-extrabold text-[#4D9830]" id="total-penjualan-preview">Rp0</div>
        </div>
        <div class="md:col-span-2 xl:col-span-3">
          <label for="catatan" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">Catatan</label>
          <textarea name="catatan" id="catatan" rows="2" maxlength="1000" placeholder="Catatan transaksi (opsional)" class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830] resize-y">{{ old('catatan') }}</textarea>
        </div>
        <div class="md:col-span-2 xl:col-span-3 flex justify-end pt-1">
          <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#4D9830] hover:bg-[#3D7826] text-white text-xs sm:text-sm font-bold transition-colors shadow-sm"><i data-lucide="save" class="w-4 h-4"></i> Simpan Transaksi</button>
        </div>
      </form>
    @endif
  </section>

  <section class="rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs overflow-visible">
    <div class="p-4 sm:p-5 border-b border-[#F0F7E8]">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3"><div><h2 class="text-sm font-bold text-[#1A2D10]">Riwayat Penjualan</h2><p class="text-[11px] text-[#9AB880] mt-0.5">Transaksi kelompok dari aplikasi Mobile/Marketplace dan pencatatan Pengurus.</p></div><span class="text-[11px] text-[#9AB880]">{{ $penjualan->total() }} transaksi</span></div>
      <form method="GET" action="{{ route('pengurus.penjualan') }}" id="filter-riwayat-penjualan" class="grid grid-cols-1 lg:grid-cols-[minmax(260px,0.95fr)_minmax(260px,1.2fr)_auto] gap-2.5 items-start">
        <input type="hidden" name="tanggal_mulai" id="tanggal-mulai-hidden" value="{{ $tanggalMulai->toDateString() }}">
        <input type="hidden" name="tanggal_selesai" id="tanggal-selesai-hidden" value="{{ $tanggalSelesai->toDateString() }}">

        <div class="relative min-w-0">
          <button type="button" id="toggle-periode-penjualan" aria-expanded="false" aria-controls="panel-periode-penjualan"
            class="w-full min-h-[42px] flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-left hover:bg-white hover:border-[#9AB880] focus:outline-none focus:ring-2 focus:ring-[#4D9830]/15 transition-all">
            <span class="flex items-center gap-2.5 min-w-0">
              <span class="w-8 h-8 rounded-lg bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0"><i data-lucide="calendar-range" class="w-4 h-4"></i></span>
              <span class="min-w-0">
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-[#9AB880]">Periode Penjualan</span>
                <span id="label-periode-penjualan" class="block text-xs sm:text-sm font-semibold text-[#1A2D10] truncate">{{ $tanggalMulai->translatedFormat('d M Y') }} – {{ $tanggalSelesai->translatedFormat('d M Y') }}</span>
              </span>
            </span>
            <i data-lucide="chevron-down" id="ikon-periode-penjualan" class="w-4 h-4 text-[#6B7F5B] shrink-0 transition-transform"></i>
          </button>

          <div id="panel-periode-penjualan" class="hidden absolute left-0 top-full z-30 mt-2 w-full lg:w-[22rem] max-w-[calc(100vw-2rem)] rounded-2xl border border-[#E4F0D6] bg-white p-4 shadow-xl">
            <div class="mb-3">
              <p class="text-xs font-bold text-[#1A2D10]">Pilih periode</p>
              <p class="text-[11px] text-[#9AB880] mt-0.5">Gunakan rentang tanggal atau pilih periode cepat.</p>
            </div>
            <div class="flex flex-wrap gap-2 mb-4">
              <button type="button" data-preset-periode="today" class="preset-periode rounded-lg border border-[#C5DFB0] bg-white px-3 py-2 text-[11px] font-semibold text-[#4A6030] hover:bg-[#F5F8F1]">Hari Ini</button>
              <button type="button" data-preset-periode="last7" class="preset-periode rounded-lg border border-[#C5DFB0] bg-white px-3 py-2 text-[11px] font-semibold text-[#4A6030] hover:bg-[#F5F8F1]">7 Hari Terakhir</button>
              <button type="button" data-preset-periode="month" class="preset-periode rounded-lg border border-[#C5DFB0] bg-white px-3 py-2 text-[11px] font-semibold text-[#4A6030] hover:bg-[#F5F8F1]">Bulan Ini</button>
              <button type="button" data-preset-periode="custom" class="preset-periode rounded-lg border border-[#C5DFB0] bg-white px-3 py-2 text-[11px] font-semibold text-[#4A6030] hover:bg-[#F5F8F1]">Pilih Rentang</button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label for="tanggal-mulai-picker" class="block text-[11px] font-semibold text-[#4A6030] mb-1.5">Dari</label>
                <input type="date" id="tanggal-mulai-picker" value="{{ $tanggalMulai->toDateString() }}" class="w-full min-w-0 px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs text-[#1A2D10] focus:outline-none focus:border-[#4D9830]">
              </div>
              <div>
                <label for="tanggal-selesai-picker" class="block text-[11px] font-semibold text-[#4A6030] mb-1.5">Sampai</label>
                <input type="date" id="tanggal-selesai-picker" min="{{ $tanggalMulai->toDateString() }}" value="{{ $tanggalSelesai->toDateString() }}" class="w-full min-w-0 px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs text-[#1A2D10] focus:outline-none focus:border-[#4D9830]">
              </div>
            </div>
            <p id="error-periode-penjualan" class="hidden mt-2 text-[11px] font-medium text-red-600">Tanggal akhir harus sama atau setelah tanggal awal.</p>
            <div class="flex justify-end mt-4 pt-3 border-t border-[#F0F7E8]">
              <button type="button" id="terapkan-periode-penjualan" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white text-xs font-bold shadow-sm shadow-[#4D9830]/20 transition-all">
                <i data-lucide="check" class="w-3.5 h-3.5"></i> Gunakan Periode
              </button>
            </div>
          </div>
        </div>

        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari pembeli, komoditas, ID..." class="w-full min-h-[42px] px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830] focus:bg-white transition-all">
        <button type="submit" class="inline-flex min-h-[42px] justify-center items-center gap-2 px-4 py-2.5 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white text-xs sm:text-sm font-bold shadow-sm shadow-[#4D9830]/20 transition-all"><i data-lucide="filter" class="w-4 h-4"></i> Filter</button>
      </form>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[850px] text-left border-collapse">
        <thead><tr class="bg-[#F5F8F1]/70 text-[10px] font-bold text-[#6B7F5B] uppercase tracking-wider border-b border-[#E4F0D6]"><th class="py-3 px-4">Tanggal</th><th class="py-3 px-4">Kode Transaksi</th><th class="py-3 px-4">Pembeli</th><th class="py-3 px-4">Komoditas / Panen</th><th class="py-3 px-4 text-right">Jumlah</th><th class="py-3 px-4 text-right">Harga / Satuan</th><th class="py-3 px-4 text-right">Total</th></tr></thead>
        <tbody class="divide-y divide-[#F0F7E8] text-xs">
          @forelse($penjualan as $item)
            <tr class="hover:bg-[#F5F8F1]/50"><td class="py-3 px-4 whitespace-nowrap text-[#4A6030]">{{ $item->tanggal_transaksi?->translatedFormat('d M Y') ?? '-' }}</td><td class="py-3 px-4 font-semibold text-[#1A2D10]">{{ $item->kode_transaksi ?: $item->id_penjualan }}<span class="block mt-1 text-[10px] font-medium {{ $item->status_pembayaran === 'lunas' ? 'text-[#4D9830]' : 'text-[#9AB880]' }}">{{ $item->status_label }}</span></td><td class="py-3 px-4 text-[#4A6030]">{{ $item->nama_pembeli ?: '-' }}</td><td class="py-3 px-4"><span class="font-semibold text-[#1A2D10]">{{ $item->komoditas?->nama_komoditas ?: $item->nama_produk }}</span><span class="block text-[10px] text-[#9AB880] mt-0.5">{{ $item->panen?->kode_panen ?? ($item->id_produk ? 'Marketplace' : 'Transaksi Mobile') }}</span></td><td class="py-3 px-4 text-right whitespace-nowrap text-[#4A6030]">{{ number_format((float)$item->jumlah,2,',','.') }} {{ $item->satuan }}</td><td class="py-3 px-4 text-right whitespace-nowrap text-[#4A6030]">Rp{{ number_format((float)$item->harga_satuan,0,',','.') }}</td><td class="py-3 px-4 text-right whitespace-nowrap font-bold text-[#1A2D10]">Rp{{ number_format((float)$item->total_harga,0,',','.') }}</td></tr>
          @empty
            <tr><td colspan="7" class="py-10 text-center"><div class="w-10 h-10 mx-auto rounded-xl bg-[#F5F8F1] text-[#9AB880] flex items-center justify-center mb-2"><i data-lucide="receipt-text" class="w-5 h-5"></i></div><p class="text-xs font-semibold text-[#4A6030]">Belum ada transaksi penjualan</p><p class="text-[11px] text-[#9AB880] mt-1">Transaksi yang dicatat akan muncul di sini.</p></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($penjualan->hasPages())<div class="px-4 sm:px-5 py-4 border-t border-[#F0F7E8]">{{ $penjualan->links() }}</div>@endif
  </section>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Filter satu kolom periode dengan pilihan cepat dan rentang tanggal manual.
    const filterForm = document.getElementById('filter-riwayat-penjualan');
    const togglePeriod = document.getElementById('toggle-periode-penjualan');
    const periodPanel = document.getElementById('panel-periode-penjualan');
    const periodLabel = document.getElementById('label-periode-penjualan');
    const startPicker = document.getElementById('tanggal-mulai-picker');
    const endPicker = document.getElementById('tanggal-selesai-picker');
    const startHidden = document.getElementById('tanggal-mulai-hidden');
    const endHidden = document.getElementById('tanggal-selesai-hidden');
    const periodError = document.getElementById('error-periode-penjualan');
    const applyPeriod = document.getElementById('terapkan-periode-penjualan');
    const periodIcon = document.getElementById('ikon-periode-penjualan');

    const parseLocalDate = (value) => {
      const [year, month, day] = (value || '').split('-').map(Number);
      return year && month && day ? new Date(year, month - 1, day) : null;
    };
    const toLocalIso = (date) => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    };
    const formatPeriodDate = (value) => {
      const date = parseLocalDate(value);
      return date ? new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(date) : value;
    };
    const updatePeriodLabel = () => {
      if (!startPicker?.value || !endPicker?.value || !periodLabel) return;
      periodLabel.textContent = `${formatPeriodDate(startPicker.value)} – ${formatPeriodDate(endPicker.value)}`;
    };
    const closePeriodPanel = () => {
      periodPanel?.classList.add('hidden');
      togglePeriod?.setAttribute('aria-expanded', 'false');
      periodIcon?.classList.remove('rotate-180');
    };
    const openPeriodPanel = () => {
      periodPanel?.classList.remove('hidden');
      togglePeriod?.setAttribute('aria-expanded', 'true');
      periodIcon?.classList.add('rotate-180');
    };
    const syncPeriod = (showError = true) => {
      const start = startPicker?.value || '';
      const end = endPicker?.value || '';
      if (!start || !end || start > end) {
        if (periodError && showError) periodError.classList.remove('hidden');
        return false;
      }
      periodError?.classList.add('hidden');
      if (startHidden) startHidden.value = start;
      if (endHidden) endHidden.value = end;
      if (endPicker) endPicker.min = start;
      updatePeriodLabel();
      return true;
    };

    togglePeriod?.addEventListener('click', function () {
      if (periodPanel?.classList.contains('hidden')) openPeriodPanel();
      else closePeriodPanel();
    });
    startPicker?.addEventListener('change', function () {
      if (endPicker) endPicker.min = startPicker.value;
      periodError?.classList.add('hidden');
    });
    endPicker?.addEventListener('change', function () {
      periodError?.classList.add('hidden');
    });
    document.querySelectorAll('[data-preset-periode]').forEach((button) => {
      button.addEventListener('click', function () {
        const preset = button.dataset.presetPeriode;
        const today = new Date();
        let start = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        let end = new Date(today.getFullYear(), today.getMonth(), today.getDate());

        if (preset === 'last7') start.setDate(start.getDate() - 6);
        if (preset === 'month') start = new Date(today.getFullYear(), today.getMonth(), 1);
        if (preset === 'custom') {
          startPicker?.focus();
          return;
        }

        if (startPicker) startPicker.value = toLocalIso(start);
        if (endPicker) endPicker.value = toLocalIso(end);
        syncPeriod(false);
        closePeriodPanel();
      });
    });
    applyPeriod?.addEventListener('click', function () {
      if (syncPeriod()) closePeriodPanel();
    });
    filterForm?.addEventListener('submit', function (event) {
      if (!syncPeriod()) {
        event.preventDefault();
        openPeriodPanel();
      }
    });
    document.addEventListener('click', function (event) {
      if (periodPanel && togglePeriod && !periodPanel.contains(event.target) && !togglePeriod.contains(event.target)) {
        closePeriodPanel();
      }
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closePeriodPanel();
    });
    updatePeriodLabel();

    const panenSelect = document.getElementById('id_panen');
    const beratInput = document.getElementById('berat_kg');
    const hargaInput = document.getElementById('harga_per_kg');
    const sisaInfo = document.getElementById('sisa-panen-info');
    const totalPreview = document.getElementById('total-penjualan-preview');
    const rupiah = (value) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value || 0);

    function updatePreview() {
      if (!totalPreview) return;
      const berat = Number(beratInput?.value || 0);
      const harga = Number(hargaInput?.value || 0);
      totalPreview.textContent = 'Rp' + rupiah(berat * harga);
    }

    function updatePanenChoice() {
      if (!panenSelect) return;
      const option = panenSelect.options[panenSelect.selectedIndex];
      const remaining = option?.dataset?.sisa;
      if (remaining) {
        beratInput.max = remaining;
        sisaInfo.textContent = 'Sisa hasil panen yang dapat dijual: ' + rupiah(Number(remaining)) + ' kg.';
        if (!hargaInput.value && Number(option.dataset.harga || 0) > 0) {
          hargaInput.value = Number(option.dataset.harga).toFixed(0);
        }
      } else if (sisaInfo) {
        sisaInfo.textContent = 'Pilih hasil panen untuk melihat sisa berat yang tersedia.';
        if (beratInput) beratInput.removeAttribute('max');
      }
      updatePreview();
    }

    panenSelect?.addEventListener('change', updatePanenChoice);
    beratInput?.addEventListener('input', updatePreview);
    hargaInput?.addEventListener('input', updatePreview);
    updatePanenChoice();
  });
</script>
@endpush
