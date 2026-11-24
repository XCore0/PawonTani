<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">
  <title>@yield('title', 'Marketplace PawonTani - Produk Tani Segar Langsung dari Petani')</title>
  <meta name="description" content="@yield('meta_description', 'Beli hasil pertanian segar dan berkualitas langsung dari petani lokal kelompok tani binaan PawonTani.')">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest" defer></script>
</head>

<body class="font-sans antialiased bg-[#F5F8F1] text-slate-800 selection:bg-pawon-100 selection:text-pawon-900 flex flex-col min-h-screen">

  <!-- ==================== 1. NAVBAR CUSTOMER ==================== -->
  <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-[#E4F0D6] transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">

        <!-- Logo Brand -->
        <a href="{{ route('marketplace.home') }}" class="flex items-center gap-3 group py-1">
          <img src="{{ asset('images/Logo2.png') }}" alt="PawonTani Logo" class="h-10 sm:h-12 w-auto object-contain hover:opacity-95 transition-opacity">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-pawon-100 text-pawon-800 border border-[#C5DFB0]">
            Marketplace
          </span>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center space-x-8 text-[15px]">
          <a href="{{ route('marketplace.home') }}" class="font-medium text-slate-700 hover:text-pawon-700 transition-colors {{ request()->routeIs('marketplace.home') ? 'text-pawon-700 font-bold border-b-2 border-pawon-700 pb-1' : '' }}">
            Beranda
          </a>
          <a href="{{ route('marketplace.katalog') }}" class="font-medium text-slate-700 hover:text-pawon-700 transition-colors {{ request()->routeIs('marketplace.katalog') || request()->routeIs('marketplace.detail') ? 'text-pawon-700 font-bold border-b-2 border-pawon-700 pb-1' : '' }}">
            Marketplace
          </a>
          <a href="{{ request()->routeIs('marketplace.home') ? '#tentang-pawontani' : route('marketplace.home').'#tentang-pawontani' }}" class="font-medium text-slate-700 hover:text-pawon-700 transition-colors">
            Tentang PawonTani
          </a>
        </nav>

        <!-- CTA Buttons Desktop -->
        <div class="hidden md:flex items-center gap-3">
          <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200/80 rounded-full transition-all">
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Portal Utama
          </a>
          <a href="{{ route('marketplace.katalog') }}" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow-sm hover:shadow-md hover:shadow-pawon-700/25 transition-all hover:translate-y-[-1px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            Jelajahi Produk
          </a>
        </div>

        <!-- Mobile Menu Button -->
        <div class="flex md:hidden">
          <button id="customer-mobile-menu-btn" type="button" class="p-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
        </div>

      </div>
    </div>

    <!-- Mobile Menu Drawer -->
    <div id="customer-mobile-menu" class="hidden md:hidden border-b border-[#E4F0D6] bg-white px-5 pt-4 pb-6 space-y-3 shadow-xl">
      <a href="{{ route('marketplace.home') }}" class="block text-base font-semibold py-1.5 {{ request()->routeIs('marketplace.home') ? 'text-pawon-700 font-bold' : 'text-slate-700' }}">
        Beranda Marketplace
      </a>
      <a href="{{ route('marketplace.katalog') }}" class="block text-base font-semibold py-1.5 {{ request()->routeIs('marketplace.katalog') ? 'text-pawon-700 font-bold' : 'text-slate-700' }}">
        Katalog Produk
      </a>
      <a href="{{ route('marketplace.home') }}#tentang-pawontani" class="block text-base font-semibold py-1.5 text-slate-700">
        Tentang PawonTani
      </a>
      <div class="pt-3 border-t border-slate-100 flex flex-col gap-2.5">
        <a href="{{ route('marketplace.katalog') }}" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          Jelajahi Produk
        </a>
        <a href="{{ route('home') }}" class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-full">
          Kembali ke Portal PawonTani
        </a>
      </div>
    </div>
  </header>

  <!-- ==================== 2. MAIN CONTENT ==================== -->
  <main class="flex-grow">
    @yield('content')
  </main>

  <!-- ==================== 3. FOOTER CUSTOMER (SERAGAM DENGAN HOMEPAGE PAWONTANI) ==================== -->
  <footer class="bg-white border-t border-slate-200/80 pt-16 pb-12 text-slate-600 text-sm mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Footer Main Grid: 12 Cols Lapang -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-slate-100">
        
        <!-- Brand Summary Column (5 cols) -->
        <div class="md:col-span-5 space-y-4">
          <a href="{{ route('marketplace.home') }}" class="inline-block group">
            <img src="{{ asset('images/Logo2.png') }}" alt="PawonTani Logo" class="h-10 sm:h-12 w-auto object-contain hover:opacity-95 transition-opacity">
          </a>
          <p class="text-slate-500 max-w-sm text-sm leading-relaxed">
            Marketplace resmi PawonTani yang mempertemukan petani anggota kelompok tani dengan konsumen dan mitra secara langsung. Dapatkan hasil tani berkualitas dengan harga yang adil dan transparan.
          </p>

          <!-- Social Icons (Persis seperti Homepage) -->
          <div class="flex items-center gap-3 pt-2 text-slate-500">
            <a href="#" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-pawon-50 hover:text-pawon-700 flex items-center justify-center transition-colors" aria-label="Facebook">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.5 5H18V0h-3.808C10.597 0 9 1.583 9 4.615V8z"/></svg>
            </a>
            <a href="#" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-pawon-50 hover:text-pawon-700 flex items-center justify-center transition-colors" aria-label="Instagram">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
            </a>
            <a href="#" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-pawon-50 hover:text-pawon-700 flex items-center justify-center transition-colors" aria-label="YouTube">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
            </a>
            <a href="#" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-pawon-50 hover:text-pawon-700 flex items-center justify-center transition-colors" aria-label="LinkedIn">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
            </a>
          </div>
        </div>

        <!-- Links Column 1: Marketplace (2 cols) -->
        <div class="md:col-span-2 space-y-3">
          <h4 class="font-bold text-slate-900 text-sm tracking-wide">Marketplace</h4>
          <ul class="space-y-2 text-xs sm:text-sm">
            <li><a href="{{ route('marketplace.home') }}" class="hover:text-pawon-700 transition-colors">Beranda Customer</a></li>
            <li><a href="{{ route('marketplace.katalog') }}" class="hover:text-pawon-700 transition-colors">Katalog Hasil Tani</a></li>
            <li><a href="{{ route('marketplace.home') }}#tentang-pawontani" class="hover:text-pawon-700 transition-colors">Tentang PawonTani</a></li>
            <li><a href="{{ route('marketplace.katalog') }}" class="hover:text-pawon-700 transition-colors">Kategori Komoditas</a></li>
            <li><a href="{{ route('marketplace.katalog') }}" class="hover:text-pawon-700 transition-colors">Pemesanan WhatsApp</a></li>
          </ul>
        </div>

        <!-- Links Column 2: Ekosistem Poktan (2 cols) -->
        <div class="md:col-span-2 space-y-3">
          <h4 class="font-bold text-slate-900 text-sm tracking-wide">Ekosistem Poktan</h4>
          <ul class="space-y-2 text-xs sm:text-sm">
            <li><a href="{{ route('home') }}" class="hover:text-pawon-700 transition-colors">Portal Poktan</a></li>
            <li><a href="{{ route('home') }}#tentang" class="hover:text-pawon-700 transition-colors">Profil Kelompok</a></li>
            <li><a href="{{ route('home') }}#fitur" class="hover:text-pawon-700 transition-colors">Pencatatan Panen</a></li>
            <li><a href="{{ route('home') }}#edukasi" class="hover:text-pawon-700 transition-colors">Edukasi Pertanian</a></li>
            <li><a href="{{ route('login') }}" class="hover:text-pawon-700 transition-colors">Login Anggota</a></li>
          </ul>
        </div>

        <!-- Links Column 3: Bantuan & Kontak (3 cols) -->
        <div class="md:col-span-3 space-y-3">
          <h4 class="font-bold text-slate-900 text-sm tracking-wide">Bantuan &amp; Layanan</h4>
          <p class="text-xs text-slate-500 leading-relaxed">
            Mendukung digitalisasi kelompok tani binaan di wilayah Jawa Timur (Sumbersari, Patrang, Kaliwates, Ajung, Kalisat) serta kemitraan pangan nasional.
          </p>
          <div class="text-xs space-y-1.5 text-slate-500 pt-1">
            <p>Kantor: <span class="font-medium text-slate-700">Gedung Agro Inovasi Nusantara, Jakarta</span></p>
            <p>Email: <a href="mailto:halo@pawontani.id" class="text-pawon-700 font-semibold hover:underline">halo@pawontani.id</a></p>
            <p>Pemesanan: <span class="font-semibold text-slate-700">Langsung ke Petani via WhatsApp</span></p>
          </div>
        </div>

      </div>

      <!-- Copyright Bar (Persis seperti Homepage) -->
      <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
        <p>&copy; 2026 PawonTani Indonesia. Seluruh hak cipta dilindungi.</p>
        <div class="flex items-center gap-6">
          <a href="{{ route('home') }}" class="hover:text-pawon-700 transition-colors">Sistem Informasi Poktan</a>
          <a href="{{ route('marketplace.home') }}#tentang-pawontani" class="hover:text-pawon-700 transition-colors">Ketentuan Pemesanan</a>
          <a href="{{ route('login') }}" class="hover:text-pawon-700 transition-colors">Portal Anggota</a>
        </div>
      </div>

    </div>
  </footer>

  <!-- Toast Notification Container for Copy Link & Feedback -->
  <div id="toast-container" class="fixed bottom-6 right-6 z-50 pointer-events-none flex flex-col gap-2"></div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Toggle Mobile Menu
      const btn = document.getElementById('customer-mobile-menu-btn');
      const menu = document.getElementById('customer-mobile-menu');
      if (btn && menu) {
        btn.addEventListener('click', function () {
          menu.classList.toggle('hidden');
        });
      }

      // Lucide icon initializer if needed
      if (window.lucide) {
        window.lucide.createIcons();
      }
    });

    // Helper Toast
    function showToast(message, type = 'success') {
      const container = document.getElementById('toast-container');
      if (!container) return;

      const toast = document.createElement('div');
      const isSuccess = type === 'success';
      toast.className = `px-5 py-3.5 rounded-2xl shadow-xl border text-sm font-semibold flex items-center gap-3 transition-all transform duration-300 translate-y-4 opacity-0 pointer-events-auto ${
        isSuccess ? 'bg-[#1A2D10] text-white border-pawon-700' : 'bg-red-900 text-white border-red-700'
      }`;
      toast.innerHTML = `
        <svg class="w-5 h-5 ${isSuccess ? 'text-pawon-600' : 'text-red-400'}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          ${isSuccess ? '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />' : '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />'}
        </svg>
        <span>${message}</span>
      `;
      container.appendChild(toast);

      requestAnimationFrame(() => {
        toast.classList.remove('translate-y-4', 'opacity-0');
      });

      setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
      }, 3500);
    }
  </script>

  @yield('scripts')
</body>

</html>
