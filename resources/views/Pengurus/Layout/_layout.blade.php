@if(request()->header('X-Pengurus-Partial') === 'true')
  @yield('content')
@else
<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="{{ asset('images/Logo2.png') }}">
  <title>@yield('title', 'Pengurus') - PawonTani</title>

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
      background-color: #F5F8F1;
    }

    /* Custom scrollbar for clean administrative tables */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: #F5F8F1;
    }
    ::-webkit-scrollbar-thumb {
      background: #C5DFB0;
      border-radius: 9999px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #9AB880;
    }
  </style>

  @stack('styles')
</head>

<body class="min-h-full flex bg-[#F5F8F1] text-slate-800 antialiased selection:bg-[#C5DFB0] selection:text-[#1A2D10]">

  <!-- Include Sidebar (230px wide) -->
  @include('Pengurus.Layout._sidebar')

  <!-- Main Content Wrapper (shifted by 230px on desktop lg:ml-[230px]) -->
  <div class="pengurus-shell-content flex-1 flex flex-col min-w-0 min-h-screen">

    <!-- Include Header (64px high) -->
    @include('Pengurus.Layout._header')

    <!-- Main Page Body -->
    <div class="relative flex-1 min-h-0">
      <main id="pengurus-content" data-pengurus-content class="h-full min-w-0 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
        @yield('content')
      </main>

      <div id="pengurus-loading" class="hidden fixed inset-0 z-[100] items-center justify-center bg-[#F5F8F1]/80 backdrop-blur-[2px] transition-opacity duration-200">
        <div class="flex items-center gap-3 rounded-2xl border border-[#E4F0D6] bg-white px-5 py-3.5 text-sm font-semibold text-[#4A6030] shadow-lg">
          <span class="h-5 w-5 animate-spin rounded-full border-2 border-[#C5DFB0] border-t-[#4D9830]"></span>
          <span>Memuat halaman...</span>
        </div>
      </div>
    </div>

  </div>

  <!-- Global Pengurus Scripts -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // 1. Initialize Lucide Icons
      if (window.lucide) {
        window.lucide.createIcons();
      }

      // 2. Mobile Sidebar Drawer Logic
      const sidebar = document.getElementById('sidebar');
      const backdrop = document.getElementById('sidebar-backdrop');
      const openBtn = document.getElementById('sidebar-open-btn');
      const closeBtn = document.getElementById('sidebar-close-btn');

      function openSidebar() {
        if (!sidebar || !backdrop) return;
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
      }

      function closeSidebar() {
        if (!sidebar || !backdrop) return;
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
      }

      if (openBtn) openBtn.addEventListener('click', openSidebar);
      if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
      if (backdrop) backdrop.addEventListener('click', closeSidebar);

      document.querySelectorAll('[data-dropdown-trigger]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
          event.stopPropagation();
          const dropdown = document.getElementById(trigger.dataset.dropdownTrigger);
          if (!dropdown) return;

          document.querySelectorAll('[data-header-dropdown] > div[id]').forEach((item) => {
            if (item !== dropdown) item.classList.add('hidden');
          });
          dropdown.classList.toggle('hidden');
        });
      });

      document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-header-dropdown]')) {
          document.querySelectorAll('[data-header-dropdown] > div[id]').forEach((dropdown) => {
            dropdown.classList.add('hidden');
          });
        }
      });

      const pengurusContent = document.querySelector('[data-pengurus-content]');
      const pengurusLoading = document.getElementById('pengurus-loading');
      const pengurusLinks = document.querySelectorAll('#sidebar nav a[href], header a[href]');

      function updatePengurusNavigation(url) {
        const currentPath = new URL(url, window.location.origin).pathname;
        let activeLabel = 'Pengurus';

        document.querySelectorAll('#sidebar nav a[href]').forEach((link) => {
          if (link.getAttribute('href') === '#') return;
          const linkPath = new URL(link.href, window.location.origin).pathname;
          const isActive = linkPath === currentPath;

          link.classList.toggle('bg-[#EBF6E0]', isActive);
          link.classList.toggle('text-[#4D9830]', isActive);
          link.classList.toggle('font-semibold', isActive);
          link.classList.toggle('text-[#4A6030]', !isActive);

          const icon = link.querySelector('[data-lucide]');
          if (icon) {
            icon.classList.toggle('text-[#4D9830]', isActive);
            icon.classList.toggle('text-[#4A6030]', !isActive);
            icon.classList.toggle('group-hover:text-[#1A2D10]', !isActive);
          }

          if (isActive) {
            activeLabel = link.querySelector('span')?.textContent.trim() || link.textContent.trim() || activeLabel;
          }
        });

        const currentPengurusLink = Array.from(document.querySelectorAll('#sidebar nav a[href], header a[href]')).find((link) => {
          if (link.getAttribute('href') === '#') return false;
          return new URL(link.href, window.location.origin).pathname === currentPath;
        });
        if (currentPengurusLink?.dataset.pengurusTitle) {
          activeLabel = currentPengurusLink.dataset.pengurusTitle;
        }

        const pageTitle = document.querySelector('[data-pengurus-page-title]');
        if (pageTitle) pageTitle.textContent = activeLabel;
        document.title = `${activeLabel} - PawonTani`;
      }

      function runContentScripts() {
        if (!pengurusContent) return;
        pengurusContent.querySelectorAll('script').forEach((oldScript) => {
          const newScript = document.createElement('script');
          Array.from(oldScript.attributes).forEach((attribute) => {
            newScript.setAttribute(attribute.name, attribute.value);
          });
          newScript.textContent = oldScript.textContent;
          oldScript.replaceWith(newScript);
        });
        if (window.lucide) window.lucide.createIcons();
      }

      async function loadPengurusContent(url, addHistory = true) {
        if (!pengurusContent) return;
        pengurusContent.classList.add('opacity-60', 'pointer-events-none');
        if (pengurusLoading) {
          pengurusLoading.classList.remove('hidden');
          pengurusLoading.classList.add('flex');
        }

        try {
          const response = await fetch(url, {
            headers: {
              'X-Pengurus-Partial': 'true',
              'Accept': 'text/html'
            }
          });

          if (!response.ok) throw new Error(`HTTP ${response.status}`);
          pengurusContent.innerHTML = await response.text();
          runContentScripts();
          updatePengurusNavigation(url);
          if (addHistory) window.history.pushState({}, '', url);
        } catch (error) {
          window.location.assign(url);
        } finally {
          pengurusContent.classList.remove('opacity-60', 'pointer-events-none');
          if (pengurusLoading) {
            pengurusLoading.classList.add('hidden');
            pengurusLoading.classList.remove('flex');
          }
        }
      }

      pengurusLinks.forEach((link) => {
        link.addEventListener('click', (event) => {
          if (link.getAttribute('href') === '#') return;
          const url = new URL(link.href, window.location.origin);
          if (url.origin !== window.location.origin || !url.pathname.startsWith('/pengurus/')) return;
          event.preventDefault();
          loadPengurusContent(url.href);
          document.getElementById('sidebar-backdrop')?.classList.add('hidden');
          document.getElementById('sidebar')?.classList.add('-translate-x-full');
        });
      });

      window.addEventListener('popstate', () => loadPengurusContent(window.location.href, false));
      updatePengurusNavigation(window.location.href);
    });
  </script>

  @stack('scripts')
</body>

</html>
@endif
