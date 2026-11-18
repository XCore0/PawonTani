@extends('Admin.Layout._layout')

@section('title', 'Data Anggota')

@section('content')
<div class="space-y-6">
  <div>
    <p class="text-xs font-semibold uppercase tracking-wider text-[#9AB880]">Manajemen keanggotaan</p>
    <h1 class="mt-1 text-2xl sm:text-[28px] font-extrabold tracking-tight text-[#1A2D10]">Data Anggota</h1>
    <p class="mt-1 text-sm text-[#6B7F5B]">Kelola pemulihan status keanggotaan dan penghapusan data pribadi. Dokumen anggota tetap dikelola melalui menu Dokumen e-RDKK.</p>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="rounded-[18px] border border-[#E4F0D6] bg-white p-5"><p class="text-xs font-semibold uppercase tracking-wide text-[#9AB880]">Anggota Aktif</p><p class="mt-2 text-2xl font-extrabold text-[#1A2D10]">{{ number_format($stats['aktif']) }}</p></div>
    <div class="rounded-[18px] border border-[#E4F0D6] bg-white p-5"><p class="text-xs font-semibold uppercase tracking-wide text-[#9AB880]">Keluar dari Kelompok</p><p class="mt-2 text-2xl font-extrabold text-[#1A2D10]">{{ number_format($stats['keluar']) }}</p></div>
    <div class="rounded-[18px] border border-[#E4F0D6] bg-white p-5"><p class="text-xs font-semibold uppercase tracking-wide text-[#9AB880]">Keanggotaan Tidak Aktif</p><p class="mt-2 text-2xl font-extrabold text-[#1A2D10]">{{ number_format($stats['tidak_aktif']) }}</p></div>
  </div>

  @if(session('success'))<div class="rounded-xl border border-[#CDE8BA] bg-[#F2FAEC] px-4 py-3 text-sm text-[#315F20]">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

  <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 rounded-[20px] border border-[#E4F0D6] bg-white p-4">
    <div class="md:col-span-5"><label class="mb-1 block text-xs font-semibold text-[#4A6030]">Cari anggota</label><input name="q" value="{{ request('q') }}" placeholder="Nama, NIK, atau username" class="h-11 w-full rounded-xl border border-[#C5DFB0] px-3 text-sm outline-none focus:border-[#4D9830]"></div>
    <div class="md:col-span-3"><label class="mb-1 block text-xs font-semibold text-[#4A6030]">Kelompok tani</label><select name="kelompok" class="h-11 w-full rounded-xl border border-[#C5DFB0] bg-white px-3 text-sm"><option value="">Semua kelompok</option>@foreach($groups as $group)<option value="{{ $group->id_kelompok }}" @selected(request('kelompok') === $group->id_kelompok)>{{ $group->nama_kelompok }}</option>@endforeach</select></div>
    <div class="md:col-span-2"><label class="mb-1 block text-xs font-semibold text-[#4A6030]">Status</label><select name="status" class="h-11 w-full rounded-xl border border-[#C5DFB0] bg-white px-3 text-sm"><option value="">Semua</option>@foreach(['Aktif','Keluar','Tidak Aktif','Dihapus'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div>
    <div class="flex items-end md:col-span-2"><button class="h-11 w-full rounded-xl bg-[#4D9830] px-3 text-sm font-bold text-white hover:bg-[#3D8024]">Terapkan</button></div>
  </form>

  <div class="overflow-hidden rounded-[20px] border border-[#E4F0D6] bg-white">
    <div class="border-b border-[#E4F0D6] bg-[#F5F8F1] px-5 py-4"><h2 class="text-sm font-bold text-[#1A2D10]">Daftar anggota dan arsip</h2><p class="mt-0.5 text-xs text-[#9AB880]">Admin dapat mengaktifkan kembali anggota yang keluar atau menghapus data pribadi secara permanen.</p></div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead class="bg-white text-[11px] uppercase tracking-wide text-[#9AB880]"><tr><th class="px-5 py-3">Anggota</th><th class="px-5 py-3">Kelompok</th><th class="px-5 py-3">Tanggal keluar</th><th class="px-5 py-3">Status keanggotaan</th><th class="px-5 py-3">Tindakan</th></tr></thead>
        <tbody class="divide-y divide-[#E4F0D6]">
          @forelse($members as $member)
            @php($membership = $member->anggota?->status_keanggotaan ?? '-')
            <tr class="align-top hover:bg-[#F5F8F1]/60">
              <td class="px-5 py-4"><p class="font-bold text-[#1A2D10]">{{ $member->nama }}</p><p class="mt-0.5 text-xs text-[#9AB880]">{{ $member->id_pengguna }}</p></td>
              <td class="px-5 py-4 text-xs text-[#4A6030]">{{ $member->kelompokTani?->nama_kelompok ?? 'Tidak terikat kelompok' }}</td>
              <td class="px-5 py-4 text-xs text-[#6B7F5B]">{{ $member->anggota?->tanggal_keluar?->format('d M Y, H:i') ?? '—' }}</td>
              <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $membership === 'Aktif' ? 'bg-emerald-50 text-emerald-700' : ($membership === 'Keluar' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">{{ $membership }}</span></td>
              <td class="px-5 py-4"><div class="flex flex-wrap gap-2">
                @if($membership === 'Keluar' && $member->status !== 'Dihapus')
                  <form method="POST" action="{{ route('admin.anggota.restore', $member->id_pengguna) }}">@csrf<button class="rounded-lg bg-[#EBF6E0] px-3 py-2 text-xs font-bold text-[#3D8024] hover:bg-[#DFF0D0]">Aktifkan kembali</button></form>
                @endif
                @if($member->status !== 'Dihapus' && $membership !== 'Dihapus')
                  <button type="button" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100" data-erase-action="{{ route('admin.anggota.destroy', $member->id_pengguna) }}" data-erase-name="{{ $member->nama }}" onclick="openEraseModal(this.dataset.eraseAction, this.dataset.eraseName)">Hapus permanen</button>
                @else
                  <span class="text-xs text-[#9AB880]">Data pribadi telah dianonimkan</span>
                @endif
              </div></td>
            </tr>
          @empty
            <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-[#9AB880]">Data anggota tidak ditemukan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="border-t border-[#E4F0D6] p-4">{{ $members->links() }}</div>
  </div>

  <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs leading-relaxed text-amber-900"><strong>Perhatian:</strong> penghapusan permanen akan menghapus dokumen identitas, SPPT, kontak, alamat, foto, dan informasi pribadi akun. ID teknis serta riwayat audit yang diperlukan untuk menjaga integritas database dipertahankan dalam bentuk anonim.</div>
</div>

<div id="erase-account-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 p-4">
  <div class="w-full max-w-md rounded-2xl border border-[#E4F0D6] bg-white p-6 shadow-xl">
    <div class="flex items-start gap-3"><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600"><i data-lucide="alert-triangle" class="h-5 w-5"></i></div><div><h2 class="text-lg font-extrabold text-[#1A2D10]">Hapus data pribadi anggota?</h2><p id="erase-member-name" class="mt-1 text-sm text-[#6B7F5B]"></p></div></div>
    <p class="mt-4 text-xs leading-relaxed text-slate-600">Tindakan ini tidak dapat dibatalkan. Dokumen KTP, KK, SPPT dan informasi pribadi akan dihapus. Masukkan password Admin dan ketik <strong>HAPUS PERMANEN</strong>.</p>
    <form id="erase-account-form" method="POST" class="mt-4 space-y-3">@csrf @method('DELETE')
      <input type="password" name="password" required autocomplete="current-password" placeholder="Password Admin" class="h-11 w-full rounded-xl border border-red-200 px-3 text-sm outline-none focus:border-red-500">
      <input name="konfirmasi" required autocomplete="off" placeholder="Ketik HAPUS PERMANEN" class="h-11 w-full rounded-xl border border-red-200 px-3 text-sm outline-none focus:border-red-500">
      <div class="flex justify-end gap-2"><button type="button" onclick="closeEraseModal()" class="rounded-xl border border-[#E4F0D6] px-4 py-2 text-sm font-semibold text-[#4A6030]">Batal</button><button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700">Hapus permanen</button></div>
    </form>
  </div>
</div>
<script>
function openEraseModal(action, name) {
  const modal = document.getElementById('erase-account-modal');
  document.getElementById('erase-account-form').action = action;
  document.getElementById('erase-member-name').textContent = name;
  modal.classList.remove('hidden'); modal.classList.add('flex');
}
function closeEraseModal() {
  const modal = document.getElementById('erase-account-modal');
  modal.classList.add('hidden'); modal.classList.remove('flex');
  document.getElementById('erase-account-form').reset();
}
</script>
@endsection
