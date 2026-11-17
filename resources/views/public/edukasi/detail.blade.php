@php
    // Mendukung dua bentuk controller: $item/$type atau $konten/$jenis.
    $detailItem = $item ?? $konten;
    $detailType = $type ?? $jenis;
    $detailListUrl = match ($detailType) {
        'Tips' => route('edukasi.tips'),
        'Artikel' => route('edukasi.artikel'),
        default => route('edukasi.panduan'),
    };

    // Jika controller telah menyiapkan URL gambar, gunakan URL tersebut.
    // Jika belum, siapkan URL dari nilai kolom gambar.
    if (isset($imageUrl) && filled($imageUrl)) {
        $detailImageUrl = $imageUrl;
    } else {
        $sourceImage = $detailItem->gambar ?? null;

        if (blank($sourceImage)) {
            $detailImageUrl = match ($detailType) {
                'Tips' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1400&q=85',
                'Artikel' => 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=1400&q=85',
                default => 'https://images.unsplash.com/photo-1592982537447-7440770cbfc9?auto=format&fit=crop&w=1400&q=85',
            };
        } elseif (\Illuminate\Support\Str::startsWith($sourceImage, ['http://', 'https://'])) {
            $detailImageUrl = $sourceImage;
        } elseif (\Illuminate\Support\Str::startsWith($sourceImage, ['storage/', 'images/', 'uploads/'])) {
            $detailImageUrl = asset($sourceImage);
        } else {
            $detailImageUrl = asset('storage/' . $sourceImage);
        }
    }

    // Gunakan HTML aman dari controller jika tersedia. Jika tidak, bentuk dari isi konten.
    if (isset($contentHtml) && filled($contentHtml)) {
        $detailContentHtml = $contentHtml;
    } elseif (!isset($detailContentHtml)) {
        $sourceContent = filled($detailItem->isi)
            ? $detailItem->isi
            : ($detailItem->ringkasan ?? '');

        $detailContentHtml = \Illuminate\Support\Str::markdown(
            (string) $sourceContent,
            ['html_input' => 'strip']
        );
    }
@endphp

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $detailItem->judul }} - Edukasi PawonTani</title>
    <meta name="description" content="{{ $detailItem->ringkasan }}">
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=2.0">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }


        /* Layout bagian atas: gambar kecil di kiri, informasi di kanan.
           CSS khusus ini memastikan susunan tetap sejajar meskipun utility Tailwind belum ter-refresh. */
        .detail-top-layout {
            display: grid !important;
            grid-template-columns: 112px minmax(0, 1fr) !important;
            align-items: stretch !important;
            gap: 14px !important;
            padding: 26px 20px 0 !important;
        }

        .detail-image-box {
            width: 112px !important;
            height: 112px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            overflow: hidden !important;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #f8fafc;
            box-shadow: 0 2px 8px rgba(15, 23, 42, .05);
        }

        .detail-image-box img {
            display: block !important;
            width: 100% !important;
            height: 100% !important;
            padding: 6px !important;
            object-fit: contain !important;
        }

        .detail-info-card {
            min-width: 0 !important;
            padding: 13px 14px !important;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #f8fafc;
        }

        .detail-body-content {
            padding: 25px 20px 34px !important;
        }

        @media (min-width: 640px) {
            .detail-top-layout {
                grid-template-columns: 148px minmax(0, 1fr) !important;
                gap: 20px !important;
                padding: 32px 32px 0 !important;
            }

            .detail-image-box {
                width: 148px !important;
                height: 138px !important;
                border-radius: 16px;
            }

            .detail-info-card {
                padding: 16px 18px !important;
                border-radius: 16px;
            }

            .detail-body-content {
                padding: 28px 32px 40px !important;
            }
        }

        @media (max-width: 380px) {
            .detail-top-layout {
                grid-template-columns: 94px minmax(0, 1fr) !important;
                gap: 10px !important;
                padding-left: 14px !important;
                padding-right: 14px !important;
            }

            .detail-image-box {
                width: 94px !important;
                height: 104px !important;
            }

            .detail-info-card {
                padding: 10px !important;
            }
        }

        .education-content {
            color: #475569;
            font-size: 1rem;
            line-height: 1.9;
            overflow-wrap: anywhere;
        }

        .education-content h1,
        .education-content h2,
        .education-content h3 {
            color: #0f172a;
            font-weight: 800;
            line-height: 1.35;
            margin: 1.8rem 0 .75rem;
        }

        .education-content h1 { font-size: 1.5rem; }
        .education-content h2 { font-size: 1.3rem; }
        .education-content h3 { font-size: 1.1rem; }

        .education-content p {
            margin: .85rem 0;
        }

        .education-content ul,
        .education-content ol {
            margin: .8rem 0 1.1rem;
            padding-left: 1.5rem;
        }

        .education-content ul { list-style: disc; }
        .education-content ol { list-style: decimal; }

        .education-content li {
            padding-left: .25rem;
            margin: .35rem 0;
        }

        .education-content strong {
            color: #166534;
            font-weight: 700;
        }

        .education-content a {
            color: #15803d;
            text-decoration: underline;
        }

        .education-content blockquote {
            border-left: 3px solid #86efac;
            padding-left: 1rem;
            color: #64748b;
            margin: 1rem 0;
        }

        /* Judul berada di sisi kanan gambar, jadi tidak memakai jarak atas besar. */
        .detail-title {
            margin-top: 0;
            margin-bottom: 1rem;
        }

        /* Tombol kembali memakai gaya yang sama dengan tombol di halaman daftar edukasi. */
        .education-return-button {
            display: inline-flex;
            width: 100%;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            gap: .75rem;
            padding: .7rem 1.15rem .7rem .8rem;
            border: 1px solid #bbf7d0;
            border-radius: 9999px;
            background: #ffffff;
            color: #166534;
            font-size: .84rem;
            font-weight: 800;
            text-decoration: none !important;
            box-shadow: 0 4px 14px rgba(15, 23, 42, .045);
            transition: all .2s ease;
        }

        .education-return-button:hover {
            transform: translateY(-2px);
            border-color: #86efac;
            background: #f0fdf4;
            color: #14532d;
            box-shadow: 0 10px 22px rgba(21, 128, 61, .10);
        }

        .education-return-button:focus-visible {
            outline: 3px solid #bbf7d0;
            outline-offset: 3px;
        }

        .education-return-icon {
            display: inline-flex;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: #dcfce7;
            color: #15803d;
        }

        .education-return-button > svg {
            color: #166534;
        }

        .share-content-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            min-height: 42px;
            padding: .65rem 1rem;
            border: 1px solid #bbf7d0;
            border-radius: 9999px;
            background: #f0fdf4;
            color: #166534;
            font-size: .82rem;
            font-weight: 800;
            cursor: pointer;
            transition: background-color .2s ease, border-color .2s ease, transform .2s ease;
        }

        .share-content-button:hover {
            background: #dcfce7;
            border-color: #86efac;
            transform: translateY(-1px);
        }

        .share-content-button:focus-visible {
            outline: 3px solid #bbf7d0;
            outline-offset: 3px;
        }

        .share-action-group {
            position: relative;
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: flex-end;
        }

        .share-feedback {
            position: absolute;
            top: calc(100% + .3rem);
            right: 0;
            min-height: 0;
            color: #15803d;
            font-size: .75rem;
            font-weight: 700;
            line-height: 1.35;
            white-space: nowrap;
        }

        @media (min-width: 640px) {
            .education-return-button {
                width: auto;
            }
        }

        @media (max-width: 380px) {
            .share-feedback {
                font-size: .68rem;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-white text-slate-800 antialiased selection:bg-pawon-100 selection:text-pawon-900">

    <!-- HEADER: mengikuti header Beranda PawonTani -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100 transition-all duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18 md:h-20">

                <!-- Logo -->
                <a href="{{ route('home') }}#beranda" class="flex items-center group py-1">
                    <img
                        src="{{ asset('images/Logo2.png') }}"
                        alt="PawonTani Logo"
                        class="h-9 sm:h-11 md:h-12 w-auto object-contain hover:opacity-95 transition-opacity">
                </a>

                <!-- Navigasi Desktop -->
                <nav class="hidden md:flex items-center space-x-8 text-[15px]" aria-label="Navigasi utama">
                    <a href="{{ route('home') }}#beranda" class="nav-link">Beranda</a>
                    <a href="{{ route('home') }}#tentang" class="nav-link">Tentang PawonTani</a>
                    <a href="{{ route('home') }}#fitur" class="nav-link">Fitur</a>
                    <a href="{{ route('home') }}#cara-kerja" class="nav-link">Cara Kerja</a>
                    <a href="{{ route('home') }}#edukasi" class="nav-link active" aria-current="page">Edukasi</a>
                </nav>

                <!-- Tombol Masuk Desktop -->
                <div class="hidden md:flex items-center gap-4">
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow-sm hover:shadow-md hover:shadow-pawon-700/25 transition-all hover:translate-y-[-1px]">
                        Masuk ke Sistem
                    </a>
                </div>

                <!-- Tombol Menu Mobile -->
                <div class="flex md:hidden">
                    <button
                        id="mobile-menu-btn"
                        type="button"
                        class="p-2 rounded-lg text-slate-700 hover:text-slate-900 hover:bg-slate-100 focus:outline-none"
                        aria-label="Buka Menu Navigasi"
                        aria-controls="mobile-menu"
                        aria-expanded="false">
                        <svg id="menu-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg id="menu-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Dropdown Menu Mobile -->
        <div id="mobile-menu" class="hidden md:hidden border-b border-slate-100 bg-white px-4 pt-3 pb-6 space-y-2 shadow-xl">
            <a href="{{ route('home') }}#beranda" class="mobile-nav-link">Beranda</a>
            <a href="{{ route('home') }}#tentang" class="mobile-nav-link">Tentang PawonTani</a>
            <a href="{{ route('home') }}#fitur" class="mobile-nav-link">Fitur</a>
            <a href="{{ route('home') }}#cara-kerja" class="mobile-nav-link">Cara Kerja</a>
            <a href="{{ route('home') }}#edukasi" class="mobile-nav-link active" aria-current="page">Edukasi</a>

            <div class="pt-2">
                <a href="{{ route('login') }}"
                    class="w-full inline-flex items-center justify-center px-5 py-3 text-sm font-bold text-white bg-pawon-700 hover:bg-pawon-800 rounded-full shadow">
                    Masuk ke Sistem
                </a>
            </div>
        </div>
    </header>

    <main class="bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-7 sm:pt-10 pb-14 sm:pb-20">

            <!-- Breadcrumb -->
            <nav aria-label="Breadcrumb" class="mb-6 flex flex-wrap items-center gap-2 text-xs sm:text-sm font-medium text-slate-500">
                <a href="{{ route('home') }}" class="transition hover:text-green-700">Beranda</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('home') }}#edukasi" class="transition hover:text-green-700">Edukasi Pertanian</a>
                <span aria-hidden="true">/</span>
                <a href="{{ $detailListUrl }}" class="transition hover:text-green-700">Daftar {{ $detailType }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-green-700">Detail</span>
            </nav>

            <article class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">

                <!-- Bagian atas: gambar kecil di kiri dan informasi konten di kanan -->
                <section class="detail-top-layout">

                    <div class="detail-image-box">
                        <img
                            src="{{ $detailImageUrl }}"
                            alt="{{ $detailItem->judul }}"
                            loading="eager">
                    </div>

                    <div class="detail-info-card">
                        <p class="mb-2.5 text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-500 sm:text-[11px]">
                            Informasi Konten
                        </p>

                        <div class="space-y-2 text-xs sm:text-sm">
                            <div class="flex flex-wrap items-start gap-x-1.5 gap-y-0.5">
                                <span class="font-semibold text-slate-500">Jenis:</span>
                                <span class="font-bold text-green-800">{{ $detailType }}</span>
                            </div>

                            @if($detailItem->kategori)
                                <div class="flex flex-wrap items-start gap-x-1.5 gap-y-0.5">
                                    <span class="font-semibold text-slate-500">Kategori:</span>
                                    <span class="font-bold text-slate-800">{{ $detailItem->kategori }}</span>
                                </div>
                            @endif

                            @if($detailItem->tanggal)
                                <div class="flex flex-wrap items-start gap-x-1.5 gap-y-0.5">
                                    <span class="font-semibold text-slate-500">Tanggal:</span>
                                    <span class="font-bold text-slate-800">{{ $detailItem->tanggal->format('d-m-Y') }}</span>
                                </div>
                            @endif

                            @if($detailItem->komoditas?->nama_komoditas)
                                <div class="flex flex-wrap items-start gap-x-1.5 gap-y-0.5">
                                    <span class="font-semibold text-slate-500">Komoditas:</span>
                                    <span class="font-bold text-slate-800">{{ $detailItem->komoditas->nama_komoditas }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                <!-- Judul dan isi panduan/tips/artikel berada di bawah bagian gambar + informasi -->
                <div class="detail-body-content">

                    <h1 class="detail-title mb-5 text-2xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
                        {{ $detailItem->judul }}
                    </h1>

                    <div class="mb-7 flex flex-wrap items-center justify-between gap-3 sm:gap-5">
                        <p class="text-xs leading-relaxed text-slate-500 sm:text-sm">Bagikan {{ strtolower($detailType) }} ini kepada orang lain.</p>
                        <div class="share-action-group">
                            <button type="button" id="share-content-btn" class="share-content-button" aria-label="Bagikan {{ $detailType }} ini">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <circle cx="18" cy="5" r="3" />
                                    <circle cx="6" cy="12" r="3" />
                                    <circle cx="18" cy="19" r="3" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.7 10.7 6.6-4.4m-6.6 9.4 6.6 2.6" />
                                </svg>
                                <span>Bagikan</span>
                            </button>
                            <span id="share-feedback" class="share-feedback" role="status" aria-live="polite"></span>
                        </div>
                    </div>

                    @if($detailItem->ringkasan)
                        <div class="mb-8 rounded-2xl border-l-4 border-green-600 bg-green-50/80 px-5 py-5 sm:px-6 sm:py-6">
                            <p class="mb-2 text-xs font-extrabold uppercase tracking-[0.12em] text-green-800">
                                Ringkasan
                            </p>
                            <p class="text-sm leading-8 text-slate-600 sm:text-base">
                                {{ $detailItem->ringkasan }}
                            </p>
                        </div>
                    @endif

                    <div class="border-t border-slate-100 pt-7 sm:pt-8">
                        <h2 class="mb-4 text-xl font-extrabold text-slate-900 sm:text-2xl">
                            Isi {{ $detailType }}
                        </h2>

                        <div class="education-content">
                            {!! $detailContentHtml !!}
                        </div>
                    </div>

                    <!-- Navigasi kembali ke daftar sesuai kategori konten -->
                    <div class="mt-14 border-t border-slate-200 pt-8 sm:mt-16 sm:pt-9">
                        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                            <div>
                                <p class="text-sm font-bold text-slate-800">Selesai membaca?</p>
                                <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                    Lihat konten {{ strtolower($detailType) }} lainnya di PawonTani.
                                </p>
                            </div>

                            <a href="{{ $detailListUrl }}"
                                class="education-return-button"
                                aria-label="Kembali ke daftar {{ $detailType }}">
                                <span class="education-return-icon" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 18l-6-6 6-6" />
                                    </svg>
                                </span>
                                <span>Kembali ke Daftar {{ $detailType }}</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                            </a>
                        </div>
                    </div>

                </div>
            </article>
        </div>
    </main>

    <footer class="border-t border-slate-100 bg-white py-6 text-center text-xs text-slate-500">
        © {{ date('Y') }} PawonTani. Sistem Informasi Kelompok Tani.
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const shareButton = document.getElementById('share-content-btn');
            const shareFeedback = document.getElementById('share-feedback');

            async function copyCurrentLink() {
                const pageUrl = window.location.href;

                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(pageUrl);
                    return;
                }

                const temporaryInput = document.createElement('textarea');
                temporaryInput.value = pageUrl;
                temporaryInput.setAttribute('readonly', '');
                temporaryInput.style.position = 'absolute';
                temporaryInput.style.left = '-9999px';
                document.body.appendChild(temporaryInput);
                temporaryInput.select();
                const copied = document.execCommand('copy');
                temporaryInput.remove();

                if (!copied) {
                    throw new Error('Browser tidak mendukung penyalinan tautan.');
                }
            }

            shareButton?.addEventListener('click', async function () {
                const originalLabel = shareButton.innerHTML;
                shareButton.disabled = true;
                shareButton.style.opacity = '0.7';
                if (shareFeedback) shareFeedback.textContent = '';

                try {
                    if (navigator.share) {
                        await navigator.share({
                            title: @json($detailItem->judul),
                            text: @json('Baca ' . $detailType . ' ini di PawonTani'),
                            url: window.location.href
                        });
                        if (shareFeedback) shareFeedback.textContent = 'Menu bagikan berhasil dibuka.';
                    } else {
                        await copyCurrentLink();
                        if (shareFeedback) shareFeedback.textContent = 'Tautan berhasil disalin.';
                    }
                } catch (error) {
                    if (error && error.name === 'AbortError') {
                        // Pengguna menutup menu bagikan; tidak perlu menampilkan pesan error.
                    } else {
                        try {
                            await copyCurrentLink();
                            if (shareFeedback) shareFeedback.textContent = 'Tautan berhasil disalin.';
                        } catch (copyError) {
                            if (shareFeedback) shareFeedback.textContent = 'Tautan belum bisa dibagikan dari browser ini.';
                        }
                    }
                } finally {
                    shareButton.disabled = false;
                    shareButton.style.opacity = '';
                    shareButton.innerHTML = originalLabel;
                }
            });

            const menuButton = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const iconOpen = document.getElementById('menu-icon-open');
            const iconClose = document.getElementById('menu-icon-close');

            if (!menuButton || !mobileMenu || !iconOpen || !iconClose) {
                return;
            }

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

            mobileMenu.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', closeMenu);
            });
        });
    </script>

</body>
</html>
