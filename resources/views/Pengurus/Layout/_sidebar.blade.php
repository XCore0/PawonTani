<!-- ==================== SIDEBAR COMPONENT ==================== -->
<!-- Backdrop for mobile drawer -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 lg:hidden hidden transition-opacity duration-300"></div>

<aside id="sidebar"
  class="fixed top-0 left-0 z-50 h-screen w-[230px] bg-white border-r border-[#E4F0D6] flex flex-col justify-between transition-transform duration-300 -translate-x-full lg:translate-x-0 select-none">

  <!-- 1. Sidebar Header (Brand Logo & Name) -->
  <div>
    <div class="relative h-16 px-4 flex items-center justify-center border-b border-[#F0F7E8]">
      <a href="{{ route('pengurus.dashboard') }}" class="flex items-center justify-center group py-1">
        <img src="{{ asset('images/Logo2.png') }}" alt="PawonTani Logo" class="h-9 sm:h-10 w-auto max-w-[170px] object-contain hover:opacity-95 transition-opacity">
      </a>
      <!-- Mobile Close Button -->
      <button id="sidebar-close-btn" class="absolute right-4 lg:hidden p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100" aria-label="Tutup Menu">
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
          <a href="{{ route('pengurus.dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('pengurus.dashboard') ? 'bg-[#EBF6E0] text-[#4D9830] font-semibold' : 'text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10]' }}">
            <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('pengurus.dashboard') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
            <span>Dashboard</span>
          </a>

          <!-- Kelola Anggota -->
          <a href="{{ route('pengurus.anggota') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('pengurus.anggota') ? 'bg-[#EBF6E0] text-[#4D9830] font-semibold' : 'text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10]' }}">
            <i data-lucide="users" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('pengurus.anggota') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
            <span>Kelola Anggota</span>
          </a>

          <!-- Kelola Lahan -->
          <a href="{{ route('pengurus.lahan') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group {{ request()->routeIs('pengurus.lahan') ? 'bg-[#EBF6E0] text-[#4D9830] font-semibold' : '' }}">
            <i data-lucide="map" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('pengurus.lahan') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
            <span>Kelola Lahan</span>
          </a>

          <!-- Monitoring Pertanian -->
          <a href="{{ route('pengurus.monitoring') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group {{ request()->routeIs('pengurus.monitoring') ? 'bg-[#EBF6E0] text-[#4D9830] font-semibold' : '' }}">
            <i data-lucide="leaf" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('pengurus.monitoring') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
            <span>Monitoring Pertanian</span>
          </a>

          <!-- Panen -->
          <a href="{{ route('pengurus.panen') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group {{ request()->routeIs('pengurus.panen') ? 'bg-[#EBF6E0] text-[#4D9830] font-semibold' : '' }}">
            <i data-lucide="wheat" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('pengurus.panen') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
            <span>Panen</span>
          </a>

          <!-- Penjualan -->
          <a href="{{ route('pengurus.penjualan') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group {{ request()->routeIs('pengurus.penjualan') ? 'bg-[#EBF6E0] text-[#4D9830] font-semibold' : '' }}">
            <i data-lucide="bar-chart-3" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('pengurus.penjualan') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
            <span>Penjualan</span>
          </a>

          <!-- Laporan -->
          <a href="{{ route('pengurus.laporan') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group {{ request()->routeIs('pengurus.laporan') ? 'bg-[#EBF6E0] text-[#4D9830] font-semibold' : '' }}">
            <i data-lucide="file-text" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('pengurus.laporan') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
            <span>Laporan</span>
          </a>
        </nav>
      </div>

      <!-- Section: INFORMASI -->
      <div>
        <span class="block px-3 mb-2 text-[11px] font-semibold text-[#9AB880] tracking-wider uppercase">
          Informasi
        </span>
        <nav class="space-y-1">
          <!-- Informasi & Prediksi -->
          <a href="{{ route('pengurus.informasi') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group {{ request()->routeIs('pengurus.informasi') ? 'bg-[#EBF6E0] text-[#4D9830] font-semibold' : '' }}">
            <i data-lucide="info" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('pengurus.informasi') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
            <span>Informasi & Prediksi</span>
          </a>

          <!-- Edukasi -->
          <a href="{{ route('pengurus.edukasi') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group {{ request()->routeIs('pengurus.edukasi') ? 'bg-[#EBF6E0] text-[#4D9830] font-semibold' : '' }}">
            <i data-lucide="book-open" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('pengurus.edukasi') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
            <span>Edukasi</span>
          </a>
        </nav>
      </div>

      <!-- Section: AKUN -->
      <div>
        <span class="block px-3 mb-2 text-[11px] font-semibold text-[#9AB880] tracking-wider uppercase">
          Akun
        </span>
        <nav class="space-y-1">
          <!-- Notifikasi -->
          <a href="{{ route('pengurus.notifikasi') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group {{ request()->routeIs('pengurus.notifikasi') ? 'bg-[#EBF6E0] text-[#4D9830] font-semibold' : '' }}">
            <i data-lucide="bell" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('pengurus.notifikasi') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
            <span>Notifikasi</span>
            <span class="ml-auto rounded-full bg-orange-400 px-1.5 py-0.5 text-[10px] font-bold text-white">3</span>
          </a>

          <!-- Profil -->
          <a href="{{ route('pengurus.profil') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-all group {{ request()->routeIs('pengurus.profil') ? 'bg-[#EBF6E0] text-[#4D9830] font-semibold' : '' }}">
            <i data-lucide="user" class="w-4 h-4 shrink-0 transition-colors {{ request()->routeIs('pengurus.profil') ? 'text-[#4D9830]' : 'text-[#4A6030] group-hover:text-[#1A2D10]' }}"></i>
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
        S
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-xs font-bold text-[#1A2D10] truncate">
          Sugiarto Wibowo
        </p>
        <p class="text-[10px] text-[#9AB880] truncate font-medium">
          Ketua Kelompok
        </p>
      </div>
    </div>

    <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 py-2 text-xs font-semibold text-red-600 hover:bg-red-100 transition-colors">
      <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
      Keluar
    </a>
  </div>

</aside>
