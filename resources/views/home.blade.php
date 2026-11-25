<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">
  <title>PawonTani - Digitalisasi Kelompok Tani: Lahan, Panen Terverifikasi, Marketplace</title>
  <meta name="description"
    content="PawonTani menyatukan aplikasi mobile anggota, dashboard web pengurus dan PPL, serta marketplace hasil bumi. Catat lahan dan aktivitas tanam, verifikasi panen dengan bukti timbangan, lalu jual langsung ke pembeli.">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
    rel="stylesheet">

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest" defer></script>

  <!-- Custom Stylesheet -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=2.0">
</head>

<body class="antialiased selection:bg-pawon-100 selection:text-pawon-900">

  <!-- ==================== 1. TOP NAVIGATION ==================== -->
  <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100 transition-all duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-18 md:h-20">

        <!-- Brand Logo -->
        <a href="#" class="flex items-center group py-1">
          <img src="{{ asset('images/Logo2.png') }}" alt="PawonTani Logo"
            class="h-9 sm:h-11 md:h-12 w-auto object-contain hover:opacity-95 transition-opacity">
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center gap-6 lg:gap-8 text-[15px]">
          <a href="#beranda" class="nav-link active">Beranda</a>
          <a href="#tentang" class="nav-link">Tentang</a>
          <a href="#fitur" class="nav-link">Fitur</a>
          <a href="#cara-kerja" class="nav-link">Cara Kerja</a>
          <a href="#edukasi" class="nav-link">Edukasi</a>
        </nav>

        <!-- CTA Buttons Desktop -->
        <div class="hidden md:flex items-center gap-3 shrink-0">
          <a href="{{ route('marketplace.home') }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-pawon-800 bg-pawon-50 hover:bg-pawon-100 border border-[#C5DFB0] rounded-full transition-all hover:translate-y-[-1px]">
            <svg class="w-4 h-4 text-pawon-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span>Marketplace</span>
          </a>
          <a href="{{ route('login') }}"
            class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow-sm hover:shadow-md hover:shadow-pawon-700/25 transition-all hover:translate-y-[-1px]">
            Masuk ke Sistem
          </a>
        </div>

        <!-- Mobile Menu Hamburger Button -->
        <div class="flex md:hidden">
          <button id="mobile-menu-btn" type="button"
            class="p-2 rounded-lg text-slate-700 hover:text-slate-900 hover:bg-slate-100 focus:outline-none"
            aria-label="Buka Menu Navigasi">
            <svg id="menu-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg id="menu-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div id="mobile-menu"
      class="hidden md:hidden border-b border-slate-100 bg-white px-4 pt-3 pb-6 space-y-2 shadow-xl">
      <a href="#beranda" class="mobile-nav-link active">Beranda</a>
      <a href="#tentang" class="mobile-nav-link">Tentang PawonTani</a>
      <a href="#fitur" class="mobile-nav-link">Fitur</a>
      <a href="#cara-kerja" class="mobile-nav-link">Cara Kerja</a>
      <a href="#edukasi" class="mobile-nav-link">Edukasi</a>
      <div class="pt-3 space-y-2 border-t border-slate-100">
        <a href="{{ route('marketplace.home') }}"
          class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold text-pawon-800 bg-pawon-50 hover:bg-pawon-100 border border-[#C5DFB0] rounded-full shadow-xs">
          <svg class="w-4 h-4 text-pawon-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
          </svg>
          <span>Kunjungi Marketplace</span>
        </a>
        <a href="{{ route('login') }}"
          class="w-full inline-flex items-center justify-center px-5 py-3 text-sm font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow">
          Masuk ke Sistem
        </a>
      </div>
    </div>
  </header>

  <main>
    <!-- ==================== 2. HERO SECTION ==================== -->
    <section id="beranda" class="relative pt-3 pb-16 md:pt-5 md:pb-20 overflow-hidden">
      <!-- Atmospheric decorative backdrop blurs -->
      <div
        class="absolute top-0 right-0 -z-10 w-[480px] h-[480px] bg-emerald-100/40 rounded-full blur-3xl pointer-events-none">
      </div>
      <div
        class="absolute top-1/3 left-0 -z-10 w-[420px] h-[420px] bg-sky-100/50 rounded-full blur-3xl pointer-events-none">
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

          <!-- Hero Left Column (7 Cols) -->
          <div class="lg:col-span-7 space-y-6 text-left">

            <!-- Top Tag Pill -->
            <div
              class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-semibold tracking-wide shadow-xs">
              <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
              Ekosistem Digital Kelompok Tani
            </div>

            <!-- Hero Main Headline -->
            <h1 class="text-4xl sm:text-5xl lg:text-[54px] font-extrabold text-slate-900 tracking-tight leading-[1.12]">
              Dari Olah Lahan Sampai Panen Terjual, <br class="hidden sm:inline">
              Semua Tercatat di <span class="text-pawon-600 inline-block">PawonTani</span>
            </h1>

            <!-- Subheading Description -->
            <p class="text-base sm:text-lg text-slate-600 max-w-2xl font-normal leading-relaxed">
              Satu ekosistem, dua sisi kerja: <strong class="text-slate-800 font-semibold">aplikasi mobile untuk anggota</strong>
              mencatat lahan, aktivitas tanam, dan hasil panen langsung dari sawah &mdash; sementara
              <strong class="text-slate-800 font-semibold">dashboard web untuk pengurus dan PPL</strong> memverifikasi data
              serta memantau statistik kelompok tani secara real-time.
            </p>

            <!-- Hero Action Buttons -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
              <a href="{{ route('login') }}"
                class="inline-flex items-center justify-center gap-2.5 px-7 py-3.5 text-base font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow-lg shadow-pawon-700/25 transition-all hover:translate-y-[-1px]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                <span>Masuk ke Sistem</span>
              </a>
              <a href="{{ route('marketplace.home') }}"
                class="inline-flex items-center justify-center gap-2.5 px-7 py-3.5 text-base font-bold text-pawon-900 bg-pawon-100 hover:bg-pawon-50 border border-pawon-600/40 rounded-full shadow-sm hover:shadow-md transition-all hover:translate-y-[-1px]">
                <svg class="w-5 h-5 text-pawon-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span>Belanja Hasil Bumi</span>
              </a>
              <a href="javascript:void(0)" onclick="openRegistrationModal()"
                class="inline-flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100/90 border border-slate-200/80 rounded-full transition-all">
                <span>Daftar Kelompok</span>
              </a>
            </div>

            <!-- Stats Bar Row -->
            <div class="pt-8 border-t border-slate-100 grid grid-cols-3 gap-4 sm:gap-8 max-w-lg">
              <div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">2 Sisi</div>
                <div class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Mobile Anggota &amp; Web Pengurus</div>
              </div>
              <div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">3 Tahap</div>
                <div class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Verifikasi Panen</div>
              </div>
              <div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">1 Pintu</div>
                <div class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Marketplace Hasil Bumi</div>
              </div>
            </div>

          </div>

          <!-- Hero Right Column: Dashboard Preview Card (5 Cols) -->
          <div class="lg:col-span-5 relative">

            <div
              class="relative mx-auto max-w-md bg-white/95 rounded-3xl p-6 shadow-2xl shadow-slate-200/90 border border-slate-100 backdrop-blur-sm">

              <!-- Card Header -->
              <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                      <path
                        d="M7 20h10M10 20c5.5-2.5.8-6.4 3-10M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4.1 5.5.8zM14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-sm font-bold text-slate-800 leading-none">Panen Siap Diverifikasi</h3>
                    <span class="text-[11px] text-slate-400">Gabah IR64 &bull; Poktan Makmur Jaya</span>
                  </div>
                </div>
                <span
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                  &#9679; Terverifikasi
                </span>
              </div>

              <!-- 3 Metric Boxes -->
              <div class="grid grid-cols-3 gap-2.5 mb-5">
                <div class="bg-slate-50/90 rounded-2xl p-3 text-center border border-slate-100">
                  <div class="text-[11px] font-medium text-slate-500 mb-1">Tonase</div>
                  <div class="text-lg font-bold text-slate-900">4,2 Ton</div>
                  <span class="text-[10px] text-emerald-600 font-semibold">Sesuai Catatan</span>
                </div>
                <div class="bg-slate-50/90 rounded-2xl p-3 text-center border border-slate-100">
                  <div class="text-[11px] font-medium text-slate-500 mb-1">Kualitas</div>
                  <div class="text-lg font-bold text-slate-900">Grade A</div>
                  <span class="text-[10px] text-slate-500 font-medium">Kadar air 14%</span>
                </div>
                <div class="bg-slate-50/90 rounded-2xl p-3 text-center border border-slate-100">
                  <div class="text-[11px] font-medium text-slate-500 mb-1">Foto Bukti</div>
                  <div class="text-lg font-bold text-pawon-700">3</div>
                  <span class="text-[10px] text-pawon-600 font-medium">Timbangan</span>
                </div>
              </div>

              <!-- Growth Chart Widget -->
              <div class="bg-slate-50/90 rounded-2xl p-4 border border-slate-100 mb-4">
                <div class="flex items-center justify-between mb-3">
                  <div class="text-xs font-bold text-slate-700">Produktivitas Panen 3 Musim</div>
                  <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-semibold">Musim
                    Ini</span>
                </div>

                <!-- SVG Sparkline Wave with gradient -->
                <div class="h-20 w-full flex items-end">
                  <svg class="w-full h-full overflow-visible" viewBox="0 0 280 70" fill="none"
                    preserveAspectRatio="none">
                    <defs>
                      <linearGradient id="growthGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#16a34a" stop-opacity="0.3" />
                        <stop offset="100%" stop-color="#16a34a" stop-opacity="0.0" />
                      </linearGradient>
                    </defs>
                    <path d="M0,55 Q40,30 80,42 T160,25 T240,15 T280,8 L280,70 L0,70 Z" fill="url(#growthGrad)" />
                    <path d="M0,55 Q40,30 80,42 T160,25 T240,15 T280,8" stroke="#16a34a" stroke-width="2.5"
                      stroke-linecap="round" />
                    <circle cx="280" cy="8" r="4" fill="#15803d" />
                  </svg>
                </div>

                <div class="flex items-center justify-between pt-2 text-[11px] text-slate-500 font-medium">
                  <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    +18,4% Peningkatan
                  </span>
                  <span>Target: 6,8 Ton/Ha</span>
                </div>
              </div>

              <!-- Transaction Summary Item -->
              <div
                class="bg-white rounded-2xl p-3.5 border border-slate-100 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl bg-pawon-100 text-pawon-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div>
                    <div class="text-xs font-semibold text-slate-800">Validasi Kuantitas Panen</div>
                    <div class="text-[11px] text-slate-400">Gabah IR64 &bull; 4.200 kg</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-xs font-bold text-slate-900">Disetujui Pengurus</div>
                  <span class="text-[10px] text-emerald-600 font-semibold">Siap terbit ke marketplace</span>
                </div>
              </div>

            </div>

            <!-- Floating Pill Badge -->
            <div
              class="hidden sm:flex absolute -bottom-4 -left-6 bg-white py-2.5 px-4 rounded-2xl shadow-xl border border-slate-100 items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <div class="text-xs">
                <p class="font-bold text-slate-800">Web &amp; Mobile Tersinkron</p>
                <p class="text-slate-400 text-[10px]">Catatan lapangan langsung masuk dashboard</p>
              </div>
            </div>

          </div>

        </div>
      </div>
    </section>

    <!-- ==================== 3. SECTION: TENTANG PAWONTANI ==================== -->
    <section id="tentang" class="py-10 bg-gradient-to-b from-skycard/60 via-softblue/40 to-white relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Text Centered -->
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
          <span class="badge-pill">Tentang Kami</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Kenapa PawonTani Hadir?
          </h2>
          <p class="text-base text-slate-600 leading-relaxed">
            Selama ini catatan tani berhenti di buku tulis, angka timbangan panen sulit dibuktikan, dan harga
            ditentukan pembeli perantara. PawonTani menyatukan ketiganya dalam satu sistem digital yang bisa
            dipakai petani, pengurus, dan pembeli.
          </p>
        </div>

        <!-- 3 Masalah Nyata & Solusinya -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

          <!-- Masalah 1: Pencatatan Manual -->
          <div
            class="feature-card bg-white rounded-2xl p-6 border border-slate-100 shadow-xs flex flex-col justify-between">
            <div>
              <div class="w-11 h-11 rounded-xl bg-sky-50 text-pawon-700 flex items-center justify-center mb-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                  <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                  <path d="M8 7h8M8 11h5" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">MASALAH 01</span>
              <h3 class="text-lg font-bold text-slate-900 mb-2">Catatan Masih di Buku Tani</h3>
              <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                Data lahan, pemupukan, dan panen ditulis tangan: mudah hilang, sulit direkap, dan tidak pernah
                sampai ke pengurus kelompok.
              </p>
            </div>
            <div class="mt-6 pt-3 flex items-center justify-between text-pawon-700 font-bold text-xs">
              <a href="#fitur"
                class="inline-flex items-center justify-between w-full hover:text-pawon-800 transition-colors">
                <span>Solusi: catatan digital tersinkron</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </div>

          <!-- Masalah 2: Timbangan Panen -->
          <div
            class="feature-card bg-white rounded-2xl p-6 border border-slate-100 shadow-xs flex flex-col justify-between">
            <div>
              <div class="w-11 h-11 rounded-xl bg-sky-50 text-pawon-700 flex items-center justify-center mb-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <circle cx="7" cy="17" r="3" />
                  <circle cx="17.5" cy="15.5" r="4.5" />
                  <path d="M10 17h3M5 14H3V9h7l3 3v2M14 9V5h3l3 4v3" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">MASALAH 02</span>
              <h3 class="text-lg font-bold text-slate-900 mb-2">Timbangan Panen Tanpa Bukti</h3>
              <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                Klaim hasil panen sulit dicocokkan dengan kondisi lapangan karena tidak ada foto bukti dan angka
                kuantitas yang tervalidasi pengurus.
              </p>
            </div>
            <div class="mt-6 pt-3 flex items-center justify-between text-pawon-700 font-bold text-xs">
              <a href="#cara-kerja"
                class="inline-flex items-center justify-between w-full hover:text-pawon-800 transition-colors">
                <span>Solusi: foto timbangan + verifikasi</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </div>

          <!-- Masalah 3: Rantai Tengkulak -->
          <div
            class="feature-card bg-white rounded-2xl p-6 border border-slate-100 shadow-xs flex flex-col justify-between">
            <div>
              <div class="w-11 h-11 rounded-xl bg-sky-50 text-pawon-700 flex items-center justify-center mb-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  <path d="M9 14l6 6M15 14l-6 6" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">MASALAH 03</span>
              <h3 class="text-lg font-bold text-slate-900 mb-2">Rantai Tengkulak Terlalu Panjang</h3>
              <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                Harga ditentukan pembeli perantara dan petani tidak punya kanal untuk menawarkan hasil panen
                langsung kepada konsumen atau mitra.
              </p>
            </div>
            <div class="mt-6 pt-3 flex items-center justify-between text-pawon-700 font-bold text-xs">
              <a href="{{ route('marketplace.home') }}"
                class="inline-flex items-center justify-between w-full hover:text-pawon-800 transition-colors">
                <span>Solusi: marketplace hasil bumi</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- ==================== 4. SECTION: EKOSISTEM DUA SISI ==================== -->
    <section id="keunggulan" class="py-10 bg-white relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Two Column Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16">
          <div class="space-y-3 max-w-xl">
            <span class="badge-pill">Ekosistem Dua Sisi</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
              Web untuk Pengurus, Mobile untuk Anggota
            </h2>
          </div>
          <div class="max-w-md">
            <p class="text-base text-slate-600 leading-relaxed">
              Satu basis data, dua antarmuka yang saling melengkapi. Anggota mencatat dari lapangan lewat
              aplikasi mobile, pengurus memverifikasi dan memantau lewat dashboard web.
            </p>
          </div>
        </div>

        <!-- 2 Kolom: Mobile & Web -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- Sisi 1: Aplikasi Mobile Anggota -->
          <div class="bg-softblue/60 hover:bg-softblue rounded-3xl p-8 border border-sky-100/80 transition-all">
            <div class="flex items-start justify-between mb-6">
              <div class="w-12 h-12 rounded-2xl bg-white text-pawon-700 shadow-xs flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <rect width="14" height="20" x="5" y="2" rx="2" ry="2" />
                  <path d="M12 18h.01" />
                </svg>
              </div>
              <span class="text-2xl font-extrabold text-slate-300">01</span>
            </div>
            <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">APLIKASI MOBILE</span>
            <h3 class="text-xl font-bold text-slate-900 mb-2.5">Anggota Petani di Lapangan</h3>
            <p class="text-sm sm:text-base text-slate-600 leading-relaxed mb-6">
              Dibawa ke sawah, dipakai harian. Semua catatan langsung masuk ke sistem kelompok tanpa perlu
              menyalin ulang di kertas.
            </p>

            <ul class="space-y-3 text-sm text-slate-700 border-t border-slate-200/70 pt-5">
              <li class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Catat lahan garapan: luas, komoditas, dan titik koordinat GPS</span>
              </li>
              <li class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Log aktivitas harian: pemupukan, penyemprotan, pengairan</span>
              </li>
              <li class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Catat hasil panen lengkap dengan foto bukti timbangan</span>
              </li>
              <li class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Baca artikel, panduan, dan tips dari penyuluh (PPL)</span>
              </li>
            </ul>
          </div>

          <!-- Sisi 2: Dashboard Web Pengurus -->
          <div class="bg-softblue/60 hover:bg-softblue rounded-3xl p-8 border border-sky-100/80 transition-all">
            <div class="flex items-start justify-between mb-6">
              <div class="w-12 h-12 rounded-2xl bg-white text-pawon-700 shadow-xs flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <rect width="20" height="14" x="2" y="3" rx="2" />
                  <path d="M8 21h8M12 17v4" />
                </svg>
              </div>
              <span class="text-2xl font-extrabold text-slate-300">02</span>
            </div>
            <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">DASHBOARD WEB</span>
            <h3 class="text-xl font-bold text-slate-900 mb-2.5">Pengurus &amp; Penyuluh (PPL)</h3>
            <p class="text-sm sm:text-base text-slate-600 leading-relaxed mb-6">
              Meja kerja kelompok tani: memeriksa data yang masuk dari lapangan, memberi catatan revisi, dan
              membaca kondisi kelompok dalam satu layar.
            </p>

            <ul class="space-y-3 text-sm text-slate-700 border-t border-slate-200/70 pt-5">
              <li class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-pawon-700 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Verifikasi lahan beserta nomor revisi dan catatan perbaikan</span>
              </li>
              <li class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-pawon-700 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Validasi kuantitas hasil panen terhadap bukti lapangan</span>
              </li>
              <li class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-pawon-700 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Monitoring statistik kelompok tani secara real-time</span>
              </li>
              <li class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-pawon-700 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Kelola anggota, konten edukasi, dan data penjualan</span>
              </li>
            </ul>
          </div>

        </div>

      </div>
    </section>

    <!-- ==================== 5. SECTION: 4 PILAR FITUR UTAMA ==================== -->
    <section id="fitur" class="py-10 bg-gradient-to-b from-skycard/50 to-softblue/80 relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Text Centered -->
        <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
          <span class="badge-pill">Fitur Utama</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Empat Pilar PawonTani
          </h2>
          <p class="text-base text-slate-600">
            Dari pencatatan lahan sampai hasil bumi terjual &mdash; setiap pilar sudah berjalan di sistem,
            bukan sekadar rencana.
          </p>
        </div>

        <!-- 4 Pilar Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch">

          <!-- Pilar 1: Lahan & Geo-lokasi -->
          <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
              <div class="w-11 h-11 rounded-xl bg-emerald-50 text-pawon-700 flex items-center justify-center mb-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M12 21s-7-5.686-7-11a7 7 0 1114 0c0 5.314-7 11-7 11z" />
                  <circle cx="12" cy="10" r="2.5" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">PILAR 01</span>
              <h3 class="text-xl font-bold text-slate-900 mb-2.5">Kelola Lahan &amp; Geo-lokasi</h3>
              <p class="text-sm text-slate-600 leading-relaxed mb-6">
                Anggota mendaftarkan lahan garapan lengkap dengan luas, komoditas, alamat, dan titik koordinat.
                Pengurus memverifikasi kelayakan data beserta nomor revisinya.
              </p>

              <ul class="space-y-3 text-sm text-slate-700 border-t border-slate-100 pt-5">
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Luas, komoditas, dan musim tanam per petak</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Titik koordinat dan foto lahan dari lokasi</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Status verifikasi, catatan revisi, dan riwayatnya</span>
                </li>
              </ul>
            </div>
            <div class="pt-6">
              <a href="#cara-kerja" class="text-xs font-bold text-pawon-700 hover:text-pawon-800 inline-flex items-center gap-1">
                <span>Lihat alur verifikasi lahan</span>
                <span aria-hidden="true">&rarr;</span>
              </a>
            </div>
          </div>

          <!-- Pilar 2: Aktivitas & Panen Terverifikasi -->
          <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
              <div class="w-11 h-11 rounded-xl bg-emerald-50 text-pawon-700 flex items-center justify-center mb-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M3 3v18h18" />
                  <path d="m19 9-5 5-4-4-3 3" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">PILAR 02</span>
              <h3 class="text-xl font-bold text-slate-900 mb-2.5">Monitoring Aktivitas &amp; Panen Terverifikasi</h3>
              <p class="text-sm text-slate-600 leading-relaxed mb-6">
                Setiap pemupukan dan penyemprotan tercatat per lahan. Hasil panen dilengkapi foto timbangan,
                lalu divalidasi pengurus sebelum boleh masuk penjualan.
              </p>

              <ul class="space-y-3 text-sm text-slate-700 border-t border-slate-100 pt-5">
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Log aktivitas tanam harian per petak lahan</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Foto bukti timbangan dan kualitas hasil panen</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Validasi kuantitas oleh pengurus sebelum diterbitkan</span>
                </li>
              </ul>
            </div>
            <div class="pt-6">
              <a href="#cara-kerja" class="text-xs font-bold text-pawon-700 hover:text-pawon-800 inline-flex items-center gap-1">
                <span>Lihat alur panen transparan</span>
                <span aria-hidden="true">&rarr;</span>
              </a>
            </div>
          </div>

          <!-- Pilar 3: Marketplace -->
          <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
              <div class="w-11 h-11 rounded-xl bg-emerald-50 text-pawon-700 flex items-center justify-center mb-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">PILAR 03</span>
              <h3 class="text-xl font-bold text-slate-900 mb-2.5">Marketplace Hasil Bumi Terbuka</h3>
              <p class="text-sm text-slate-600 leading-relaxed mb-6">
                Panen yang sudah terverifikasi tampil di marketplace publik dengan harga transparan, lengkap
                dengan profil petani dan kelompok tani asalnya.
              </p>

              <ul class="space-y-3 text-sm text-slate-700 border-t border-slate-100 pt-5">
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Katalog gabah, padi, jagung, cabai, dan komoditas lain</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Harga, satuan, dan stok tampil terbuka</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Terhubung langsung ke petani via WhatsApp</span>
                </li>
              </ul>
            </div>
            <div class="pt-6">
              <a href="{{ route('marketplace.home') }}" class="text-xs font-bold text-pawon-700 hover:text-pawon-800 inline-flex items-center gap-1">
                <span>Buka marketplace</span>
                <span aria-hidden="true">&rarr;</span>
              </a>
            </div>
          </div>

          <!-- Pilar 4: Edukasi & PPL -->
          <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
              <div class="w-11 h-11 rounded-xl bg-emerald-50 text-pawon-700 flex items-center justify-center mb-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                  <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                  <path d="M8 7h8M8 11h6" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">PILAR 04</span>
              <h3 class="text-xl font-bold text-slate-900 mb-2.5">Edukasi &amp; Bimbingan Penyuluh (PPL)</h3>
              <p class="text-sm text-slate-600 leading-relaxed mb-6">
                Artikel, panduan budidaya, dan tips lapangan dari penyuluh dapat dibaca di web maupun aplikasi
                mobile anggota &mdash; kapan saja, di mana saja.
              </p>

              <ul class="space-y-3 text-sm text-slate-700 border-t border-slate-100 pt-5">
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Artikel pertanian dan tips lapangan</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Panduan budidaya seperti Jajar Legowo dan polybag</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Materi dari penyuluh, tersaji untuk web &amp; mobile</span>
                </li>
              </ul>
            </div>
            <div class="pt-6">
              <a href="#edukasi" class="text-xs font-bold text-pawon-700 hover:text-pawon-800 inline-flex items-center gap-1">
                <span>Baca materi edukasi</span>
                <span aria-hidden="true">&rarr;</span>
              </a>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- ==================== 6. SECTION: CARA KERJA ==================== -->
    <section id="cara-kerja" class="py-14 md:py-20 bg-white relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Text Centered -->
        <div class="text-center max-w-2xl mx-auto mb-10 md:mb-12 space-y-3">
          <span class="badge-pill">Alur Kerja Sistem</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Tiga Langkah, Panen Terverifikasi
          </h2>
          <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
            Dari catatan petani di lapangan sampai pembeli menerima hasil bumi &mdash; setiap langkah punya
            bukti dan pihak yang memverifikasi.
          </p>
        </div>

        <!-- 3 Structured Process Cards (Grid 3 Columns on Desktop with Flow Arrows) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">

          <!-- Langkah 01: Petani Mencatat -->
          <div
            class="group relative bg-[#f8fafc] hover:bg-white transition-all duration-300 rounded-3xl p-6 border border-slate-200/80 hover:border-pawon-600/50 shadow-xs hover:shadow-xl hover:shadow-pawon-700/5 hover:-translate-y-1 flex flex-col justify-between">
            <div class="space-y-4">
              <!-- Top Row: Badge Step & Icon -->
              <div class="flex items-center justify-between">
                <span
                  class="w-12 h-12 rounded-2xl bg-[#0a5c2b] text-white font-extrabold text-base flex items-center justify-center shadow-md shadow-[#0a5c2b]/20 group-hover:scale-105 transition-transform">
                  01
                </span>
                <div class="w-10 h-10 rounded-xl bg-pawon-100/70 text-pawon-700 flex items-center justify-center">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <rect width="14" height="20" x="5" y="2" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M12 18h.01" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </div>
              </div>

              <!-- Content -->
              <div>
                <h3 class="font-extrabold text-lg text-slate-900 group-hover:text-pawon-800 transition-colors">
                  Petani Mencatat via Mobile
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                  Anggota mencatat lahan, aktivitas tanam harian, dan hasil panen lengkap dengan foto timbangan langsung dari lapangan.
                </p>
              </div>
            </div>

            <!-- Bottom Tag -->
            <div class="pt-5 mt-4 border-t border-slate-100 flex items-center gap-1.5 text-xs font-semibold text-pawon-700">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              Aplikasi Mobile Petani
            </div>
          </div>

          <!-- Langkah 02: Pengurus Validasi -->
          <div
            class="group relative bg-[#f8fafc] hover:bg-white transition-all duration-300 rounded-3xl p-6 border border-slate-200/80 hover:border-pawon-600/50 shadow-xs hover:shadow-xl hover:shadow-pawon-700/5 hover:-translate-y-1 flex flex-col justify-between">
            <div class="space-y-4">
              <!-- Top Row: Badge Step & Icon -->
              <div class="flex items-center justify-between">
                <span
                  class="w-12 h-12 rounded-2xl bg-[#0a5c2b] text-white font-extrabold text-base flex items-center justify-center shadow-md shadow-[#0a5c2b]/20 group-hover:scale-105 transition-transform">
                  02
                </span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100/70 text-emerald-700 flex items-center justify-center">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                </div>
              </div>

              <!-- Content -->
              <div>
                <h3 class="font-extrabold text-lg text-slate-900 group-hover:text-pawon-800 transition-colors">
                  Pengurus Validasi via Web
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                  Pengurus atau PPL memverifikasi data lahan dan kuantitas panen, memeriksa bukti foto, lalu memberikan approval resmi.
                </p>
              </div>
            </div>

            <!-- Bottom Tag -->
            <div class="pt-5 mt-4 border-t border-slate-100 flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              Dashboard Web Pengurus
            </div>
          </div>

          <!-- Langkah 03: Publik Membeli -->
          <div
            class="group relative bg-[#f8fafc] hover:bg-white transition-all duration-300 rounded-3xl p-6 border border-slate-200/80 hover:border-pawon-600/50 shadow-xs hover:shadow-xl hover:shadow-pawon-700/5 hover:-translate-y-1 flex flex-col justify-between">
            <div class="space-y-4">
              <!-- Top Row: Badge Step & Icon -->
              <div class="flex items-center justify-between">
                <span
                  class="w-12 h-12 rounded-2xl bg-[#0a5c2b] text-white font-extrabold text-base flex items-center justify-center shadow-md shadow-[#0a5c2b]/20 group-hover:scale-105 transition-transform">
                  03
                </span>
                <div class="w-10 h-10 rounded-xl bg-amber-100/70 text-amber-700 flex items-center justify-center">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                </div>
              </div>

              <!-- Content -->
              <div>
                <h3 class="font-extrabold text-lg text-slate-900 group-hover:text-pawon-800 transition-colors">
                  Publik Membeli di Marketplace
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                  Panen yang lolos verifikasi langsung terbit di katalog publik. Pembeli dapat memesan tanpa perantara panjang.
                </p>
              </div>
            </div>

            <!-- Bottom Tag -->
            <div class="pt-5 mt-4 border-t border-slate-100 flex items-center gap-1.5 text-xs font-semibold text-amber-700">
              <span class="w-2 h-2 rounded-full bg-amber-500"></span>
              Marketplace Publik
            </div>
          </div>

        </div>

        <!-- Sub-blok: Alur Panen Transparan -->
        <div class="mt-10 rounded-2xl border border-emerald-100 bg-emerald-50/50 p-5 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-5">
            <h3 class="text-sm sm:text-base font-bold text-slate-900">Alur Panen Transparan &amp; Terverifikasi</h3>
            <span class="text-[11px] sm:text-xs font-semibold text-pawon-700">Setiap tahap terekam di sistem</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-slate-100 p-4">
              <div class="text-[11px] font-extrabold text-pawon-700 mb-1">TAHAP 1</div>
              <p class="text-xs sm:text-sm font-semibold text-slate-800 leading-snug">Petani mencatat panen di lapangan</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-100 p-4">
              <div class="text-[11px] font-extrabold text-pawon-700 mb-1">TAHAP 2</div>
              <p class="text-xs sm:text-sm font-semibold text-slate-800 leading-snug">Mandor / pengurus poktan memverifikasi</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-100 p-4">
              <div class="text-[11px] font-extrabold text-pawon-700 mb-1">TAHAP 3</div>
              <p class="text-xs sm:text-sm font-semibold text-slate-800 leading-snug">Bukti foto &amp; timbangan terekam jelas</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-100 p-4">
              <div class="text-[11px] font-extrabold text-pawon-700 mb-1">TAHAP 4</div>
              <p class="text-xs sm:text-sm font-semibold text-slate-800 leading-snug">Siap terbit ke penjualan &amp; dokumen PDF</p>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- ==================== 7. SECTION: TARGET PENGGUNA ==================== -->
    <section id="pengguna" class="py-12 md:py-16 bg-gradient-to-b from-skycard/50 via-softblue/30 to-white relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Text Centered -->
        <div class="text-center max-w-3xl mx-auto mb-14 space-y-3">
          <span class="badge-pill">Siapa yang Memakai</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Tiga Peran, Satu Ekosistem
          </h2>
          <p class="text-base text-slate-600 leading-relaxed">
            Pengurus mengelola lewat web, anggota mencatat lewat mobile, dan konsumen membeli lewat marketplace.
          </p>
        </div>

        <!-- 3 Peran Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">

          <!-- Peran 1: Pengurus & PPL -->
          <div
            class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
            <div>
              <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-pawon-700 flex items-center justify-center mb-6">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                  <circle cx="9" cy="7" r="4" />
                  <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                  <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">DASHBOARD WEB</span>
              <h3 class="text-xl font-bold text-slate-900 mb-2">Pengurus &amp; PPL</h3>
              <p class="text-sm text-slate-600 leading-relaxed mb-6">
                Mengelola keanggotaan, memverifikasi data lapangan, dan memantau statistik kelompok dalam satu
                dashboard.
              </p>

              <ul class="space-y-2.5 text-xs sm:text-sm text-slate-600 border-t border-slate-100 pt-5">
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Verifikasi lahan &amp; nomor revisi</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Validasi kuantitas hasil panen</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Monitoring statistik kelompok real-time</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Kelola anggota &amp; konten edukasi</span>
                </li>
              </ul>
            </div>
            <div class="pt-6">
              <a href="{{ route('login') }}"
                class="text-xs font-bold text-pawon-700 hover:text-pawon-800 inline-flex items-center gap-1">
                <span>Masuk sebagai pengurus</span>
                <span aria-hidden="true">&rarr;</span>
              </a>
            </div>
          </div>

          <!-- Peran 2: Petani / Anggota (Highlighted) -->
          <div
            class="bg-white rounded-3xl p-8 border-2 border-pawon-600 shadow-xl shadow-pawon-700/10 flex flex-col justify-between relative">
            <div class="absolute -top-3 left-8">
              <span
                class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full text-[11px] font-bold uppercase bg-pawon-700 text-white shadow-sm">
                FOKUS UTAMA
              </span>
            </div>

            <div>
              <div class="w-12 h-12 rounded-2xl bg-pawon-700 text-white flex items-center justify-center mb-6 mt-1 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <rect width="14" height="20" x="5" y="2" rx="2" ry="2" />
                  <path d="M12 18h.01" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">APLIKASI MOBILE</span>
              <h3 class="text-xl font-bold text-slate-900 mb-2">Petani / Anggota</h3>
              <p class="text-sm text-slate-600 leading-relaxed mb-6">
                Mencatat seluruh kegiatan tani dari sawah: lahan garapan, perawatan harian, sampai panen dengan
                bukti timbangan.
              </p>

              <ul class="space-y-2.5 text-xs sm:text-sm text-slate-600 border-t border-slate-100 pt-5">
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span class="font-medium text-slate-900">Catat lahan, luas, &amp; koordinat GPS</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Log aktivitas tanam harian</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Catat panen + foto bukti timbangan</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Baca edukasi &amp; tips dari penyuluh</span>
                </li>
              </ul>
            </div>
            <div class="pt-6">
              <a href="#fitur"
                class="text-xs font-bold text-pawon-700 hover:text-pawon-800 inline-flex items-center gap-1">
                <span>Lihat fitur anggota</span>
                <span aria-hidden="true">&rarr;</span>
              </a>
            </div>
          </div>

          <!-- Peran 3: Konsumen & Mitra -->
          <div
            class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
            <div>
              <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-pawon-700 flex items-center justify-center mb-6">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">MARKETPLACE PUBLIK</span>
              <h3 class="text-xl font-bold text-slate-900 mb-2">Konsumen &amp; Mitra</h3>
              <p class="text-sm text-slate-600 leading-relaxed mb-6">
                Melihat hasil panen riil petani binaan, lengkap dengan asal kelompok tani dan harga yang
                transparan.
              </p>

              <ul class="space-y-2.5 text-xs sm:text-sm text-slate-600 border-t border-slate-100 pt-5">
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Katalog komoditas langsung dari petani</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Harga, satuan, dan stok terbuka</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Hubungi petani via WhatsApp</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Tanpa perantara panjang</span>
                </li>
              </ul>
            </div>
            <div class="pt-6">
              <a href="{{ route('marketplace.home') }}"
                class="text-xs font-bold text-pawon-700 hover:text-pawon-800 inline-flex items-center gap-1">
                <span>Jelajahi marketplace</span>
                <span aria-hidden="true">&rarr;</span>
              </a>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- ==================== 8. SECTION: EDUKASI PERTANIAN ==================== -->
    <section id="edukasi" class="py-10 bg-white relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Edukasi: disamakan dengan gaya heading section Target Pengguna -->
        <div class="text-center max-w-3xl mx-auto mb-14 space-y-3">
          <span class="badge-pill">Pusat Edukasi</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Edukasi &amp; Bimbingan Penyuluh
          </h2>
          <p class="text-base text-slate-600 leading-relaxed">
            Artikel, panduan budidaya seperti Jajar Legowo dan polybag, serta tips lapangan langsung dari
            penyuluh (PPL). Materi yang sama bisa dibaca anggota lewat aplikasi mobile.
          </p>
        </div>

        <!-- 3 Blog/Article Cards (Tips, Panduan, Artikel) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

          <!-- Card 1: Tips -->
          @php
            $tipsImage = $tipsTerbaru?->gambar;
            $tipsImageUrl = $tipsImage
              ? (\Illuminate\Support\Str::startsWith($tipsImage, ['http://', 'https://'])
                  ? $tipsImage
                  : (\Illuminate\Support\Str::startsWith($tipsImage, ['storage/', 'images/', 'uploads/'])
                      ? asset($tipsImage)
                      : asset('storage/' . $tipsImage)))
              : 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&q=80';
            $tipsTitle = $tipsTerbaru?->judul ?? 'Tips Pengelolaan Irigasi dan Air untuk Lahan Kelompok Tani';
            $tipsSummary = $tipsTerbaru?->ringkasan ?? 'Pelajari teknik efisiensi air mandiri yang praktis untuk menjaga tanaman anggota kelompok tani tetap subur di musim kemarau.';
            $tipsUrl = $tipsTerbaru
              ? route('tips.show', $tipsTerbaru->id_tips)
              : route('tips.show', 'contoh-irigasi-air-kelompok-tani');
          @endphp
          <article
            class="h-full bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-xs hover:shadow-lg transition-all group flex flex-col justify-between">
            <div>
              <div class="relative h-48 overflow-hidden bg-slate-100">
                <img src="{{ $tipsImageUrl }}"
                  alt="{{ $tipsTitle }}"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  loading="lazy">
                <div class="absolute top-3.5 left-3.5">
                  <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white/95 backdrop-blur text-emerald-800 shadow-xs">
                    Tips
                  </span>
                </div>
              </div>
              <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900 group-hover:text-pawon-700 transition-colors mb-2.5 leading-snug">
                  {{ $tipsTitle }}
                </h3>
                <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed mb-4">
                  {{ $tipsSummary }}
                </p>
              </div>
            </div>
            <div class="mt-auto px-6 pb-6 pt-2">
              <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                <a href="{{ $tipsUrl }}"
                  class="inline-flex items-center gap-1.5 text-xs font-bold text-pawon-700 hover:text-pawon-800">
                  <span>Baca Selengkapnya</span>
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                  </svg>
                </a>
                <a href="{{ route('edukasi.tips') }}" class="inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50/70 px-3 py-2 text-[11px] font-extrabold text-pawon-700 transition-all hover:border-emerald-300 hover:bg-emerald-100 hover:text-pawon-800">
                  Lihat semua tips <span aria-hidden="true">→</span>
                </a>
              </div>
            </div>
          </article>

          <!-- Card 2: Panduan -->
          @if($panduanTerbaru)
            @php
              $panduanImage = $panduanTerbaru->gambar;
              $panduanImageUrl = $panduanImage
                ? (\Illuminate\Support\Str::startsWith($panduanImage, ['http://', 'https://'])
                    ? $panduanImage
                    : (\Illuminate\Support\Str::startsWith($panduanImage, ['storage/', 'images/', 'uploads/'])
                        ? asset($panduanImage)
                        : asset('storage/' . $panduanImage)))
                : 'https://images.unsplash.com/photo-1592982537447-7440770cbfc9?auto=format&fit=crop&w=800&q=80';
            @endphp
            <article
              class="h-full bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-xs hover:shadow-lg transition-all group flex flex-col justify-between">
              <div>
                <div class="relative h-48 overflow-hidden bg-slate-100">
                  <img src="{{ $panduanImageUrl }}"
                    alt="{{ $panduanTerbaru->judul }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    loading="lazy">
                  <div class="absolute top-3.5 left-3.5">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white/95 backdrop-blur text-emerald-800 shadow-xs">
                      Panduan
                    </span>
                  </div>
                </div>
                <div class="p-6">
                  <h3 class="text-lg font-bold text-slate-900 group-hover:text-pawon-700 transition-colors mb-2.5 leading-snug">
                    {{ $panduanTerbaru->judul }}
                  </h3>
                  <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed mb-4">
                    {{ $panduanTerbaru->ringkasan }}
                  </p>
                </div>
              </div>
              <div class="mt-auto px-6 pb-6 pt-2">
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                  <a href="{{ route('panduan.show', $panduanTerbaru->slug) }}"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-pawon-700 hover:text-pawon-800">
                    <span>Baca Selengkapnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                  </a>
                  <a href="{{ route('edukasi.panduan') }}" class="inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50/70 px-3 py-2 text-[11px] font-extrabold text-pawon-700 transition-all hover:border-emerald-300 hover:bg-emerald-100 hover:text-pawon-800">
                    Lihat semua panduan <span aria-hidden="true">→</span>
                  </a>
                </div>
              </div>
            </article>
          @else
            <article class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-xs flex flex-col items-center justify-center text-center p-6 text-slate-400">
              <p class="text-sm">Belum ada panduan yang dipublikasikan.</p>
              <a href="{{ route('edukasi.panduan') }}" class="mt-4 text-xs font-bold text-pawon-700 hover:text-pawon-800">
                Lihat daftar panduan
              </a>
            </article>
          @endif

          <!-- Card 3: Artikel -->
          @php
            $artikelImage = $artikelTerbaru?->gambar;
            $artikelImageUrl = $artikelImage
              ? (\Illuminate\Support\Str::startsWith($artikelImage, ['http://', 'https://'])
                  ? $artikelImage
                  : (\Illuminate\Support\Str::startsWith($artikelImage, ['storage/', 'images/', 'uploads/'])
                      ? asset($artikelImage)
                      : asset('storage/' . $artikelImage)))
              : 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=800&q=80';
            $artikelTitle = $artikelTerbaru?->judul ?? 'Artikel: Pemantauan Rutin dan Deteksi Dini Kondisi Tanaman di Lapangan';
            $artikelSummary = $artikelTerbaru?->ringkasan ?? 'Pentingnya mencatat aktivitas dan memantau kondisi tanaman secara berkala agar pengurus dan anggota kelompok dapat mengambil tindakan tepat.';
            $artikelUrl = $artikelTerbaru
              ? route('artikel.show', $artikelTerbaru->id_artikel)
              : route('artikel.show', 'contoh-pemantauan-rutin-tanaman');
          @endphp
          <article
            class="h-full bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-xs hover:shadow-lg transition-all group flex flex-col justify-between">
            <div>
              <div class="relative h-48 overflow-hidden bg-slate-100">
                <img src="{{ $artikelImageUrl }}"
                  alt="{{ $artikelTitle }}"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  loading="lazy">
                <div class="absolute top-3.5 left-3.5">
                  <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white/95 backdrop-blur text-emerald-800 shadow-xs">
                    Artikel
                  </span>
                </div>
              </div>
              <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900 group-hover:text-pawon-700 transition-colors mb-2.5 leading-snug">
                  {{ $artikelTitle }}
                </h3>
                <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed mb-4">
                  {{ $artikelSummary }}
                </p>
              </div>
            </div>
            <div class="mt-auto px-6 pb-6 pt-2">
              <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                <a href="{{ $artikelUrl }}"
                  class="inline-flex items-center gap-1.5 text-xs font-bold text-pawon-700 hover:text-pawon-800">
                  <span>Baca Selengkapnya</span>
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                  </svg>
                </a>
                <a href="{{ route('edukasi.artikel') }}" class="inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50/70 px-3 py-2 text-[11px] font-extrabold text-pawon-700 transition-all hover:border-emerald-300 hover:bg-emerald-100 hover:text-pawon-800">
                  Lihat semua artikel <span aria-hidden="true">→</span>
                </a>
              </div>
            </div>
          </article>
        </div>

      </div>
    </section>

    <!-- ==================== 8b. SECTION: TEASER MARKETPLACE ==================== -->
    <section id="marketplace-teaser" class="py-10 bg-gradient-to-b from-white to-softblue/50 relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
          <div class="space-y-3 max-w-2xl">
            <span class="badge-pill">Marketplace Hasil Bumi</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
              Panen Petani Binaan, Langsung ke Pembeli
            </h2>
            <p class="text-base text-slate-600 leading-relaxed">
              Produk di bawah berasal dari panen yang sudah dicatat di aplikasi mobile dan diverifikasi
              pengurus kelompok tani. Harga terbuka, asal jelas, tanpa perantara panjang.
            </p>
          </div>
          <div class="shrink-0">
            <a href="{{ route('marketplace.home') }}"
              class="inline-flex items-center justify-center gap-2.5 px-7 py-3.5 text-base font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow-lg shadow-pawon-700/25 transition-all hover:translate-y-[-1px]">
              <span>Jelajahi Marketplace</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </a>
          </div>
        </div>

        @php
          $produkTeaser = $produkUnggulan ?? collect();
          $jumlahTeaser = $produkTeaser->count();
          // Kartu ajakan mengisi sisa baris agar grid tetap rapi walau produk masih sedikit.
          // Kelas ditulis literal agar tetap terdeteksi oleh Tailwind.
          $kelasAjakan = match (true) {
              $jumlahTeaser === 1 => 'lg:col-span-3',
              $jumlahTeaser === 2 => 'lg:col-span-2',
              default => '',
          };
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

          @forelse($produkTeaser as $produk)
            <a href="{{ route('marketplace.detail', $produk->id_produk) }}"
              class="group bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-xs hover:shadow-lg transition-all flex flex-col">
              <div class="relative h-40 overflow-hidden bg-[#EBF6E0]">
                <div class="absolute inset-0 flex items-center justify-center text-4xl">🌾</div>
                @if($produk->foto_produk)
                  <img src="{{ $produk->foto_produk }}" alt="{{ $produk->nama_produk }}"
                    class="relative w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    loading="lazy"
                    onerror="this.onerror=null;this.style.display='none';">
                @endif
                @if($produk->komoditas)
                  <div class="absolute top-3 left-3">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-white/95 backdrop-blur text-emerald-800 shadow-xs">
                      {{ $produk->komoditas->nama_komoditas }}
                    </span>
                  </div>
                @endif
              </div>
              <div class="p-5 flex flex-col justify-between grow">
                <div>
                  <h3 class="text-sm font-bold text-slate-900 group-hover:text-pawon-700 transition-colors leading-snug mb-1">
                    {{ $produk->nama_produk }}
                  </h3>
                  <p class="text-[11px] text-slate-500">
                    {{ $produk->pengguna->kelompokTani->nama_kelompok ?? ($produk->pengguna->nama ?? 'Petani PawonTani') }}
                  </p>
                </div>
                <div class="mt-4 flex items-end justify-between">
                  <div>
                    <div class="text-sm font-extrabold text-pawon-700">{{ $produk->harga_formatted }}</div>
                    <div class="text-[11px] text-slate-400">per {{ $produk->satuan ?? 'kg' }}</div>
                  </div>
                  <span class="text-[11px] font-bold text-pawon-700 inline-flex items-center gap-1">
                    Lihat
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                  </span>
                </div>
              </div>
            </a>
          @empty
            <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-6 flex flex-col items-center justify-center text-center min-h-[240px]">
              <div class="text-4xl mb-3">🌾</div>
              <p class="text-sm font-semibold text-slate-700">Produk sedang disiapkan</p>
              <p class="text-xs text-slate-500 mt-1">Panen petani binaan tampil di sini setelah diverifikasi pengurus.</p>
            </div>
            <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-6 flex flex-col items-center justify-center text-center min-h-[240px]">
              <div class="text-4xl mb-3">🌽</div>
              <p class="text-sm font-semibold text-slate-700">Komoditas menyusul</p>
              <p class="text-xs text-slate-500 mt-1">Gabah, jagung, cabai, dan hasil bumi lain dari kelompok tani binaan.</p>
            </div>
            <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-6 flex flex-col items-center justify-center text-center min-h-[240px]">
              <div class="text-4xl mb-3">🌶️</div>
              <p class="text-sm font-semibold text-slate-700">Harga transparan</p>
              <p class="text-xs text-slate-500 mt-1">Harga, satuan, dan stok ditampilkan terbuka untuk pembeli.</p>
            </div>
          @endforelse

          <!-- Kartu ajakan mengisi sisa kolom -->
          <a href="{{ route('marketplace.home') }}"
            class="{{ $kelasAjakan }} group rounded-2xl p-6 sm:p-8 bg-gradient-to-br from-[#0c6b32] to-[#074b21] text-white shadow-xl shadow-pawon-900/15 flex flex-col justify-between min-h-[240px]">
            <div>
              <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur text-white flex items-center justify-center mb-4 border border-white/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
              <h3 class="text-lg font-bold leading-snug mb-2">Belanja Langsung dari Petani</h3>
              <p class="text-xs sm:text-sm text-emerald-50/90 leading-relaxed max-w-md">
                Lihat seluruh komoditas yang tersedia, lengkap dengan asal kelompok tani dan kontak petani.
                Tanpa perantara panjang, harga lebih masuk akal untuk kedua pihak.
              </p>
            </div>
            <div class="pt-6 flex items-center justify-between border-t border-white/10 mt-6">
              <span class="text-xs font-semibold text-emerald-100">Buka katalog marketplace</span>
              <span class="inline-flex items-center gap-1.5 text-xs font-bold text-white">
                Mulai
                <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </span>
            </div>
          </a>

        </div>

      </div>
    </section>

    <!-- ==================== 9. SECTION: CTA BANNER ==================== -->
    <section id="kontak" class="py-16 bg-gradient-to-b from-white to-softblue/60 relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div
          class="relative rounded-3xl bg-gradient-to-b from-skycard via-softblue to-white border border-sky-200/70 p-8 sm:p-14 text-center overflow-hidden shadow-xl shadow-sky-900/5">
          <!-- Atmospheric decorative blurs -->
          <div class="absolute -top-20 -left-20 w-64 h-64 bg-emerald-200/40 rounded-full blur-3xl pointer-events-none">
          </div>
          <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-blue-200/40 rounded-full blur-3xl pointer-events-none">
          </div>

          <div class="relative z-10 max-w-3xl mx-auto space-y-6">

            <div
              class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-100/80 text-pawon-800 text-xs font-bold uppercase tracking-wider">
              MULAI DARI SATU KELOMPOK TANI
            </div>

            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
              Saatnya Data Petani <br class="hidden sm:inline">
              Bisa Dibuktikan, Bukan Hanya Diceritakan
            </h2>

            <p class="text-base sm:text-lg text-slate-600 max-w-xl mx-auto leading-relaxed">
              Daftarkan kelompok tani Anda, ajak anggota mencatat lewat aplikasi mobile, dan buka hasil panen
              ke marketplace publik &mdash; semuanya dalam satu sistem.
            </p>

            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
              <a href="javascript:void(0)" onclick="openRegistrationModal()"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 text-base font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow-lg shadow-pawon-700/25 transition-all hover:scale-105">
                <span>Daftarkan Kelompok Tani</span>
                <span aria-hidden="true">&rarr;</span>
              </a>
              <a href="{{ route('marketplace.home') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-bold text-emerald-900 bg-white hover:bg-slate-50 border border-slate-200 rounded-full transition-all">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span>Lihat Hasil Bumi</span>
              </a>
            </div>

            <!-- Trust Badges -->
            <div
              class="pt-6 flex flex-wrap items-center justify-center gap-6 text-xs sm:text-sm text-slate-500 font-medium">
              <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-pawon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  stroke-width="3">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                Mobile untuk anggota, web untuk pengurus
              </span>
              <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-pawon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  stroke-width="3">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                Panen tervalidasi &amp; berbukti foto
              </span>
              <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-pawon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  stroke-width="3">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                Terhubung ke marketplace publik
              </span>
            </div>

          </div>

        </div>

      </div>
    </section>
  </main>

  <!-- ==================== 10. FOOTER ==================== -->
  <footer class="bg-white border-t border-slate-200/80 pt-16 pb-12 text-slate-600 text-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-slate-100">

        <!-- Brand Summary Column (5 cols) -->
        <div class="md:col-span-5 space-y-4">
          <a href="#" class="inline-block group">
            <img src="{{ asset('images/Logo2.png') }}" alt="PawonTani Logo"
              class="h-10 sm:h-12 w-auto object-contain hover:opacity-95 transition-opacity">
          </a>
          <p class="text-slate-500 max-w-sm text-sm leading-relaxed">
            Ekosistem digital kelompok tani: aplikasi mobile untuk anggota, dashboard web untuk pengurus dan
            PPL, serta marketplace hasil bumi yang terbuka untuk konsumen dan mitra.
          </p>

          <!-- Social Icons -->
          <div class="flex items-center gap-3 pt-2 text-slate-500">
            <a href="#"
              class="w-9 h-9 rounded-full bg-slate-100 hover:bg-pawon-50 hover:text-pawon-700 flex items-center justify-center transition-colors"
              aria-label="Facebook">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.5 5H18V0h-3.808C10.597 0 9 1.583 9 4.615V8z" />
              </svg>
            </a>
            <a href="#"
              class="w-9 h-9 rounded-full bg-slate-100 hover:bg-pawon-50 hover:text-pawon-700 flex items-center justify-center transition-colors"
              aria-label="Instagram">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
              </svg>
            </a>
            <a href="#"
              class="w-9 h-9 rounded-full bg-slate-100 hover:bg-pawon-50 hover:text-pawon-700 flex items-center justify-center transition-colors"
              aria-label="YouTube">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
              </svg>
            </a>
            <a href="#"
              class="w-9 h-9 rounded-full bg-slate-100 hover:bg-pawon-50 hover:text-pawon-700 flex items-center justify-center transition-colors"
              aria-label="LinkedIn">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
              </svg>
            </a>
          </div>
        </div>

        <!-- Links Column 1 (2-3 cols) -->
        <div class="md:col-span-2 space-y-3">
          <h4 class="font-bold text-slate-900 text-sm tracking-wide">Fitur Sistem</h4>
          <ul class="space-y-2 text-xs sm:text-sm">
            <li><a href="#fitur" class="hover:text-pawon-700 transition-colors">Lahan &amp; Geo-lokasi</a></li>
            <li><a href="#fitur" class="hover:text-pawon-700 transition-colors">Aktivitas Tanam</a></li>
            <li><a href="#cara-kerja" class="hover:text-pawon-700 transition-colors">Panen Terverifikasi</a></li>
            <li><a href="{{ route('marketplace.home') }}" class="hover:text-pawon-700 transition-colors">Marketplace Hasil Bumi</a></li>
            <li><a href="#edukasi" class="hover:text-pawon-700 transition-colors">Edukasi &amp; PPL</a></li>
          </ul>
        </div>

        <!-- Links Column 2 (2-3 cols) -->
        <div class="md:col-span-2 space-y-3">
          <h4 class="font-bold text-slate-900 text-sm tracking-wide">PawonTani</h4>
          <ul class="space-y-2 text-xs sm:text-sm">
            <li><a href="#tentang" class="hover:text-pawon-700 transition-colors">Tentang PawonTani</a></li>
            <li><a href="#keunggulan" class="hover:text-pawon-700 transition-colors">Keunggulan</a></li>
            <li><a href="#fitur" class="hover:text-pawon-700 transition-colors">Fitur Poktan</a></li>
            <li><a href="#cara-kerja" class="hover:text-pawon-700 transition-colors">Alur Kerja</a></li>
            <li><a href="#edukasi" class="hover:text-pawon-700 transition-colors">Edukasi Pertanian</a></li>
          </ul>
        </div>

        <!-- Links Column 3 (3 cols) -->
        <div class="md:col-span-3 space-y-3">
          <h4 class="font-bold text-slate-900 text-sm tracking-wide">Bantuan & Kantor</h4>
          <p class="text-xs text-slate-500 leading-relaxed">
            Gedung Agro Inovasi Nusantara Lt. 3<br>
            Jl. Pertanian Raya No. 45, Jakarta Selatan
          </p>
          <div class="text-xs space-y-1 text-slate-500">
            <p>Email: <a href="mailto:halo@pawontani.id" class="text-pawon-700 hover:underline">halo@pawontani.id</a>
            </p>
            <p>WhatsApp: <span class="font-semibold text-slate-700">+62 812-3456-7890</span></p>
          </div>
        </div>

      </div>

      <!-- Copyright Bar -->
      <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
        <p>&copy; 2026 PawonTani Indonesia. Seluruh hak cipta dilindungi.</p>
        <div class="flex items-center gap-6">
          <a href="#" class="hover:text-pawon-700 transition-colors">Syarat & Ketentuan</a>
          <a href="#" class="hover:text-pawon-700 transition-colors">Kebijakan Privasi</a>
          <a href="#" class="hover:text-pawon-700 transition-colors">Keamanan Data</a>
        </div>
      </div>

    </div>
  </footer>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
          },
          colors: {
            pawon: {
              50: '#f0fdf4',
              100: '#dcfce7',
              200: '#bbf7d0',
              300: '#86efac',
              400: '#4ade80',
              500: '#22c55e',
              600: '#16a34a',
              700: '#15803d',
              800: '#166534',
              900: '#14532d',
              950: '#052e16',
            },
            softblue: '#f4f8fc',
            skycard: '#edf5fa',
          }
        }
      }
    }
  </script>

  <!-- ==================== JAVASCRIPT LOGIC ==================== -->
  <script>
    // Lucide Icons initialization (if available)
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }

    // Mobile Navigation Toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIconOpen = document.getElementById('menu-icon-open');
    const menuIconClose = document.getElementById('menu-icon-close');

    if (mobileMenuBtn) {
      mobileMenuBtn.addEventListener('click', () => {
        const isHidden = mobileMenu.classList.contains('hidden');
        if (isHidden) {
          mobileMenu.classList.remove('hidden');
          menuIconOpen.classList.add('hidden');
          menuIconClose.classList.remove('hidden');
        } else {
          mobileMenu.classList.add('hidden');
          menuIconOpen.classList.remove('hidden');
          menuIconClose.classList.add('hidden');
        }
      });
    }

    // Close mobile menu on nav link click
    document.querySelectorAll('.mobile-nav-link').forEach(link => {
      link.addEventListener('click', () => {
        if (mobileMenu) {
          mobileMenu.classList.add('hidden');
          menuIconOpen.classList.remove('hidden');
          menuIconClose.classList.add('hidden');
        }
      });
    });

    // ==================== SCROLLSPY & ACTIVE NAV MENU ====================
    const navItems = [
      { id: 'beranda', el: document.getElementById('beranda') },
      { id: 'tentang', el: document.getElementById('tentang') },
      { id: 'fitur', el: document.getElementById('fitur') },
      { id: 'cara-kerja', el: document.getElementById('cara-kerja') },
      { id: 'edukasi', el: document.getElementById('edukasi') }
    ];

    const desktopLinks = document.querySelectorAll('nav .nav-link');
    const mobileLinks = document.querySelectorAll('#mobile-menu .mobile-nav-link');

    function updateActiveMenu(activeId) {
      desktopLinks.forEach(link => {
        const targetId = (link.getAttribute('href') || '').replace('#', '');
        if (targetId === activeId) {
          link.classList.add('active');
          link.style.color = '#15803d'; // Hijau PawonTani
          link.style.fontWeight = '700';
        } else {
          link.classList.remove('active');
          link.style.color = '#475569'; // Slate 600
          link.style.fontWeight = '500';
        }
      });

      mobileLinks.forEach(link => {
        const targetId = (link.getAttribute('href') || '').replace('#', '');
        if (targetId === activeId) {
          link.classList.add('active');
          link.style.color = '#15803d';
          link.style.fontWeight = '700';
          link.style.backgroundColor = '#f0fdf4';
        } else {
          link.classList.remove('active');
          link.style.color = '#334155';
          link.style.fontWeight = '500';
          link.style.backgroundColor = 'transparent';
        }
      });
    }

    function onScrollSpy() {
      // Offset from top of viewport (navbar height + buffer)
      const scrollPos = window.scrollY + 140;

      let currentId = 'beranda';

      // Check which section we are currently in
      for (let i = 0; i < navItems.length; i++) {
        const item = navItems[i];
        if (item.el) {
          const sectionTop = item.el.getBoundingClientRect().top + window.scrollY;
          if (scrollPos >= sectionTop) {
            currentId = item.id;
          }
        }
      }

      // If user reaches near the very bottom of the page, activate the last menu (edukasi)
      if ((window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 80)) {
        currentId = 'edukasi';
      }

      updateActiveMenu(currentId);
    }

    // Run on scroll
    window.addEventListener('scroll', onScrollSpy, { passive: true });

    // Run on page load and immediately
    window.addEventListener('DOMContentLoaded', onScrollSpy);
    onScrollSpy();

    // Click handler for instant active highlight on desktop & mobile
    [...desktopLinks, ...mobileLinks].forEach(link => {
      link.addEventListener('click', function () {
        const targetId = (this.getAttribute('href') || '').replace('#', '');
        if (targetId) {
          updateActiveMenu(targetId);
        }
      });
    });
  </script>

  <!-- Modal Formulir Pendaftaran Kelompok Tani -->
<div id="registration-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <!-- Backdrop blur -->
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeRegistrationModal()"></div>

  <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
    <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
      
      <!-- Modal Header with gradient -->
      <div class="bg-gradient-to-r from-emerald-700 to-pawon-700 px-6 py-6 text-white relative">
        <button type="button" onclick="closeRegistrationModal()" class="absolute top-5 right-5 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-1.5 transition-colors focus:outline-none">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-xs font-semibold tracking-wide mb-2">
          <span>🌾 Pendaftaran Poktan</span>
        </div>
        <h3 class="text-xl font-bold" id="modal-title">Daftarkan Kelompok Tani</h3>
        <p class="text-emerald-100 text-xs sm:text-sm mt-1">
          Bergabung bersama ribuan kelompok tani dalam ekosistem digital PawonTani.
        </p>
      </div>

      <!-- Modal Body / Form -->
      <form id="registration-form" action="javascript:void(0)" method="POST" class="p-6 sm:p-8 space-y-4">
        @csrf

        <!-- Notification container -->
        <div id="modal-alert" class="hidden p-4 rounded-xl text-sm font-medium"></div>

        <div>
          <label for="nama_lengkap" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap Penanggung Jawab *</label>
          <input type="text" id="nama_lengkap" name="nama_lengkap" required placeholder="Contoh: Bpk. Sugeng Wibowo"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-slate-800 text-sm focus:border-pawon-600 focus:ring-2 focus:ring-pawon-600/20 outline-none transition-all">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="nama_kelompok" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Poktan / Gapoktan *</label>
            <input type="text" id="nama_kelompok" name="nama_kelompok" required placeholder="Contoh: Poktan Makmur Jaya"
              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-slate-800 text-sm focus:border-pawon-600 focus:ring-2 focus:ring-pawon-600/20 outline-none transition-all">
          </div>
          <div>
            <label for="nomor_wa" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nomor WhatsApp *</label>
            <input type="text" id="nomor_wa" name="nomor_wa" required placeholder="Contoh: 081234567890"
              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-slate-800 text-sm focus:border-pawon-600 focus:ring-2 focus:ring-pawon-600/20 outline-none transition-all">
          </div>
        </div>

        <div>
          <label for="daerah" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Lokasi Wilayah (Desa, Kecamatan, Kab/Kota)</label>
          <input type="text" id="daerah" name="daerah" placeholder="Contoh: Sukamaju, Kec. Ciawi, Kab. Bogor"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-slate-800 text-sm focus:border-pawon-600 focus:ring-2 focus:ring-pawon-600/20 outline-none transition-all">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="luas_lahan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Estimasi Luas Lahan</label>
            <input type="text" id="luas_lahan" name="luas_lahan" placeholder="Contoh: 15 Hektar"
              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-slate-800 text-sm focus:border-pawon-600 focus:ring-2 focus:ring-pawon-600/20 outline-none transition-all">
          </div>
          <div>
            <label for="komoditas" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Komoditas Utama</label>
            <input type="text" id="komoditas" name="komoditas" placeholder="Contoh: Padi & Jagung"
              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-slate-800 text-sm focus:border-pawon-600 focus:ring-2 focus:ring-pawon-600/20 outline-none transition-all">
          </div>
        </div>

        <div>
          <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kebutuhan / Catatan Tambahan</label>
          <textarea id="catatan" name="catatan" rows="2" placeholder="Tuliskan kendala atau modul yang paling dibutuhkan..."
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-slate-800 text-sm focus:border-pawon-600 focus:ring-2 focus:ring-pawon-600/20 outline-none transition-all"></textarea>
        </div>

        <!-- Submit Buttons -->
        <div class="pt-3 flex items-center justify-end gap-3">
          <button type="button" onclick="closeRegistrationModal()"
            class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-all">
            Batal
          </button>
          <button type="submit" id="btn-submit-reg"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-xl shadow-md hover:shadow-pawon-700/25 transition-all">
            <span id="btn-text">Kirim Pendaftaran</span>
            <svg id="btn-spinner" class="hidden animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

  <script>
    // ==================== MODAL PENDAFTARAN ====================
    function openRegistrationModal() {
      const modal = document.getElementById('registration-modal');
      if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
      }
    }

    function closeRegistrationModal() {
      const modal = document.getElementById('registration-modal');
      if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        const alert = document.getElementById('modal-alert');
        if (alert) alert.classList.add('hidden');
      }
    }

    // Handle AJAX Registration Form
    const regForm = document.getElementById('registration-form');
    if (regForm) {
      regForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const alertEl = document.getElementById('modal-alert');
        const submitBtn = document.getElementById('btn-submit-reg');
        const btnText = document.getElementById('btn-text');
        const btnSpinner = document.getElementById('btn-spinner');

        submitBtn.disabled = true;
        btnText.textContent = 'Memproses...';
        btnSpinner.classList.remove('hidden');
        alertEl.classList.add('hidden');

        setTimeout(() => {
          alertEl.className = 'p-4 rounded-xl text-sm font-medium bg-emerald-50 text-emerald-800 border border-emerald-200';
          alertEl.innerHTML = '<strong>Alhamdulillah!</strong> Pendaftaran kelompok tani Anda telah kami terima. Tim konsultan PawonTani akan segera menghubungi Anda.';
          alertEl.classList.remove('hidden');
          regForm.reset();
          submitBtn.disabled = false;
          btnText.textContent = 'Kirim Pendaftaran';
          btnSpinner.classList.add('hidden');
          setTimeout(() => {
            closeRegistrationModal();
          }, 2500);
        }, 500);
      });
    }
  </script>
</body>

</html>