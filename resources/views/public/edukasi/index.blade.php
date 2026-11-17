@php
    $description = match ($type) {
        'Semua Edukasi' => 'Jelajahi seluruh tips, panduan, dan artikel pertanian dalam satu halaman.',
        'Tips' => 'Kumpulan tips praktis untuk membantu kegiatan pertanian sehari-hari.',
        'Artikel' => 'Baca artikel dan wawasan seputar pengelolaan pertanian.',
        default => 'Temukan panduan lengkap untuk mendukung kegiatan dan pengelolaan pertanian.',
    };

    $pageTitle = $type === 'Semua Edukasi' ? 'Semua Edukasi Pertanian' : 'Daftar ' . $type;

    $tabs = [
        ['label' => 'Semua Edukasi', 'url' => route('edukasi.semua'), 'active' => $type === 'Semua Edukasi'],
        ['label' => 'Tips', 'url' => route('edukasi.tips'), 'active' => $type === 'Tips'],
        ['label' => 'Panduan', 'url' => route('edukasi.panduan'), 'active' => $type === 'Panduan'],
        ['label' => 'Artikel', 'url' => route('edukasi.artikel'), 'active' => $type === 'Artikel'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }} - PawonTani</title>
    <meta name="description" content="{{ $description }}">
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=2.0">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .education-tab { display:inline-flex; align-items:center; justify-content:center; padding:.65rem 1rem; border:1px solid #e2e8f0; border-radius:999px; color:#475569; background:#fff; font-size:.82rem; font-weight:700; transition:all .2s ease; }
        .education-tab:hover { border-color:#86efac; color:#166534; background:#f0fdf4; }
        .education-tab.active { border-color:#15803d; background:#15803d; color:#fff; box-shadow:0 6px 14px rgba(21,128,61,.16); }
        .education-card { display:flex; flex-direction:column; min-width:0; overflow:hidden; border:1px solid #e2e8f0; border-radius:1.25rem; background:#fff; box-shadow:0 2px 8px rgba(15,23,42,.035); transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease; }
        .education-card:hover { transform:translateY(-3px); border-color:#bbf7d0; box-shadow:0 14px 28px rgba(15,23,42,.08); }
        .education-card-image { width:100%; height:190px; object-fit:cover; background:#f1f5f9; }
        .education-card-body { display:flex; flex:1; flex-direction:column; padding:1.25rem; }
        .education-card-summary { display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
        .education-empty { border:1px dashed #cbd5e1; border-radius:1.5rem; background:#f8fafc; }
        .education-count { display:inline-flex; align-items:center; gap:.55rem; border:1px solid #dcfce7; border-radius:9999px; background:#f0fdf4; padding:.7rem 1rem; color:#166534; font-size:.82rem; font-weight:800; white-space:nowrap; }
        .education-count-dot { width:.5rem; height:.5rem; border-radius:9999px; background:#22c55e; box-shadow:0 0 0 4px #dcfce7; }
        .education-filter-bar { display:flex; flex-direction:column; gap:1rem; margin-top:2rem; padding:1.25rem 0; border-top:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0; }
        .education-filter-label { color:#64748b; font-size:.75rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .education-tabs { display:flex; flex-wrap:wrap; gap:.65rem; }
        .education-return-row { display:flex; justify-content:flex-end; margin-top:3.5rem; padding-top:1.75rem; border-top:1px solid #e2e8f0; }
        .education-return-button { display:inline-flex; align-items:center; justify-content:center; gap:.75rem; min-height:48px; padding:.7rem 1.15rem .7rem .8rem; border:1px solid #bbf7d0; border-radius:9999px; background:#fff; color:#166534; font-size:.84rem; font-weight:800; text-decoration:none; box-shadow:0 4px 14px rgba(15,23,42,.045); transition:all .2s ease; }
        .education-return-button:hover { transform:translateY(-2px); border-color:#86efac; background:#f0fdf4; color:#14532d; box-shadow:0 10px 22px rgba(21,128,61,.10); }
        .education-return-button:focus-visible { outline:3px solid #bbf7d0; outline-offset:3px; }
        .education-return-icon { display:inline-flex; width:30px; height:30px; align-items:center; justify-content:center; border-radius:9999px; background:#dcfce7; color:#15803d; }
        @media (min-width:640px) { .education-filter-bar { flex-direction:row; align-items:center; justify-content:space-between; gap:1.5rem; } }
        @media (max-width:480px) { .education-return-row { justify-content:stretch; } .education-return-button { width:100%; } }
    </style>
</head>
<body class="min-h-screen bg-white text-slate-800 antialiased">
    <header class="sticky top-0 z-50 border-b border-slate-100 bg-white/95 backdrop-blur-md">
        <div class="mx-auto flex h-18 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8 md:h-20">
            <a href="{{ route('home') }}#beranda" class="flex items-center py-1">
                <img src="{{ asset('images/Logo2.png') }}" alt="PawonTani Logo" class="h-9 w-auto object-contain sm:h-11 md:h-12">
            </a>
            <nav class="hidden items-center space-x-8 text-[15px] md:flex" aria-label="Navigasi utama">
                <a href="{{ route('home') }}#beranda" class="nav-link">Beranda</a>
                <a href="{{ route('home') }}#tentang" class="nav-link">Tentang PawonTani</a>
                <a href="{{ route('home') }}#fitur" class="nav-link">Fitur</a>
                <a href="{{ route('home') }}#cara-kerja" class="nav-link">Cara Kerja</a>
                <a href="{{ route('home') }}#edukasi" class="nav-link active" aria-current="page">Edukasi</a>
            </nav>
            <div class="hidden md:flex">
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full bg-pawon-700 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-pawon-800">Masuk ke Sistem</a>
            </div>
            <button id="mobile-menu-btn" type="button" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 md:hidden" aria-label="Buka Menu Navigasi" aria-controls="mobile-menu" aria-expanded="false">
                <svg id="menu-icon-open" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                <svg id="menu-icon-close" class="hidden h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div id="mobile-menu" class="hidden space-y-2 border-b border-slate-100 bg-white px-4 pb-6 pt-3 shadow-xl md:hidden">
            <a href="{{ route('home') }}#beranda" class="mobile-nav-link">Beranda</a>
            <a href="{{ route('home') }}#tentang" class="mobile-nav-link">Tentang PawonTani</a>
            <a href="{{ route('home') }}#fitur" class="mobile-nav-link">Fitur</a>
            <a href="{{ route('home') }}#cara-kerja" class="mobile-nav-link">Cara Kerja</a>
            <a href="{{ route('home') }}#edukasi" class="mobile-nav-link active">Edukasi</a>
            <a href="{{ route('login') }}" class="mt-2 inline-flex w-full items-center justify-center rounded-full bg-pawon-700 px-5 py-3 text-sm font-bold text-white">Masuk ke Sistem</a>
        </div>
    </header>

    <main>
        <section class="relative overflow-hidden bg-gradient-to-b from-green-50 via-white to-white pb-10 pt-10 sm:pt-14">
            <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-emerald-100/60 blur-3xl"></div>
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <nav aria-label="Breadcrumb" class="mb-6 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500 sm:text-sm">
                    <a href="{{ route('home') }}" class="transition hover:text-green-700">Beranda</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('home') }}#edukasi" class="transition hover:text-green-700">Edukasi Pertanian</a>
                    <span aria-hidden="true">/</span>
                    <span class="text-green-700">{{ $type }}</span>
                </nav>
                <div class="mt-2 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between lg:gap-10">
                    <div class="max-w-2xl">
                        <span class="badge-pill">Pusat Edukasi</span>
                        <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ $pageTitle }}</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">{{ $description }}</p>
                    </div>
                    <div class="lg:shrink-0 lg:pt-8">
                        <span class="education-count">
                            <span class="education-count-dot" aria-hidden="true"></span>
                            {{ $items->count() }} {{ $items->count() === 1 ? 'konten tersedia' : 'konten tersedia' }}
                        </span>
                    </div>
                </div>
                <div class="education-filter-bar" aria-label="Navigasi kategori edukasi">
                    <span class="education-filter-label">Jelajahi kategori</span>
                    <nav class="education-tabs" aria-label="Pilih kategori edukasi">
                        @foreach($tabs as $tab)
                            <a href="{{ $tab['url'] }}" class="education-tab {{ $tab['active'] ? 'active' : '' }}" @if($tab['active']) aria-current="page" @endif>{{ $tab['label'] }}</a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </section>

        <section class="pb-16 pt-5 sm:pb-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @if($items->isNotEmpty())
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($items as $item)
                            @php
                                $cardType = $type === 'Semua Edukasi' ? ($item->education_type ?? 'Panduan') : $type;
                                $sourceImage = $item->gambar ?? null;
                                if (blank($sourceImage)) {
                                    $imageUrl = match ($cardType) {
                                        'Tips' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=900&q=80',
                                        'Artikel' => 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=900&q=80',
                                        default => 'https://images.unsplash.com/photo-1592982537447-7440770cbfc9?auto=format&fit=crop&w=900&q=80',
                                    };
                                } elseif (\Illuminate\Support\Str::startsWith($sourceImage, ['http://', 'https://'])) {
                                    $imageUrl = $sourceImage;
                                } elseif (\Illuminate\Support\Str::startsWith($sourceImage, ['storage/', 'images/', 'uploads/'])) {
                                    $imageUrl = asset($sourceImage);
                                } else {
                                    $imageUrl = asset('storage/' . $sourceImage);
                                }
                                $detailUrl = match ($cardType) {
                                    'Tips' => route('tips.show', $item->id_tips),
                                    'Artikel' => route('artikel.show', $item->id_artikel),
                                    default => route('panduan.show', $item->slug),
                                };
                            @endphp
                            <article class="education-card">
                                <div class="relative overflow-hidden">
                                    <img src="{{ $imageUrl }}" alt="{{ $item->judul }}" class="education-card-image" loading="lazy">
                                    <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-green-800 shadow-sm">{{ $cardType }}</span>
                                </div>
                                <div class="education-card-body">
                                    <div class="mb-3 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        @if($item->kategori)
                                            <span class="rounded-full bg-green-50 px-2.5 py-1 font-semibold text-green-800">{{ $item->kategori }}</span>
                                        @endif
                                        @if($item->tanggal)
                                            <span>{{ $item->tanggal->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                    <h2 class="text-lg font-extrabold leading-snug text-slate-900">{{ $item->judul }}</h2>
                                    @if($item->ringkasan)
                                        <p class="education-card-summary mt-3 text-sm leading-6 text-slate-600">{{ $item->ringkasan }}</p>
                                    @else
                                        <p class="education-card-summary mt-3 text-sm leading-6 text-slate-500">Buka konten ini untuk membaca informasi selengkapnya.</p>
                                    @endif
                                    @if($item->komoditas?->nama_komoditas)
                                        <p class="mt-3 text-xs font-semibold text-slate-500">Komoditas: <span class="text-slate-700">{{ $item->komoditas->nama_komoditas }}</span></p>
                                    @endif
                                    <a href="{{ $detailUrl }}" class="mt-auto inline-flex items-center gap-2 pt-6 text-sm font-extrabold text-pawon-700 transition hover:text-pawon-800">
                                        Baca selengkapnya
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="education-empty flex flex-col items-center px-5 py-16 text-center sm:py-20">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-green-100 text-green-700">
                            @if($type === 'Tips')
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 18h6m-5 4h4m-2-20a7 7 0 0 0-4 12.75c.7.5 1 1.1 1 1.75h6c0-.65.3-1.25 1-1.75A7 7 0 0 0 12 2Z" /></svg>
                            @elseif($type === 'Artikel')
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3h8l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm8 0v5h5M9 13h6m-6 4h6" /></svg>
                            @else
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21V5.5Zm0 0V21" /></svg>
                            @endif
                        </div>
                        <h2 class="mt-5 text-xl font-extrabold text-slate-900">{{ $type === 'Semua Edukasi' ? 'Belum ada konten edukasi yang dipublikasikan' : 'Belum ada ' . strtolower($type) . ' yang dipublikasikan' }}</h2>
                        <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">{{ $type === 'Semua Edukasi' ? 'Tips, panduan, dan artikel akan muncul di sini setelah dipublikasikan oleh administrator.' : 'Konten ' . strtolower($type) . ' akan muncul di sini setelah dipublikasikan oleh administrator.' }}</p>
                        <a href="{{ route('home') }}#edukasi" class="mt-6 inline-flex items-center gap-2 rounded-full bg-pawon-700 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-pawon-800">Kembali ke Edukasi</a>
                    </div>
                @endif
                <div class="education-return-row">
                    <a href="{{ route('home') }}#edukasi" class="education-return-button" aria-label="Kembali ke Pusat Edukasi PawonTani">
                        <span class="education-return-icon" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 18l-6-6 6-6" /></svg>
                        </span>
                        <span>Kembali ke Pusat Edukasi</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-100 bg-white py-6 text-center text-xs text-slate-500">
        © {{ date('Y') }} PawonTani. Sistem Informasi Kelompok Tani.
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuButton = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const iconOpen = document.getElementById('menu-icon-open');
            const iconClose = document.getElementById('menu-icon-close');
            if (!menuButton || !mobileMenu || !iconOpen || !iconClose) return;
            function closeMenu() {
                mobileMenu.classList.add('hidden');
                iconOpen.classList.remove('hidden');
                iconClose.classList.add('hidden');
                menuButton.setAttribute('aria-expanded', 'false');
            }
            menuButton.addEventListener('click', function () {
                const isOpening = mobileMenu.classList.contains('hidden');
                mobileMenu.classList.toggle('hidden');
                iconOpen.classList.toggle('hidden');
                iconClose.classList.toggle('hidden');
                menuButton.setAttribute('aria-expanded', isOpening ? 'true' : 'false');
            });
            mobileMenu.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
        });
    </script>
</body>
</html>
