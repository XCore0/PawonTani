<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CommodityService
{
    /**
     * Get commodity prices
     * Note: Using realistic mock data - can be replaced with real API (e.g., BPS, IHSG)
     */
    public function getCommodityPrices(): array
    {
        return Cache::remember('commodity_prices', 86400, function () {
            // In production, replace with real API call
            // Example: Http::get('https://api.example.com/commodity-prices')
            return $this->getCommodityData();
        });
    }

    /**
     * Get price prediction for rice
     */
    public function getRicePricePrediction(): array
    {
        return Cache::remember('rice_price_prediction', 86400, function () {
            return [
                'period' => 'Des 2024',
                'minPrice' => 'Rp 5.400',
                'maxPrice' => 'Rp 5.600',
                'change' => '+3.8%',
                'trend' => 'up',
            ];
        });
    }

    /**
     * Commodity data (mock - replace with real API)
     */
    private function getCommodityData(): array
    {
        return [
            [
                'nama' => 'Padi GKG',
                'harga' => 'Rp 5.200',
                'sebelum' => 'Rp 5.091/kg',
                'change' => '+2.1%',
                'up' => true,
            ],
            [
                'nama' => 'Singkong',
                'harga' => 'Rp 1.800',
                'sebelum' => 'Rp 1.809/kg',
                'change' => '-0.5%',
                'up' => false,
            ],
            [
                'nama' => 'Jagung',
                'harga' => 'Rp 3.800',
                'sebelum' => 'Rp 3.733/kg',
                'change' => '+1.8%',
                'up' => true,
            ],
            [
                'nama' => 'Sayuran (Bayam)',
                'harga' => 'Rp 4.500',
                'sebelum' => 'Rp 4.361/kg',
                'change' => '+3.2%',
                'up' => true,
            ],
            [
                'nama' => 'Ubi Jalar',
                'harga' => 'Rp 3.200',
                'sebelum' => 'Rp 3.239/kg',
                'change' => '-1.2%',
                'up' => false,
            ],
            [
                'nama' => 'Kedelai',
                'harga' => 'Rp 9.500',
                'sebelum' => 'Rp 9.425/kg',
                'change' => '+0.8%',
                'up' => true,
            ],
        ];
    }
}
