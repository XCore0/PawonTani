@extends('Admin.Layout._layout')

@section('title', 'Permintaan Anggota')

@section('content')
<div class="space-y-6">

  <!-- ==================== FLASH ALERT NOTIFICATION ==================== -->
  @if(session('success'))
    <div id="flash-banner" class="flex items-center justify-between p-4 rounded-2xl bg-[#EBF6E0] border border-[#C5DFB0] text-[#1A2D10] text-xs sm:text-sm shadow-xs transition-all">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-[#4D9830] text-white flex items-center justify-center shrink-0">
          <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
        </div>
        <div>
          <span class="font-bold">Berhasil!</span>
          <span class="text-[#4A6030] ml-1">{{ session('success') }}</span>
        </div>
      </div>
      <button type="button" onclick="document.getElementById('flash-banner').remove()" class="p-1 text-[#4A6030] hover:text-[#1A2D10] rounded-lg hover:bg-[#dff0d0] transition-colors cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>
  @endif

  @if($errors->any())
    <div id="error-banner" class="flex items-center justify-between p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm shadow-xs transition-all">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0">
          <i data-lucide="alert-circle" class="w-4 h-4 stroke-[2.5]"></i>
        </div>
        <div>
          <span class="font-bold">Perhatian!</span>
          <span class="text-red-700 ml-1">{{ $errors->first() }}</span>
        </div>
      </div>
      <button type="button" onclick="document.getElementById('error-banner').remove()" class="p-1 text-red-600 hover:text-red-900 rounded-lg hover:bg-red-100 transition-colors cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>
  @endif

  <!-- ==================== 1. TOP HEADER & ACTION BUTTONS ==================== -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Permintaan Anggota
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Tinjau dan proses permohonan anggota untuk bergabung ke kelompok tani baru.
      </p>
    </div>

   
  </div>

  <!-- ==================== 2. SUMMARY METRICS CARDS ==================== -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <!-- Card 1: Menunggu -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[#F4A020] flex items-center justify-center shrink-0">
        <i data-lucide="clock" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Menunggu Tinjauan</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($stats['menunggu']) }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Perlu Persetujuan Admin</span>
      </div>
    </div>

    <!-- Card 2: Disetujui -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
        <i data-lucide="check-circle-2" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Disetujui</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($stats['disetujui']) }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Masuk Kelompok Tujuan</span>
      </div>
    </div>

    <!-- Card 3: Ditolak -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
        <i data-lucide="x-circle" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Ditolak / Batal</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($stats['ditolak']) }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Permohonan Tidak Diterima</span>
      </div>
    </div>
  </div>

  <!-- ==================== 3. FILTER & SEARCH TOOLBAR ==================== -->
  <div class="p-4 sm:p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
      
      <!-- Filter Status Dropdown (Col 11) -->
      <div class="md:col-span-11">
        <select name="status" onchange="this.form.submit()"
          class="w-full h-11 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="">Status: Menunggu Tinjauan (Default)</option>
          @foreach(['Menunggu' => 'Menunggu Tinjauan', 'Disetujui' => 'Disetujui', 'Ditolak' => 'Ditolak', 'Dibatalkan' => 'Dibatalkan'] as $val => $lbl)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
          @endforeach
        </select>
      </div>

      <!-- Reset Filter Button (Col 1) -->
      <div class="md:col-span-1 flex items-center justify-end">
        @if(request('status'))
          <a href="{{ route('admin.permintaan-keanggotaan') }}"
            class="w-full h-11 rounded-xl border border-[#C5DFB0] bg-white hover:bg-[#F5F8F1] text-[#4A6030] flex items-center justify-center transition-colors cursor-pointer"
            title="Reset ke Menunggu">
            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
          </a>
        @else
          <button type="submit"
            class="w-full h-11 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white flex items-center justify-center transition-all cursor-pointer"
            title="Filter">
            <i data-lucide="filter" class="w-4 h-4"></i>
          </button>
        @endif
      </div>

    </form>
  </div>

  <!-- ==================== 4. DATA LIST / TABLE ==================== -->
  <div class="bg-white rounded-[20px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
    
    <div class="divide-y divide-[#E4F0D6]/60 text-slate-700">
      @forelse($requests as $item)
        <div class="p-5 hover:bg-[#F5F8F1]/50 transition-colors">
          <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-5 items-start">
            
            <!-- Detail Informasi Pemohon -->
            <div class="space-y-3">
              <div class="flex flex-wrap items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-[#4D9830] to-[#72BE4A] text-white font-bold flex items-center justify-center text-xs shrink-0 border border-emerald-200">
                  {{ strtoupper(substr($item->pengguna?->nama ?? 'P', 0, 2)) }}
                </div>
                <div>
                  <h3 class="font-bold text-[#1A2D10] text-sm sm:text-base leading-tight">
                    {{ $item->pengguna?->nama ?? 'Akun Petani Tidak Tersedia' }}
                  </h3>
                  <span class="text-[11px] text-slate-500 font-mono">
                    ID: {{ $item->id_pengguna }}
                  </span>
                </div>

                <!-- Status Badge -->
                <div class="ml-auto sm:ml-2">
                  @if($item->status === 'Menunggu')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                      <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                      Menunggu
                    </span>
                  @elseif($item->status === 'Disetujui')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#EBF6E0] text-[#4D9830] border border-[#C5DFB0]">
                      <span class="w-1.5 h-1.5 rounded-full bg-[#4D9830]"></span>
                      Disetujui
                    </span>
                  @elseif($item->status === 'Ditolak')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                      <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                      Ditolak
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                      {{ $item->status }}
                    </span>
                  @endif
                </div>
              </div>

              <!-- Kelompok Tani Tujuan -->
              <div class="flex items-center gap-2 pt-1 text-xs">
                <span class="text-slate-500">Kelompok Tujuan:</span>
                <span class="font-medium text-[#1A2D10]">
                  {{ $item->kelompokTani?->nama_kelompok ?? 'Kelompok Tidak Ditemukan' }}
                </span>
              </div>

              <!-- Alasan -->
              <div class="p-3.5 rounded-xl border border-[#E4F0D6] bg-[#F5F8F1]/40 text-xs text-slate-700 leading-relaxed">
                <span class="font-bold text-[#1A2D10] block mb-1 text-[11px] uppercase tracking-wider">Alasan Pengajuan:</span>
                {{ $item->alasan ?: 'Petani tidak menyertakan pesan atau alasan khusus.' }}
              </div>

              <!-- Metadata Waktu -->
              <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-400 font-medium">
                <span class="flex items-center gap-1">
                  <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                  Diajukan: {{ $item->created_at?->translatedFormat('d F Y, H:i') }}
                </span>
                @if($item->ditinjau_pada)
                  <span class="flex items-center gap-1 text-[#4D9830]">
                    <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                    Diproses {{ $item->ditinjau_pada->translatedFormat('d F Y, H:i') }} oleh {{ $item->peninjau?->nama ?? 'Admin' }}
                  </span>
                @endif
              </div>

              @if($item->catatan_admin)
                <div class="p-3 rounded-xl border border-[#C5DFB0] bg-[#EBF6E0] text-xs text-[#1A2D10]">
                  <strong class="text-[#4D9830] font-bold">Catatan Admin:</strong> {{ $item->catatan_admin }}
                </div>
              @endif
            </div>

            <!-- Form Aksi Persetujuan / Penolakan -->
            @if($item->status === 'Menunggu')
              <div class="p-4 rounded-[18px] border border-[#E4F0D6] bg-[#F5F8F1]/40 space-y-3.5">
                <span class="text-xs font-bold text-[#1A2D10] uppercase tracking-wider block flex items-center gap-1.5">
                  <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 text-[#4D9830]"></i>
                  Tindakan Otorisasi
                </span>

                <!-- Form Setujui -->
                <form method="POST" action="{{ route('admin.permintaan-keanggotaan.approve', $item->id) }}" class="space-y-2">
                  @csrf
                  <input name="catatan_admin" maxlength="500" placeholder="Catatan persetujuan (opsional)"
                    class="h-10 w-full rounded-xl border border-[#C5DFB0] bg-white px-3 text-xs outline-none focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830]">
                  <button type="submit"
                    class="w-full h-10 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm shadow-[#4D9830]/20 transition-all cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
                    <span>Setujui Permintaan</span>
                  </button>
                </form>

                <div class="border-t border-[#E4F0D6] pt-3">
                  <!-- Form Tolak -->
                  <form method="POST" action="{{ route('admin.permintaan-keanggotaan.reject', $item->id) }}" class="space-y-2">
                    @csrf
                    <input name="catatan_admin" maxlength="500" placeholder="Alasan penolakan (opsional)"
                      class="h-10 w-full rounded-xl border border-red-200 bg-white px-3 text-xs outline-none focus:border-red-400 focus:ring-1 focus:ring-red-300">
                    <button type="submit"
                      class="w-full h-10 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                      <i data-lucide="x" class="w-3.5 h-3.5 stroke-[2.5]"></i>
                      <span>Tolak Permintaan</span>
                    </button>
                  </form>
                </div>
              </div>
            @endif

          </div>
        </div>
      @empty
        <div class="py-12 text-center text-slate-400">
          <div class="flex flex-col items-center justify-center">
            <div class="w-12 h-12 rounded-2xl bg-[#F5F8F1] flex items-center justify-center mb-3">
              <i data-lucide="inbox" class="w-6 h-6 text-[#9AB880]"></i>
            </div>
            <span class="font-bold text-slate-700 text-sm">Tidak ada permintaan anggota</span>
            <span class="text-xs text-slate-400 mt-1">Belum ada pengajuan dengan kriteria filter saat ini</span>
          </div>
        </div>
      @endforelse
    </div>

    <!-- Pagination Footer -->
    <div class="p-4 sm:p-5 border-t border-[#E4F0D6]/60 bg-white">
      {{ $requests->links() }}
    </div>
  </div>

</div>
@endsection
