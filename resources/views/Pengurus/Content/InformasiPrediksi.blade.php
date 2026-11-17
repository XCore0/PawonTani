@extends('Pengurus.Layout._layout')

@section('title', 'Informasi & Prediksi')

@section('content')
<div class="space-y-6">

  <!-- ==================== HEADER ==================== -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Informasi & Prediksi
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Prakiraan cuaca presisi, analisis harga komoditas pasar, dan estimasi hasil panen poktan
      </p>
    </div>

    @if(!empty($pengurus->latitude) && !empty($pengurus->longitude))
    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-[#E4F0D6] shadow-2xs self-start sm:self-auto">
      <span class="w-2 h-2 rounded-full bg-[#4D9830]"></span>
      <span class="text-xs font-semibold text-[#1A2D10]">Lokasi Terkini:</span>
      <span class="text-xs text-[#4A6030] font-medium max-w-[200px] sm:max-w-xs truncate" title="{{ $pengurus->lokasi_nama ?? 'GPS Aktif' }}">
        {{ $pengurus->lokasi_nama ?? ($pengurus->latitude . ', ' . $pengurus->longitude) }}
      </span>
    </div>
    @endif
  </div>

  <!-- ==================== CUACA CARD (CLEAN & NATURAL THEME) ==================== -->
  <div class="rounded-2xl border border-[#E4F0D6] shadow-2xs overflow-hidden bg-white">
    <!-- Top Action Bar -->
    <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-3 border-b border-[#E4F0D6] bg-white">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] flex items-center justify-center text-[#4D9830]">
          <i data-lucide="map-pin" class="w-4 h-4"></i>
        </div>
        <div>
          <span class="text-[11px] font-semibold text-[#9AB880] uppercase tracking-wider block">Wilayah Pantauan Cuaca</span>
          <span class="text-sm font-bold text-[#1A2D10]">
            {{ $weatherData['_lokasi_nama'] ?? ($pengurus->lokasi_nama ?? 'Belum ada lokasi aktif') }}
          </span>
        </div>
      </div>

      <!-- Tombol GPS -->
      <div class="flex items-center gap-3">
        <div id="lokasi-loading" class="hidden items-center gap-2 text-xs text-[#4D9830] font-semibold">
          <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <span id="lokasi-loading-text">Mencari satelit GPS...</span>
        </div>

        <button type="button" id="btn-gps-lokasi" class="inline-flex items-center gap-2 h-9 px-4 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white text-xs sm:text-sm font-bold shadow-2xs transition cursor-pointer active:scale-95">
          <i data-lucide="crosshair" class="w-4 h-4"></i>
          <span id="btn-gps-text">Gunakan Lokasi Terkini</span>
        </button>
      </div>
    </div>

    <!-- Status Bar GPS Feedback -->
    <div id="gps-status-bar" class="hidden px-5 py-2.5 bg-[#F5F8F1] border-b border-[#E4F0D6] text-xs text-[#2E5A1C] flex items-center justify-between">
      <div class="flex items-center gap-2">
        <i data-lucide="info" class="w-4 h-4 text-[#4D9830] shrink-0"></i>
        <span id="gps-status-msg">Mendeteksi koordinat perangkat...</span>
      </div>
      <button type="button" onclick="document.getElementById('gps-status-bar').classList.add('hidden')" class="text-[#9AB880] hover:text-[#1A2D10] font-bold text-base px-1">
        &times;
      </button>
    </div>

    @if($weatherData)
    @php
      $current = $weatherData['current'] ?? [];
      $daily = $weatherData['daily'] ?? [];
      $currentTemp = round($current['temperature_2m'] ?? 0);
      $currentHumidity = $current['relative_humidity_2m'] ?? null;
      $currentWind = $current['wind_speed_10m'] ?? null;
      $currentWeatherCode = $current['weather_code'] ?? -1;
      $lokasiDisplay = $weatherData['_lokasi_nama'] ?? ($pengurus->lokasi_nama ?? 'Lokasi Terpilih');
      $currentDate = now()->locale('id')->isoFormat('dddd, D MMMM YYYY');

      $wmoDesc = [
        0 => 'Cerah Berawan', 1 => 'Cerah', 2 => 'Cerah Berawan', 3 => 'Berawan Tebal',
        45 => 'Berkabut', 48 => 'Berkabut Tebal', 51 => 'Gerimis Ringan', 53 => 'Gerimis Sedang',
        55 => 'Gerimis Lebat', 61 => 'Hujan Ringan', 63 => 'Hujan Sedang', 65 => 'Hujan Lebat',
        71 => 'Hujan Es Ringan', 73 => 'Hujan Es', 75 => 'Hujan Es Lebat', 80 => 'Hujan Rintik',
        81 => 'Hujan Deras', 82 => 'Hujan Sangat Deras', 95 => 'Hujan Petir', 96 => 'Hujan Badai Petir',
        99 => 'Hujan Badai Ekstrem',
      ];
      $weatherDesc = $wmoDesc[$currentWeatherCode] ?? 'Cerah Berawan';

      $wmoIcon = function($code) {
        if ($code === 0 || $code === 1) return 'sun';
        if ($code === 2 || $code === 3) return 'cloud-sun';
        if (in_array($code, [45, 48])) return 'cloud-fog';
        if (in_array($code, [51, 53, 55, 61, 63, 65, 80, 81, 82])) return 'cloud-rain';
        if (in_array($code, [95, 96, 99])) return 'cloud-lightning';
        return 'cloud';
      };

      $todayPrecip = $daily['precipitation_probability_mean'][0] ?? ($daily['precipitation_probability_max'][0] ?? 0);
      $todayMaxTemp = round($daily['temperature_2m_max'][0] ?? $currentTemp);
      
      if ($todayPrecip >= 60 || in_array($currentWeatherCode, [63, 65, 81, 82, 95, 96, 99])) {
        $recTitle = 'Waspada Hujan & Genangan';
        $recMsg = 'Tunda pemupukan tabur dan penyemprotan pestisida agar tidak tercuci air hujan. Periksa kelancaran saluran drainase sawah.';
        $recBadgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
        $recIcon = 'cloud-rain';
        $recIconColor = 'text-amber-600';
      } elseif ($todayMaxTemp >= 33) {
        $recTitle = 'Suhu Siang Terik';
        $recMsg = 'Optimalkan pengairan irigasi pada pagi atau sore hari untuk mengurangi penguapan air tanah.';
        $recBadgeClass = 'bg-orange-50 text-orange-800 border-orange-200';
        $recIcon = 'sun';
        $recIconColor = 'text-orange-600';
      } elseif ($currentHumidity >= 90) {
        $recTitle = 'Kelembapan Udara Tinggi';
        $recMsg = 'Kondisi lembap berisiko memicu jamur atau blas padi. Lakukan monitoring rutin pada rumpun tanaman.';
        $recBadgeClass = 'bg-blue-50 text-blue-800 border-blue-200';
        $recIcon = 'droplets';
        $recIconColor = 'text-blue-600';
      } else {
        $recTitle = 'Kondisi Cuaca Mendukung';
        $recMsg = 'Waktu sangat baik untuk pemupukan, penyiangan gulma, serta penyemprotan nutrisi daun.';
        $recBadgeClass = 'bg-[#EBF6E0] text-[#2E5A1C] border-[#D1DFC4]';
        $recIcon = 'check-circle';
        $recIconColor = 'text-[#4D9830]';
      }
    @endphp

    <!-- Weather Content Body (Clean White & Natural Accent) -->
    <div class="p-5 sm:p-7">
      <div class="flex flex-col lg:flex-row items-start justify-between gap-6">
        
        <!-- Sisi Kiri: Suhu Saat Ini -->
        <div class="flex items-start gap-4 sm:gap-5">
          <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-[#F5F8F1] border border-[#E4F0D6] flex items-center justify-center text-[#4D9830] shrink-0">
            <i data-lucide="{{ $wmoIcon($currentWeatherCode) }}" class="w-10 h-10 sm:w-12 sm:h-12"></i>
          </div>
          <div>
            <div class="flex items-baseline gap-2">
              <span class="text-4xl sm:text-5xl font-extrabold text-[#1A2D10] tracking-tight leading-none">{{ $currentTemp }}</span>
              <span class="text-xl sm:text-2xl font-bold text-[#9AB880]">°C</span>
              <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-[#EBF6E0] text-[#2E5A1C] border border-[#D1DFC4] font-semibold ml-2">
                Realtime
              </span>
            </div>
            <p class="text-base font-bold text-[#1A2D10] mt-2 flex items-center gap-2">
              <span>{{ $weatherDesc }}</span>
              <span class="text-[#D1DFC4]">•</span>
              <span class="text-[#4A6030] font-medium truncate max-w-[200px] sm:max-w-md">{{ $lokasiDisplay }}</span>
            </p>
            <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-[#9AB880]">
              <span class="flex items-center gap-1.5 text-[#1A2D10]">
                <i data-lucide="droplets" class="w-3.5 h-3.5 text-blue-500"></i> Kelembapan: <strong>{{ $currentHumidity ?? '--' }}%</strong>
              </span>
              <span>•</span>
              <span class="flex items-center gap-1.5 text-[#1A2D10]">
                <i data-lucide="wind" class="w-3.5 h-3.5 text-teal-600"></i> Angin: <strong>{{ $currentWind ?? '--' }} km/h</strong>
              </span>
              <span>•</span>
              <span>{{ $currentDate }}</span>
            </div>
          </div>
        </div>

        <!-- Sisi Kanan: Rekomendasi Agrikultur Card -->
        <div class="w-full lg:max-w-md rounded-2xl p-4 sm:p-5 bg-[#FBFDF9] border border-[#E4F0D6]">
          <div class="flex items-center gap-2.5 mb-2">
            <div class="w-7 h-7 rounded-lg {{ $recBadgeClass }} border flex items-center justify-center shrink-0">
              <i data-lucide="{{ $recIcon }}" class="w-4 h-4 {{ $recIconColor }}"></i>
            </div>
            <div>
              <span class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880] block">Panduan Lapangan</span>
              <h4 class="text-xs sm:text-sm font-bold text-[#1A2D10]">{{ $recTitle }}</h4>
            </div>
          </div>
          <p class="text-xs text-[#4A6030] leading-relaxed font-medium mt-1">
            {{ $recMsg }}
          </p>
        </div>

      </div>

      <!-- 7-Day Forecast Cards (Clean Minimalist Cards Horizontal) -->
      @if(!empty($daily['time']))
      @php
        $dailyTimes = $daily['time'] ?? [];
        $dailyMaxTemps = $daily['temperature_2m_max'] ?? [];
        $dailyMinTemps = $daily['temperature_2m_min'] ?? [];
        $dailyCodes = $daily['weather_code'] ?? [];
        $dailyPrecip = $daily['precipitation_probability_mean'] ?? $daily['precipitation_probability_max'] ?? [];
      @endphp
      <div class="mt-6 pt-5 border-t border-[#E4F0D6]">
        <div class="flex items-center justify-between mb-3.5">
          <p class="text-xs font-bold uppercase tracking-wider text-[#9AB880] flex items-center gap-1.5">
            <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#4D9830]"></i>
            Prakiraan 7 Hari Ke Depan
          </p>
          <span class="text-[11px] text-[#9AB880]">Suhu Siang / Malam</span>
        </div>

        <!-- Kontainer Flex Horizontal: Geser ke Kanan -->
        <div class="overflow-x-auto pb-2 scrollbar-thin">
          <div class="flex items-stretch gap-3 min-w-[720px] w-full">
            @foreach($dailyTimes as $dayIndex => $date)
            @php
              $isToday = $dayIndex === 0;
              $dayHi = round($dailyMaxTemps[$dayIndex] ?? 0);
              $dayLo = round($dailyMinTemps[$dayIndex] ?? 0);
              $dayCode = $isToday ? $currentWeatherCode : ($dailyCodes[$dayIndex] ?? -1);
              $dayPrecip = $dailyPrecip[$dayIndex] ?? null;
              $dayDate = \Carbon\Carbon::parse($date);
              $dayTitle = $isToday ? 'Hari Ini' : $dayDate->locale('id')->isoFormat('ddd, D MMM');
              $wmoShort = $wmoDesc[$dayCode] ?? 'Cerah';
            @endphp
            <div class="flex-1 min-w-[95px] rounded-xl p-3 text-center transition border {{ $isToday ? 'bg-[#EBF6E0]/60 border-[#C5DFB0]' : 'bg-[#FBFDF9] border-[#E4F0D6]' }} flex flex-col justify-between">
              <div>
                <p class="text-[11px] font-bold {{ $isToday ? 'text-[#2E5A1C]' : 'text-[#1A2D10]' }} mb-1 truncate">
                  {{ $dayTitle }}
                </p>
                <div class="my-2">
                  <i data-lucide="{{ $wmoIcon($dayCode) }}" class="w-6 h-6 mx-auto text-[#4D9830]"></i>
                </div>
                <p class="text-[11px] text-[#9AB880] font-medium truncate mb-2">{{ $wmoShort }}</p>
              </div>

              <!-- Rentang Suhu -->
              <div class="pt-2 border-t border-[#E4F0D6]">
                <div class="flex items-center justify-center gap-1">
                  <span class="text-sm font-extrabold text-[#1A2D10]">{{ $dayHi }}°</span>
                  <span class="text-xs font-semibold text-[#9AB880]">/ {{ $dayLo }}°C</span>
                </div>
                <p class="text-[9px] text-[#9AB880] mt-0.5">Maks / Min</p>

                @if($dayPrecip !== null)
                <div class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-[10px] text-blue-700 font-semibold border border-blue-100">
                  <span>🌧</span> {{ round($dayPrecip) }}%
                </div>
                @endif
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      @endif

      <!-- Sumber Data -->
      <div class="mt-4 pt-3 border-t border-[#E4F0D6] flex items-center justify-between text-[11px] text-[#9AB880]">
        <span>Data meteorologi: Open-Meteo High Resolution Forecast</span>
        <span class="text-[#4D9830] font-semibold">Terkalibrasi GPS</span>
      </div>
    </div>
    @else
    <!-- Belum Ada Lokasi / Error -->
    <div class="px-6 py-14 text-center bg-white">
      <div class="w-16 h-16 mx-auto mb-3.5 rounded-2xl bg-[#E4F0D6] flex items-center justify-center text-[#4D9830] shadow-2xs">
        <i data-lucide="map-pin-off" class="w-8 h-8"></i>
      </div>
      <h3 class="text-lg font-bold text-[#1A2D10] mb-1">Lokasi Cuaca Belum Terdeteksi</h3>
      <p class="text-xs sm:text-sm text-[#9AB880] max-w-md mx-auto mb-5">
        Klik tombol di bawah untuk mendeteksi koordinat lokasi Anda secara otomatis dan menampilkan prakiraan cuaca 7 hari ke depan.
      </p>
      <button type="button" onclick="document.getElementById('btn-gps-lokasi').click()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#4D9830] hover:bg-[#3D8024] text-white text-sm font-bold shadow-2xs transition cursor-pointer">
        <i data-lucide="crosshair" class="w-4 h-4"></i>
        <span>Gunakan Lokasi Terkini</span>
      </button>
    </div>
    @endif
  </div>

  <!-- ==================== TWO COLUMN LAYOUT ==================== -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

    <!-- LEFT: Harga Komoditas Terkini & Tren Pasar -->
    <div class="p-5 sm:p-6 rounded-2xl bg-white border border-[#E4F0D6] shadow-2xs">
      <div class="flex items-center justify-between mb-5 pb-3 border-b border-[#E4F0D6]">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] flex items-center justify-center text-[#4D9830]">
            <i data-lucide="coins" class="w-4 h-4"></i>
          </div>
          <div>
            <h2 class="text-base sm:text-lg font-bold text-[#1A2D10]">Harga Komoditas Pasar</h2>
            <p class="text-[11px] text-[#9AB880] font-medium">Monitoring pergerakan harga produsen tingkat petani</p>
          </div>
        </div>
        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-[#F5F8F1] text-[#2E5A1C] border border-[#D1DFC4]">
          Update: {{ now()->locale('id')->isoFormat('D MMM YYYY') }}
        </span>
      </div>

      <div class="space-y-3">
        @foreach($commodities as $item)
        @php
          $isUp = $item['up'] ?? true;
          $sparkline = $item['sparkline'] ?? [5000, 5100, 5200, 5150, 5300];
          $minVal = min($sparkline);
          $maxVal = max($sparkline);
          $range = max(1, $maxVal - $minVal);

          $pts = [];
          $count = count($sparkline);
          foreach($sparkline as $i => $v) {
            $x = round(($i / max(1, $count - 1)) * 70);
            $y = round(22 - (($v - $minVal) / $range) * 18);
            $pts[] = "{$x},{$y}";
          }
          $sparkPath = implode(' ', $pts);
        @endphp
        <div class="p-3.5 rounded-xl border border-[#E4F0D6] hover:border-[#C5DFB0] transition bg-[#FBFDF9]">
          <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-[#1A2D10] truncate">{{ $item['nama'] }}</span>
                @if(!empty($item['trend_label']))
                <span class="hidden sm:inline-block text-[10px] px-2 py-0.5 rounded-md bg-[#EBF6E0] text-[#2E5A1C] font-semibold">
                  {{ $item['trend_label'] }}
                </span>
                @endif
              </div>
              <div class="flex items-center gap-3 mt-1 text-xs text-[#9AB880]">
                <span>Rentang: <strong class="text-[#1A2D10] font-semibold">{{ $item['min_harga'] ?? $item['harga'] }} – {{ $item['max_harga'] ?? $item['harga'] }}</strong></span>
                <span>•</span>
                <span>Lalu: {{ $item['sebelum'] }}</span>
              </div>
            </div>

            <!-- Sparkline & Price -->
            <div class="flex items-center gap-3 text-right shrink-0">
              <svg width="74" height="26" class="hidden sm:block overflow-visible" aria-hidden="true">
                <polyline
                  fill="none"
                  stroke="{{ $isUp ? '#16A34A' : '#DC2626' }}"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  points="{{ $sparkPath }}"
                />
              </svg>

              <div>
                <span class="text-base sm:text-lg font-black text-[#1A2D10] block">{{ $item['harga'] }}</span>
                <span class="inline-flex items-center gap-0.5 text-xs font-bold {{ $isUp ? 'text-emerald-700 bg-emerald-50' : 'text-red-700 bg-red-50' }} px-1.5 py-0.5 rounded">
                  <i data-lucide="{{ $isUp ? 'trending-up' : 'trending-down' }}" class="w-3 h-3"></i>
                  {{ $item['change'] }}
                </span>
              </div>
            </div>
          </div>

          @if(!empty($item['rekomendasi']))
          <div class="mt-2.5 pt-2 border-t border-[#E4F0D6] flex items-center justify-between text-[11px]">
            <span class="text-[#4A6030] font-semibold flex items-center gap-1.5">
              <i data-lucide="lightbulb" class="w-3.5 h-3.5 text-amber-500 shrink-0"></i>
              {{ $item['rekomendasi'] }}
            </span>
          </div>
          @endif
        </div>
        @endforeach
      </div>

      <!-- Info Footer -->
      <div class="mt-4 pt-3 border-t border-[#E4F0D6] flex items-center justify-between text-[11px] text-[#9AB880]">
        <span>Simulasi harga cerdas didukung AI & Data Pangan Pasar</span>
        <span class="text-[#4D9830] font-semibold">Terkalibrasi</span>
      </div>
    </div>

    <!-- RIGHT: Prediksi Panen & Prediksi Harga Padi -->
    <div class="space-y-6">

      <!-- Prediksi Harga Padi Komprehensif -->
      <div class="p-5 sm:p-6 rounded-2xl bg-white border border-[#E4F0D6] shadow-2xs">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-[#E4F0D6]">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] flex items-center justify-center text-[#4D9830]">
              <i data-lucide="trending-up" class="w-4 h-4"></i>
            </div>
            <div>
              <h2 class="text-base sm:text-lg font-bold text-[#1A2D10]">Proyeksi Harga Padi (GKG)</h2>
              <p class="text-[11px] text-[#9AB880] font-medium">Estimasi harga panen 1–3 bulan ke depan</p>
            </div>
          </div>
          <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200">
            {{ $ricePrediction['status'] ?? 'Tren Menguat' }}
          </span>
        </div>

        <div class="p-4 rounded-xl bg-[#FBFDF9] border border-[#E4F0D6]">
          <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-2">
            <div>
              <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Estimasi Periode {{ $ricePrediction['period'] ?? 'Mendatang' }}</span>
              <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl sm:text-3xl font-extrabold text-[#1A2D10]">
                  {{ $ricePrediction['minPrice'] ?? 'Rp 6.600' }} – {{ $ricePrediction['maxPrice'] ?? 'Rp 7.200' }}
                </span>
                <span class="text-xs text-[#9AB880] font-medium">/ kg GKG</span>
              </div>
            </div>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-[#4D9830] text-white text-xs font-bold self-start sm:self-auto shadow-2xs">
              <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
              <span>{{ $ricePrediction['change'] ?? '+4.5%' }} dari bulan lalu</span>
            </div>
          </div>

          <!-- Actionable Insight Rekomendasi -->
          <div class="mt-3.5 p-3 rounded-xl bg-white border border-[#E4F0D6] text-xs text-[#1A2D10] flex items-start gap-2.5">
            <i data-lucide="sparkles" class="w-4 h-4 text-[#4D9830] shrink-0 mt-0.5"></i>
            <div>
              <strong class="text-[#2E5A1C] block font-bold mb-0.5">Rekomendasi Waktu Jual:</strong>
              <p class="text-xs text-[#4A6030] leading-relaxed font-medium">
                {{ $ricePrediction['rekomendasi'] ?? 'Waktu ideal pelepasan stok berada di 2-3 minggu ke depan untuk mengoptimalkan margin saat penggilingan mengisi cadangan logistik.' }}
              </p>
            </div>
          </div>

          <!-- Faktor Pengaruh Pasar -->
          @if(!empty($ricePrediction['faktor']))
          <div class="mt-3 pt-3 border-t border-[#E4F0D6]">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880] block mb-1.5">Faktor Penentu Fluktuasi Pasar:</span>
            <ul class="space-y-1 text-xs text-[#4A6030]">
              @foreach($ricePrediction['faktor'] as $f)
              <li class="flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-[#4D9830] shrink-0"></span>
                <span class="font-medium">{{ $f }}</span>
              </li>
              @endforeach
            </ul>
          </div>
          @endif
        </div>
      </div>

      <!-- Prediksi Estimasi Panen Poktan -->
      <div class="p-5 sm:p-6 rounded-2xl bg-white border border-[#E4F0D6] shadow-2xs">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-[#E4F0D6]">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[#EBF6E0] flex items-center justify-center text-[#4D9830]">
              <i data-lucide="wheat" class="w-4 h-4"></i>
            </div>
            <div>
              <h2 class="text-base sm:text-lg font-bold text-[#1A2D10]">Estimasi Panen Poktan</h2>
              <p class="text-[11px] text-[#9AB880] font-medium">Proyeksi volume produksi kelompok tani</p>
            </div>
          </div>
          <span class="text-xs text-[#9AB880] font-medium">{{ $kelompok->nama_kelompok ?? 'Kelompok Tani' }}</span>
        </div>

        <div class="space-y-3">
          @forelse($harvestPredictions as $prediction)
          <div class="p-3.5 rounded-xl bg-[#FBFDF9] border border-[#E4F0D6] hover:border-[#C5DFB0] transition">
            <div class="flex items-start justify-between gap-2">
              <div>
                <span class="text-sm font-bold text-[#1A2D10] block">{{ $prediction['nama'] }}</span>
                <span class="text-xs text-[#9AB880]">Luas lahan: <strong class="text-[#1A2D10]">{{ $prediction['luas'] ?? '-' }}</strong></span>
              </div>
              <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-white text-[#2E5A1C] border border-[#D1DFC4]">
                Est: {{ $prediction['est'] }}
              </span>
            </div>

            <div class="mt-2 flex items-baseline justify-between">
              <span class="text-base sm:text-lg font-extrabold text-[#4D9830]">{{ $prediction['hasil'] }}</span>
              <span class="text-xs font-semibold {{ $prediction['color'] === 'green' ? 'text-emerald-700' : 'text-amber-700' }}">
                Tingkat Akurasi: {{ $prediction['confidence'] }}%
              </span>
            </div>

            <!-- Progress Bar -->
            <div class="h-2 bg-[#E4F0D6] rounded-full overflow-hidden mt-2">
              <div class="h-full {{ $prediction['color'] === 'green' ? 'bg-[#4D9830]' : 'bg-amber-500' }} rounded-full" style="width: {{ $prediction['confidence'] }}%"></div>
            </div>
          </div>
          @empty
          <div class="text-center py-8 text-[#9AB880]">
            <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
            <p class="text-sm">Belum ada data lahan aktif untuk estimasi panen.</p>
          </div>
          @endforelse
        </div>
      </div>

    </div>

  </div>

</div>
@endsection

@push('scripts')
<script>
(() => {
  const btnGps = document.getElementById('btn-gps-lokasi');
  const btnGpsText = document.getElementById('btn-gps-text');
  const loadingSpinner = document.getElementById('lokasi-loading');
  const loadingText = document.getElementById('lokasi-loading-text');
  const gpsStatusBar = document.getElementById('gps-status-bar');
  const gpsStatusMsg = document.getElementById('gps-status-msg');

  function showLoading(msg = 'Memproses...') {
    if (!loadingSpinner) return;
    loadingText.textContent = msg;
    loadingSpinner.classList.remove('hidden');
    loadingSpinner.classList.add('flex');
  }

  function hideLoading() {
    if (!loadingSpinner) return;
    loadingSpinner.classList.add('hidden');
    loadingSpinner.classList.remove('flex');
  }

  function showGpsStatus(msg, isError = false) {
    if (!gpsStatusBar || !gpsStatusMsg) return;
    gpsStatusMsg.textContent = msg;
    gpsStatusBar.classList.remove('hidden');
    if (isError) {
      gpsStatusBar.className = 'px-5 py-2.5 bg-red-50 border-b border-red-200 text-xs text-red-700 flex items-center justify-between';
    } else {
      gpsStatusBar.className = 'px-5 py-2.5 bg-[#F5F8F1] border-b border-[#E4F0D6] text-xs text-[#2E5A1C] flex items-center justify-between';
    }
  }

  // ==================== DETEKSI GPS & REVERSE GEOCODING ====================
  if (btnGps) {
    btnGps.addEventListener('click', () => {
      if (!navigator.geolocation) {
        showGpsStatus('Browser Anda tidak mendukung deteksi lokasi otomatis (Geolocation).', true);
        return;
      }

      btnGps.disabled = true;
      btnGpsText.textContent = 'Mencari GPS...';
      showLoading('Membaca satelit GPS perangkat...');
      showGpsStatus('Mohon izinkan akses lokasi jika browser meminta konfirmasi.');

      navigator.geolocation.getCurrentPosition(
        async (position) => {
          const lat = position.coords.latitude;
          const lon = position.coords.longitude;
          showLoading('Menerjemahkan nama wilayah...');
          showGpsStatus(`Koordinat diperoleh: ${lat.toFixed(4)}, ${lon.toFixed(4)}. Menemukan alamat...`);

          let namaWilayah = 'Lokasi GPS Terkini';

          try {
            const reverseUrl = `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lon}&localityLanguage=id`;
            const geoRes = await fetch(reverseUrl);
            if (geoRes.ok) {
              const geoJson = await geoRes.json();
              const desa = geoJson.locality || '';
              const kec = geoJson.localityInfo?.administrative?.[3]?.name || geoJson.localityInfo?.administrative?.[2]?.name || '';
              const kab = geoJson.city || geoJson.principalSubdivision || '';
              const prov = geoJson.countrySubdivision || '';

              const parts = [desa, kec, kab, prov].filter(p => p && p.trim().length > 0);
              if (parts.length > 0) {
                namaWilayah = parts.join(', ');
              }
            }
          } catch (e) {
            namaWilayah = `Titik GPS (${lat.toFixed(3)}, ${lon.toFixed(3)})`;
          }

          showLoading('Menyimpan lokasi ke server...');

          try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch('/api/lokasi/simpan', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
              },
              body: JSON.stringify({
                latitude: lat,
                longitude: lon,
                lokasi_nama: namaWilayah
              })
            });

            const data = await res.json();
            if (data.success) {
              btnGpsText.textContent = 'Lokasi Terpasang!';
              showGpsStatus(`Lokasi tersimpan: ${namaWilayah}. Memuat ulang cuaca...`);
              setTimeout(() => {
                location.reload();
              }, 600);
            } else {
              throw new Error(data.message || 'Gagal menyimpan lokasi');
            }
          } catch (err) {
            btnGps.disabled = false;
            btnGpsText.textContent = 'Gunakan Lokasi Terkini';
            hideLoading();
            showGpsStatus('Gagal menyimpan koordinat lokasi ke server: ' + err.message, true);
          }
        },
        (error) => {
          btnGps.disabled = false;
          btnGpsText.textContent = 'Gunakan Lokasi Terkini';
          hideLoading();
          let errDetail = 'Tidak dapat mengakses GPS.';
          if (error.code === error.PERMISSION_DENIED) {
            errDetail = 'Izin akses lokasi ditolak oleh browser. Mohon izinkan akses lokasi di bilah alamat browser.';
          } else if (error.code === error.POSITION_UNAVAILABLE) {
            errDetail = 'Sinyal lokasi perangkat tidak tersedia. Pastikan fitur lokasi perangkat aktif.';
          } else if (error.code === error.TIMEOUT) {
            errDetail = 'Batas waktu pencarian lokasi habis. Silakan coba klik tombol kembali.';
          }
          showGpsStatus(errDetail, true);
        },
        { enableHighAccuracy: true, timeout: 12000, maximumAge: 30000 }
      );
    });
  }
})();
</script>
@endpush
