<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="description" content="Artikel edukasi pertanian PawonTani"><link rel="icon" type="image/png" href="{{ asset('images/Logo2.png') }}"><title>@yield('title', 'Artikel') - PawonTani</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest" defer></script>
</head>
<body class="min-h-screen bg-[#F5F8F1] font-sans text-slate-800 antialiased">
  <header class="sticky top-0 z-30 border-b border-[#E4F0D6] bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"><a href="{{ route('home') }}" class="flex items-center gap-2"><img src="{{ asset('images/Logo2.png') }}" alt="PawonTani" class="h-9 w-9"><span class="text-lg font-extrabold text-[#1A2D10]">Pawon<span class="text-[#4D9830]">Tani</span></span></a><a href="{{ route('artikel.index') }}" class="rounded-xl bg-[#EBF6E0] px-3 py-2 text-xs font-bold text-[#4D9830]">Artikel</a></div>
  </header>
  <main>@yield('content')</main>
  <footer class="mt-16 border-t border-[#E4F0D6] bg-white"><div class="mx-auto max-w-7xl px-4 py-7 text-center text-xs text-[#6B7F5B] sm:px-6 lg:px-8">Informasi pertanian untuk ekosistem PawonTani.</div></footer>
  <script>document.addEventListener('DOMContentLoaded',()=>{if(window.lucide)window.lucide.createIcons();});</script>
</body>
</html>
