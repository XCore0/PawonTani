@extends('Admin.Layout._layout')

@section('title', 'Data Anggota')

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
        Data Anggota
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Kelola keanggotaan poktan, pemulihan akun, dan hak privasi data anggota.
      </p>
    </div>

    
  </div>

  <!-- ==================== 2. SUMMARY METRICS CARDS ==================== -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <!-- Card 1: Anggota Aktif -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
        <i data-lucide="user-check" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Anggota Aktif</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($stats['aktif']) }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Terdaftar di Kelompok</span>
      </div>
    </div>

    <!-- Card 2: Keluar Kelompok -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[#F4A020] flex items-center justify-center shrink-0">
        <i data-lucide="user-minus" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Keluar Kelompok</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($stats['keluar']) }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Bisa Diaktifkan Kembali</span>
      </div>
    </div>

    <!-- Card 3: Tidak Aktif -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
        <i data-lucide="user-x" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Tidak Aktif</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ number_format($stats['tidak_aktif']) }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Status Nonaktif / Hapus</span>
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
          placeholder="Cari nama, NIK, username, atau ID..."
          class="w-full h-11 pl-10 pr-4 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-slate-800 placeholder-[#9AB880] text-xs sm:text-sm focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
      </div>

      <!-- Filter Kelompok Tani (Col 4) -->
      <div class="md:col-span-4">
        <select name="kelompok" onchange="this.form.submit()"
          class="w-full h-11 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="">Semua Kelompok Tani</option>
          @foreach($groups as $group)
            <option value="{{ $group->id_kelompok }}" @selected(request('kelompok') === $group->id_kelompok)>
              {{ $group->nama_kelompok }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Filter Status (Col 2) -->
      <div class="md:col-span-2">
        <select name="status" onchange="this.form.submit()"
          class="w-full h-11 px-3.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/40 text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:bg-white focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all cursor-pointer">
          <option value="">Semua Status</option>
          @foreach(['Aktif' => 'Aktif', 'Keluar' => 'Keluar', 'Tidak Aktif' => 'Tidak Aktif', 'Dihapus' => 'Dihapus'] as $val => $lbl)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
          @endforeach
        </select>
      </div>

      <!-- Reset / Action (Col 1) -->
      <div class="md:col-span-1 flex items-center justify-end gap-1.5">
        @if(request()->hasAny(['q', 'kelompok', 'status']))
          <a href="{{ route('admin.anggota') }}"
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

  <!-- ==================== 4. DATA TABLE ==================== -->
  <div class="bg-white rounded-[20px] border border-[#E4F0D6] shadow-2xs overflow-hidden">
    
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs sm:text-sm" id="anggota-table">
        <!-- Table Header (Identik Pengurus) -->
        <thead>
          <tr class="bg-[#F5F8F1] border-b border-[#E4F0D6] text-[#1A2D10] font-bold tracking-wider uppercase text-[11px]">
            <th class="py-3.5 px-4 text-center w-12">No</th>
            <th class="py-3.5 px-4">Anggota</th>
            <th class="py-3.5 px-4">Kelompok Tani</th>
            <th class="py-3.5 px-4">Tanggal Keluar</th>
            <th class="py-3.5 px-4 text-center">Status</th>
            <th class="py-3.5 px-4 text-center w-28">Aksi</th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="divide-y divide-[#E4F0D6]/60 text-slate-700">
          @forelse($members as $index => $member)
            @php($membership = $member->anggota?->status_keanggotaan ?? '-')
            <tr class="hover:bg-[#F5F8F1]/50 transition-colors">
              <!-- NO -->
              <td class="py-4 px-4 text-center font-medium text-slate-500">
                {{ $members->firstItem() + $index }}
              </td>

              <!-- ANGGOTA (Nama, Avatar & NIK/ID) -->
              <td class="py-4 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-[#4D9830] to-[#72BE4A] text-white font-bold flex items-center justify-center text-xs shrink-0 border border-emerald-200">
                    {{ strtoupper(substr($member->nama, 0, 2)) }}
                  </div>
                  <div>
                    <span class="font-bold text-[#1A2D10] block">{{ $member->nama }}</span>
                    <span class="text-[11px] text-slate-500 block">
                      {{ $member->nik ?: $member->id_pengguna }}
                    </span>
                  </div>
                </div>
              </td>

              <!-- KELOMPOK TANI -->
              <td class="py-4 px-4">
                <span class="font-medium text-[#1A2D10] block">{{ $member->kelompokTani?->nama_kelompok ?? '-' }}</span>
                <span class="text-[11px] text-slate-400 block">Anggota Poktan</span>
              </td>

              <!-- TANGGAL KELUAR -->
              <td class="py-4 px-4 text-xs text-slate-600">
                @if($member->anggota?->tanggal_keluar)
                  {{ $member->anggota->tanggal_keluar->translatedFormat('d F Y, H:i') }}
                @else
                  <span class="text-slate-300 font-bold">—</span>
                @endif
              </td>

              <!-- STATUS -->
              <td class="py-4 px-4 text-center">
                @if($membership === 'Aktif')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#EBF6E0] text-[#4D9830] border border-[#C5DFB0]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#4D9830]"></span>
                    Aktif
                  </span>
                @elseif($membership === 'Keluar')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Keluar Kelompok
                  </span>
                @elseif($membership === 'Dihapus')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    Dianonimkan
                  </span>
                @else
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    {{ $membership }}
                  </span>
                @endif
              </td>

              <!-- AKSI (Icon buttons identik Pengurus) -->
              <td class="py-4 px-4 text-center">
                <div class="flex items-center justify-center gap-1">
                  @if($membership === 'Keluar' && $member->status !== 'Dihapus')
                    <form method="POST" action="{{ route('admin.anggota.restore', $member->id_pengguna) }}">
                      @csrf
                      <button type="submit"
                        class="p-1.5 text-[#4D9830] hover:text-[#3D8024] hover:bg-[#EBF6E0] rounded-lg transition-colors cursor-pointer"
                        title="Aktifkan Kembali">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                      </button>
                    </form>
                  @endif

                  @if($member->status !== 'Dihapus' && $membership !== 'Dihapus')
                    <button type="button"
                      class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                      title="Hapus Data Pribadi (Permanen)"
                      data-erase-action="{{ route('admin.anggota.destroy', $member->id_pengguna) }}"
                      data-erase-name="{{ $member->nama }}"
                      onclick="openEraseModal(this.dataset.eraseAction, this.dataset.eraseName)">
                      <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                  @else
                    <span class="text-[11px] text-slate-400 italic">Anonim</span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-12 text-center text-slate-400">
                <div class="flex flex-col items-center justify-center">
                  <div class="w-12 h-12 rounded-2xl bg-[#F5F8F1] flex items-center justify-center mb-3">
                    <i data-lucide="users" class="w-6 h-6 text-[#9AB880]"></i>
                  </div>
                  <span class="font-bold text-slate-700 text-sm">Data anggota tidak ditemukan</span>
                  <span class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter status</span>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination Footer -->
    <div class="p-4 sm:p-5 border-t border-[#E4F0D6]/60 bg-white">
      {{ $members->links() }}
    </div>
  </div>

  <!-- Note Footer -->
  <div class="p-4 sm:p-5 rounded-[18px] border border-amber-200 bg-amber-50/70 flex items-start gap-3.5">
    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
      <i data-lucide="shield-alert" class="w-5 h-5"></i>
    </div>
    <div class="text-xs sm:text-sm text-amber-900 leading-relaxed">
      <strong class="font-bold">Ketentuan Privasi & Integritas:</strong> Penghapusan permanen akan menghapus dokumen identitas (KTP, KK), dokumen lahan (SPPT), serta kontak pribadi anggota. ID sistem dan audit log dipertahankan secara anonim demi integritas pelaporan e-RDKK.
    </div>
  </div>

</div>

<!-- Modal Hapus Permanen -->
<div id="erase-account-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
  <div class="w-full max-w-md rounded-2xl border border-[#E4F0D6] bg-white p-6 shadow-2xl">
    <div class="flex items-start gap-3.5">
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-600 border border-red-200">
        <i data-lucide="alert-triangle" class="h-5 w-5 stroke-[2.2]"></i>
      </div>
      <div>
        <h2 class="text-lg font-extrabold text-[#1A2D10]">Hapus Data Pribadi Anggota?</h2>
        <p id="erase-member-name" class="mt-1 text-sm font-semibold text-red-600"></p>
      </div>
    </div>
    <p class="mt-4 text-xs leading-relaxed text-slate-600">
      Tindakan ini permanen dan tidak dapat dibatalkan. Seluruh dokumen identitas dan informasi akun akan dihapus. Masukkan password Admin Anda dan ketik konfirmasi di bawah ini.
    </p>
    <form id="erase-account-form" method="POST" class="mt-4 space-y-3">
      @csrf
      @method('DELETE')
      <div>
        <label class="block text-xs font-semibold text-[#4A6030] mb-1">Password Admin</label>
        <input type="password" name="password" required autocomplete="current-password"
          placeholder="Masukkan password admin Anda"
          class="h-11 w-full rounded-xl border border-red-200 px-3.5 text-xs sm:text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all">
      </div>
      <div>
        <label class="block text-xs font-semibold text-[#4A6030] mb-1">Konfirmasi Tindakan</label>
        <input name="konfirmasi" required autocomplete="off"
          placeholder="Ketik persis: HAPUS PERMANEN"
          class="h-11 w-full rounded-xl border border-red-200 px-3.5 text-xs sm:text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all">
      </div>
      <div class="mt-5 flex justify-end gap-2.5 pt-2">
        <button type="button" onclick="closeEraseModal()"
          class="rounded-xl border border-[#E4F0D6] px-4 py-2 text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] transition-colors cursor-pointer">
          Batal
        </button>
        <button type="submit"
          class="rounded-xl bg-red-600 px-4 py-2 text-xs sm:text-sm font-bold text-white hover:bg-red-700 shadow-sm shadow-red-600/30 transition-all cursor-pointer">
          Hapus Permanen
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openEraseModal(action, name) {
  const modal = document.getElementById('erase-account-modal');
  document.getElementById('erase-account-form').action = action;
  document.getElementById('erase-member-name').textContent = name;
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}
function closeEraseModal() {
  const modal = document.getElementById('erase-account-modal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
  document.getElementById('erase-account-form').reset();
}
</script>
@endsection
