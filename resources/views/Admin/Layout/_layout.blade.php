@if(request()->header('X-Admin-Partial') === 'true')
  @yield('content')
@else
<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="{{ asset('images/Logo2.png') }}">
  <title>@yield('title', 'Admin') - PawonTani</title>

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
  @include('Admin.Layout._sidebar')

  <!-- Main Content Wrapper (shifted by 230px on desktop lg:ml-[230px]) -->
  <div class="flex-1 flex flex-col min-w-0 lg:ml-[230px] min-h-screen">

    <!-- Include Header (64px high) -->
    @include('Admin.Layout._header')

    <!-- Main Page Body -->
    <div class="relative flex-1 min-h-0">
      <main id="admin-content" data-admin-content class="h-full p-4 sm:p-6 lg:p-8 overflow-y-auto">
        @yield('content')
      </main>

      <div id="admin-loading" class="hidden fixed inset-0 z-[100] items-center justify-center bg-[#F5F8F1]/80 backdrop-blur-[2px] transition-opacity duration-200">
        <div class="flex items-center gap-3 rounded-2xl border border-[#E4F0D6] bg-white px-5 py-3.5 text-sm font-semibold text-[#4A6030] shadow-lg">
          <span class="h-5 w-5 animate-spin rounded-full border-2 border-[#C5DFB0] border-t-[#4D9830]"></span>
          <span>Memuat halaman...</span>
        </div>
      </div>
    </div>

  </div>

  <!-- Global Admin Scripts -->
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

      const adminContent = document.querySelector('[data-admin-content]');
      const adminLoading = document.getElementById('admin-loading');
      const adminLinks = document.querySelectorAll('#sidebar a[href], header a[href]');

      function updateAdminNavigation(url) {
        const currentPath = new URL(url, window.location.origin).pathname;
        let activeLabel = 'Pengurus';

        document.querySelectorAll('#sidebar a[href]').forEach((link) => {
          const linkPath = new URL(link.href, window.location.origin).pathname;
          const isActive = linkPath === currentPath;
          const marker = link.querySelector(':scope > div.absolute');

          link.classList.toggle('bg-[#EBF6E0]', isActive);
          link.classList.toggle('text-[#4D9830]', isActive);
          link.classList.toggle('font-semibold', isActive);
          link.classList.toggle('text-[#4A6030]', !isActive);

          if (isActive) {
            activeLabel = link.querySelector('span')?.textContent.trim() || activeLabel;
            if (!marker) {
              const activeMarker = document.createElement('div');
              activeMarker.className = 'absolute -left-3 top-1.5 bottom-1.5 w-1 rounded-r-md bg-[#4D9830]';
              link.classList.add('relative');
              link.prepend(activeMarker);
            }
          } else if (marker) {
            marker.remove();
          }
        });

        const pageTitle = document.querySelector('[data-admin-page-title]');
        if (pageTitle) pageTitle.textContent = activeLabel;
        document.title = `${activeLabel} - PawonTani`;
      }

      function runContentScripts() {
        if (!adminContent) return;
        adminContent.querySelectorAll('script').forEach((oldScript) => {
          const newScript = document.createElement('script');
          Array.from(oldScript.attributes).forEach((attribute) => {
            newScript.setAttribute(attribute.name, attribute.value);
          });
          newScript.textContent = oldScript.textContent;
          oldScript.replaceWith(newScript);
        });
        if (window.lucide) window.lucide.createIcons();
      }

      async function loadAdminContent(url, addHistory = true) {
        if (!adminContent) return;
        adminContent.classList.add('opacity-60', 'pointer-events-none');
        if (adminLoading) {
          adminLoading.classList.remove('hidden');
          adminLoading.classList.add('flex');
        }

        try {
          const response = await fetch(url, {
            headers: {
              'X-Admin-Partial': 'true',
              'Accept': 'text/html'
            }
          });

          if (!response.ok) throw new Error(`HTTP ${response.status}`);
          adminContent.innerHTML = await response.text();
          runContentScripts();
          updateAdminNavigation(url);
          if (addHistory) window.history.pushState({}, '', url);
        } catch (error) {
          window.location.assign(url);
        } finally {
          adminContent.classList.remove('opacity-60', 'pointer-events-none');
          if (adminLoading) {
            adminLoading.classList.add('hidden');
            adminLoading.classList.remove('flex');
          }
        }
      }

      adminLinks.forEach((link) => {
        link.addEventListener('click', (event) => {
          if (link.getAttribute('href') === '#') return;
          const url = new URL(link.href, window.location.origin);
          if (url.origin !== window.location.origin || !url.pathname.startsWith('/admin/')) return;
          event.preventDefault();
          loadAdminContent(url.href);
          document.getElementById('sidebar-backdrop')?.classList.add('hidden');
          document.getElementById('sidebar')?.classList.add('-translate-x-full');
        });
      });

      window.addEventListener('popstate', () => loadAdminContent(window.location.href, false));
      updateAdminNavigation(window.location.href);
    });
  </script>

  @stack('scripts')
</body>

</html>
@endif
