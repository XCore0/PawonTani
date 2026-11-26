<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan {{ $kelompok->nama_kelompok }} - PawonTani</title>
  <style>
    *{box-sizing:border-box}body{font-family:Arial,Helvetica,sans-serif;color:#1A2D10;margin:0;padding:28px;background:#fff;font-size:11px}h1{font-size:22px;margin:0 0 5px}h2{font-size:14px;margin:22px 0 9px;color:#1A2D10}p{margin:0;color:#526647;line-height:1.5}.brand{display:flex;align-items:center;gap:12px;border-bottom:2px solid #4D9830;padding-bottom:14px;margin-bottom:16px}.brand img{width:54px;height:54px;object-fit:contain}.meta{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:12px 0 18px}.meta div{background:#F5F8F1;border:1px solid #E4F0D6;border-radius:8px;padding:9px}.meta strong{display:block;font-size:10px;color:#6B7F5B;margin-bottom:4px}.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:12px 0 18px}.stat{border:1px solid #E4F0D6;border-radius:8px;padding:10px}.stat span{display:block;color:#6B7F5B;font-size:9px;margin-bottom:5px}.stat b{font-size:15px}.note{border:1px solid #FDE68A;background:#FFFBEB;padding:10px;border-radius:8px;color:#92400E;margin:14px 0}.note b{display:block;margin-bottom:4px}table{width:100%;border-collapse:collapse;margin:7px 0 16px;page-break-inside:auto}thead{display:table-header-group}tr{page-break-inside:avoid;page-break-after:auto}th{background:#EBF6E0;color:#365A27;text-transform:uppercase;font-size:9px;letter-spacing:.04em;text-align:left}th,td{border:1px solid #DDE9D2;padding:7px 8px;vertical-align:top}td{color:#3E5234}.muted{color:#6B7F5B;font-size:9px}.footer{margin-top:24px;padding-top:10px;border-top:1px solid #E4F0D6;color:#839676;font-size:9px;text-align:right}.no-print{margin-bottom:15px;display:flex;justify-content:flex-end;gap:8px}.no-print button{border:0;border-radius:8px;padding:9px 13px;background:#4D9830;color:#fff;font-weight:bold;cursor:pointer}.no-print a{border:1px solid #C5DFB0;border-radius:8px;padding:8px 13px;color:#365A27;text-decoration:none}@media print{body{padding:0 6mm}.no-print{display:none}.note{break-inside:avoid}@page{size:A4 landscape;margin:12mm}}
  </style>
</head>
<body>
  <div class="no-print"><a href="javascript:window.close()">Tutup</a><button type="button" onclick="window.print()">Cetak / Simpan PDF</button></div>
  <div class="brand">
    <img src="{{ asset('images/Logo2.png') }}" alt="PawonTani">
    <div><h1>Laporan Kelompok Tani</h1><p><strong>{{ $kelompok->nama_kelompok }}</strong> · PawonTani</p><p class="muted">Periode {{ $tanggalMulai->translatedFormat('d F Y') }} sampai {{ $tanggalSelesai->translatedFormat('d F Y') }}</p></div>
  </div>
  <div class="meta"><div><strong>Alamat Kelompok</strong>{{ $kelompok->alamat ?: '-' }}</div><div><strong>Dicetak Oleh</strong>{{ $pengurus->nama }} · {{ now()->translatedFormat('d F Y, H:i') }}</div></div>
  <div class="stats">
    <div class="stat"><span>ANGGOTA TERDAFTAR</span><b>{{ number_format($totalAnggota,0,',','.') }}</b></div>
    <div class="stat"><span>AKTIVITAS PERTANIAN</span><b>{{ number_format($totalAktivitas,0,',','.') }}</b></div>
    <div class="stat"><span>HASIL PANEN</span><b>{{ number_format($totalPanen,0,',','.') }} catatan</b><p>{{ number_format($totalBeratPanen,2,',','.') }} kg</p></div>
    <div class="stat"><span>LAHAN TERDAFTAR</span><b>{{ number_format($totalLahan,0,',','.') }}</b><p>{{ number_format($totalLuasLahan,2,',','.') }} ha</p></div>
  </div>
  <div class="note"><b>Ringkasan penjualan dan biaya</b>
    @if($penjualanTersedia)
      Transaksi tidak dibatalkan: {{ number_format($totalTransaksiPenjualan,0,',','.') }} transaksi · {{ number_format($totalBeratTerjual,2,',','.') }} kg (satuan kg) · Total nilai transaksi Rp{{ number_format($totalNilaiPenjualan,0,',','.') }}.
    @else
      Integrasi transaksi penjualan belum tersedia. Jalankan migration terbaru untuk mengaktifkannya.
    @endif
    Estimasi nilai panen disetujui (bukan transaksi aktual): Rp{{ number_format($estimasiNilaiPanen,0,',','.') }}. Biaya aktivitas tercatat: Rp{{ number_format($totalBiayaAktivitas,0,',','.') }}.</div>

  <h2>Data Anggota ({{ $anggota->count() }})</h2>
  <table><thead><tr><th>No</th><th>Nama Anggota</th><th>No. Telepon</th><th>Status Keanggotaan</th><th>Tanggal Terdaftar</th></tr></thead><tbody>
    @forelse($anggota as $member)<tr><td>{{ $loop->iteration }}</td><td>{{ $member->nama }}</td><td>{{ $member->no_telepon ?: '-' }}</td><td>{{ $member->anggota?->status_keanggotaan ?? $member->status }}</td><td>{{ $member->created_at?->translatedFormat('d M Y') ?? '-' }}</td></tr>@empty<tr><td colspan="5">Belum ada anggota terdaftar.</td></tr>@endforelse
  </tbody></table>

  <h2>Aktivitas Pertanian ({{ $totalAktivitas }})</h2>
  <table><thead><tr><th>Tanggal</th><th>Anggota</th><th>Aktivitas</th><th>Kategori</th><th>Lahan</th><th>Kondisi Tanaman</th><th>Biaya</th></tr></thead><tbody>
    @forelse($aktivitas as $item)<tr><td>{{ $item->tanggal?->translatedFormat('d M Y') ?? '-' }}</td><td>{{ $item->pengguna?->nama ?? '-' }}</td><td>{{ $item->judul }}</td><td>{{ ucfirst(str_replace('_',' ',$item->kategori)) }}</td><td>{{ $item->lahan?->nama_lahan ?? '-' }}</td><td>{{ ucfirst(str_replace('_',' ',$item->kondisi_tanaman ?? '-')) }}</td><td>Rp{{ number_format((float)$item->biaya,0,',','.') }}</td></tr>@empty<tr><td colspan="7">Belum ada aktivitas pada periode ini.</td></tr>@endforelse
  </tbody></table>

  <h2>Hasil Panen ({{ $totalPanen }})</h2>
  <table><thead><tr><th>Tanggal Panen</th><th>Anggota</th><th>Komoditas</th><th>Varietas</th><th>Lahan</th><th>Berat (kg)</th><th>Harga / kg</th><th>Status</th></tr></thead><tbody>
    @forelse($panen as $item)<tr><td>{{ $item->tanggal_panen?->translatedFormat('d M Y') ?? '-' }}</td><td>{{ $item->pengguna?->nama ?? '-' }}</td><td>{{ $item->komoditas_nama }}</td><td>{{ $item->varietas ?: '-' }}</td><td>{{ $item->lahan?->nama_lahan ?? '-' }}</td><td>{{ number_format((float)$item->berat_kg,2,',','.') }}</td><td>Rp{{ number_format((float)$item->harga_per_kg,0,',','.') }}</td><td>{{ ucfirst($item->status) }}</td></tr>@empty<tr><td colspan="8">Belum ada catatan panen pada periode ini.</td></tr>@endforelse
  </tbody></table>
  <h2>Transaksi Penjualan ({{ $totalTransaksiPenjualan }})</h2>
  @if($penjualanTersedia)
    <table><thead><tr><th>Tanggal</th><th>Kode Transaksi</th><th>Pembeli</th><th>Komoditas</th><th>Jumlah</th><th>Harga / Satuan</th><th>Total</th></tr></thead><tbody>
      @forelse($penjualan as $item)<tr><td>{{ $item->tanggal_transaksi?->translatedFormat('d M Y') ?? '-' }}</td><td>{{ $item->kode_transaksi ?: $item->id_penjualan }}</td><td>{{ $item->nama_pembeli ?: '-' }}</td><td>{{ $item->komoditas?->nama_komoditas ?: $item->nama_produk }}</td><td>{{ number_format((float)$item->jumlah,2,',','.') }} {{ $item->satuan }}</td><td>Rp{{ number_format((float)$item->harga_satuan,0,',','.') }}</td><td>Rp{{ number_format((float)$item->total_harga,0,',','.') }}</td></tr>@empty<tr><td colspan="7">Belum ada transaksi penjualan pada periode ini.</td></tr>@endforelse
    </tbody></table>
  @else
    <p class="muted">Tabel penjualan belum tersedia. Jalankan migration terbaru untuk mengaktifkan pencatatan transaksi.</p>
  @endif

  <div class="footer">Dokumen dihasilkan oleh PawonTani · {{ now()->translatedFormat('d F Y H:i') }}</div>
  <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });</script>
</body>
</html>
