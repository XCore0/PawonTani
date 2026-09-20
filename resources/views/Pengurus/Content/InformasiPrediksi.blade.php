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
  <div class="p-6 sm:p-8 rounded-2xl bg-gradient-to-br from-[#4D9830] to-[#3D8024] text-white shadow-sm">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-6">
      <!-- Left: Current Weather -->
      <div class="flex-1">
        <div class="flex items-start gap-4">
          <i data-lucide="{{ $weatherData['current']['icon'] }}" class="w-12 h-12 text-yellow-300 shrink-0"></i>
          <div>
            <span class="text-5xl sm:text-6xl font-bold">{{ $weatherData['current']['temp'] }}</span>
            <p class="text-lg mt-1">{{ $weatherData['current']['condition'] }}</p>
            <p class="text-sm text-white/70 mt-1">Kelembapan: {{ $weatherData['current']['humidity'] }} · Kecepatan Angin: {{ $weatherData['current']['windSpeed'] }} · {{ $weatherData['current']['date'] }}</p>
          </div>
        </div>
      </div>

      <!-- Right: Rekomendasi -->
      <div class="md:text-right shrink-0">
        <span class="block text-sm font-medium text-white/80 mb-2">Rekomendasi Pertanian</span>
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 backdrop-blur-sm">
          @if($weatherData['recommendation']['status'] === 'good')
            <i data-lucide="check-circle" class="w-4 h-4 text-green-300"></i>
          @elseif($weatherData['recommendation']['status'] === 'warning')
            <i data-lucide="alert-triangle" class="w-4 h-4 text-yellow-300"></i>
          @else
            <i data-lucide="info" class="w-4 h-4 text-blue-300"></i>
          @endif
          <span class="text-sm font-medium">{{ $weatherData['recommendation']['message'] }}</span>
        </div>
      </div>
    </div>

    <!-- Forecast -->
    <div class="mt-8 overflow-x-auto">
      <div class="grid gap-2 min-w-[500px]" style="grid-template-columns: repeat({{ max(count($weatherData['forecast']), 1) }}, minmax(0, 1fr))">
        @foreach($weatherData['forecast'] as $day)
        <div class="text-center p-3 rounded-xl bg-white/10 backdrop-blur-sm">
          <span class="text-xs text-white/70 block">{{ $day['day'] }}</span>
          <i data-lucide="{{ $day['icon'] }}" class="w-5 h-5 mx-auto my-2 text-yellow-300"></i>
          <span class="text-base font-bold">{{ $day['temp'] }}</span>
          <span class="text-xs text-white/60 block mt-1">🌧 {{ $day['rain'] }}</span>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  <!-- ==================== TWO COLUMN LAYOUT ==================== -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- LEFT: Harga Komoditas -->
    <div class="p-6 rounded-2xl bg-white border border-[#E4F0D6] shadow-2xs">
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-[#1A2D10]">Harga Komoditas</h2>
        <span class="text-xs text-[#9AB880] font-medium">Update: {{ now()->locale('id')->isoFormat('D MMM YYYY') }}</span>
      </div>

      <div class="space-y-4">
        @foreach($commodities as $item)
        <div class="pb-4 border-b border-[#E4F0D6] last:border-0 last:pb-0">
          <div class="flex items-start justify-between">
            <div>
              <span class="text-sm font-semibold text-[#1A2D10] block">{{ $item['nama'] }}</span>
              <span class="text-xs text-[#9AB880]">Sebelumnya: {{ $item['sebelum'] }}</span>
            </div>
            <div class="text-right">
              <span class="text-lg font-bold text-[#1A2D10]">{{ $item['harga'] }}</span>
              <span class="text-xs {{ $item['up'] ? 'text-emerald-600' : 'text-red-600' }} font-medium block">
                {{ $item['up'] ? '↑' : '↓' }} {{ $item['change'] }}
              </span>
            </div>
          </div>
        </div>
        @endforeach
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

        <div class="space-y-4">
          @forelse($harvestPredictions as $prediction)
          <div class="p-4 rounded-xl bg-[#F5F8F1]">
            <span class="text-sm font-semibold text-[#1A2D10] block">{{ $prediction['nama'] }}</span>
            <span class="text-base font-bold text-[#4D9830] block mt-1">{{ $prediction['hasil'] }}</span>
            <div class="flex items-center justify-between mt-2">
              <span class="text-xs text-[#9AB880]">Est. panen: {{ $prediction['est'] }}</span>
              <span class="text-xs font-semibold {{ $prediction['color'] === 'green' ? 'text-[#4D9830]' : 'text-orange-600' }}">
                Kepercayaan: {{ $prediction['confidence'] }}%
              </span>
            </div>
            <div class="h-2 bg-[#E4F0D6] rounded-full overflow-hidden mt-2">
              <div class="h-full {{ $prediction['color'] === 'green' ? 'bg-[#4D9830]' : 'bg-orange-500' }} rounded-full w-(--bar-width)" style="--bar-width: {{ $prediction['confidence'] }}%"></div>
            </div>
          </div>
          @empty
          <div class="text-center py-8 text-[#9AB880]">
            <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
            <p class="text-sm">Belum ada data prediksi panen</p>
          </div>
          @endforelse
        </div>
      </div>

      <!-- Prediksi Harga Padi -->
      <div class="p-6 rounded-2xl bg-white border border-[#E4F0D6] shadow-2xs">
        <div class="flex items-center gap-2 mb-4">
          <i data-lucide="trending-up" class="w-5 h-5 text-[#4D9830]"></i>
          <h2 class="text-lg font-bold text-[#1A2D10]">Prediksi Harga Padi</h2>
        </div>

        <div>
          <span class="text-xs text-[#9AB880] block mb-1">Prediksi {{ $ricePrediction['period'] }}</span>
          <span class="text-3xl font-bold text-[#4D9830]">{{ $ricePrediction['minPrice'] }} – {{ $ricePrediction['maxPrice'] }}</span>
          <div class="flex items-center gap-2 mt-2">
            <i data-lucide="{{ $ricePrediction['trend'] === 'up' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4 {{ $ricePrediction['trend'] === 'up' ? 'text-emerald-600' : 'text-red-600' }}"></i>
            <span class="text-sm font-semibold {{ $ricePrediction['trend'] === 'up' ? 'text-emerald-600' : 'text-red-600' }}">{{ $ricePrediction['change'] }} dari bulan ini</span>
          </div>
        </div>
      </div>

    </div>

  </div>

</div>
