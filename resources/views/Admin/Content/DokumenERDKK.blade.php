@extends('Admin.Layout._layout')

@section('title', 'Dokumen e-RDKK')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <p class="text-xs font-semibold uppercase tracking-wider text-[#78965F]">Pengelolaan dokumen</p>
      <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#27451B] sm:text-[28px]">Dokumen e-RDKK</h1>
      <p class="mt-1 text-sm text-[#657B55]">Lihat dan unduh dokumen anggota untuk membantu proses input data e-RDKK.</p>
    </div>
    <div class="rounded-xl border border-[#DCE9D0] bg-white px-4 py-2 text-xs font-medium text-[#536F3F]">Akses Admin/PPL: lihat dan unduh saja</div>
  </div>

  <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-[18px] border border-[#DCE9D0] bg-white p-5">
      <p class="text-xs font-semibold uppercase tracking-wide text-[#78965F]">Total Anggota</p>
      <p class="mt-2 text-2xl font-extrabold text-[#27451B]">{{ number_format($totalAnggota) }}</p>
    </div>
    <div class="rounded-[18px] border border-[#DCE9D0] bg-white p-5">
      <p class="text-xs font-semibold uppercase tracking-wide text-[#78965F]">KTP & KK Tersedia</p>
      <p class="mt-2 text-2xl font-extrabold text-[#27451B]">{{ number_format($totalDokumenIdentitasLengkap) }}</p>
      <p class="mt-1 text-xs text-[#657B55]">Anggota dengan dua dokumen identitas</p>
    </div>
    <div class="rounded-[18px] border border-[#DCE9D0] bg-white p-5">
      <p class="text-xs font-semibold uppercase tracking-wide text-[#78965F]">Perlu SPPT</p>
      <p class="mt-2 text-2xl font-extrabold text-[#27451B]">{{ number_format($totalSpptKurang) }}</p>
      <p class="mt-1 text-xs text-[#657B55]">Anggota dengan lahan yang belum memiliki SPPT</p>
    </div>
  </div>

  <form method="GET" class="grid grid-cols-1 gap-3 rounded-[20px] border border-[#DCE9D0] bg-white p-4 md:grid-cols-12">
    <div class="md:col-span-5">
      <label class="mb-1 block text-xs font-semibold text-[#536F3F]">Cari anggota</label>
      <input name="q" value="{{ request('q') }}" placeholder="Nama, NIK, atau username" class="h-11 w-full rounded-xl border border-[#C9DDB9] px-3 text-sm text-[#27451B] outline-none transition focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/15">
    </div>
    <div class="md:col-span-4">
      <label class="mb-1 block text-xs font-semibold text-[#536F3F]">Kelompok tani</label>
      <select name="kelompok" class="h-11 w-full rounded-xl border border-[#C9DDB9] bg-white px-3 text-sm text-[#27451B]">
        <option value="">Semua kelompok</option>
        @foreach($kelompok as $item)
          <option value="{{ $item->id_kelompok }}" @selected(request('kelompok') === $item->id_kelompok)>{{ $item->nama_kelompok }}</option>
        @endforeach
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="mb-1 block text-xs font-semibold text-[#536F3F]">Keanggotaan</label>
      <select name="status" class="h-11 w-full rounded-xl border border-[#C9DDB9] bg-white px-3 text-sm text-[#27451B]">
        <option value="">Semua</option>
        <option value="Aktif" @selected(request('status') === 'Aktif')>Aktif</option>
        <option value="Keluar" @selected(request('status') === 'Keluar')>Keluar</option>
        <option value="Tidak Aktif" @selected(request('status') === 'Tidak Aktif')>Tidak Aktif</option>
      </select>
    </div>
    <div class="flex items-end md:col-span-1">
      <button class="h-11 w-full rounded-xl bg-[#4D9830] px-3 text-sm font-bold text-white transition hover:bg-[#3D8024]">Cari</button>
    </div>
  </form>

  <div class="overflow-hidden rounded-[20px] border border-[#DCE9D0] bg-white">
    <div class="border-b border-[#DCE9D0] bg-[#F4F8EF] px-5 py-4">
      <h2 class="text-sm font-bold text-[#27451B]">Daftar Dokumen Anggota</h2>
      <p class="mt-0.5 text-xs text-[#78965F]">Preview terbuka di tab baru. Unduhan dapat dilakukan per dokumen atau sekaligus dalam satu ZIP.</p>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead class="bg-white text-[11px] uppercase tracking-wide text-[#78965F]">
          <tr>
            <th class="px-5 py-3">Anggota</th>
            <th class="px-5 py-3">Kelompok</th>
            <th class="px-5 py-3">KTP</th>
            <th class="px-5 py-3">KK</th>
            <th class="px-5 py-3">SPPT per lahan</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3">Semua dokumen</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E3ECD9]">
          @forelse($anggota as $member)
            @php($ktp = $member->dokumenIdentitas->firstWhere('jenis', 'KTP'))
            @php($kk = $member->dokumenIdentitas->firstWhere('jenis', 'KK'))
            <tr class="align-top transition-colors hover:bg-[#F7FAF4]">
              <td class="px-5 py-4">
                <p class="font-bold text-[#27451B]">{{ $member->nama }}</p>
                <p class="mt-0.5 text-xs text-[#78965F]">{{ $member->nik ?: 'NIK belum diisi' }}</p>
              </td>
              <td class="px-5 py-4 text-xs text-[#536F3F]">{{ $member->kelompokTani?->nama_kelompok ?? '-' }}</td>
              <td class="px-5 py-4">
                @if($ktp)
                  <div class="flex flex-wrap gap-1.5">
                    <a target="_blank" rel="noopener noreferrer" data-no-admin-ajax href="{{ route('admin.dokumen.identity.preview', [$member->id_pengguna, 'KTP']) }}" class="inline-flex items-center gap-1 rounded-lg border border-[#DCE9D0] px-2 py-1.5 text-xs font-semibold text-[#536F3F] hover:bg-[#F4F8EF]">Lihat</a>
                    <a download data-no-admin-ajax href="{{ route('admin.dokumen.identity.download', [$member->id_pengguna, 'KTP']) }}" class="inline-flex items-center gap-1 rounded-lg bg-[#E8F2DF] px-2 py-1.5 text-xs font-semibold text-[#3D8024] hover:bg-[#DCECCF]">Unduh</a>
                  </div>
                @else
                  <span class="text-xs text-[#9A8354]">Belum tersedia</span>
                @endif
              </td>
              <td class="px-5 py-4">
                @if($kk)
                  <div class="flex flex-wrap gap-1.5">
                    <a target="_blank" rel="noopener noreferrer" data-no-admin-ajax href="{{ route('admin.dokumen.identity.preview', [$member->id_pengguna, 'KK']) }}" class="inline-flex items-center gap-1 rounded-lg border border-[#DCE9D0] px-2 py-1.5 text-xs font-semibold text-[#536F3F] hover:bg-[#F4F8EF]">Lihat</a>
                    <a download data-no-admin-ajax href="{{ route('admin.dokumen.identity.download', [$member->id_pengguna, 'KK']) }}" class="inline-flex items-center gap-1 rounded-lg bg-[#E8F2DF] px-2 py-1.5 text-xs font-semibold text-[#3D8024] hover:bg-[#DCECCF]">Unduh</a>
                  </div>
                @else
                  <span class="text-xs text-[#9A8354]">Belum tersedia</span>
                @endif
              </td>
              <td class="min-w-[250px] px-5 py-4">
                @forelse($member->lahan as $land)
                  <div class="mb-3 last:mb-0">
                    <p class="mb-1 text-xs font-semibold text-[#536F3F]">{{ $land->nama_lahan }}</p>
                    @if($land->dokumenSppt->isNotEmpty())
                      <div class="space-y-1.5">
                        @foreach($land->dokumenSppt as $sppt)
                          <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-[#F4F8EF] px-2.5 py-2">
                            <span class="max-w-[155px] truncate text-[11px] text-[#536F3F]" title="{{ $sppt->original_name }}">{{ $sppt->original_name }}</span>
                            <span class="flex shrink-0 items-center gap-2">
                              <a target="_blank" rel="noopener noreferrer" data-no-admin-ajax href="{{ route('admin.dokumen.sppt.preview', $sppt->id) }}" class="text-[11px] font-semibold text-[#536F3F] hover:underline">Lihat</a>
                              <a download data-no-admin-ajax href="{{ route('admin.dokumen.sppt.download', $sppt->id) }}" class="text-[11px] font-semibold text-[#3D8024] hover:underline">Unduh</a>
                            </span>
                          </div>
                        @endforeach
                      </div>
                    @else
                      <span class="text-[11px] text-[#9A8354]">SPPT belum tersedia</span>
                    @endif
                  </div>
                @empty
                  <span class="text-xs text-[#78965F]">Belum memiliki lahan</span>
                @endforelse
              </td>
              <td class="px-5 py-4">
                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $member->anggota?->status_keanggotaan === 'Aktif' ? 'bg-[#E8F2DF] text-[#3D8024]' : 'bg-[#F7F1E5] text-[#8A6D32]' }}">{{ $member->anggota?->status_keanggotaan ?? 'Tidak diketahui' }}</span>
              </td>
              <td class="px-5 py-4">
                @if($member->dokumenIdentitas->isNotEmpty() || $member->lahan->contains(fn ($land) => $land->dokumenSppt->isNotEmpty()))
                  <a download data-no-admin-ajax href="{{ route('admin.dokumen.member.download-all', $member->id_pengguna) }}" class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-xl bg-[#4D9830] px-3 py-2 text-xs font-bold text-white transition hover:bg-[#3D8024]">
                    <i data-lucide="download" class="h-3.5 w-3.5"></i> Unduh Semua
                  </a>
                @else
                  <span class="text-xs text-[#9A8354]">Belum ada dokumen</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-[#78965F]">Data anggota tidak ditemukan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="border-t border-[#DCE9D0] p-4">{{ $anggota->links() }}</div>
  </div>

  <div class="rounded-xl border border-[#E5D8B7] bg-[#FBF7EE] p-4 text-xs leading-relaxed text-[#705B30]">
    <strong>Pengingat keamanan:</strong> KTP, KK, dan SPPT merupakan dokumen pribadi. Simpan hasil unduhan di perangkat yang aman dan jangan membagikannya kepada pihak yang tidak berwenang.
  </div>
</div>
@endsection
