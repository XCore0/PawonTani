@extends('Admin.Layout._layout')

@section('title', 'Log Aktivitas')

@section('content')
<div class="space-y-6">
  <div>
    <p class="text-xs font-semibold uppercase tracking-wider text-[#9AB880]">Audit sistem</p>
    <h1 class="mt-1 text-2xl sm:text-[28px] font-extrabold tracking-tight text-[#1A2D10]">Log Aktivitas</h1>
    <p class="mt-1 text-sm text-[#6B7F5B]">Riwayat tindakan penting yang dilakukan Admin, Pengurus, dan Anggota.</p>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="rounded-[18px] border border-[#E4F0D6] bg-white p-5"><p class="text-xs font-semibold uppercase tracking-wide text-[#9AB880]">Aktivitas hari ini</p><p class="mt-2 text-2xl font-extrabold text-[#1A2D10]">{{ number_format($totalHariIni) }}</p></div>
    <div class="rounded-[18px] border border-[#E4F0D6] bg-white p-5"><p class="text-xs font-semibold uppercase tracking-wide text-[#9AB880]">Aktivitas minggu ini</p><p class="mt-2 text-2xl font-extrabold text-[#1A2D10]">{{ number_format($totalMingguIni) }}</p></div>
  </div>
  <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 rounded-[20px] border border-[#E4F0D6] bg-white p-4">
    <div class="md:col-span-4"><label class="mb-1 block text-xs font-semibold text-[#4A6030]">Cari aktivitas/pengguna</label><input name="q" value="{{ request('q') }}" placeholder="Nama, uraian, atau ID data" class="h-11 w-full rounded-xl border border-[#C5DFB0] px-3 text-sm outline-none focus:border-[#4D9830]"></div>
    <div class="md:col-span-3"><label class="mb-1 block text-xs font-semibold text-[#4A6030]">Kelompok tani</label><select name="kelompok" class="h-11 w-full rounded-xl border border-[#C5DFB0] bg-white px-3 text-sm"><option value="">Semua kelompok</option>@foreach($kelompok as $item)<option value="{{ $item->id_kelompok }}" @selected(request('kelompok') === $item->id_kelompok)>{{ $item->nama_kelompok }}</option>@endforeach</select></div>
    <div class="md:col-span-3"><label class="mb-1 block text-xs font-semibold text-[#4A6030]">Jenis aktivitas</label><select name="event" class="h-11 w-full rounded-xl border border-[#C5DFB0] bg-white px-3 text-sm"><option value="">Semua aktivitas</option>@foreach($events as $event)<option value="{{ $event }}" @selected(request('event') === $event)>{{ str_replace('.', ' · ', $event) }}</option>@endforeach</select></div>
    <div class="flex items-end md:col-span-2"><button class="h-11 w-full rounded-xl bg-[#4D9830] px-3 text-sm font-bold text-white hover:bg-[#3D8024]">Terapkan</button></div>
  </form>
  <div class="overflow-hidden rounded-[20px] border border-[#E4F0D6] bg-white">
    <div class="border-b border-[#E4F0D6] bg-[#F5F8F1] px-5 py-4"><h2 class="text-sm font-bold text-[#1A2D10]">Riwayat tindakan</h2><p class="mt-0.5 text-xs text-[#9AB880]">Log hanya dapat dibaca oleh Admin/PPL melalui halaman ini.</p></div>
    <div class="divide-y divide-[#E4F0D6]">
      @forelse($logs as $log)
        <div class="flex gap-3 px-5 py-4">
          <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#EBF6E0] text-[#4D9830]"><i data-lucide="activity" class="h-4 w-4"></i></div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"><p class="text-sm font-bold text-[#1A2D10]">{{ $log->actor_name ?? 'Sistem' }} <span class="ml-1 rounded-full bg-[#F5F8F1] px-2 py-0.5 text-[10px] font-semibold text-[#6B7F5B]">{{ $log->actor_role ?? 'System' }}</span></p><time class="text-[11px] text-[#9AB880]">{{ $log->created_at?->format('d M Y, H:i') }}</time></div>
            <p class="mt-1 text-sm text-[#4A6030]">{{ $log->description }}</p>
            <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-[10px] text-[#9AB880]"><span>{{ $log->event }}</span>@if($log->id_kelompok)<span>Kelompok: {{ $log->id_kelompok }}</span>@endif @if($log->subject_id)<span>ID data: {{ $log->subject_id }}</span>@endif</div>
          </div>
        </div>
      @empty
        <div class="px-5 py-12 text-center text-sm text-[#9AB880]">Belum ada log aktivitas yang tercatat.</div>
      @endforelse
    </div>
    <div class="border-t border-[#E4F0D6] p-4">{{ $logs->links() }}</div>
  </div>
  <p class="text-xs text-[#9AB880]">Log tidak menyimpan password maupun isi dokumen. Akses jaringan dan konfigurasi server tetap perlu diamankan.</p>
</div>
@endsection
