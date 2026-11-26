@extends('Pengurus.Layout._layout')

@section('title', 'Laporan Kelompok Tani')

@section('content')
<div class="space-y-6">

  {{-- Header halaman --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">Laporan Kelompok Tani</h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Ringkasan anggota, aktivitas pertanian, hasil panen, dan transaksi penjualan {{ $kelompok->nama_kelompok }}.
      </p>
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
      <a href="{{ route('pengurus.laporan.cetak', request()->query()) }}" target="_blank" rel="noopener"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] hover:border-[#9AB880] transition-all shadow-2xs"
        aria-label="Cetak atau simpan laporan sebagai PDF" title="Cetak atau simpan sebagai PDF">
        <i data-lucide="printer" class="w-4 h-4 text-[#4D9830]"></i>
        Cetak / PDF
      </a>
    </div>
  </div>

  {{-- Filter periode --}}
  <div class="p-4 sm:p-5 rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs">
    <div class="flex items-center gap-2 mb-3">
      <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center">
        <i data-lucide="calendar-range" class="w-4 h-4"></i>
      </div>
      <div>
        <h2 class="text-sm font-bold text-[#1A2D10]">Periode Laporan</h2>
        <p class="text-[11px] text-[#9AB880]">Filter periode diterapkan pada aktivitas, panen, dan penjualan; anggota serta lahan menampilkan kondisi saat ini.</p>
      </div>
    </div>
    <form method="GET" action="{{ route('pengurus.laporan') }}" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[1fr_1fr_auto_auto] gap-3 items-end">
      <div>
        <label for="tanggal_mulai" class="block text-xs font-semibold text-[#4A6030] mb-1.5">Tanggal Mulai</label>
        <input id="tanggal_mulai" name="tanggal_mulai" type="date" value="{{ $tanggalMulai->toDateString() }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830] focus:bg-white transition-all">
      </div>
      <div>
        <label for="tanggal_selesai" class="block text-xs font-semibold text-[#4A6030] mb-1.5">Tanggal Selesai</label>
        <input id="tanggal_selesai" name="tanggal_selesai" type="date" min="{{ $tanggalMulai->toDateString() }}" value="{{ $tanggalSelesai->toDateString() }}"
          class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#4D9830] focus:bg-white transition-all">
      </div>
      <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#4D9830] hover:bg-[#3D7826] text-white text-xs sm:text-sm font-bold transition-colors">
        <i data-lucide="filter" class="w-4 h-4"></i> Terapkan
      </button>
      <a href="{{ route('pengurus.laporan') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] transition-colors">
        <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
      </a>
    </form>
    @if($errors->any())
      <p class="mt-3 text-xs font-medium text-red-600">{{ $errors->first() }}</p>
    @endif
  </div>

  {{-- Kartu ringkasan --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0"><i data-lucide="users" class="w-6 h-6"></i></div>
      <div><span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Anggota Terdaftar</span><span class="text-2xl font-extrabold text-[#1A2D10]">{{ number_format($totalAnggota, 0, ',', '.') }}</span><span class="text-[11px] text-[#4A6030] block mt-0.5">Keanggotaan saat ini</span></div>
    </div>
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#E0F2FE] text-[#0284C7] flex items-center justify-center shrink-0"><i data-lucide="clipboard-list" class="w-6 h-6"></i></div>
      <div><span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Aktivitas Pertanian</span><span class="text-2xl font-extrabold text-[#0284C7]">{{ number_format($totalAktivitas, 0, ',', '.') }}</span><span class="text-[11px] text-[#4A6030] block mt-0.5">Pada periode terpilih</span></div>
    </div>
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#DCFCE7] text-[#16A34A] flex items-center justify-center shrink-0"><i data-lucide="wheat" class="w-6 h-6"></i></div>
      <div><span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Hasil Panen</span><span class="text-2xl font-extrabold text-[#16A34A]">{{ number_format($totalPanen, 0, ',', '.') }}</span><span class="text-[11px] text-[#4A6030] block mt-0.5">{{ number_format($totalBeratPanen, 2, ',', '.') }} kg tercatat</span></div>
    </div>
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#FEF3C7] text-[#D97706] flex items-center justify-center shrink-0"><i data-lucide="map" class="w-6 h-6"></i></div>
      <div><span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Lahan Terdaftar</span><span class="text-2xl font-extrabold text-[#D97706]">{{ number_format($totalLahan, 0, ',', '.') }}</span><span class="text-[11px] text-[#4A6030] block mt-0.5">Total luas {{ number_format($totalLuasLahan, 2, ',', '.') }} ha</span></div>
    </div>
  </div>

  {{-- Ringkasan keuangan & transparansi data penjualan --}}
  <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    <div class="p-5 rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center"><i data-lucide="wallet" class="w-5 h-5"></i></div>
        <div><h2 class="text-sm font-bold text-[#1A2D10]">Ringkasan Biaya Aktivitas</h2><p class="text-[11px] text-[#9AB880]">Jumlah biaya yang dicatat selama periode laporan</p></div>
      </div>
      <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#1A2D10] mt-4">Rp{{ number_format($totalBiayaAktivitas, 0, ',', '.') }}</p>
      <p class="text-[11px] text-[#6B7F5B] mt-1">Berdasarkan kolom biaya pada catatan aktivitas pertanian.</p>
    </div>
    <div class="p-5 rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#FEF3C7] text-[#D97706] flex items-center justify-center"><i data-lucide="shopping-bag" class="w-5 h-5"></i></div>
        <div><h2 class="text-sm font-bold text-[#1A2D10]">Data Penjualan</h2><p class="text-[11px] text-[#9AB880]">Transaksi kelompok dari aplikasi Mobile/Marketplace dan pencatatan Pengurus</p></div>
      </div>
      @if($penjualanTersedia)
        <div class="mt-4 grid grid-cols-2 gap-3">
          <div class="p-3 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6]"><span class="block text-[10px] font-semibold uppercase tracking-wider text-[#9AB880]">Transaksi</span><strong class="block text-lg font-extrabold text-[#1A2D10]">{{ number_format($totalTransaksiPenjualan, 0, ',', '.') }}</strong></div>
          <div class="p-3 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6]"><span class="block text-[10px] font-semibold uppercase tracking-wider text-[#9AB880]">Berat Terjual</span><strong class="block text-lg font-extrabold text-[#1A2D10]">{{ number_format($totalBeratTerjual, 2, ',', '.') }} kg</strong></div>
        </div>
        <p class="text-[11px] text-[#6B7F5B] mt-3">Total nilai transaksi (tidak termasuk dibatalkan):</p>
        <p class="text-2xl font-extrabold text-[#4D9830] mt-0.5">Rp{{ number_format($totalNilaiPenjualan, 0, ',', '.') }}</p>
      @else
        <div class="mt-4 p-3.5 rounded-xl border border-[#FDE68A] bg-[#FFFBEB]">
          <p class="text-xs font-bold text-[#92400E]">Tabel transaksi penjualan belum tersedia di database.</p>
          <p class="text-[11px] leading-relaxed text-[#92400E] mt-1">Jalankan migration terbaru agar menu Penjualan dapat menyimpan transaksi aktual dan laporan menampilkannya.</p>
        </div>
      @endif
      <div class="flex items-end justify-between gap-3 mt-3 pt-3 border-t border-[#F0F7E8]">
        <div><span class="block text-[11px] text-[#9AB880] font-semibold">Estimasi nilai panen disetujui</span><span class="block text-lg font-extrabold text-[#1A2D10]">Rp{{ number_format($estimasiNilaiPanen, 0, ',', '.') }}</span></div>
        <span class="text-[10px] font-bold text-[#92400E] bg-[#FEF3C7] rounded-full px-2.5 py-1">Bukan penjualan aktual</span>
      </div>
    </div>
  </div>

  {{-- Tabel anggota --}}
  <section class="rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs overflow-hidden">
    <div class="px-4 sm:px-5 py-4 border-b border-[#F0F7E8] flex items-center justify-between gap-3">
      <div><h2 class="text-sm font-bold text-[#1A2D10]">Data Anggota</h2><p class="text-[11px] text-[#9AB880] mt-0.5">Daftar anggota yang masih tercatat pada kelompok saat ini.</p></div>
      <span class="text-xs font-bold text-[#4D9830] bg-[#EBF6E0] rounded-full px-3 py-1.5">{{ $anggota->count() }} anggota</span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[680px] text-left border-collapse">
        <thead><tr class="bg-[#F5F8F1]/70 text-[10px] font-bold text-[#6B7F5B] uppercase tracking-wider border-b border-[#E4F0D6]"><th class="py-3 px-4 w-12">No</th><th class="py-3 px-4">Nama Anggota</th><th class="py-3 px-4">No. Telepon</th><th class="py-3 px-4">Status Keanggotaan</th><th class="py-3 px-4">Tanggal Terdaftar</th></tr></thead>
        <tbody class="divide-y divide-[#F0F7E8] text-xs">
          @forelse($anggota as $member)
            @php($statusKeanggotaan = $member->anggota?->status_keanggotaan ?? $member->status)
            <tr class="hover:bg-[#F5F8F1]/50"><td class="py-3 px-4 text-[#9AB880]">{{ $loop->iteration }}</td><td class="py-3 px-4 font-semibold text-[#1A2D10]">{{ $member->nama }}</td><td class="py-3 px-4 text-[#4A6030]">{{ $member->no_telepon ?: '-' }}</td><td class="py-3 px-4"><span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold {{ $statusKeanggotaan === 'Aktif' ? 'bg-[#EBF6E0] text-[#4D9830]' : 'bg-slate-100 text-slate-600' }}">{{ $statusKeanggotaan }}</span></td><td class="py-3 px-4 text-[#4A6030]">{{ $member->created_at?->translatedFormat('d M Y') ?? '-' }}</td></tr>
          @empty
            <tr><td colspan="5" class="py-8 text-center text-xs text-[#9AB880]">Belum ada anggota terdaftar pada kelompok ini.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  {{-- Tabel aktivitas --}}
  <section class="rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs overflow-hidden">
    <div class="px-4 sm:px-5 py-4 border-b border-[#F0F7E8] flex items-center justify-between gap-3">
      <div><h2 class="text-sm font-bold text-[#1A2D10]">Aktivitas Pertanian</h2><p class="text-[11px] text-[#9AB880] mt-0.5">Catatan aktivitas pada {{ $tanggalMulai->translatedFormat('d M Y') }} – {{ $tanggalSelesai->translatedFormat('d M Y') }}.</p></div>
      <a href="{{ route('pengurus.monitoring') }}" class="text-[11px] font-bold text-[#4D9830] hover:text-[#3D7826]">Lihat monitoring <span aria-hidden="true">→</span></a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] text-left border-collapse">
        <thead><tr class="bg-[#F5F8F1]/70 text-[10px] font-bold text-[#6B7F5B] uppercase tracking-wider border-b border-[#E4F0D6]"><th class="py-3 px-4">Tanggal</th><th class="py-3 px-4">Anggota</th><th class="py-3 px-4">Aktivitas</th><th class="py-3 px-4">Lahan</th><th class="py-3 px-4">Kondisi</th><th class="py-3 px-4 text-right">Biaya</th></tr></thead>
        <tbody class="divide-y divide-[#F0F7E8] text-xs">
          @forelse($aktivitas as $item)
            <tr class="hover:bg-[#F5F8F1]/50"><td class="py-3 px-4 whitespace-nowrap text-[#4A6030]">{{ $item->tanggal?->translatedFormat('d M Y') ?? '-' }}</td><td class="py-3 px-4 font-semibold text-[#1A2D10]">{{ $item->pengguna?->nama ?? '-' }}</td><td class="py-3 px-4"><span class="font-semibold text-[#1A2D10]">{{ $item->judul }}</span><span class="block text-[10px] text-[#9AB880] mt-0.5">{{ ucfirst(str_replace('_', ' ', $item->kategori)) }}</span></td><td class="py-3 px-4 text-[#4A6030]">{{ $item->lahan?->nama_lahan ?? '-' }}</td><td class="py-3 px-4 text-[#4A6030]">{{ ucfirst(str_replace('_', ' ', $item->kondisi_tanaman ?? '-')) }}</td><td class="py-3 px-4 text-right font-semibold text-[#1A2D10] whitespace-nowrap">Rp{{ number_format((float) $item->biaya, 0, ',', '.') }}</td></tr>
          @empty
            <tr><td colspan="6" class="py-8 text-center text-xs text-[#9AB880]">Belum ada aktivitas pada periode yang dipilih.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($totalAktivitas > $aktivitas->count())<div class="px-5 py-3 border-t border-[#F0F7E8] text-[11px] text-[#9AB880]">Menampilkan {{ $aktivitas->count() }} dari {{ $totalAktivitas }} aktivitas terbaru. Buka Monitoring Pertanian untuk melihat seluruh catatan.</div>@endif
  </section>

  {{-- Tabel panen --}}
  <section class="rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs overflow-hidden">
    <div class="px-4 sm:px-5 py-4 border-b border-[#F0F7E8] flex items-center justify-between gap-3">
      <div><h2 class="text-sm font-bold text-[#1A2D10]">Hasil Panen</h2><p class="text-[11px] text-[#9AB880] mt-0.5">Catatan panen pada periode laporan. {{ $totalPanenDisetujui }} data telah disetujui.</p></div>
      <a href="{{ route('pengurus.panen') }}" class="text-[11px] font-bold text-[#4D9830] hover:text-[#3D7826]">Lihat data panen <span aria-hidden="true">→</span></a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[820px] text-left border-collapse">
        <thead><tr class="bg-[#F5F8F1]/70 text-[10px] font-bold text-[#6B7F5B] uppercase tracking-wider border-b border-[#E4F0D6]"><th class="py-3 px-4">Tanggal Panen</th><th class="py-3 px-4">Anggota</th><th class="py-3 px-4">Komoditas</th><th class="py-3 px-4">Lahan</th><th class="py-3 px-4 text-right">Berat</th><th class="py-3 px-4">Status</th><th class="py-3 px-4 text-right">Harga / Kg</th></tr></thead>
        <tbody class="divide-y divide-[#F0F7E8] text-xs">
          @forelse($panen as $item)
            <tr class="hover:bg-[#F5F8F1]/50"><td class="py-3 px-4 whitespace-nowrap text-[#4A6030]">{{ $item->tanggal_panen?->translatedFormat('d M Y') ?? '-' }}</td><td class="py-3 px-4 font-semibold text-[#1A2D10]">{{ $item->pengguna?->nama ?? '-' }}</td><td class="py-3 px-4"><span class="font-semibold text-[#1A2D10]">{{ $item->komoditas_nama }}</span><span class="block text-[10px] text-[#9AB880] mt-0.5">{{ $item->varietas ?: 'Varietas -' }}</span></td><td class="py-3 px-4 text-[#4A6030]">{{ $item->lahan?->nama_lahan ?? '-' }}</td><td class="py-3 px-4 text-right font-semibold text-[#1A2D10] whitespace-nowrap">{{ number_format((float) $item->berat_kg, 2, ',', '.') }} kg</td><td class="py-3 px-4"><span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold {{ $item->status === 'disetujui' ? 'bg-[#EBF6E0] text-[#4D9830]' : ($item->status === 'ditolak' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">{{ ucfirst($item->status) }}</span></td><td class="py-3 px-4 text-right text-[#4A6030] whitespace-nowrap">Rp{{ number_format((float) $item->harga_per_kg, 0, ',', '.') }}</td></tr>
          @empty
            <tr><td colspan="7" class="py-8 text-center text-xs text-[#9AB880]">Belum ada catatan panen pada periode yang dipilih.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($totalPanen > $panen->count())<div class="px-5 py-3 border-t border-[#F0F7E8] text-[11px] text-[#9AB880]">Menampilkan {{ $panen->count() }} dari {{ $totalPanen }} catatan panen terbaru. Buka Data Panen untuk melihat seluruh catatan.</div>@endif
  </section>

  {{-- Tabel transaksi penjualan aktual --}}
  <section class="rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs overflow-hidden">
    <div class="px-4 sm:px-5 py-4 border-b border-[#F0F7E8] flex items-center justify-between gap-3">
      <div><h2 class="text-sm font-bold text-[#1A2D10]">Transaksi Penjualan</h2><p class="text-[11px] text-[#9AB880] mt-0.5">Transaksi yang tercatat untuk kelompok pada periode laporan.</p></div>
      @if($penjualanTersedia)
        <div class="flex items-center gap-2">
          <span class="text-xs font-bold text-[#4D9830] bg-[#EBF6E0] rounded-full px-3 py-1.5">{{ $totalTransaksiPenjualan }} transaksi</span>
          <a href="{{ route('pengurus.penjualan') }}" class="text-[11px] font-bold text-[#4D9830] hover:underline">Kelola</a>
        </div>
      @endif
    </div>
    @if(!$penjualanTersedia)
      <div class="p-5 text-xs text-[#92400E] bg-[#FFFBEB]">Integrasi transaksi penjualan belum tersedia. Jalankan migration `2026_11_26_000002_add_group_harvest_and_recorder_to_transaksi_penjualan` terlebih dahulu.</div>
    @else
      <div class="overflow-x-auto">
        <table class="w-full min-w-[800px] text-left border-collapse">
          <thead><tr class="bg-[#F5F8F1]/70 text-[10px] font-bold text-[#6B7F5B] uppercase tracking-wider border-b border-[#E4F0D6]"><th class="py-3 px-4">Tanggal</th><th class="py-3 px-4">Kode Transaksi</th><th class="py-3 px-4">Pembeli</th><th class="py-3 px-4">Komoditas</th><th class="py-3 px-4 text-right">Jumlah</th><th class="py-3 px-4 text-right">Harga / Satuan</th><th class="py-3 px-4 text-right">Total</th></tr></thead>
          <tbody class="divide-y divide-[#F0F7E8] text-xs">
            @forelse($penjualan as $item)
              <tr class="hover:bg-[#F5F8F1]/50"><td class="py-3 px-4 whitespace-nowrap text-[#4A6030]">{{ $item->tanggal_transaksi?->translatedFormat('d M Y') ?? '-' }}</td><td class="py-3 px-4 font-semibold text-[#1A2D10]">{{ $item->kode_transaksi ?: $item->id_penjualan }}</td><td class="py-3 px-4 text-[#4A6030]">{{ $item->nama_pembeli ?: '-' }}</td><td class="py-3 px-4 font-semibold text-[#1A2D10]">{{ $item->komoditas?->nama_komoditas ?: $item->nama_produk }}</td><td class="py-3 px-4 text-right whitespace-nowrap">{{ number_format((float)$item->jumlah, 2, ',', '.') }} {{ $item->satuan }}</td><td class="py-3 px-4 text-right whitespace-nowrap">Rp{{ number_format((float)$item->harga_satuan, 0, ',', '.') }}</td><td class="py-3 px-4 text-right whitespace-nowrap font-bold text-[#1A2D10]">Rp{{ number_format((float)$item->total_harga, 0, ',', '.') }}</td></tr>
            @empty
              <tr><td colspan="7" class="py-8 text-center text-xs text-[#9AB880]">Belum ada transaksi penjualan pada periode yang dipilih.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($totalTransaksiPenjualan > $penjualan->count())<div class="px-5 py-3 border-t border-[#F0F7E8] text-[11px] text-[#9AB880]">Menampilkan {{ $penjualan->count() }} dari {{ $totalTransaksiPenjualan }} transaksi terbaru. Buka menu Penjualan untuk seluruh riwayat.</div>@endif
    @endif
  </section>

</div>
@endsection
