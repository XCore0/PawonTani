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

  <!-- Right: Notifications & User Profile Chip -->
  <div class="flex items-center gap-3">

    <!-- Notification Bell Button (matching SVG rounded-lg border #E4F0D6, bell icon #4A6030, dot #F4A020) -->
    <div class="relative">
      <button type="button" onclick="alert('Anda memiliki 3 notifikasi verifikasi kelompok tani baru.');"
        class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white border border-[#E4F0D6] text-[#4A6030] hover:text-[#1A2D10] hover:bg-[#F5F8F1] flex items-center justify-center transition-colors relative cursor-pointer"
        aria-label="Notifikasi">
        <i data-lucide="bell" class="w-4 h-4 sm:w-[18px] sm:h-[18px]"></i>
        <!-- Orange Unread Dot (from SVG) -->
        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-[#F4A020] ring-2 ring-white"></span>
      </button>
    </div>

    <!-- User Profile Chip (matching SVG container 1254..1415, border #E4F0D6, avatar initial, name, chevron) -->
    <div class="relative">
      <div class="flex items-center gap-2.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl bg-white border border-[#E4F0D6] hover:bg-[#F5F8F1] transition-colors cursor-pointer select-none">
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
      </div>
    </div>

  </div>

</header>
