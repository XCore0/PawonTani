<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="{{ asset('images/Logo2.png') }}">
  <title>PawonTani - Sistem Informasi untuk Kelompok Tani</title>
  <meta name="description"
    content="PawonTani merupakan sistem informasi untuk kelompok tani yang membantu mengelola anggota, lahan, aktivitas pertanian, panen, hingga penjualan dalam satu platform digital.">

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
        <nav class="hidden md:flex items-center space-x-8 text-[15px]">
          <a href="#beranda" class="nav-link active">Beranda</a>
          <a href="#tentang" class="nav-link">Tentang PawonTani</a>
          <a href="#fitur" class="nav-link">Fitur</a>
          <a href="#cara-kerja" class="nav-link">Cara Kerja</a>
          <a href="#edukasi" class="nav-link">Edukasi</a>
        </nav>

        <!-- CTA Button Desktop -->
        <div class="hidden md:flex items-center gap-4">
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
      <div class="pt-2">
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
              Sistem Informasi Kelompok Tani
            </div>

            <!-- Hero Main Headline -->
            <h1 class="text-4xl sm:text-5xl lg:text-[54px] font-extrabold text-slate-900 tracking-tight leading-[1.12]">
              Kelola Pertanian Lebih Mudah Bersama <br class="hidden sm:inline">
              <span class="text-pawon-600 inline-block">PawonTani</span>
            </h1>

            <!-- Subheading Description -->
            <p class="text-base sm:text-lg text-slate-600 max-w-2xl font-normal leading-relaxed">
              PawonTani merupakan sistem informasi untuk kelompok tani yang membantu mengelola anggota, lahan, aktivitas pertanian, panen, hingga penjualan dalam satu platform digital.
            </p>

            <!-- Hero Action Buttons -->
            <div class="flex flex-wrap items-center gap-4 pt-2">
              <a href="javascript:void(0)" onclick="openRegistrationModal()"
                class="inline-flex items-center gap-2.5 px-7 py-3.5 text-base font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow-lg shadow-pawon-700/25 transition-all hover:translate-y-[-1px]">
                <span>Daftar untuk Kelompok</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
              <a href="#fitur"
                class="inline-flex items-center gap-2 px-7 py-3.5 text-base font-semibold text-slate-700 bg-sky-50/80 hover:bg-sky-100/90 border border-sky-200/70 rounded-full transition-all">
                <span>Pelajari Fitur</span>
              </a>
            </div>

            <!-- Stats Bar Row -->
            <div class="pt-8 border-t border-slate-100 grid grid-cols-3 gap-4 sm:gap-8 max-w-lg">
              <div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">10rb+</div>
                <div class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Petani Terdaftar</div>
              </div>
              <div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">2.500+</div>
                <div class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Hektar Lahan</div>
              </div>
              <div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">99,4%</div>
                <div class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Akurasi Catatan</div>
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
                    <h3 class="text-sm font-bold text-slate-800 leading-none">Ringkasan Lahan Hari Ini</h3>
                    <span class="text-[11px] text-slate-400">Blok A - Lahan Padi Pandanwangi</span>
                  </div>
                </div>
                <span
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                  ● Aktif
                </span>
              </div>

              <!-- 3 Metric Boxes -->
              <div class="grid grid-cols-3 gap-2.5 mb-5">
                <div class="bg-slate-50/90 rounded-2xl p-3 text-center border border-slate-100">
                  <div class="text-[11px] font-medium text-slate-500 mb-1">Kelembaban</div>
                  <div class="text-lg font-bold text-slate-900">64%</div>
                  <span class="text-[10px] text-emerald-600 font-semibold">Optimal</span>
                </div>
                <div class="bg-slate-50/90 rounded-2xl p-3 text-center border border-slate-100">
                  <div class="text-[11px] font-medium text-slate-500 mb-1">Suhu Udara</div>
                  <div class="text-lg font-bold text-slate-900">28°C</div>
                  <span class="text-[10px] text-slate-500 font-medium">Cerah</span>
                </div>
                <div class="bg-slate-50/90 rounded-2xl p-3 text-center border border-slate-100">
                  <div class="text-[11px] font-medium text-slate-500 mb-1">Est. Panen</div>
                  <div class="text-lg font-bold text-pawon-700">12 Hari</div>
                  <span class="text-[10px] text-pawon-600 font-medium">Musim Gadu</span>
                </div>
              </div>

              <!-- Growth Chart Widget -->
              <div class="bg-slate-50/90 rounded-2xl p-4 border border-slate-100 mb-4">
                <div class="flex items-center justify-between mb-3">
                  <div class="text-xs font-bold text-slate-700">Performa Pertumbuhan Tanaman</div>
                  <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-semibold">Bulan
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
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 8v2m0-6c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div>
                    <div class="text-xs font-semibold text-slate-800">Transaksi Panen Terakhir</div>
                    <div class="text-[11px] text-slate-400">Cabai Rawit Merah • 1.200 kg</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-xs font-bold text-slate-900">Rp 14.500.000</div>
                  <span class="text-[10px] text-emerald-600 font-semibold">Selesai Dibayar</span>
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
                <p class="font-bold text-slate-800">Sinkronisasi Cloud Aktif</p>
                <p class="text-slate-400 text-[10px]">Pembaruan otomatis tiap 5 menit</p>
              </div>
            </div>

          </div>

        </div>
      </div>
    </section>

    <!-- ==================== 3. SECTION: APA ITU PAWONTANI ==================== -->
    <section id="tentang" class="py-10 bg-gradient-to-b from-skycard/60 via-softblue/40 to-white relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Text Centered -->
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
          <span class="badge-pill">Tentang Kami</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Apa itu PawonTani?
          </h2>
          <p class="text-base text-slate-600 leading-relaxed">
            PawonTani merupakan sistem informasi yang dirancang untuk membantu kelompok tani dalam mengelola dan memantau kegiatan pertanian secara digital.
          </p>
        </div>

        <!-- 7 Feature Cards Grid (Row 1: 4 Cards, Row 2: 2 Cards + 1 Standout Hero Card) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

          <!-- Card 1: Langkah 01 - Kelompok Tani -->
          <div
            class="feature-card bg-white rounded-2xl p-6 border border-slate-100 shadow-xs flex flex-col justify-between">
            <div>
              <div class="w-11 h-11 rounded-xl bg-sky-50 text-pawon-700 flex items-center justify-center mb-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M3 21h18M3 7v14M21 7v14M6 11h2M6 15h2M11 11h2M11 15h2M16 11h2M16 15h2M12 3l9 4H3l9-4z" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">LANGKAH 01</span>
              <h3 class="text-lg font-bold text-slate-900 mb-2">Kelompok Tani</h3>
              <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                Pusat kelembagaan, legalitas tani, dan agregasi data struktural poktan di tingkat desa.
              </p>
            </div>
            <div class="mt-6 pt-3 flex items-center justify-between text-pawon-700 font-bold text-xs">
              <a href="#fitur"
                class="inline-flex items-center justify-between w-full hover:text-pawon-800 transition-colors">
                <span>Fondasi Kolektif</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </div>

          <!-- Card 2: Langkah 02 - Anggota -->
          <div
            class="feature-card bg-white rounded-2xl p-6 border border-slate-100 shadow-xs flex flex-col justify-between">
            <div>
              <div class="w-11 h-11 rounded-xl bg-sky-50 text-pawon-700 flex items-center justify-center mb-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M16 18H8a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2Z" />
                  <path d="M12 3v3" />
                  <circle cx="12" cy="10" r="2" />
                  <path d="M9 15c0-1.5 1.5-2 3-2s3 .5 3 2" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">LANGKAH 02</span>
              <h3 class="text-lg font-bold text-slate-900 mb-2">Anggota</h3>
              <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                Data petani terverifikasi dengan profil keahlian, status kepemilikan, dan rekam jejak garapan.
              </p>
            </div>
            <div class="mt-6 pt-3 flex items-center justify-between text-pawon-700 font-bold text-xs">
              <a href="#fitur"
                class="inline-flex items-center justify-between w-full hover:text-pawon-800 transition-colors">
                <span>Identitas Digital</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </div>

          <!-- Card 3: Langkah 03 - Lahan -->
          <div
            class="feature-card bg-white rounded-2xl p-6 border border-slate-100 shadow-xs flex flex-col justify-between">
            <div>
              <div class="w-11 h-11 rounded-xl bg-sky-50 text-pawon-700 flex items-center justify-center mb-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <circle cx="18" cy="5" r="3" />
                  <circle cx="6" cy="12" r="3" />
                  <circle cx="18" cy="19" r="3" />
                  <line x1="8.59" x2="15.42" y1="13.51" y2="17.49" />
                  <line x1="15.41" x2="8.59" y1="6.51" y2="10.49" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">LANGKAH 03</span>
              <h3 class="text-lg font-bold text-slate-900 mb-2">Lahan</h3>
              <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                Pemetaan poligon spasial, luas baku hamparan, ketinggian MDPL, dan status kesuburan tanah.
              </p>
            </div>
            <div class="mt-6 pt-3 flex items-center justify-between text-pawon-700 font-bold text-xs">
              <a href="#fitur"
                class="inline-flex items-center justify-between w-full hover:text-pawon-800 transition-colors">
                <span>Pemetaan Presisi</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </div>

          <!-- Card 4: Langkah 04 - Musim Tanam -->
          <div
            class="feature-card bg-white rounded-2xl p-6 border border-slate-100 shadow-xs flex flex-col justify-between">
            <div>
              <div class="w-11 h-11 rounded-xl bg-sky-50 text-pawon-700 flex items-center justify-center mb-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path
                    d="M7 20h10M10 20c5.5-2.5.8-6.4 3-10M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4.1 5.5.8zM14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">LANGKAH 04</span>
              <h3 class="text-lg font-bold text-slate-900 mb-2">Musim Tanam</h3>
              <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                Penjadwalan komoditas tanaman, alokasi benih dan sarana, serta koordinasi jadwal tanam kelompok.
              </p>
            </div>
            <div class="mt-6 pt-3 flex items-center justify-between text-pawon-700 font-bold text-xs">
              <a href="#fitur"
                class="inline-flex items-center justify-between w-full hover:text-pawon-800 transition-colors">
                <span>Musim Tanam</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </div>

          <!-- Card 5: Langkah 05 - Aktivitas & Kondisi -->
          <div
            class="feature-card bg-white rounded-2xl p-6 border border-slate-100 shadow-xs flex flex-col justify-between">
            <div>
              <div class="w-11 h-11 rounded-xl bg-sky-50 text-pawon-700 flex items-center justify-center mb-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <rect width="16" height="18" x="4" y="4" rx="2" />
                  <path d="M8 2h8v4H8zM8 10h8M8 14h5" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">LANGKAH 05</span>
              <h3 class="text-lg font-bold text-slate-900 mb-2">Aktivitas & Kondisi</h3>
              <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                Log harian pemupukan presisi, pemantauan serangan hama/OPT, dan pencatatan cuaca lapangan.
              </p>
            </div>
            <div class="mt-6 pt-3 flex items-center justify-between text-pawon-700 font-bold text-xs">
              <a href="#fitur"
                class="inline-flex items-center justify-between w-full hover:text-pawon-800 transition-colors">
                <span>Monitoring Rutin</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </div>

          <!-- Card 6: Langkah 06 - Panen -->
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
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">LANGKAH 06</span>
              <h3 class="text-lg font-bold text-slate-900 mb-2">Panen</h3>
              <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                Verifikasi tonase riil, kadar air gabah, uji kualitas hasil panen, dan tanggal kesiapan tebas.
              </p>
            </div>
            <div class="mt-6 pt-3 flex items-center justify-between text-pawon-700 font-bold text-xs">
              <a href="#fitur"
                class="inline-flex items-center justify-between w-full hover:text-pawon-800 transition-colors">
                <span>Validasi Hasil</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </div>

          <!-- Card 7: STANDOUT HERO CARD (Langkah 07 - Penjualan - Spans 2 Cols) -->
          <div
            class="feature-card lg:col-span-2 bg-gradient-to-br from-[#0c6b32] to-[#074b21] rounded-2xl p-7 text-white shadow-xl shadow-pawon-900/15 flex flex-col justify-between relative overflow-hidden">
            <div>
              <div
                class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur text-white flex items-center justify-center mb-5 border border-white/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <rect width="20" height="12" x="2" y="6" rx="2" />
                  <circle cx="12" cy="12" r="2.5" />
                  <line x1="6" x2="6.01" y1="12" y2="12" />
                  <line x1="18" x2="18.01" y1="12" y2="12" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-emerald-200 uppercase tracking-wider block mb-1">LANGKAH 07 •
                HASIL AKHIR</span>
              <h3 class="text-xl font-bold text-white mb-2.5">Penjualan</h3>
              <p class="text-xs sm:text-sm text-emerald-50/90 leading-relaxed max-w-xl">
                Pencatatan dan pengelolaan hasil penjualan panen kelompok tani secara transparan, adil, dan terorganisir.
              </p>
            </div>

            <div class="mt-6 pt-5 border-t border-white/10 flex items-center justify-between">
              <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-100">
                <span>Pengelolaan Terpadu</span>
                <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  stroke-width="2.5">
                  <circle cx="12" cy="12" r="10" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" />
                </svg>
              </span>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- ==================== 4. SECTION: MENGAPA PAWONTANI ==================== -->
    <section id="keunggulan" class="py-10 bg-white relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Two Column Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16">
          <div class="space-y-3 max-w-xl">
            <span class="badge-pill">Keunggulan Utama</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
              Mengapa PawonTani?
            </h2>
          </div>
          <div class="max-w-md">
            <p class="text-base text-slate-600 leading-relaxed">
              Dirancang menjawab tantangan nyata kelompok tani pedesaan dengan kemudahan operasional yang nyata.
            </p>
          </div>
        </div>

        <!-- 4 Grid Cards with 01, 02, 03, 04 numbers -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- Benefit 1: Mengelola Anggota -->
          <div class="bg-softblue/60 hover:bg-softblue rounded-3xl p-8 border border-sky-100/80 transition-all">
            <div class="flex items-start justify-between mb-6">
              <div class="w-12 h-12 rounded-2xl bg-white text-pawon-700 shadow-xs flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                  <circle cx="9" cy="7" r="4" />
                  <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                  <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
              </div>
              <span class="text-2xl font-extrabold text-slate-300">01</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2.5">Mengelola Anggota</h3>
            <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
              Membantu kelompok tani mengelola data anggota secara lebih terorganisir.
            </p>
          </div>

          <!-- Benefit 2: Mengelola Lahan -->
          <div class="bg-softblue/60 hover:bg-softblue rounded-3xl p-8 border border-sky-100/80 transition-all">
            <div class="flex items-start justify-between mb-6">
              <div class="w-12 h-12 rounded-2xl bg-white text-pawon-700 shadow-xs flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="m8 3 4 8 5-5 5 15H2L8 3z" />
                </svg>
              </div>
              <span class="text-2xl font-extrabold text-slate-300">02</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2.5">Mengelola Lahan</h3>
            <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
              Mencatat dan mengelola data lahan serta tanaman yang dikelola anggota.
            </p>
          </div>

          <!-- Benefit 3: Monitoring Pertanian -->
          <div class="bg-softblue/60 hover:bg-softblue rounded-3xl p-8 border border-sky-100/80 transition-all">
            <div class="flex items-start justify-between mb-6">
              <div class="w-12 h-12 rounded-2xl bg-white text-pawon-700 shadow-xs flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M3 3v18h18" />
                  <path d="m19 9-5 5-4-4-3 3" />
                </svg>
              </div>
              <span class="text-2xl font-extrabold text-slate-300">03</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2.5">Monitoring Pertanian</h3>
            <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
              Membantu pengurus memantau aktivitas dan kondisi pertanian anggota.
            </p>
          </div>

          <!-- Benefit 4: Mengelola Panen & Penjualan -->
          <div class="bg-softblue/60 hover:bg-softblue rounded-3xl p-8 border border-sky-100/80 transition-all">
            <div class="flex items-start justify-between mb-6">
              <div class="w-12 h-12 rounded-2xl bg-white text-pawon-700 shadow-xs flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <rect width="18" height="12" x="3" y="9" rx="2" />
                  <path d="M7 9V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v4" />
                  <circle cx="8" cy="15" r="1" />
                  <circle cx="12" cy="15" r="1" />
                  <circle cx="16" cy="15" r="1" />
                </svg>
              </div>
              <span class="text-2xl font-extrabold text-slate-300">04</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2.5">Mengelola Panen & Penjualan</h3>
            <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
              Mencatat hasil panen dan penjualan untuk membantu pengelolaan hasil pertanian.
            </p>
          </div>

        </div>

      </div>
    </section>

    <!-- ==================== 5. SECTION: FITUR & PAKET ==================== -->
    <section id="fitur" class="py-10 bg-gradient-to-b from-skycard/50 to-softblue/80 relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Text Centered -->
        <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
          <span class="badge-pill">Fitur Sistem</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Fitur PawonTani
          </h2>
          <p class="text-base text-slate-600">
            Sistem informasi terintegrasi yang memudahkan operasional anggota, pengurus, dan admin kelompok tani.
          </p>
        </div>

        <!-- 3 Feature Cards by User Roles -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">

          <!-- Card 1: Anggota / Petani -->
          <div
            class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm flex flex-col justify-between relative">
            <div>
              <div class="w-10 h-10 rounded-xl bg-emerald-50 text-pawon-700 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                  <circle cx="12" cy="7" r="4" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-slate-900 mb-1">Anggota / Petani</h3>
              <p class="text-xs text-slate-500 mb-6">Digunakan oleh anggota kelompok tani untuk mengelola dan mencatat kegiatan pertanian.</p>

              <!-- Features Checklist -->
              <ul class="space-y-3.5 text-sm text-slate-700 mb-8">
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Lahan</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Aktivitas & Kondisi</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Panen</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Penjualan</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Cuaca</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Harga Komoditas</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Prediksi Panen</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Prediksi Harga</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Edukasi</span>
                </li>
              </ul>
            </div>

            <div>
              <a href="#kontak"
                class="w-full inline-flex items-center justify-center px-5 py-3 text-sm font-bold text-pawon-800 bg-emerald-50 hover:bg-emerald-100 rounded-full transition-colors">
                Lihat Fitur Anggota
              </a>
            </div>
          </div>

          <!-- Card 2: Pengurus Kelompok Tani (Highlighted Role) -->
          <div
            class="bg-white rounded-3xl p-8 border-2 border-pawon-600 shadow-xl shadow-pawon-700/10 flex flex-col justify-between relative scale-105 z-10">

            <!-- Role Badge -->
            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
              <span
                class="inline-flex items-center gap-1.5 px-4 py-1 rounded-full text-xs font-bold uppercase bg-pawon-700 text-white shadow-md">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Peran Pengurus
              </span>
            </div>

            <div>
              <div class="w-10 h-10 rounded-xl bg-pawon-100 text-pawon-800 flex items-center justify-center mb-4 mt-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                  <circle cx="9" cy="7" r="4" />
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                  <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-slate-900 mb-1">Pengurus Kelompok Tani</h3>
              <p class="text-xs text-slate-500 mb-6">Digunakan oleh pengurus untuk mengelola anggota dan memantau kegiatan pertanian kelompok.</p>

              <!-- Features Checklist -->
              <ul class="space-y-3.5 text-sm text-slate-700 mb-8">
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-pawon-700 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span class="font-medium text-slate-900">Anggota & Lahan</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-pawon-700 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Lahan Tanaman</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-pawon-700 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Monitoring Pertanian</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-pawon-700 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span class="font-medium text-slate-900">Panen & Penjualan</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-pawon-700 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Laporan</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-pawon-700 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Informasi & Prediksi</span>
                </li>
              </ul>
            </div>

            <div>
              <a href="#kontak"
                class="w-full inline-flex items-center justify-center px-5 py-3.5 text-sm font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow-lg shadow-pawon-700/25 transition-all">
                Lihat Fitur Pengurus
              </a>
            </div>
          </div>

          <!-- Card 3: Admin Sistem -->
          <div
            class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm flex flex-col justify-between relative">
            <div>
              <div class="w-10 h-10 rounded-xl bg-emerald-50 text-pawon-700 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-slate-900 mb-1">Admin Sistem</h3>
              <p class="text-xs text-slate-500 mb-6">Digunakan untuk mengelola data dan konten utama dalam sistem PawonTani.</p>

              <!-- Features Checklist -->
              <ul class="space-y-3.5 text-sm text-slate-700 mb-8">
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Kelompok Tani</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Pengguna</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Artikel</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Tips</span>
                </li>
                <li class="flex items-start gap-2.5">
                  <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Panduan</span>
                </li>
              </ul>
            </div>

            <div>
              <a href="#kontak"
                class="w-full inline-flex items-center justify-center px-5 py-3 text-sm font-bold text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-full transition-colors">
                Lihat Fitur Admin
              </a>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- ==================== 6. SECTION: BAGAIMANA PAWONTANI BEKERJA ==================== -->
    <section id="cara-kerja" class="py-12 md:py-16 bg-white relative">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Text Centered -->
        <div class="text-center max-w-2xl mx-auto mb-10 md:mb-12 space-y-3">
          <span class="badge-pill">Alur Kerja Sistem</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Bagaimana PawonTani Bekerja?
          </h2>
          <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
            Alur terstruktur pengelolaan kegiatan pertanian kelompok tani dari pendataan kelompok hingga penjualan hasil panen.
          </p>
        </div>

        <!-- 7 Structured Process Cards List -->
        <div class="space-y-3.5 sm:space-y-4">

          <!-- Card 01: Kelompok Tani -->
          <div
            class="bg-[#f2f7fb] hover:bg-[#eaf3f9] transition-colors rounded-2xl p-4 sm:py-5 sm:px-6 flex items-center justify-between gap-4 border border-slate-100/60 shadow-xs">
            <div class="flex items-center gap-4 sm:gap-5 min-w-0">
              <span
                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#0a5c2b] text-white font-extrabold text-sm sm:text-base flex items-center justify-center shrink-0 shadow-xs">01</span>
              <div class="min-w-0">
                <h3 class="font-bold text-base sm:text-lg text-slate-900 leading-snug">Kelompok Tani</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-relaxed">
                  Pendaftaran dan pendataan kelembagaan kelompok tani dalam sistem digital.
                </p>
              </div>
            </div>
            <div class="shrink-0 text-pawon-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
          </div>

          <!-- Card 02: Anggota -->
          <div
            class="bg-[#f2f7fb] hover:bg-[#eaf3f9] transition-colors rounded-2xl p-4 sm:py-5 sm:px-6 flex items-center justify-between gap-4 border border-slate-100/60 shadow-xs">
            <div class="flex items-center gap-4 sm:gap-5 min-w-0">
              <span
                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#0a5c2b] text-white font-extrabold text-sm sm:text-base flex items-center justify-center shrink-0 shadow-xs">02</span>
              <div class="min-w-0">
                <h3 class="font-bold text-base sm:text-lg text-slate-900 leading-snug">Anggota</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-relaxed">
                  Pengelolaan data anggota kelompok tani yang terdaftar dan terorganisir.
                </p>
              </div>
            </div>
            <div class="shrink-0 text-pawon-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </div>
          </div>

          <!-- Card 03: Lahan -->
          <div
            class="bg-[#f2f7fb] hover:bg-[#eaf3f9] transition-colors rounded-2xl p-4 sm:py-5 sm:px-6 flex items-center justify-between gap-4 border border-slate-100/60 shadow-xs">
            <div class="flex items-center gap-4 sm:gap-5 min-w-0">
              <span
                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#0a5c2b] text-white font-extrabold text-sm sm:text-base flex items-center justify-center shrink-0 shadow-xs">03</span>
              <div class="min-w-0">
                <h3 class="font-bold text-base sm:text-lg text-slate-900 leading-snug">Lahan</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-relaxed">
                  Pencatatan data petak lahan dan tanaman yang dikelola oleh anggota kelompok.
                </p>
              </div>
            </div>
            <div class="shrink-0 text-pawon-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6" stroke-linecap="round" stroke-linejoin="round" />
                <line x1="8" y1="2" x2="8" y2="18" stroke-linecap="round" stroke-linejoin="round" />
                <line x1="16" y1="6" x2="16" y2="22" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
          </div>

          <!-- Card 04: Musim Tanam -->
          <div
            class="bg-[#f2f7fb] hover:bg-[#eaf3f9] transition-colors rounded-2xl p-4 sm:py-5 sm:px-6 flex items-center justify-between gap-4 border border-slate-100/60 shadow-xs">
            <div class="flex items-center gap-4 sm:gap-5 min-w-0">
              <span
                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#0a5c2b] text-white font-extrabold text-sm sm:text-base flex items-center justify-center shrink-0 shadow-xs">04</span>
              <div class="min-w-0">
                <h3 class="font-bold text-base sm:text-lg text-slate-900 leading-snug">Musim Tanam</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-relaxed">
                  Perencanaan dan penjadwalan musim tanam komoditas kelompok tani.
                </p>
              </div>
            </div>
            <div class="shrink-0 text-pawon-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round" />
                <line x1="16" y1="2" x2="16" y2="6" stroke-linecap="round" stroke-linejoin="round" />
                <line x1="8" y1="2" x2="8" y2="6" stroke-linecap="round" stroke-linejoin="round" />
                <line x1="3" y1="10" x2="21" y2="10" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
          </div>

          <!-- Card 05: Aktivitas & Kondisi -->
          <div
            class="bg-[#f2f7fb] hover:bg-[#eaf3f9] transition-colors rounded-2xl p-4 sm:py-5 sm:px-6 flex items-center justify-between gap-4 border border-slate-100/60 shadow-xs">
            <div class="flex items-center gap-4 sm:gap-5 min-w-0">
              <span
                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#0a5c2b] text-white font-extrabold text-sm sm:text-base flex items-center justify-center shrink-0 shadow-xs">05</span>
              <div class="min-w-0">
                <h3 class="font-bold text-base sm:text-lg text-slate-900 leading-snug">Aktivitas & Kondisi</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-relaxed">
                  Pencatatan aktivitas pertanian harian serta pemantauan kondisi tanaman anggota.
                </p>
              </div>
            </div>
            <div class="shrink-0 text-pawon-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l3.5 3.5L13 7M16 11h5M16 15h5" />
              </svg>
            </div>
          </div>

          <!-- Card 06: Panen -->
          <div
            class="bg-[#f2f7fb] hover:bg-[#eaf3f9] transition-colors rounded-2xl p-4 sm:py-5 sm:px-6 flex items-center justify-between gap-4 border border-slate-100/60 shadow-xs">
            <div class="flex items-center gap-4 sm:gap-5 min-w-0">
              <span
                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#0a5c2b] text-white font-extrabold text-sm sm:text-base flex items-center justify-center shrink-0 shadow-xs">06</span>
              <div class="min-w-0">
                <h3 class="font-bold text-base sm:text-lg text-slate-900 leading-snug">Panen</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-relaxed">
                  Pencatatan dan verifikasi data hasil panen pertanian anggota kelompok tani.
                </p>
              </div>
            </div>
            <div class="shrink-0 text-pawon-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M5 22h14M5 2h14M17 22v-4.172a2 2 0 00-.586-1.414L12 12l-4.414 4.414A2 2 0 007 17.828V22M7 2v4.172a2 2 0 00.586 1.414L12 12l4.414-4.414A2 2 0 0017 6.172V2" />
              </svg>
            </div>
          </div>

          <!-- Card 07: Penjualan -->
          <div
            class="bg-[#f2f7fb] hover:bg-[#eaf3f9] transition-colors rounded-2xl p-4 sm:py-5 sm:px-6 flex items-center justify-between gap-4 border border-slate-100/60 shadow-xs">
            <div class="flex items-center gap-4 sm:gap-5 min-w-0">
              <span
                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#0a5c2b] text-white font-extrabold text-sm sm:text-base flex items-center justify-center shrink-0 shadow-xs">07</span>
              <div class="min-w-0">
                <h3 class="font-bold text-base sm:text-lg text-slate-900 leading-snug">Penjualan</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-relaxed">
                  Pencatatan dan pengelolaan penjualan hasil panen kelompok tani secara terorganisir.
                </p>
              </div>
            </div>
            <div class="shrink-0 text-pawon-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m-1.5-6h3a1 1 0 010 2h-3a1 1 0 000 2h3" />
              </svg>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- ==================== 7. SECTION: TARGET PENGGUNA (KELOMPOK TANI) ==================== -->
    <section id="pengguna" class="py-12 md:py-16 bg-gradient-to-b from-skycard/50 via-softblue/30 to-white relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Text Centered -->
        <div class="text-center max-w-3xl mx-auto mb-14 space-y-3">
          <span class="badge-pill">Target Pengguna</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Dikhususkan untuk Kelompok Tani
          </h2>
          <p class="text-base text-slate-600 leading-relaxed">
            PawonTani dirancang untuk menjawab kebutuhan nyata kelembagaan kelompok tani secara terpadu.
          </p>
        </div>

        <!-- 3 Kelompok Tani Ecosystem Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">

          <!-- Pillar 1: Pengurus Kelompok Tani -->
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
              <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">PENGELOLA KELOMPOK</span>
              <h3 class="text-xl font-bold text-slate-900 mb-2">Pengurus Kelompok Tani</h3>
              <p class="text-sm text-slate-600 leading-relaxed mb-6">
                Membantu pengurus mengelola anggota, lahan, serta memantau aktivitas pertanian kelompok secara lebih terorganisir.
              </p>

              <ul class="space-y-2.5 text-xs sm:text-sm text-slate-600 border-t border-slate-100 pt-5">
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Kelola data anggota</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Kelola data lahan</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Monitoring aktivitas pertanian</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Pantau panen dan penjualan</span>
                </li>
              </ul>
            </div>
            <div class="pt-6">
              <a href="#fitur"
                class="text-xs font-bold text-pawon-700 hover:text-pawon-800 inline-flex items-center gap-1">
                <span>Pelajari Peran Pengurus</span>
                <span aria-hidden="true">&rarr;</span>
              </a>
            </div>
          </div>

          <!-- Pillar 2: Petani / Anggota (Highlighted Card) -->
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
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                  <circle cx="9" cy="7" r="4" />
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                  <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-pawon-700 uppercase tracking-wider block mb-1">ANGGOTA KELOMPOK</span>
              <h3 class="text-xl font-bold text-slate-900 mb-2">Petani / Anggota</h3>
              <p class="text-sm text-slate-600 leading-relaxed mb-6">
                Memberikan kemudahan bagi setiap anggota kelompok tani untuk mencatat kegiatan pertanian, kondisi tanaman, hasil panen, dan penjualan.
              </p>

              <ul class="space-y-2.5 text-xs sm:text-sm text-slate-600 border-t border-slate-100 pt-5">
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span class="font-medium text-slate-900">Kelola lahan dan musim tanam</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Catat aktivitas & kondisi tanaman</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Catat panen dan penjualan</span>
                </li>
              </ul>
            </div>
            <div class="pt-6">
              <a href="#fitur"
                class="text-xs font-bold text-pawon-700 hover:text-pawon-800 inline-flex items-center gap-1">
                <span>Pelajari Manfaat Anggota</span>
                <span aria-hidden="true">&rarr;</span>
              </a>
            </div>
          </div>

          <!-- Pillar 3: Informasi Pertanian -->
          <div
            class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
            <div>
              <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-pawon-700 flex items-center justify-center mb-6">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                  <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                  <path d="M8 7h8M8 11h6" />
                </svg>
              </div>
              <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">INFORMASI & EDUKASI</span>
              <h3 class="text-xl font-bold text-slate-900 mb-2">Informasi Pertanian</h3>
              <p class="text-sm text-slate-600 leading-relaxed mb-6">
                Menyediakan informasi yang membantu anggota dan pengurus dalam mendukung kegiatan pertanian.
              </p>

              <ul class="space-y-2.5 text-xs sm:text-sm text-slate-600 border-t border-slate-100 pt-5">
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Informasi cuaca</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Harga komoditas</span>
                </li>
                <li class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-pawon-600 shrink-0"></span>
                  <span>Artikel, tips, dan panduan</span>
                </li>
              </ul>
            </div>
            <div class="pt-6">
              <a href="#edukasi"
                class="text-xs font-bold text-pawon-700 hover:text-pawon-800 inline-flex items-center gap-1">
                <span>Lihat Informasi</span>
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

        <!-- Two Column Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-14">
          <div class="space-y-3 max-w-xl">
            <span class="badge-pill">Pusat Edukasi</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
              Edukasi Pertanian
            </h2>
          </div>
          <div class="max-w-md">
            <p class="text-base text-slate-600 leading-relaxed">
              Tingkatkan wawasan dan keterampilan bertani anggota serta pengurus kelompok tani dengan panduan, artikel, dan tips aplikatif.
            </p>
          </div>
        </div>

        <!-- 3 Blog/Article Cards (Tips, Panduan, Artikel) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

          <!-- Card 1: Tips -->
          <article
            class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-xs hover:shadow-lg transition-all group flex flex-col justify-between">
            <div>
              <div class="relative h-48 overflow-hidden bg-slate-100">
                <img src="https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&q=80"
                  alt="Tips Pengelolaan Air Lahan"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  loading="lazy">
                <div class="absolute top-3.5 left-3.5">
                  <span
                    class="px-3 py-1 rounded-full text-xs font-semibold bg-white/95 backdrop-blur text-emerald-800 shadow-xs">
                    Tips
                  </span>
                </div>
              </div>
              <div class="p-6">
                <h3
                  class="text-lg font-bold text-slate-900 group-hover:text-pawon-700 transition-colors mb-2.5 leading-snug">
                  Tips Pengelolaan Irigasi dan Air untuk Lahan Kelompok Tani
                </h3>
                <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed mb-4">
                  Pelajari teknik efisiensi air mandiri yang praktis untuk menjaga tanaman anggota kelompok tani tetap subur di musim kemarau.
                </p>
              </div>
            </div>
            <div class="px-6 pb-6 pt-0">
              <a href="#"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-pawon-700 hover:text-pawon-800">
                <span>Baca Selengkapnya</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </article>

          <!-- Card 2: Panduan -->
          <article
            class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-xs hover:shadow-lg transition-all group flex flex-col justify-between">
            <div>
              <div class="relative h-48 overflow-hidden bg-slate-100">
                <img src="https://images.unsplash.com/photo-1592982537447-7440770cbfc9?auto=format&fit=crop&w=800&q=80"
                  alt="Panduan Pembuatan Pupuk Organik"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  loading="lazy">
                <div class="absolute top-3.5 left-3.5">
                  <span
                    class="px-3 py-1 rounded-full text-xs font-semibold bg-white/95 backdrop-blur text-emerald-800 shadow-xs">
                    Panduan
                  </span>
                </div>
              </div>
              <div class="p-6">
                <h3
                  class="text-lg font-bold text-slate-900 group-hover:text-pawon-700 transition-colors mb-2.5 leading-snug">
                  Panduan Pembuatan Pupuk Organik Cair Mandiri bagi Kelompok Tani
                </h3>
                <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed mb-4">
                  Cara praktis memfermentasi limbah pertanian lokal menjadi Pupuk Organik Cair (POC) bernutrisi tinggi untuk menunjang kesuburan tanah anggota.
                </p>
              </div>
            </div>
            <div class="px-6 pb-6 pt-0">
              <a href="#"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-pawon-700 hover:text-pawon-800">
                <span>Baca Selengkapnya</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </article>

          <!-- Card 3: Artikel -->
          <article
            class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-xs hover:shadow-lg transition-all group flex flex-col justify-between">
            <div>
              <div class="relative h-48 overflow-hidden bg-slate-100">
                <img src="https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=800&q=80"
                  alt="Artikel Pemantauan Rutin Tanaman"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  loading="lazy">
                <div class="absolute top-3.5 left-3.5">
                  <span
                    class="px-3 py-1 rounded-full text-xs font-semibold bg-white/95 backdrop-blur text-emerald-800 shadow-xs">
                    Artikel
                  </span>
                </div>
              </div>
              <div class="p-6">
                <h3
                  class="text-lg font-bold text-slate-900 group-hover:text-pawon-700 transition-colors mb-2.5 leading-snug">
                  Artikel: Pemantauan Rutin dan Deteksi Dini Kondisi Tanaman di Lapangan
                </h3>
                <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed mb-4">
                  Pentingnya mencatat aktivitas dan memantau kondisi tanaman secara berkala agar pengurus dan anggota kelompok dapat mengambil tindakan tepat.
                </p>
              </div>
            </div>
            <div class="px-6 pb-6 pt-0">
              <a href="#"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-pawon-700 hover:text-pawon-800">
                <span>Baca Selengkapnya</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </article>

        </div>

      </div>
    </section>

    <!-- ==================== 9. SECTION: CTA BANNER ==================== -->
    <section id="kontak" class="py-16 bg-gradient-to-b from-white to-softblue/60 relative">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

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
              SISTEM INFORMASI KELOMPOK TANI
            </div>

            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
              Bersama PawonTani, <br class="hidden sm:inline">
              Pertanian Lebih Terorganisir
            </h2>

            <p class="text-base sm:text-lg text-slate-600 max-w-xl mx-auto leading-relaxed">
              Bergabung dalam ekosistem digital untuk membantu kelompok tani mengelola kegiatan pertanian dengan lebih mudah dan terorganisir.
            </p>

            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
              <a href="javascript:void(0)" onclick="openRegistrationModal()"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 text-base font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow-lg shadow-pawon-700/25 transition-all hover:scale-105">
                <span>Daftar untuk Kelompok</span>
                <span aria-hidden="true">&rarr;</span>
              </a>
              <a href="https://wa.me/" target="_blank" rel="noopener noreferrer"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-bold text-emerald-900 bg-white hover:bg-slate-50 border border-slate-200 rounded-full transition-all">
                <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.698.077-2.222-.556-1.951-.81-3.21-2.8-3.307-2.931-.097-.132-.782-1.039-.782-1.981 0-.943.493-1.408.669-1.599.176-.192.383-.24.51-.24.127 0 .254.001.365.006.118.006.275-.045.431.328.163.388.552 1.345.6 1.443.048.099.08.216.016.345-.064.129-.096.21-.192.321-.096.112-.202.25-.288.336-.097.096-.198.2-.086.393.112.193.498.823 1.069 1.332.735.654 1.355.857 1.547.953.192.096.304.08.416-.048.112-.129.48-.56.608-.752.128-.193.256-.16.432-.096.176.064 1.119.528 1.312.624.192.096.32.144.368.224.048.08.048.464-.096.869z" />
                </svg>
                <span>Hubungi Tim Konsultan</span>
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
                Mudah digunakan oleh poktan
              </span>
              <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-pawon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  stroke-width="3">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                Data anggota & lahan aman
              </span>
              <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-pawon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  stroke-width="3">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                Pendampingan sistem langsung
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
            Sistem informasi digital yang dirancang khusus untuk membantu kelompok tani mengelola anggota, lahan, aktivitas pertanian, panen, hingga penjualan secara terpadu.
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
            <li><a href="#tentang" class="hover:text-pawon-700 transition-colors">Data Anggota</a></li>
            <li><a href="#tentang" class="hover:text-pawon-700 transition-colors">Pengelolaan Lahan</a></li>
            <li><a href="#tentang" class="hover:text-pawon-700 transition-colors">Musim Tanam</a></li>
            <li><a href="#tentang" class="hover:text-pawon-700 transition-colors">Aktivitas & Kondisi</a></li>
            <li><a href="#tentang" class="hover:text-pawon-700 transition-colors">Panen & Penjualan</a></li>
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