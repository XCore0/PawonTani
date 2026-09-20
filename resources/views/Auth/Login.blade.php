<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">
  <title>Masuk ke PawonTani - Sistem Informasi Kelompok Tani</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest" defer></script>


  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: linear-gradient(135deg, #EBF6E0 0%, #F5F8F1 50%, #FDF9F0 100%);
      min-height: 100vh;
    }

    /* Custom drop shadow matching SVG filter0_d_92_2 (dy=8, blur=20, #4D9830 at 12% opacity) */
    .svg-card-shadow {
      box-shadow: 0 8px 30px rgba(77, 152, 48, 0.12), 0 2px 8px rgba(77, 152, 48, 0.04);
    }
  </style>
</head>

<body class="min-h-screen flex flex-col justify-center items-center py-8 sm:py-12 px-4 relative overflow-x-hidden selection:bg-[#C5DFB0] selection:text-[#1A2D10]">

  <!-- Atmospheric Background Ambient Elements (from SVG design) -->
  <div class="fixed top-[-100px] right-[-60px] w-[400px] h-[400px] rounded-full bg-[#4D9830]/[0.06] blur-3xl pointer-events-none -z-10"></div>
  <div class="fixed bottom-[-60px] left-[-60px] w-[320px] h-[320px] rounded-full bg-[#72BE4A]/[0.08] blur-3xl pointer-events-none -z-10"></div>

  <!-- Main Container (max-width 440px matching SVG width=440) -->
  <div class="w-full max-w-[440px] flex flex-col items-center">

    <!-- Top Return to Home Button -->
    <div class="w-full mb-3 px-1 flex items-center justify-between">
      <a href="{{ route('home') }}"
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#4D9830] hover:text-[#3D8024] transition-all hover:-translate-x-0.5 group">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-0.5"></i>
        <span>Kembali ke Beranda</span>
      </a>
      <span class="text-[11px] font-medium text-[#9AB880]">PawonTani v1.0</span>
    </div>

    <!-- Login Card (width: 440px, rounded-3xl / 20px, border #E4F0D6, white bg) -->
    <div class="w-full bg-white rounded-[20px] border border-[#E4F0D6] svg-card-shadow px-8 py-9 sm:px-10 sm:py-10 relative">

      <!-- Logo Section (Emblem photo uploaded by user) -->
      <div class="flex flex-col items-center text-center">
        <div class="relative group mb-3">
          <!-- Soft hover glow -->
          <div class="absolute -inset-1.5 rounded-full bg-gradient-to-tr from-[#72BE4A]/20 to-[#4D9830]/20 blur-md opacity-70 group-hover:opacity-100 transition-opacity"></div>
          
          <!-- Logo Image (Crisp, large and unobstructed) -->
          <img src="{{ asset('images/Logo.png') }}" alt="PawonTani Logo"
            class="relative w-20 h-20 sm:w-24 sm:h-24 object-contain drop-shadow-sm transition-transform duration-300 group-hover:scale-105">
        </div>

        <!-- Headline & Subtitle (from SVG) -->
        <h1 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#1A2D10]">
          Masuk ke PawonTani
        </h1>
        <p class="text-xs sm:text-[13px] font-normal text-[#9AB880] mt-1">
          Sistem Informasi Kelompok Tani
        </p>
      </div>

      <!-- Optional Flash Notification Message -->
      @if(session('info'))
        <div class="mt-4 p-3 rounded-lg bg-[#EBF6E0] border border-[#C5DFB0] text-[#1A2D10] text-xs flex items-center gap-2">
          <i data-lucide="info" class="w-4 h-4 text-[#4D9830] shrink-0"></i>
          <span>{{ session('info') }}</span>
        </div>
      @endif

      <!-- Error Messages -->
      @if($errors->any())
        <div class="mt-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-xs flex items-start gap-2">
          <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0 mt-0.5"></i>
          <div>
            @foreach($errors->all() as $error)
              <p>{{ $error }}</p>
            @endforeach
          </div>
        </div>
      @endif

      <!-- Form Section -->
      <form id="login-form" method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <!-- Email / Username Input (from SVG: label with red asterisk, rounded-lg border #C5DFB0) -->
        <div>
          <label for="login" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">
            Email / Username <span class="text-[#DC2626]">*</span>
          </label>
          <input type="text" id="login" name="login" required autocomplete="username"
            placeholder="Masukkan email atau username"
            class="w-full h-[44px] px-3.5 rounded-lg border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] text-xs sm:text-sm focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
        </div>

        <!-- Password Input (from SVG: label with red asterisk, eye toggle icon, rounded-lg border #C5DFB0) -->
        <div>
          <label for="password" class="block text-xs font-semibold text-[#1A2D10] mb-1.5">
            Password <span class="text-[#DC2626]">*</span>
          </label>
          <div class="relative">
            <input type="password" id="password" name="password" required autocomplete="current-password"
              placeholder="Masukkan password"
              class="w-full h-[44px] px-3.5 pr-10 rounded-lg border border-[#C5DFB0] bg-white text-slate-800 placeholder-[#9AB880] text-xs sm:text-sm focus:outline-none focus:border-[#4D9830] focus:ring-2 focus:ring-[#4D9830]/20 transition-all">
            
            <!-- Eye Toggle Button -->
            <button type="button" id="toggle-password"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-[#9AB880] hover:text-[#4D9830] focus:outline-none transition-colors p-1"
              aria-label="Tampilkan / Sembunyikan Password">
              <i data-lucide="eye" id="eye-icon" class="w-4 h-4"></i>
              <i data-lucide="eye-off" id="eye-off-icon" class="w-4 h-4 hidden"></i>
            </button>
          </div>
        </div>

        <!-- Options: Ingat saya & Lupa Password? (from SVG) -->
        <div class="flex items-center justify-between pt-0.5">
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" name="remember" id="remember"
              class="w-3.5 h-3.5 rounded border-[#C5DFB0] text-[#4D9830] accent-[#4D9830] focus:ring-[#4D9830]/30 cursor-pointer">
            <span class="text-xs font-medium text-[#4A6030]">Ingat saya</span>
          </label>

          <a href="#" onclick="alert('Silakan hubungi Pengurus / Admin Kelompok Tani Anda untuk mereset kata sandi.'); return false;"
            class="text-xs font-semibold text-[#4D9830] hover:text-[#3D8024] hover:underline transition-colors">
            Lupa Password?
          </a>
        </div>

        <!-- Submit Button: Masuk (from SVG: bg #4D9830, rounded-lg, height ~46px, text white font-bold) -->
        <div class="pt-2">
          <button type="submit" id="submit-btn"
            class="w-full h-[46px] rounded-lg bg-[#4D9830] hover:bg-[#3D8024] active:scale-[0.99] text-white font-bold text-sm shadow-sm hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer">
            <span>Masuk</span>
          </button>
        </div>
      </form>

      <!-- Bottom Separator & Copyright Line (exactly from SVG) -->
      <div class="mt-8 pt-5 border-t border-[#E4F0D6] text-center">
        <p class="text-[11px] text-[#9AB880] tracking-wide">
          &copy; 2024 PawonTani. Sistem Informasi Kelompok Tani.
        </p>
      </div>

    </div>

    <!-- Sub-footer Support Link -->
    <div class="mt-4 text-center">
      <p class="text-xs text-[#7A9860]">
        Belum terdaftar?
        <a href="{{ route('home') }}#kontak" class="font-semibold text-[#4D9830] hover:text-[#3D8024] hover:underline">
          Hubungi Pengurus Kelompok Tani
        </a>
      </p>
    </div>

  </div>

  <!-- Client-side script for Lucide Icons & Password Toggle -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Render Lucide Icons
      if (window.lucide) {
        window.lucide.createIcons();
      }

      // Password visibility toggle logic
      const toggleBtn = document.getElementById('toggle-password');
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eye-icon');
      const eyeOffIcon = document.getElementById('eye-off-icon');

      if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function(e) {
          e.preventDefault();
          const isPassword = passwordInput.getAttribute('type') === 'password';
          passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
          
          if (isPassword) {
            eyeIcon.classList.add('hidden');
            eyeOffIcon.classList.remove('hidden');
          } else {
            eyeIcon.classList.remove('hidden');
            eyeOffIcon.classList.add('hidden');
          }
        });
      }

      // Form submission feedback
      const loginForm = document.getElementById('login-form');
      const submitBtn = document.getElementById('submit-btn');

      if (loginForm && submitBtn) {
        loginForm.addEventListener('submit', function() {
          submitBtn.disabled = true;
          submitBtn.innerHTML = `
            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Memproses...</span>
          `;
        });
      }
    });
  </script>

</body>

</html>
