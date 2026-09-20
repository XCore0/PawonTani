<?php

namespace App\Services;

use App\Models\Pengguna;
use Illuminate\Support\Facades\Cache;

class HarvestPredictionService
{
    /**
     * Get harvest predictions for a specific kelompok tani
     */
    public function getPredictions(string $idKelompok): array
    {
        $cacheKey = "harvest_predictions_{$idKelompok}";

        return Cache::remember($cacheKey, 86400, function () use ($idKelompok) {
            // In production, this would use ML models or historical data
            // For now, return realistic predictions based on kelompok data
            return $this->getPredictionData($idKelompok);
        });
    }

    /**
     * Get prediction data for a kelompok
     */
    private function getPredictionData(string $idKelompok): array
    {
        // Get kelompok info for personalized predictions
        $kelompok = \App\Models\KelompokTani::find($idKelompok);
        $kelompokName = $kelompok ? $kelompok->nama_kelompok : 'Kelompok Tani';

        return [
            [
                'nama' => "Padi Ciherang (Sawah {$kelompokName} 1)",
                'hasil' => '4.500 – 5.000 kg GKG',
                'est' => 'Nov 2024',
                'confidence' => 88,
                'color' => 'green',
            ],
            [
                'nama' => "Padi IR64 (Sawah {$kelompokName} 2)",
                'hasil' => '2.800 – 3.200 kg GKG',
                'est' => 'Des 2024',
                'confidence' => 75,
                'color' => 'amber',
            ],
            [
                'nama' => "Jagung Hibrida (Ladang {$kelompokName})",
                'hasil' => '3.000 – 3.500 kg',
                'est' => 'Des 2024',
                'confidence' => 82,
                'color' => 'green',
            ],
        ];
    }
}
