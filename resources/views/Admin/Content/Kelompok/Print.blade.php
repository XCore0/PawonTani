<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Kelompok Tani - PawonTani</title>
  <style>
    *{box-sizing:border-box}body{font-family:Arial,Helvetica,sans-serif;color:#1A2D10;margin:0;padding:28px;background:#fff;font-size:11px}.toolbar{display:flex;justify-content:flex-end;gap:8px;margin-bottom:16px}.toolbar a,.toolbar button{font-size:11px;font-weight:bold;border-radius:8px;padding:9px 13px;text-decoration:none;cursor:pointer}.toolbar a{color:#365A27;border:1px solid #C5DFB0}.toolbar button{color:#fff;background:#4D9830;border:1px solid #4D9830}.brand{display:flex;align-items:center;gap:14px;border-bottom:2px solid #4D9830;padding-bottom:14px;margin-bottom:16px}.brand img{width:58px;height:58px;object-fit:contain}.brand h1{font-size:22px;margin:0 0 5px}.brand p{font-size:11px;color:#6B7F5B;margin:0}.info{display:flex;justify-content:space-between;gap:12px;padding:10px 12px;background:#F5F8F1;border:1px solid #E4F0D6;border-radius:8px;margin-bottom:16px;color:#4A6030}.info strong{color:#1A2D10}table{width:100%;border-collapse:collapse}th{background:#EBF6E0;color:#365A27;text-transform:uppercase;font-size:9px;letter-spacing:.04em;text-align:left}th,td{border:1px solid #DDE9D2;padding:8px;vertical-align:top}td{color:#3E5234}.status{font-weight:bold}.footer{margin-top:20px;padding-top:10px;border-top:1px solid #E4F0D6;text-align:right;color:#839676;font-size:9px}@media print{body{padding:0 5mm}.toolbar{display:none}@page{size:A4 landscape;margin:12mm}}
  </style>
</head>
<body>
  <div class="toolbar"><a href="javascript:window.close()">Tutup</a><button type="button" onclick="window.print()">Cetak / Simpan PDF</button></div>
  <div class="brand">
    <img src="{{ asset('images/Logo2.png') }}" alt="PawonTani">
    <div><h1>Daftar Kelompok Tani</h1><p>Data kelompok tani yang terdaftar pada sistem PawonTani</p></div>
  </div>
  <div class="info"><span><strong>Total Kelompok:</strong> {{ $kelompokList->count() }}</span><span><strong>Tanggal Cetak:</strong> {{ now()->translatedFormat('d F Y, H:i') }}</span></div>
  <table>
    <thead><tr><th style="width:5%">No</th><th style="width:15%">ID Kelompok</th><th style="width:20%">Nama Kelompok</th><th>Alamat</th><th style="width:11%">Status</th><th style="width:11%">Jumlah Anggota</th><th style="width:13%">Tanggal Dibuat</th></tr></thead>
    <tbody>
      @forelse($kelompokList as $kelompok)
        <tr><td>{{ $loop->iteration }}</td><td>{{ $kelompok->id_kelompok }}</td><td><strong>{{ $kelompok->nama_kelompok }}</strong></td><td>{{ $kelompok->alamat }}</td><td class="status">{{ $kelompok->status }}</td><td>{{ $kelompok->total_anggota }}</td><td>{{ $kelompok->created_at?->translatedFormat('d M Y') ?? '-' }}</td></tr>
      @empty
        <tr><td colspan="7" style="text-align:center;padding:22px">Tidak ada data kelompok tani yang sesuai dengan filter.</td></tr>
      @endforelse
    </tbody>
  </table>
  <div class="footer">Dokumen dihasilkan oleh PawonTani · {{ now()->translatedFormat('d F Y H:i') }}</div>
  <script>window.addEventListener('load',function(){setTimeout(function(){window.print()},350)});</script>
</body>
</html>
