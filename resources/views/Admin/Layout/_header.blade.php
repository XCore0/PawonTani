<!-- ==================== TOP HEADER BAR ==================== -->
<header class="sticky top-0 z-30 h-16 bg-white border-b border-[#E4F0D6] flex items-center justify-between px-4 sm:px-6 lg:px-8 select-none">

  <!-- Left: Mobile Menu Toggle & Breadcrumbs -->
  <div class="flex items-center gap-3 sm:gap-4">
    <!-- Hamburger button for mobile/tablet -->
    <button id="sidebar-open-btn" type="button"
      class="lg:hidden p-2 rounded-xl text-[#4A6030] hover:text-[#1A2D10] hover:bg-[#F5F8F1] focus:outline-none transition-colors"
      aria-label="Buka Menu Navigasi">
      <i data-lucide="menu" class="w-5 h-5"></i>
    </button>

    <!-- Breadcrumb (matching SVG coordinates & colors) -->
    <nav class="flex items-center gap-2 text-xs sm:text-sm">
      <a href="{{ route('admin.dashboard') }}" class="font-medium text-[#4A6030] hover:text-[#4D9830] transition-colors">
        PawonTani
      </a>
      <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#9AB880]"></i>
      <span data-admin-page-title class="font-bold text-[#1A2D10]">
        @yield('title', 'Pengurus')
      </span>
    </nav>
  </div>

  <!-- Right: Notifications & User Profile -->
  <div class="flex items-center gap-3">

    <!-- Notification Dropdown -->
    <div class="relative" data-header-dropdown>
      <button type="button" data-dropdown-trigger="notification-dropdown"
        class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white border border-[#E4F0D6] text-[#4A6030] hover:text-[#1A2D10] hover:bg-[#F5F8F1] flex items-center justify-center transition-colors relative cursor-pointer"
        aria-label="Notifikasi">
        <i data-lucide="bell" class="w-4 h-4 sm:w-[18px] sm:h-[18px]"></i>
        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-[#F4A020] ring-2 ring-white"></span>
      </button>
      <div id="notification-dropdown" class="absolute right-0 top-12 z-50 hidden w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-[#E4F0D6] bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-[#E4F0D6] px-4 py-3">
          <div>
            <span class="block text-sm font-bold text-[#1A2D10]">Notifikasi</span>
            <span class="mt-0.5 block text-[10px] text-[#9AB880]">Pembaruan terbaru untuk Anda</span>
          </div>
          <span class="rounded-full bg-[#EBF6E0] px-2 py-0.5 text-[10px] font-bold text-[#4D9830]">5 baru</span>
        </div>
        <div class="max-h-[360px] divide-y divide-[#E4F0D6]/70 overflow-y-auto">
          <a href="{{ route('admin.verifikasi') }}" data-admin-title="Verifikasi Lapangan" class="flex items-start gap-3 px-4 py-3 transition-colors hover:bg-[#F5F8F1]">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600"><i data-lucide="file-check-2" class="h-4 w-4"></i></span>
            <span class="min-w-0 flex-1"><span class="block text-xs font-bold text-[#1A2D10]">Verifikasi baru menunggu</span><span class="mt-0.5 block text-[11px] leading-relaxed text-[#6B7F5B]">Data kelompok perlu diperiksa.</span><span class="mt-1 block text-[10px] text-[#9AB880]">10 menit lalu</span></span>
            <i data-lucide="chevron-right" class="mt-2 h-3.5 w-3.5 shrink-0 text-[#9AB880]"></i>
          </a>
          <a href="{{ route('admin.pengurus') }}" data-admin-title="Pengurus" class="flex items-start gap-3 px-4 py-3 transition-colors hover:bg-[#F5F8F1]">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#EBF6E0] text-[#4D9830]"><i data-lucide="user-plus" class="h-4 w-4"></i></span>
            <span class="min-w-0 flex-1"><span class="block text-xs font-bold text-[#1A2D10]">Pengurus baru ditambahkan</span><span class="mt-0.5 block text-[11px] leading-relaxed text-[#6B7F5B]">Satu data pengurus masuk.</span><span class="mt-1 block text-[10px] text-[#9AB880]">1 jam lalu</span></span>
            <i data-lucide="chevron-right" class="mt-2 h-3.5 w-3.5 shrink-0 text-[#9AB880]"></i>
          </a>
          <a href="{{ route('admin.kelompok') }}" data-admin-title="Kelompok Tani" class="flex items-start gap-3 px-4 py-3 transition-colors hover:bg-[#F5F8F1]">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><i data-lucide="sprout" class="h-4 w-4"></i></span>
            <span class="min-w-0 flex-1"><span class="block text-xs font-bold text-[#1A2D10]">Data kelompok diperbarui</span><span class="mt-0.5 block text-[11px] leading-relaxed text-[#6B7F5B]">Informasi Sri Rejeki berubah.</span><span class="mt-1 block text-[10px] text-[#9AB880]">3 jam lalu</span></span>
            <i data-lucide="chevron-right" class="mt-2 h-3.5 w-3.5 shrink-0 text-[#9AB880]"></i>
          </a>
          <a href="{{ route('admin.edukasi.tips') }}" data-admin-title="Tips" class="flex items-start gap-3 px-4 py-3 transition-colors hover:bg-[#F5F8F1]">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><i data-lucide="book-open" class="h-4 w-4"></i></span>
            <span class="min-w-0 flex-1"><span class="block text-xs font-bold text-[#1A2D10]">Materi edukasi baru</span><span class="mt-0.5 block text-[11px] leading-relaxed text-[#6B7F5B]">Tips pertanian sedang disiapkan.</span><span class="mt-1 block text-[10px] text-[#9AB880]">Kemarin</span></span>
            <i data-lucide="chevron-right" class="mt-2 h-3.5 w-3.5 shrink-0 text-[#9AB880]"></i>
          </a>
          <a href="{{ route('admin.dashboard') }}" data-admin-title="Dashboard" class="flex items-start gap-3 px-4 py-3 transition-colors hover:bg-[#F5F8F1]">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600"><i data-lucide="info" class="h-4 w-4"></i></span>
            <span class="min-w-0 flex-1"><span class="block text-xs font-bold text-[#1A2D10]">Pembaruan sistem</span><span class="mt-0.5 block text-[11px] leading-relaxed text-[#6B7F5B]">Sistem berjalan normal.</span><span class="mt-1 block text-[10px] text-[#9AB880]">2 hari lalu</span></span>
            <i data-lucide="chevron-right" class="mt-2 h-3.5 w-3.5 shrink-0 text-[#9AB880]"></i>
          </a>
        </div>
        <a href="{{ route('admin.notifikasi') }}" data-admin-title="Notifikasi" class="block border-t border-[#E4F0D6] px-4 py-3 text-center text-xs font-bold text-[#4D9830] hover:bg-[#F5F8F1]">Lihat selengkapnya</a>
      </div>
    </div>

    <!-- Profile Dropdown -->
    <div class="relative" data-header-dropdown>
      <button type="button" data-dropdown-trigger="profile-dropdown" class="flex items-center gap-2.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl bg-white border border-[#E4F0D6] hover:bg-[#F5F8F1] transition-colors cursor-pointer select-none">
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-tr from-[#4D9830] to-[#72BE4A] text-white flex items-center justify-center font-bold text-xs shadow-2xs shrink-0">
          A
        </div>
        <div class="hidden sm:flex flex-col text-left">
          <span class="text-xs font-bold text-[#1A2D10] leading-tight">
            Ahmad Fauzi
          </span>
          <span class="text-[10px] text-[#9AB880] leading-tight">
            Admin
          </span>
        </div>
        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#4A6030]"></i>
      </button>
      <div id="profile-dropdown" class="absolute right-0 top-12 z-50 hidden w-48 overflow-hidden rounded-2xl border border-[#E4F0D6] bg-white p-1.5 shadow-xl">
        <a href="{{ route('admin.profil') }}" data-admin-title="Profil" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-xs font-semibold text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10]"><i data-lucide="user" class="h-4 w-4"></i>Profil</a>
        <a href="{{ route('login') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-xs font-semibold text-red-600 hover:bg-red-50"><i data-lucide="log-out" class="h-4 w-4"></i>Keluar</a>
      </div>
    </div>

  </div>

</header>
