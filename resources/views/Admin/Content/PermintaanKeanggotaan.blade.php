@extends('Admin.Layout._layout')

@section('title', 'Permintaan Anggota')

@section('content')
<div class="space-y-6">
  <div>
    <p class="text-xs font-semibold uppercase tracking-wider text-[#9AB880]">Manajemen keanggotaan</p>
    <h1 class="mt-1 text-2xl sm:text-[28px] font-extrabold tracking-tight text-[#1A2D10]">Permintaan Anggota</h1>
    <p class="mt-1 text-sm text-[#6B7F5B]">Tinjau pengajuan anggota yang ingin bergabung dengan kelompok tani baru.</p>
  </div>

  @if(session('success'))<div class="rounded-xl border border-[#CDE8BA] bg-[#F2FAEC] px-4 py-3 text-sm text-[#315F20]">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="rounded-[18px] border border-[#E4F0D6] bg-white p-5"><p class="text-xs font-semibold uppercase tracking-wide text-[#9AB880]">Menunggu</p><p class="mt-2 text-2xl font-extrabold text-[#1A2D10]">{{ number_format($stats['menunggu']) }}</p></div>
    <div class="rounded-[18px] border border-[#E4F0D6] bg-white p-5"><p class="text-xs font-semibold uppercase tracking-wide text-[#9AB880]">Disetujui</p><p class="mt-2 text-2xl font-extrabold text-[#1A2D10]">{{ number_format($stats['disetujui']) }}</p></div>
    <div class="rounded-[18px] border border-[#E4F0D6] bg-white p-5"><p class="text-xs font-semibold uppercase tracking-wide text-[#9AB880]">Ditolak</p><p class="mt-2 text-2xl font-extrabold text-[#1A2D10]">{{ number_format($stats['ditolak']) }}</p></div>
  </div>

  <form method="GET" class="flex flex-col sm:flex-row gap-3 rounded-[20px] border border-[#E4F0D6] bg-white p-4">
    <label class="flex-1"><span class="mb-1 block text-xs font-semibold text-[#4A6030]">Status pengajuan</span><select name="status" class="h-11 w-full rounded-xl border border-[#C5DFB0] bg-white px-3 text-sm"><option value="">Menunggu</option>@foreach(['Menunggu','Disetujui','Ditolak','Dibatalkan'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></label>
    <div class="flex items-end"><button class="h-11 rounded-xl bg-[#4D9830] px-5 text-sm font-bold text-white hover:bg-[#3D8024]">Terapkan</button></div>
  </form>

  <div class="overflow-hidden rounded-[20px] border border-[#E4F0D6] bg-white">
    <div class="border-b border-[#E4F0D6] bg-[#F5F8F1] px-5 py-4"><h2 class="text-sm font-bold text-[#1A2D10]">Daftar pengajuan</h2><p class="mt-0.5 text-xs text-[#9AB880]">Persetujuan akan mengubah kelompok aktif anggota dan membuka kembali akses fitur kelompok di aplikasi.</p></div>
    <div class="divide-y divide-[#E4F0D6]">
      @forelse($requests as $item)
        <div class="grid grid-cols-1 gap-4 px-5 py-5 lg:grid-cols-[1fr_auto] lg:items-start">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2"><p class="font-bold text-[#1A2D10]">{{ $item->pengguna?->nama ?? 'Akun tidak tersedia' }}</p><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $item->status === 'Menunggu' ? 'bg-amber-50 text-amber-700' : ($item->status === 'Disetujui' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600') }}">{{ $item->status }}</span></div>
            <p class="mt-1 text-xs text-[#6B7F5B]">ID anggota: {{ $item->id_pengguna }} · Tujuan: {{ $item->kelompokTani?->nama_kelompok ?? 'Kelompok sudah tidak tersedia' }}</p>
            <p class="mt-2 text-sm text-[#4A6030]">{{ $item->alasan ?: 'Tidak ada alasan tambahan.' }}</p>
            <p class="mt-1 text-[11px] text-[#9AB880]">Diajukan {{ $item->created_at?->format('d M Y, H:i') }}@if($item->ditinjau_pada) · Diproses {{ $item->ditinjau_pada->format('d M Y, H:i') }} oleh {{ $item->peninjau?->nama ?? 'Admin' }}@endif</p>
            @if($item->catatan_admin)<p class="mt-2 rounded-lg bg-[#F5F8F1] px-3 py-2 text-xs text-[#6B7F5B]">Catatan Admin: {{ $item->catatan_admin }}</p>@endif
          </div>
          @if($item->status === 'Menunggu')
            <div class="flex flex-col gap-2 sm:flex-row lg:min-w-[240px] lg:flex-col">
              <form method="POST" action="{{ route('admin.permintaan-keanggotaan.approve', $item->id) }}" class="flex flex-1 flex-col gap-2">@csrf<input name="catatan_admin" maxlength="500" placeholder="Catatan (opsional)" class="h-10 w-full rounded-lg border border-[#E4F0D6] px-3 text-xs"><button class="h-10 rounded-lg bg-[#4D9830] px-4 text-xs font-bold text-white hover:bg-[#3D8024]">Setujui</button></form>
              <form method="POST" action="{{ route('admin.permintaan-keanggotaan.reject', $item->id) }}" class="flex flex-1 flex-col gap-2">@csrf<input name="catatan_admin" maxlength="500" placeholder="Alasan penolakan (opsional)" class="h-10 w-full rounded-lg border border-[#E4F0D6] px-3 text-xs"><button class="h-10 rounded-lg border border-red-200 bg-red-50 px-4 text-xs font-bold text-red-700 hover:bg-red-100">Tolak</button></form>
            </div>
          @endif
        </div>
      @empty
        <div class="px-5 py-12 text-center text-sm text-[#9AB880]">Belum ada permintaan dengan status ini.</div>
      @endforelse
    </div>
    <div class="border-t border-[#E4F0D6] p-4">{{ $requests->links() }}</div>
  </div>
  <p class="text-xs text-[#9AB880]">Admin perlu memastikan data kelompok tujuan benar sebelum menyetujui permintaan. Pengurus tidak dapat menyetujui permintaan untuk kelompok lain.</p>
</div>
@endsection
