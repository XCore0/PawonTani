@extends('Pengurus.Layout._layout')

@section('title', 'Informasi & Prediksi')

@section('content')
<div class="space-y-6">

  <!-- ==================== HEADER ==================== -->
  <div>
    <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
      Informasi & Prediksi
    </h1>
    <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
      Informasi cuaca, harga komoditas, dan prediksi pertanian
    </p>
  </div>

  <!-- ==================== CUACA CARD ==================== -->
  <div class="rounded-2xl border border-[#E4F0D6] shadow-sm overflow-hidden">
    <!-- Location Selector Header -->
    <div class="px-4 sm:px-6 pt-4 sm:pt-5 pb-3 flex flex-wrap items-center gap-2 border-b border-[#E4F0D6] bg-white">
      <i data-lucide="map-pin" class="w-4 h-4 text-[#4D9830] shrink-0"></i>
      <span class="text-sm font-semibold text-[#1A2D10] mr-2">Lokasi Cuaca:</span>
  
      <select id="select-provinsi" class="h-9 rounded-lg border border-[#D1DFC4] bg-white px-3 text-sm text-[#1A2D10] focus:outline-none focus:ring-2 focus:ring-[#4D9830]/30 focus:border-[#4D9830] w-full sm:w-auto">
        <option value="">Pilih Provinsi</option>
        @foreach($provinces as $id => $nama)
        <option value="{{ $id }}" {{ (string)($pengurus->provinsi_id ?? '') === (string)$id ? 'selected' : '' }}>{{ $nama }}</option>
        @endforeach
      </select>
  
      <select id="select-kabupaten" class="h-9 rounded-lg border border-[#D1DFC4] bg-white px-3 text-sm text-[#1A2D10] focus:outline-none focus:ring-2 focus:ring-[#4D9830]/30 focus:border-[#4D9830] {{ !$pengurus->provinsi_id ? 'hidden' : '' }} w-full sm:w-auto">
        <option value="">Pilih Kabupaten</option>
      </select>
  
      <select id="select-kecamatan" class="h-9 rounded-lg border border-[#D1DFC4] bg-white px-3 text-sm text-[#1A2D10] focus:outline-none focus:ring-2 focus:ring-[#4D9830]/30 focus:border-[#4D9830] {{ !$pengurus->kabupaten_id ? 'hidden' : '' }} w-full sm:w-auto">
        <option value="">Pilih Kecamatan</option>
      </select>

      <!-- Loading Spinner -->
      <div id="lokasi-loading" class="hidden items-center gap-1.5 text-xs text-[#9AB880]">
        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span>Memuat...</span>
      </div>
  
      <button type="button" id="btn-simpan-lokasi" class="ml-auto h-9 px-4 rounded-lg bg-[#4D9830] text-white text-sm font-medium hover:bg-[#3D8024] transition cursor-pointer disabled:opacity-50 {{ !$pengurus->kecamatan_id ? 'hidden' : '' }} w-full sm:w-auto" {{ !$pengurus->kecamatan_id ? 'disabled' : '' }}>
        Simpan Lokasi
      </button>
    </div>
  
    @if($weatherData)
    @php
      // Parse Open-Meteo data
      $current = $weatherData['current'] ?? [];
      $daily = $weatherData['daily'] ?? [];
      $currentTemp = round($current['temperature_2m'] ?? 0);
      $currentHumidity = $current['relative_humidity_2m'] ?? null;
      $currentWind = $current['wind_speed_10m'] ?? null;
      $currentWeatherCode = $current['weather_code'] ?? -1;
      $lokasiDisplay = $weatherData['_lokasi_nama'] ?? ($pengurus->lokasi_nama ?? 'Belum pilih lokasi');
      $currentDate = now()->locale('id')->isoFormat('dddd, D MMM YYYY');

      // WMO weather code to Indonesian description
      $wmoDesc = [
        0 => 'Cerah', 1 => 'Cerah', 2 => 'Cerah Berawan', 3 => 'Berawan',
        45 => 'Berkabut', 48 => 'Berkabut', 51 => 'Gerimis', 53 => 'Gerimis',
        55 => 'Gerimis Lebat', 61 => 'Hujan Ringan', 63 => 'Hujan', 65 => 'Hujan Lebat',
        71 => 'Salju Ringan', 73 => 'Salju', 75 => 'Salju Lebat', 80 => 'Hujan Ringan',
        81 => 'Hujan', 82 => 'Hujan Lebat', 95 => 'Hujan Petir', 96 => 'Hujan Petir',
        99 => 'Hujan Petir Lebat',
      ];
      $weatherDesc = $wmoDesc[$currentWeatherCode] ?? 'Tidak diketahui';

      // Weather code to icon
      $wmoIcon = function($code) {
        if ($code === 0 || $code === 1) return 'sun';
        if ($code === 2 || $code === 3) return 'cloud-sun';
        if (in_array($code, [45, 48])) return 'cloud-fog';
        if (in_array($code, [51, 53, 55, 61, 63, 65, 80, 81, 82])) return 'cloud-rain';
        if (in_array($code, [95, 96, 99])) return 'cloud-lightning';
        return 'cloud';
      };
      $wmoIconColor = function($code) {
        if ($code === 0 || $code === 1) return 'text-amber-300';
        if ($code === 2 || $code === 3) return 'text-white/80';
        if (in_array($code, [51, 53, 55, 61, 63, 65, 80, 81, 82])) return 'text-blue-300';
        if (in_array($code, [95, 96, 99])) return 'text-purple-300';
        return 'text-white/60';
      };

      // Rekomendasi
      $dailyMaxTempsAll = $daily['temperature_2m_max'] ?? [];
      $avgTemp = !empty($dailyMaxTempsAll) ? array_sum($dailyMaxTempsAll) / count($dailyMaxTempsAll) : 28;
      if ($avgTemp > 33) { $recMsg = 'Suhu tinggi, pastikan tanaman mendapat cukup air.'; }
      elseif ($avgTemp < 22) { $recMsg = 'Suhu rendah, cocok untuk tanaman sayur dataran tinggi.'; }
      else { $recMsg = 'Baik untuk pemupukan & penyemprotan.'; }
    @endphp

    <!-- Green Weather Section + Forecast (1 gradient) -->
    <div style="background: linear-gradient(135deg, #2E7D32 0%, #43A047 40%, #81C784 100%);">
    <div class="px-4 sm:px-6 py-5 sm:py-6">
      <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
        <!-- Left: Current Weather -->
        <div class="flex items-start gap-3 sm:gap-4">
          <i data-lucide="{{ $wmoIcon($currentWeatherCode) }}" class="w-12 h-12 sm:w-16 sm:h-16 text-white shrink-0 mt-1"></i>
          <div>
            <div class="flex items-baseline gap-1">
              <span class="text-4xl sm:text-6xl font-bold text-white">{{ $currentTemp }}</span>
              <span class="text-lg sm:text-2xl text-white/80">°C</span>
            </div>
            <p class="text-xs sm:text-base text-white/90 mt-0.5">{{ $weatherDesc }} · {{ $lokasiDisplay }}</p>
            <p class="text-[10px] sm:text-xs text-white/60 mt-1">Kelembapan: {{ $currentHumidity ?? '--' }}% · Kecepatan Angin: {{ $currentWind ?? '--' }} km/h · {{ $currentDate }}</p>
          </div>
        </div>
        <!-- Right: Rekomendasi -->
        <div class="text-right shrink-0 w-full sm:w-auto">
          <p class="text-xs text-white/70 mb-1.5">Rekomendasi Pertanian</p>
          <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/20 backdrop-blur-sm">
            <i data-lucide="check-circle" class="w-4 h-4 text-green-200"></i>
            <span class="text-xs text-white font-medium">{{ $recMsg }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 7-Day Forecast Cards -->
    @if(!empty($daily['time']))
    @php
      $dailyTimes = $daily['time'] ?? [];
      $dailyMaxTemps = $daily['temperature_2m_max'] ?? [];
      $dailyMinTemps = $daily['temperature_2m_min'] ?? [];
      $dailyCodes = $daily['weather_code'] ?? [];
      $dailyPrecip = $daily['precipitation_probability_mean'] ?? $daily['precipitation_probability_max'] ?? [];
    @endphp
    <div class="px-3 sm:px-6 pb-4 sm:pb-6">
      <div class="flex gap-2 overflow-x-auto pb-1 sm:hidden">
        @foreach($dailyTimes as $dayIndex => $date)
        @php
          $dayHi = round($dailyMaxTemps[$dayIndex] ?? 0);
          $dayCode = $dailyCodes[$dayIndex] ?? -1;
          $dayPrecip = $dailyPrecip[$dayIndex] ?? null;
          $dayDate = \Carbon\Carbon::parse($date);
          $dayName = $dayIndex === 0 ? 'Hari ini' : $dayDate->locale('id')->isoFormat('ddd');
        @endphp
        <div class="rounded-xl p-3 text-center min-w-[80px] flex-shrink-0" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(4px);">
          <p class="text-xs font-medium text-white/90 mb-2">{{ $dayName }}</p>
          <i data-lucide="{{ $wmoIcon($dayCode) }}" class="w-7 h-7 mx-auto mb-2 text-white"></i>
          <p class="text-lg font-bold text-white">{{ $dayHi }}°C</p>
          @if($dayPrecip !== null)
          <p class="text-[10px] text-white/70 mt-1">🌧 {{ round($dayPrecip) }}%</p>
          @endif
        </div>
        @endforeach
      </div>
      <div class="hidden sm:grid sm:grid-cols-7 sm:gap-3">
        @foreach($dailyTimes as $dayIndex => $date)
        @php
          $dayHi = round($dailyMaxTemps[$dayIndex] ?? 0);
          $dayCode = $dailyCodes[$dayIndex] ?? -1;
          $dayPrecip = $dailyPrecip[$dayIndex] ?? null;
          $dayDate = \Carbon\Carbon::parse($date);
          $dayName = $dayIndex === 0 ? 'Hari ini' : $dayDate->locale('id')->isoFormat('ddd');
        @endphp
        <div class="rounded-xl p-3 text-center" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(4px);">
          <p class="text-xs font-medium text-white/90 mb-2">{{ $dayName }}</p>
          <i data-lucide="{{ $wmoIcon($dayCode) }}" class="w-7 h-7 mx-auto mb-2 text-white"></i>
          <p class="text-lg font-bold text-white">{{ $dayHi }}°C</p>
          @if($dayPrecip !== null)
          <p class="text-[10px] text-white/70 mt-1">🌧 {{ round($dayPrecip) }}%</p>
          @endif
        </div>
        @endforeach
      </div>
    </div>
    @endif

    <!-- Sumber Data -->
    <div class="px-6 py-2 text-center">
      <span class="text-[10px] text-white/40">Sumber data: Open-Meteo.com</span>
    </div>
    </div>
    @else
    <!-- Belum pilih lokasi / Error -->
    <div class="px-6 py-12 text-center bg-white">
      <i data-lucide="map-pin-off" class="w-12 h-12 mx-auto mb-3 text-[#C5DFB0]"></i>
      @if($weatherError ?? false)
        <p class="text-sm font-medium text-[#1A2D10] mb-1">{{ $weatherError }}</p>
        <p class="text-xs text-[#9AB880]">Silakan pilih lokasi di atas, lalu klik Simpan Lokasi.</p>
      @else
        <p class="text-sm font-medium text-[#1A2D10] mb-1">Belum ada lokasi cuaca</p>
        <p class="text-xs text-[#9AB880]">Pilih Provinsi → Kabupaten → Kecamatan di atas, lalu klik Simpan Lokasi untuk melihat cuaca.</p>
      @endif
    </div>
    @endif
  </div>

  <!-- ==================== TWO COLUMN LAYOUT ==================== -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- LEFT: Harga Komoditas -->
    <div class="p-6 rounded-2xl bg-white border border-[#E4F0D6] shadow-2xs">
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-[#1A2D10]">Harga Komoditas</h2>
        <span class="text-xs text-[#9AB880] font-medium">Update: {{ now()->locale('id')->isoFormat('D MMM YYYY') }}</span>
      </div>
    </div>

    <!-- RIGHT: Prediksi Panen & Harga -->
    <div class="space-y-6">

      <!-- Prediksi Panen -->
      <div class="p-6 rounded-2xl bg-white border border-[#E4F0D6] shadow-2xs">
        <div class="flex items-center gap-2 mb-5">
          <i data-lucide="wheat" class="w-5 h-5 text-[#4D9830]"></i>
          <h2 class="text-lg font-bold text-[#1A2D10]">Prediksi Panen</h2>
        </div>
      </div>

      <!-- Prediksi Harga Padi -->
      <div class="p-6 rounded-2xl bg-white border border-[#E4F0D6] shadow-2xs">
        <div class="flex items-center gap-2 mb-4">
          <i data-lucide="trending-up" class="w-5 h-5 text-[#4D9830]"></i>
          <h2 class="text-lg font-bold text-[#1A2D10]">Prediksi Harga Padi</h2>
        </div>
      </div>

    </div>

  </div>

</div>

@endsection

@push('scripts')
<script type="application/json" id="lokasi-data" data-kab="{{ $pengurus->kabupaten_id }}" data-kec="{{ $pengurus->kecamatan_id }}"></script>
<script>
(() => {
  const selectProv = document.getElementById('select-provinsi');
  const selectKab = document.getElementById('select-kabupaten');
  const selectKec = document.getElementById('select-kecamatan');
  const btnSimpan = document.getElementById('btn-simpan-lokasi');
  const loadingSpinner = document.getElementById('lokasi-loading');

  function showLoading() { loadingSpinner.classList.remove('hidden'); loadingSpinner.classList.add('flex'); }
  function hideLoading() { loadingSpinner.classList.add('hidden'); loadingSpinner.classList.remove('flex'); }

  // Get saved values from server (for pre-selecting dropdowns on page load)
  const lokasiData = document.getElementById('lokasi-data');
  const savedKab = lokasiData.dataset.kab || '';
  const savedKec = lokasiData.dataset.kec || '';
  let selectedProv = selectProv.value;
  let selectedKab = savedKab;
  let selectedKec = savedKec;

  // Load kabupaten when provinsi changes
  async function loadKabupaten(provId) {
    // Hide kabupaten, kecamatan, and save button
    selectKab.classList.add('hidden');
    selectKec.classList.add('hidden');
    btnSimpan.classList.add('hidden');
    btnSimpan.disabled = true;
    
    selectKab.innerHTML = '<option value="">Memuat...</option>';
    selectKec.innerHTML = '<option value="">Pilih Kabupaten dulu</option>';

    if (!provId) {
      selectKab.innerHTML = '<option value="">Pilih Kabupaten</option>';
      return;
    }

    showLoading();
    try {
      const res = await fetch('/api/lokasi/kabupaten?provinsi_id=' + provId);
      const data = await res.json();
      selectKab.innerHTML = '<option value="">Pilih Kabupaten</option>';
      for (const [id, kab] of Object.entries(data)) {
        const selected = String(id) === String(selectedKab) ? ' selected' : '';
        selectKab.innerHTML += '<option value="' + id + '"' + selected + '>' + kab.nama + '</option>';
      }
      // Show kabupaten dropdown
      selectKab.classList.remove('hidden');
    } catch (e) {
      selectKab.innerHTML = '<option value="">Gagal memuat</option>';
      selectKab.classList.remove('hidden');
    }
    hideLoading();
  }

  // Load kecamatan when kabupaten changes
  async function loadKecamatan(kabId) {
    // Hide kecamatan and save button
    selectKec.classList.add('hidden');
    btnSimpan.classList.add('hidden');
    btnSimpan.disabled = true;
    
    selectKec.innerHTML = '<option value="">Memuat...</option>';

    if (!kabId) {
      selectKec.innerHTML = '<option value="">Pilih Kecamatan</option>';
      return;
    }

    showLoading();
    try {
      const res = await fetch('/api/lokasi/kecamatan?kabupaten_id=' + kabId);
      const data = await res.json();
      selectKec.innerHTML = '<option value="">Pilih Kecamatan</option>';
      for (const [id, kec] of Object.entries(data)) {
        const selected = String(id) === String(selectedKec) ? ' selected' : '';
        selectKec.innerHTML += '<option value="' + id + '"' + selected + ' data-adm4="' + (kec.adm4 || '') + '">' + kec.nama + '</option>';
      }
      // Show kecamatan dropdown
      selectKec.classList.remove('hidden');
    } catch (e) {
      selectKec.innerHTML = '<option value="">Gagal memuat</option>';
      selectKec.classList.remove('hidden');
    }
    hideLoading();
  }

  // Check if save button should be enabled and shown
  function checkSaveButton() {
    if (selectProv.value && selectKab.value && selectKec.value) {
      btnSimpan.classList.remove('hidden');
      btnSimpan.disabled = false;
    } else {
      btnSimpan.classList.add('hidden');
      btnSimpan.disabled = true;
    }
  }

  selectProv.addEventListener('change', () => {
    selectedProv = selectProv.value;
    selectedKab = '';
    selectedKec = '';
    loadKabupaten(selectedProv);
  });

  selectKab.addEventListener('change', () => {
    selectedKab = selectKab.value;
    selectedKec = '';
    loadKecamatan(selectedKab);
  });

  selectKec.addEventListener('change', () => {
    selectedKec = selectKec.value;
    checkSaveButton();
  });

  // Save location
  btnSimpan.addEventListener('click', async () => {
    const kecOption = selectKec.options[selectKec.selectedIndex];
    const adm4 = kecOption ? kecOption.dataset.adm4 : '';
    const lokasiNama = selectKec.options[selectKec.selectedIndex]?.text + ', ' +
                       selectKab.options[selectKab.selectedIndex]?.text + ', ' +
                       selectProv.options[selectProv.selectedIndex]?.text;

    btnSimpan.disabled = true;
    btnSimpan.textContent = 'Menyimpan...';

    try {
      const res = await fetch('/api/lokasi/simpan', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          provinsi_id: selectProv.value,
          kabupaten_id: selectKab.value,
          kecamatan_id: selectKec.value,
          desa_id: adm4,
          lokasi_nama: lokasiNama
        })
      });
      const data = await res.json();
      if (data.success) {
        btnSimpan.textContent = 'Tersimpan!';
        setTimeout(() => { location.reload(); }, 1000);
      }
    } catch (e) {
      btnSimpan.textContent = 'Gagal simpan';
      setTimeout(() => { btnSimpan.textContent = 'Simpan Lokasi'; checkSaveButton(); }, 2000);
    }
  });

  // Initialize: load kabupaten then kecamatan if values are saved
  async function initializeDropdowns() {
    if (selectedProv) {
      await loadKabupaten(selectedProv);
      // Wait for DOM to update
      await new Promise(resolve => setTimeout(resolve, 100));
      
      if (selectedKab) {
        await loadKecamatan(selectedKab);
        // Wait for DOM to update
        await new Promise(resolve => setTimeout(resolve, 100));
        
        // Pre-select kecamatan
        if (selectedKec) {
          selectKec.value = selectedKec;
          checkSaveButton();
        }
      }
    }
  }
  
  initializeDropdowns();
})();
</script>
@endpush