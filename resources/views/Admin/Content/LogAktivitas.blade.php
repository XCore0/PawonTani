@extends('Admin.Layout._layout')

@section('title', 'Log Aktivitas')

@section('content')
<div class="space-y-6">

  <!-- ==================== 1. TOP HEADER & ACTION BUTTONS ==================== -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Log Aktivitas
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Audit trail dan riwayat tindakan pengguna pada sistem PawonTani.
      </p>
    </div>

    <!-- Quick Info Badge -->
    <div class="flex items-center gap-2.5 shrink-0">
      <div class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4D9830]">
        <i data-lucide="shield" class="w-4 h-4 text-[#4D9830]"></i>
        <span>Read-Only Audit Trail</span>
      </div>
    </div>
  </div>

  <!-- ==================== 2. SUMMARY METRICS CARDS ==================== -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <!-- Card 1: Hari Ini -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
        <i data-lucide="calendar-check" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Aktivitas Hari Ini</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalHariIni) }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Tercatat Hari Ini</span>
      </div>
    </div>

    <!-- Card 2: Pekan Ini -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
        <i data-lucide="trending-up" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Aktivitas Pekan Ini</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalMingguIni) }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Total Sejak Senin</span>
      </div>
    </div>
  </div>

  <!-- ==================== 3. FILTER & SEARCH TOOLBAR ==================== -->
  <div class="p-4 sm:p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
      
      <!-- Search Box (Col 5) -->
      <div class="md:col-span-5 relative">
        <i data-lucide="search" class="w-4 h-4 text-[#9AB880] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
        <input type="text" name="q" value="{{ request('q') }}"
          placeholder="Cari aktor, uraian, ID..."
          class="w-full h-11 pl-10 pr-4 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-slate-800 placeholder-[#9AB880] text-xs sm:text-sm focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
      </div>

      <!-- Filter Kelompok Tani (Col 4) -->
      <div class="md:col-span-4">
        <select name="kelompok" onchange="this.form.submit()"
          class="w-full h-11 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="">Semua Kelompok Tani</option>
          @foreach($kelompok as $item)
            <option value="{{ $item->id_kelompok }}" @selected(request('kelompok') === $item->id_kelompok)>
              {{ $item->nama_kelompok }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Filter Event (Col 2) -->
      <div class="md:col-span-2">
        <select name="event" onchange="this.form.submit()"
          class="w-full h-11 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="">Semua Kategori</option>
          @foreach($events as $event)
            <option value="{{ $event }}" @selected(request('event') === $event)>
              {{ ucwords(str_replace(['.', '_'], ' ', $event)) }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Reset / Action (Col 1) -->
      <div class="md:col-span-1 flex items-center justify-end">
        @if(request()->hasAny(['q', 'kelompok', 'event']))
          <a href="{{ route('admin.log-aktivitas') }}"
            class="w-full h-11 rounded-xl border border-[#C5DFB0] bg-white hover:bg-[#F5F8F1] text-[#4A6030] flex items-center justify-center transition-colors cursor-pointer"
            title="Reset Filter">
            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
          </a>
        @else
          <button type="submit"
            class="w-full h-11 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white flex items-center justify-center transition-all cursor-pointer"
            title="Cari">
            <i data-lucide="search" class="w-4 h-4"></i>
          </button>
        @endif
      </div>

    </form>
  </div>

  <!-- ==================== 4. DATA LIST ==================== -->
  <div class="bg-white rounded-[20px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
    
    <div class="divide-y divide-[#E4F0D6]/60 text-slate-700">
      @forelse($logs as $log)
        <div class="p-5 hover:bg-[#F5F8F1]/50 transition-colors flex items-start gap-4">
          <!-- Icon Context -->
          <div class="w-10 h-10 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0 border border-[#C5DFB0]">
            @if(str_contains($log->event, 'delete') || str_contains($log->event, 'destroy') || str_contains($log->event, 'reject'))
              <i data-lucide="trash-2" class="w-5 h-5 text-red-600"></i>
            @elseif(str_contains($log->event, 'approve') || str_contains($log->event, 'restore') || str_contains($log->event, 'create'))
              <i data-lucide="check" class="w-5 h-5 text-[#4D9830] stroke-[2.5]"></i>
            @elseif(str_contains($log->event, 'auth') || str_contains($log->event, 'login'))
              <i data-lucide="key" class="w-5 h-5 text-amber-600"></i>
            @else
              <i data-lucide="activity" class="w-5 h-5 text-[#4D9830]"></i>
            @endif
          </div>

          <!-- Log Content -->
          <div class="min-w-0 flex-1">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
              <div class="flex items-center gap-2">
                <span class="font-bold text-[#1A2D10] text-sm">{{ $log->actor_name ?? 'Sistem PawonTani' }}</span>
                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $log->actor_role === 'Admin' ? 'bg-[#EBF6E0] text-[#3D8024] border border-[#C5DFB0]' : ($log->actor_role === 'Pengurus' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700') }}">
                  {{ $log->actor_role ?? 'System' }}
                </span>
              </div>
              <time class="text-xs text-slate-400 font-medium flex items-center gap-1">
                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                {{ $log->created_at?->translatedFormat('d F Y, H:i:s') }}
              </time>
            </div>

            <!-- Description -->
            <p class="mt-1 text-xs sm:text-sm text-slate-600 leading-relaxed">
              {{ $log->description }}
            </p>

            <!-- Metadata Badges -->
            <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
              <span class="rounded-lg bg-[#F5F8F1] border border-[#E4F0D6] px-2 py-0.5 font-mono text-[10px] text-[#4D9830]">
                {{ $log->event }}
              </span>
              @if($log->id_kelompok)
                <span class="flex items-center gap-1">
                  <i data-lucide="users" class="w-3 h-3 text-slate-400"></i>
                  Kelompok: <strong class="text-[#1A2D10] font-medium">{{ $log->id_kelompok }}</strong>
                </span>
              @endif
              @if($log->subject_id)
                <span class="text-slate-300">·</span>
                <span>ID Subjek: <strong class="text-[#1A2D10] font-mono">{{ $log->subject_id }}</strong></span>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="py-12 text-center text-slate-400">
          <div class="flex flex-col items-center justify-center">
            <div class="w-12 h-12 rounded-2xl bg-[#F5F8F1] flex items-center justify-center mb-3">
              <i data-lucide="activity" class="w-6 h-6 text-[#9AB880]"></i>
            </div>
            <span class="font-bold text-slate-700 text-sm">Belum ada log aktivitas</span>
            <span class="text-xs text-slate-400 mt-1">Belum ada rekaman riwayat yang sesuai dengan kriteria filter</span>
          </div>
        </div>
      @endforelse
    </div>

    <!-- Pagination Footer -->
    <div class="p-4 sm:p-5 border-t border-[#E4F0D6]/60 bg-white">
      {{ $logs->links() }}
    </div>
  </div>

</div>
@endsection
