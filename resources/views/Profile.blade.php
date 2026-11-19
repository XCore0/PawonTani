@php
  $layout = $layout ?? 'Pengurus.Layout._layout';
@endphp
@extends($layout)

@section('title', 'Profil Pengguna')

@section('content')
<div class="space-y-6 w-full">

  <!-- ==================== HEADER ==================== -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Biodata Profil
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Kelola informasi identitas akun, kontak, dan keamanan kredensial Anda.
      </p>
    </div>

    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-[#E4F0D6] shadow-2xs self-start sm:self-auto">
      <span class="w-2 h-2 rounded-full bg-[#4D9830]"></span>
      <span class="text-xs font-semibold text-[#1A2D10]">Role Akun:</span>
      <span class="text-xs text-[#4A6030] font-bold">{{ $user->role }}</span>
      @if($user->jabatan)
        <span class="text-slate-300">•</span>
        <span class="text-xs text-[#2E5A1C] font-semibold">{{ $user->jabatan }}</span>
      @endif
    </div>
  </div>

  @if(session('success'))
    <div class="p-4 rounded-xl border border-[#C5DFB0] bg-[#EBF6E0] text-[#2E5A1C] text-xs sm:text-sm font-semibold flex items-center gap-3">
      <i data-lucide="check-circle-2" class="w-5 h-5 text-[#4D9830] shrink-0"></i>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div class="p-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 text-xs sm:text-sm space-y-1">
      <div class="flex items-center gap-2 font-bold">
        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
        <span>Terdapat beberapa kesalahan input:</span>
      </div>
      <ul class="list-disc list-inside text-xs space-y-0.5 pl-2">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- ==================== FORM BIODATA (RESPONSIVE FLEX LAYOUT) ==================== -->
  <form action="{{ $user->role === 'Admin' || $user->role === 'PPL' ? route('admin.profil.update') : route('pengurus.profil.update') }}"
        method="POST"
        enctype="multipart/form-data"
        class="flex flex-col lg:flex-row gap-6 items-start w-full">
    @csrf
    @method('PUT')

    <!-- KOLOM KIRI: FOTO PROFIL & KARTU IDENTITAS (Lebar Tetap di Layar Lebar) -->
    <div class="w-full lg:w-[320px] lg:shrink-0 space-y-6">

      <!-- Kartu Avatar / Foto -->
      <div class="p-6 rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs text-center space-y-4">
        <div class="relative w-28 h-28 mx-auto">
          @if($user->foto_profil)
            <img id="avatar-preview" src="{{ asset($user->foto_profil) }}" alt="{{ $user->nama }}"
                 class="w-full h-full rounded-full object-cover border-4 border-[#EBF6E0] shadow-sm">
          @else
            <div id="avatar-placeholder" class="w-full h-full rounded-full bg-gradient-to-tr from-[#4D9830] to-[#72BE4A] text-white flex items-center justify-center font-extrabold text-3xl shadow-sm border-4 border-[#EBF6E0]">
              {{ strtoupper(substr($user->nama ?? 'U', 0, 1)) }}
            </div>
            <img id="avatar-preview" src="#" alt="Preview" class="hidden w-full h-full rounded-full object-cover border-4 border-[#EBF6E0] shadow-sm">
          @endif

          <label for="foto_profil" class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-[#4D9830] hover:bg-[#3D8024] text-white flex items-center justify-center shadow-md cursor-pointer transition-transform active:scale-95" title="Ubah Foto">
            <i data-lucide="camera" class="w-4 h-4"></i>
            <input type="file" id="foto_profil" name="foto_profil" accept="image/png,image/jpeg,image/webp" class="hidden" onchange="previewImage(this)">
          </label>
        </div>

        <div>
          <h3 class="text-base font-extrabold text-[#1A2D10]">{{ $user->nama }}</h3>
          <p class="text-xs text-[#9AB880] font-mono mt-0.5">{{ $user->id_pengguna }}</p>
          <span class="inline-block mt-2 text-[11px] font-bold px-3 py-1 rounded-full bg-[#EBF6E0] text-[#2E5A1C] border border-[#D1DFC4]">
            {{ $user->kelompokTani->nama_kelompok ?? ($user->role === 'Admin' || $user->role === 'PPL' ? 'Penyuluh Pertanian Lapangan' : 'Kelompok Tani') }}
          </span>
        </div>

        <p class="text-[11px] text-slate-400">
          Format gambar yang didukung: JPG, PNG, atau WEBP. Maksimal 2 MB.
        </p>
      </div>

      <!-- Kartu Detail Keanggotaan -->
      <div class="p-5 rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs space-y-3">
        <h4 class="text-xs font-bold uppercase tracking-wider text-[#9AB880] pb-2 border-b border-[#E4F0D6]">
          Data Kelembagaan
        </h4>

        <div class="space-y-2.5 text-xs">
          <div class="flex items-center justify-between">
            <span class="text-slate-500">Status Akun:</span>
            <span class="inline-flex items-center gap-1 font-bold text-[#4D9830]">
              <span class="w-2 h-2 rounded-full bg-[#4D9830]"></span>
              {{ $user->status ?? 'Aktif' }}
            </span>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-slate-500">Kelompok:</span>
            <span class="font-semibold text-[#1A2D10] text-right truncate max-w-[170px]" title="{{ $user->kelompokTani->nama_kelompok ?? '-' }}">
              {{ $user->kelompokTani->nama_kelompok ?? '-' }}
            </span>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-slate-500">ID Kelompok:</span>
            <span class="font-mono font-semibold text-slate-700">{{ $user->id_kelompok ?? '-' }}</span>
          </div>

          @if($user->jabatan)
          <div class="flex items-center justify-between">
            <span class="text-slate-500">Jabatan:</span>
            <span class="font-bold text-[#2E5A1C]">{{ $user->jabatan }}</span>
          </div>
          @endif
        </div>
      </div>

    </div>

    <!-- KOLOM KANAN: FORMULIR BIODATA & KEAMANAN (Lebar Fleksibel / Penuh) -->
    <div class="w-full flex-1 min-w-0 space-y-6">

      <!-- Bagian 1: Informasi Pribadi & Kontak -->
      <div class="p-6 sm:p-7 rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs space-y-5">
        <div class="flex items-center gap-2.5 pb-3 border-b border-[#E4F0D6]">
          <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] text-[#4D9830] flex items-center justify-center shrink-0">
            <i data-lucide="user-check" class="w-4 h-4"></i>
          </div>
          <div>
            <h2 class="text-base font-extrabold text-[#1A2D10]">Informasi Pribadi</h2>
            <p class="text-xs text-slate-500 font-medium">Perbarui identitas dan kontak resmi Anda</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full">
          <!-- Nama Lengkap -->
          <div class="space-y-1.5 sm:col-span-2">
            <label for="nama" class="block text-xs font-bold text-[#1A2D10]">
              Nama Lengkap <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="nama" name="nama" value="{{ old('nama', $user->nama) }}" required
                   class="w-full block h-10 px-3.5 rounded-xl border border-[#D1DFC4] focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830] text-xs font-semibold text-[#1A2D10] outline-hidden transition bg-[#FBFDF9]">
          </div>

          <!-- NIK -->
          <div class="space-y-1.5">
            <label for="nik" class="block text-xs font-bold text-[#1A2D10]">
              Nomor Induk Kependudukan (NIK)
            </label>
            <input type="text" id="nik" name="nik" value="{{ old('nik', $user->nik) }}" maxlength="16"
                   placeholder="16 digit NIK"
                   class="w-full block h-10 px-3.5 rounded-xl border border-[#D1DFC4] focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830] text-xs font-mono font-semibold text-[#1A2D10] outline-hidden transition bg-[#FBFDF9]">
          </div>

          <!-- Username -->
          <div class="space-y-1.5">
            <label for="username" class="block text-xs font-bold text-[#1A2D10]">
              Username <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required
                   class="w-full block h-10 px-3.5 rounded-xl border border-[#D1DFC4] focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830] text-xs font-mono font-semibold text-[#1A2D10] outline-hidden transition bg-[#FBFDF9]">
          </div>

          <!-- Email -->
          <div class="space-y-1.5">
            <label for="email" class="block text-xs font-bold text-[#1A2D10]">
              Alamat Email
            </label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                   placeholder="nama@email.com"
                   class="w-full block h-10 px-3.5 rounded-xl border border-[#D1DFC4] focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830] text-xs font-semibold text-[#1A2D10] outline-hidden transition bg-[#FBFDF9]">
          </div>

          <!-- No Telepon / WA -->
          <div class="space-y-1.5">
            <label for="no_telepon" class="block text-xs font-bold text-[#1A2D10]">
              Nomor Telepon / WhatsApp
            </label>
            <input type="text" id="no_telepon" name="no_telepon" value="{{ old('no_telepon', $user->no_telepon) }}"
                   placeholder="08xxxxxxxxxx"
                   class="w-full block h-10 px-3.5 rounded-xl border border-[#D1DFC4] focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830] text-xs font-semibold text-[#1A2D10] outline-hidden transition bg-[#FBFDF9]">
          </div>

          <!-- Alamat -->
          <div class="space-y-1.5 sm:col-span-2">
            <label for="alamat" class="block text-xs font-bold text-[#1A2D10]">
              Alamat Domisili
            </label>
            <textarea id="alamat" name="alamat" rows="2"
                      placeholder="Alamat lengkap desa, RT/RW, kecamatan..."
                      class="w-full block p-3 rounded-xl border border-[#D1DFC4] focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830] text-xs font-medium text-[#1A2D10] outline-hidden transition bg-[#FBFDF9]">{{ old('alamat', $user->alamat) }}</textarea>
          </div>
        </div>
      </div>

      <!-- Bagian 2: Pengaturan Kata Sandi (Opsional) -->
      <div class="p-6 sm:p-7 rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs space-y-5">
        <div class="flex items-center gap-2.5 pb-3 border-b border-[#E4F0D6]">
          <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
          </div>
          <div>
            <h2 class="text-base font-extrabold text-[#1A2D10]">Keamanan Kata Sandi</h2>
            <p class="text-xs text-slate-500 font-medium">Kosongkan bagian ini jika tidak ingin mengubah kata sandi</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
          <!-- Password Saat Ini -->
          <div class="space-y-1.5">
            <label for="password_saat_ini" class="block text-xs font-bold text-[#1A2D10]">
              Kata Sandi Saat Ini
            </label>
            <input type="password" id="password_saat_ini" name="password_saat_ini" autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full block h-10 px-3.5 rounded-xl border border-[#D1DFC4] focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830] text-xs outline-hidden transition bg-[#FBFDF9]">
          </div>

          <!-- Password Baru -->
          <div class="space-y-1.5">
            <label for="password_baru" class="block text-xs font-bold text-[#1A2D10]">
              Kata Sandi Baru
            </label>
            <input type="password" id="password_baru" name="password_baru" autocomplete="new-password"
                   placeholder="Minimal 6 karakter"
                   class="w-full block h-10 px-3.5 rounded-xl border border-[#D1DFC4] focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830] text-xs outline-hidden transition bg-[#FBFDF9]">
          </div>

          <!-- Konfirmasi Password Baru -->
          <div class="space-y-1.5">
            <label for="password_baru_confirmation" class="block text-xs font-bold text-[#1A2D10]">
              Ulangi Sandi Baru
            </label>
            <input type="password" id="password_baru_confirmation" name="password_baru_confirmation" autocomplete="new-password"
                   placeholder="Ulangi sandi baru"
                   class="w-full block h-10 px-3.5 rounded-xl border border-[#D1DFC4] focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830] text-xs outline-hidden transition bg-[#FBFDF9]">
          </div>
        </div>
      </div>

      <!-- Tombol Simpan -->
      <div class="flex items-center justify-end gap-3 pt-2">
        <button type="submit"
                class="inline-flex items-center gap-2 h-11 px-6 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white text-xs sm:text-sm font-bold shadow-xs hover:shadow-md transition-all cursor-pointer active:scale-95">
          <i data-lucide="save" class="w-4 h-4"></i>
          <span>Simpan Perubahan Biodata</span>
        </button>
      </div>

    </div>

  </form>

</div>
@endsection

@push('scripts')
<script>
function previewImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const preview = document.getElementById('avatar-preview');
      const placeholder = document.getElementById('avatar-placeholder');
      if (preview) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
      }
      if (placeholder) {
        placeholder.classList.add('hidden');
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
@endpush
