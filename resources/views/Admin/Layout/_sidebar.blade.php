<!-- ==================== SIDEBAR COMPONENT ==================== -->
<!-- Backdrop for mobile drawer -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 lg:hidden hidden transition-opacity duration-300"></div>

<aside id="sidebar"
  class="fixed top-0 left-0 z-50 h-screen w-[230px] bg-white border-r border-[#E4F0D6] flex flex-col justify-between transition-transform duration-300 -translate-x-full lg:translate-x-0 select-none">

  <!-- 1. Sidebar Header (Brand Logo & Name) -->
  <div>
    <div class="h-[76px] px-4 flex items-center border-b border-[#F0F7E8]">
      <a href="{{ route('admin.pengurus') }}" class="flex items-center group py-1">
        <img src="{{ asset('images/Logo2.png') }}" alt="PawonTani Logo" class="h-9 sm:h-10 w-auto max-w-[170px] object-contain hover:opacity-95 transition-opacity">
      </a>
      <!-- Mobile Close Button -->
      <button id="sidebar-close-btn" class="ml-auto lg:hidden p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100" aria-label="Tutup Menu">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- 2. Navigation Menus -->
    <div class="py-4 px-3 space-y-6 overflow-y-auto max-h-[calc(100vh-230px)]">

      <!-- Section: MENU UTAMA -->
      <div>
        <span class="block px-3 mb-2 text-[11px] font-semibold text-[#9AB880] tracking-wider uppercase">
          Menu Utama
        </span>
        <nav class="space-y-1">
          <!-- Dashboard -->
          <a href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('admin.dashboard') ? 'bg-[#EBF6E0] text-[#4D9830] font-semibold' : 'text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10]' }}">
            <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('admin.dashboard') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
            <span>Dashboard</span>
          </a>

          <!-- Kelompok Tani -->
          <a href="{{ route('admin.kelompok') }}"
            class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('admin.kelompok') ? 'bg-[#EBF6E0] text-[#4D9830] font-semibold' : 'text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10]' }}">
            @if(request()->routeIs('admin.kelompok'))
              <div class="absolute -left-3 top-1.5 bottom-1.5 w-1 rounded-r-md bg-[#4D9830]"></div>
            @endif
            <i data-lucide="users" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('admin.kelompok') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
            <span>Kelompok Tani</span>
          </a>

          <!-- Pengurus (Active Item in mockup) -->
          <a href="{{ route('admin.pengurus') }}"
            class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('admin.pengurus') ? 'bg-[#EBF6E0] text-[#4D9830]' : 'text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10]' }}">
            @if(request()->routeIs('admin.pengurus'))
              <div class="absolute -left-3 top-1.5 bottom-1.5 w-1 rounded-r-md bg-[#4D9830]"></div>
            @endif
            <i data-lucide="user-check" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('admin.pengurus') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
            <span>Pengurus</span>
          </a>

          <!-- Verifikasi Lapangan -->
          <a href="#" onclick="alert('Menu Verifikasi Lapangan'); return false;"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group">
            <i data-lucide="file-check" class="w-4 h-4 shrink-0 text-[#4A6030] group-hover:text-[#1A2D10] transition-colors"></i>
            <span>Verifikasi Lapangan</span>
          </a>

          <!-- Komoditas -->
          <a href="#" onclick="alert('Menu Komoditas'); return false;"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group">
            <i data-lucide="sprout" class="w-4 h-4 shrink-0 text-[#4A6030] group-hover:text-[#1A2D10] transition-colors"></i>
            <span>Komoditas</span>
          </a>

          <!-- Aktivitas -->
          <a href="#" onclick="alert('Menu Aktivitas'); return false;"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group">
            <i data-lucide="activity" class="w-4 h-4 shrink-0 text-[#4A6030] group-hover:text-[#1A2D10] transition-colors"></i>
            <span>Aktivitas</span>
          </a>
        </nav>
      </div>

      <!-- Section: AKUN -->
      <div>
        <span class="block px-3 mb-2 text-[11px] font-semibold text-[#9AB880] tracking-wider uppercase">
          Akun
        </span>
        <nav class="space-y-1">
          <!-- Notifikasi with Badge -->
          <a href="#" onclick="alert('3 Notifikasi Baru'); return false;"
            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group">
            <div class="flex items-center gap-3">
              <i data-lucide="bell" class="w-4 h-4 shrink-0 text-[#4A6030] group-hover:text-[#1A2D10] transition-colors"></i>
              <span>Notifikasi</span>
            </div>
            <span class="px-2 py-0.5 text-[11px] font-bold text-white bg-[#F4A020] rounded-full">
              3
            </span>
          </a>

          <!-- Profil -->
          <a href="#" onclick="alert('Profil Pengguna'); return false;"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group">
            <i data-lucide="user" class="w-4 h-4 shrink-0 text-[#4A6030] group-hover:text-[#1A2D10] transition-colors"></i>
            <span>Profil</span>
          </a>
        </nav>
      </div>

    </div>
  </div>

  <!-- 3. Sidebar Footer (User Card & Keluar Button) -->
  <div class="p-3.5 border-t border-[#F0F7E8] space-y-2.5 bg-white">
    <!-- User Info Card -->
    <div class="flex items-center gap-3 p-2.5 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6]">
      <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#4D9830] to-[#72BE4A] text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
        A
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-xs font-bold text-[#1A2D10] truncate">
          Ahmad Fauzi, S.P.
        </p>
        <p class="text-[10px] text-[#9AB880] truncate font-medium">
          PPL / Admin
        </p>
      </div>
    </div>

    <!-- Keluar Button (matching SVG spec #FFF5F5 / border #FCA5A5 / text #DC2626) -->
    <a href="{{ route('login') }}"
      class="flex items-center justify-center gap-2 w-full py-2 px-3 rounded-xl bg-[#FFF5F5] border border-[#FCA5A5] text-xs font-semibold text-[#DC2626] hover:bg-red-100 hover:border-red-300 transition-colors shadow-2xs">
      <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
      <span>Keluar</span>
    </a>
  </div>

</aside>
