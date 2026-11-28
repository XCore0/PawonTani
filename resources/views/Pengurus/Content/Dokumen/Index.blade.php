@extends('Pengurus.Layout._layout')

@section('title', 'Dokumen Anggota & Lahan')

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

  @if(session('error'))
    <div id="flash-error" class="flex items-center justify-between p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm shadow-xs transition-all">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
          <i data-lucide="alert-circle" class="w-4 h-4 stroke-[3]"></i>
        </div>
        <div>
          <span class="font-bold">Akses Ditolak!</span>
          <span class="text-rose-700 ml-1">{{ session('error') }}</span>
        </div>
      </div>
      <button type="button" onclick="document.getElementById('flash-error').remove()" class="p-1 text-rose-700 hover:text-rose-900 rounded-lg hover:bg-rose-100 transition-colors cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>
  @endif

  <!-- ==================== 1. TOP HEADER & PRIVACY PRINCIPLE ==================== -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Dokumen Anggota dan Lahan
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Pemeriksaan dokumen identitas dan SPPT anggota kelompok tani berdasarkan izin privasi yang dibagikan oleh petani.
      </p>
    </div>

    <!-- Security & Purpose Badge -->
    <div class="flex items-center gap-2.5 shrink-0">
      <div class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1] text-xs sm:text-sm font-semibold text-[#4A6030]">
        <i data-lucide="shield-lock" class="w-4 h-4 text-[#4D9830]"></i>
        <span>Prinsip Perlindungan Data Pribadi</span>
      </div>
    </div>
  </div>

  <!-- ==================== 2. SUMMARY METRICS CARDS ==================== -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
        <i data-lucide="users" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Anggota Kelompok</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalAnggota) }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Terdaftar di kelompok ini</span>
      </div>
    </div>

    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
        <i data-lucide="badge-check" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">KTP & KK Tersimpan</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalDokumenIdentitasLengkap) }}</span>
        <span class="text-[11px] text-emerald-600 font-semibold block mt-0.5">● Berkas Diunggah Petani</span>
      </div>
    </div>

    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[#F4A020] flex items-center justify-center shrink-0">
        <i data-lucide="file-warning" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Perlu SPPT Lahan</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($totalSpptKurang) }}</span>
        <span class="text-[11px] text-amber-600 font-semibold block mt-0.5">● Petak Belum Ada Bukti SPPT</span>
      </div>
    </div>
  </div>

  <!-- ==================== 3. SEARCH & TABLE ==================== -->
  <div class="bg-white rounded-[22px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-[#E4F0D6]/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <form method="GET" action="{{ route('pengurus.dokumen') }}" class="flex items-center gap-2 w-full sm:max-w-md">
        <div class="relative w-full">
          <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
          <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau NIK anggota..."
            class="w-full pl-9 pr-4 py-2 rounded-xl text-xs sm:text-sm border border-[#E4F0D6] focus:outline-none focus:border-[#4D9830] transition-colors">
        </div>
        <button type="submit" class="px-4 py-2 rounded-xl bg-[#4D9830] text-white text-xs sm:text-sm font-bold shrink-0 hover:bg-[#3D8024] transition-colors">
          Cari
        </button>
      </form>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs sm:text-sm">
        <thead class="bg-[#F5F8F1] text-[#4A6030] text-[11px] uppercase tracking-wider border-b border-[#E4F0D6]">
          <tr>
            <th class="py-3.5 px-4 font-bold">Anggota</th>
            <th class="py-3.5 px-4 font-bold text-center">Status Izin Akses</th>
            <th class="py-3.5 px-4 font-bold text-center">Dokumen KTP</th>
            <th class="py-3.5 px-4 font-bold text-center">Dokumen KK</th>
            <th class="py-3.5 px-4 font-bold">SPPT Lahan</th>
            <th class="py-3.5 px-4 font-bold text-center">Status Akun</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E4F0D6]/60">
          @forelse($anggota as $member)
            @php
              $ktp = $member->dokumenIdentitas->firstWhere('jenis', 'KTP');
              $kk = $member->dokumenIdentitas->firstWhere('jenis', 'KK');
              $userPerms = $permissionMap[$member->id_pengguna] ?? [];
              $hasAnyPerm = !empty($userPerms);
              $firstPerm = null;
              if ($hasAnyPerm) {
                  foreach ($userPerms as $tipe => $docs) {
                      $firstPerm = reset($docs);
                      if ($firstPerm) break;
                  }
              }
              $canAccessKtp = isset($userPerms['ktp']) || isset($userPerms['all']);
              $canAccessKk = isset($userPerms['kk']) || isset($userPerms['all']);
              $canAccessSppt = isset($userPerms['sppt']) || isset($userPerms['all']);
            @endphp
            <tr class="hover:bg-[#F5F8F1]/40 transition-colors">
              <td class="py-4 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-[#4D9830] to-[#72BE4A] text-white font-bold flex items-center justify-center text-xs shrink-0 border border-emerald-200">
                    {{ strtoupper(substr($member->nama, 0, 2)) }}
                  </div>
                  <div>
                    <span class="font-bold text-[#1A2D10] block">{{ $member->nama }}</span>
                    <span class="text-[11px] text-slate-500 block">NIK: {{ $member->nik ?: 'Belum diisi' }}</span>
                  </div>
                </div>
              </td>

              <!-- Status Izin Akses -->
              <td class="py-4 px-4 text-center">
                @if($hasAnyPerm)
                  <div class="inline-flex flex-col items-center">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                      <i data-lucide="unlock" class="w-3 h-3 text-emerald-700"></i> Diizinkan
                    </span>
                    @php
                      $remainingText = 'Aktif (Permanen)';
                      if ($firstPerm?->waktu_berakhir) {
                          $end = \Carbon\Carbon::parse($firstPerm->waktu_berakhir);
                          $diffDays = ceil(now()->floatDiffInDays($end, false));
                          if ($diffDays > 0) {
                              $remainingText = 's/d ' . $end->format('d/m/Y') . ' (Sisa ' . (int)$diffDays . ' hari)';
                          } else {
                              $remainingText = 'Kedaluwarsa';
                          }
                      }
                    @endphp
                    <span class="text-[10px] font-semibold text-emerald-700 mt-0.5">{{ $remainingText }}</span>
                  </div>
                @else
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    <i data-lucide="lock" class="w-3 h-3 text-slate-400"></i> Tidak Diizinkan
                  </span>
                @endif
              </td>

              <!-- KTP -->
              <td class="py-4 px-4 text-center">
                @if($ktp)
                  @if($canAccessKtp)
                    <div class="flex items-center justify-center gap-1">
                      <a target="_blank" rel="noopener noreferrer"
                        href="{{ route('pengurus.dokumen.identity.preview', [$member->id_pengguna, 'KTP']) }}"
                        class="p-1.5 text-slate-600 hover:text-[#4D9830] hover:bg-[#EBF6E0] rounded-lg transition-colors"
                        title="Buka KTP">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                      </a>
                      <a href="{{ route('pengurus.dokumen.identity.download', [$member->id_pengguna, 'KTP']) }}"
                        class="p-1.5 text-[#4D9830] hover:text-[#3D8024] hover:bg-[#EBF6E0] rounded-lg transition-colors"
                        title="Unduh KTP">
                        <i data-lucide="download" class="w-4 h-4"></i>
                      </a>
                    </div>
                  @else
                    <span class="text-[11px] text-slate-500 italic flex items-center justify-center gap-1">
                      <i data-lucide="lock" class="w-3 h-3 text-slate-400"></i> Tersimpan
                    </span>
                  @endif
                @else
                  <span class="text-[11px] text-amber-600 italic">Belum ada</span>
                @endif
              </td>

              <!-- KK -->
              <td class="py-4 px-4 text-center">
                @if($kk)
                  @if($canAccessKk)
                    <div class="flex items-center justify-center gap-1">
                      <a target="_blank" rel="noopener noreferrer"
                        href="{{ route('pengurus.dokumen.identity.preview', [$member->id_pengguna, 'KK']) }}"
                        class="p-1.5 text-slate-600 hover:text-[#4D9830] hover:bg-[#EBF6E0] rounded-lg transition-colors"
                        title="Buka KK">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                      </a>
                      <a href="{{ route('pengurus.dokumen.identity.download', [$member->id_pengguna, 'KK']) }}"
                        class="p-1.5 text-[#4D9830] hover:text-[#3D8024] hover:bg-[#EBF6E0] rounded-lg transition-colors"
                        title="Unduh KK">
                        <i data-lucide="download" class="w-4 h-4"></i>
                      </a>
                    </div>
                  @else
                    <span class="text-[11px] text-slate-500 italic flex items-center justify-center gap-1">
                      <i data-lucide="lock" class="w-3 h-3 text-slate-400"></i> Tersimpan
                    </span>
                  @endif
                @else
                  <span class="text-[11px] text-amber-600 italic">Belum ada</span>
                @endif
              </td>

              <!-- SPPT LAHAN -->
              <td class="py-4 px-4 min-w-[200px]">
                @forelse($member->lahan as $land)
                  <div class="mb-2 last:mb-0 p-2 rounded-xl border border-[#E4F0D6] bg-[#F5F8F1]/40">
                    <div class="flex items-center justify-between text-xs mb-1">
                      <span class="font-bold text-[#1A2D10] flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-3 h-3 text-[#4D9830]"></i>
                        {{ $land->nama_lahan }}
                      </span>
                      <span class="text-slate-500 font-medium">{{ $land->status_penguasaan ?? 'Milik Sendiri' }}</span>
                    </div>

                    @if($land->dokumenSppt->isNotEmpty())
                      @if($canAccessSppt)
                        <div class="space-y-1">
                          @foreach($land->dokumenSppt as $sppt)
                            <div class="flex items-center justify-between gap-1 text-[11px] bg-white border border-[#E4F0D6] rounded-lg px-2 py-0.5">
                              <span class="truncate max-w-[120px] text-slate-600" title="{{ $sppt->original_name }}">{{ $sppt->original_name }}</span>
                              <div class="flex items-center gap-1 shrink-0">
                                <a target="_blank" rel="noopener noreferrer"
                                  href="{{ route('pengurus.dokumen.sppt.preview', $sppt->id) }}"
                                  class="text-slate-600 hover:text-[#1A2D10] font-semibold">Buka</a>
                                <span class="text-slate-300">·</span>
                                <a href="{{ route('pengurus.dokumen.sppt.download', $sppt->id) }}"
                                  class="text-[#4D9830] hover:underline font-bold">Unduh</a>
                              </div>
                            </div>
                          @endforeach
                        </div>
                      @else
                        <span class="text-[11px] text-slate-500 italic flex items-center gap-1">
                          <i data-lucide="lock" class="w-3 h-3 text-slate-400"></i> SPPT Tersimpan (Terkunci)
                        </span>
                      @endif
                    @else
                      <span class="text-[11px] text-amber-600 italic block">SPPT belum diunggah</span>
                    @endif
                  </div>
                @empty
                  <span class="text-xs text-slate-400 italic">Belum ada lahan</span>
                @endforelse
              </td>

              <!-- Status Akun -->
              <td class="py-4 px-4 text-center">
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $member->status === 'Aktif' ? 'bg-[#EBF6E0] text-[#4D9830] border border-[#C5DFB0]' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                  {{ $member->status }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-12 text-center text-slate-400">
                <p class="text-xs">Tidak ada data anggota kelompok tani yang ditemukan.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($anggota->hasPages())
      <div class="p-4 border-t border-[#E4F0D6]">
        {{ $anggota->links() }}
      </div>
    @endif
  </div>

  <!-- ==================== 4. AUDIT LOG AKSES DOKUMEN ==================== -->
  <div class="bg-white rounded-[22px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-[#E4F0D6]/70 flex items-center gap-3">
      <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
        <i data-lucide="history" class="w-4 h-4"></i>
      </div>
      <div>
        <h2 class="text-sm font-bold text-[#1A2D10]">Catatan Transparansi Akses Dokumen</h2>
        <p class="text-xs text-slate-500">Riwayat pembukaan dan pengunduhan berkas anggota untuk keperluan audit kelompok.</p>
      </div>
    </div>
    <div class="divide-y divide-[#E4F0D6]/60 text-xs">
      @forelse($recentLogs as $log)
        <div class="p-4 flex items-center justify-between gap-4">
          <div class="space-y-0.5">
            <div class="font-bold text-[#1A2D10]">
              {{ $log->pengurus?->nama ?? ($log->pengguna?->nama ?? 'Pengurus') }} mengakses dokumen {{ $log->tipe_dokumen ?? ($log->jenis_dokumen ?? 'Berkas') }} milik {{ $log->aksesDokumen?->pengguna?->nama ?? ($log->pemilik?->nama ?? 'Anggota') }}
            </div>
            <div class="text-slate-500 text-[11px]">
              Aktivitas: <span class="uppercase font-semibold text-[#4D9830]">{{ $log->jenis_aktivitas ?? ($log->tindakan ?? 'Akses') }}</span> • Keterangan: {{ $log->keterangan ?? ($log->tujuan_akses ?? '-') }}
            </div>
          </div>
          <div class="text-[11px] text-slate-400 shrink-0">
            {{ $log->waktu_akses ? $log->waktu_akses->diffForHumans() : ($log->created_at ? $log->created_at->diffForHumans() : '-') }}
          </div>
        </div>
      @empty
        <div class="p-6 text-center text-slate-400 text-xs">
          Belum ada riwayat akses dokumen yang tercatat.
        </div>
      @endforelse
    </div>
  </div>

</div>
@endsection
