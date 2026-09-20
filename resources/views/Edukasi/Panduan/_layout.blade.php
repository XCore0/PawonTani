<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Panduan') - PawonTani</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest" defer></script>
</head>
<body class="min-h-full bg-[#F5F8F1] font-sans text-slate-800 antialiased">
  <header class="sticky top-0 z-40 border-b border-[#E4F0D6] bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
      <a href="{{ url('/') }}" class="flex items-center">
        <img src="{{ asset('images/Logo2.png') }}" alt="PawonTani" class="h-9 w-auto">
      </a>
      <nav class="flex items-center gap-2 text-xs font-semibold text-[#4A6030] sm:gap-5">
        <a href="{{ url('/') }}" class="rounded-lg px-2.5 py-2 hover:bg-[#F5F8F1]">Beranda</a>
        <a href="{{ route('edukasi.panduan') }}" class="rounded-lg bg-[#EBF6E0] px-2.5 py-2 text-[#4D9830]">Panduan</a>
      </nav>
    </div>
  </header>

  <main>
    @yield('content')
  </main>

  <footer class="mt-16 border-t border-[#E4F0D6] bg-white">
    <div class="mx-auto max-w-6xl px-4 py-8 text-center text-xs text-[#6B7F5B] sm:px-6">
      PawonTani · Informasi dan panduan pertanian
    </div>
  </footer>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      if (window.lucide) window.lucide.createIcons();
    });
  </script>
</body>
</html>
