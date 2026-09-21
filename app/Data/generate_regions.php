<?php

/**
 * Generator: Download data wilayah Indonesia & strukturkan untuk BMKG API
 * Sumber: https://emsifa.github.io/api-wilayah-indonesia/
 * 
 * Cara pakai: php generate_regions.php
 */

$baseUrl = 'https://emsifa.github.io/api-wilayah-indonesia/api';

echo "=== Generator Data Wilayah Indonesia untuk BMKG ===\n\n";

function fetchJson($url, $retries = 3) {
    for ($i = 0; $i < $retries; $i++) {
        $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
        $result = @file_get_contents($url, false, $ctx);
        if ($result !== false) {
            $data = json_decode($result, true);
            if ($data !== null) return $data;
        }
        echo "    Retry " . ($i + 1) . "/$retries...\n";
        sleep(2);
    }
    return null;
}

// 1. Fetch semua provinsi
echo "[1/4] Mengunduh data provinsi...\n";
$provinces = fetchJson("$baseUrl/provinces.json");
if (!$provinces) { echo "GAGAL fetch provinsi!\n"; exit(1); }
echo "  Ditemukan " . count($provinces) . " provinsi\n";

// 2. Fetch semua kabupaten per provinsi
echo "[2/4] Mengunduh data kabupaten/kota...\n";
$regencies = [];
foreach ($provinces as $prov) {
    $data = fetchJson("$baseUrl/regencies/{$prov['id']}.json");
    if (!$data) {
        echo "  WARNING: Gagal fetch {$prov['name']}, skip\n";
        continue;
    }
    foreach ($data as $reg) {
        $regencies[] = [
            'id' => $reg['id'],
            'province_id' => $prov['id'],
            'name' => $reg['name'],
        ];
    }
    echo "  {$prov['name']}: " . count($data) . " kabupaten/kota\n";
    usleep(500000); // 500ms delay
}
echo "  Total: " . count($regencies) . " kabupaten/kota\n";

// 3. Fetch semua kecamatan per kabupaten
echo "[3/4] Mengunduh data kecamatan (butuh ~5 menit)...\n";
$districts = [];
$count = 0;
$total = count($regencies);
$failed = 0;
foreach ($regencies as $reg) {
    $data = fetchJson("$baseUrl/districts/{$reg['id']}.json");
    if (!$data) {
        $failed++;
        if ($failed <= 5) echo "  WARNING: Gagal fetch {$reg['name']} ({$reg['id']})\n";
        continue;
    }
    foreach ($data as $dist) {
        $districts[] = [
            'id' => $dist['id'],
            'regency_id' => $reg['id'],
            'province_id' => $reg['province_id'],
            'name' => $dist['name'],
        ];
    }
    $count++;
    if ($count % 100 === 0) echo "  Progress: $count/$total kabupaten\n";
    usleep(500000); // 500ms delay
}
echo "  Berhasil: $count, Gagal: $failed\n";
echo "  Total: " . count($districts) . " kecamatan\n";

// 4. Generate file PHP
echo "[4/4] Membuat file regions.php...\n";

// Build nested structure
$nested = [];
foreach ($provinces as $prov) {
    $nested[$prov['id']] = [
        'nama' => $prov['name'],
        'kotkab' => [],
    ];
}

foreach ($regencies as $reg) {
    $pid = $reg['province_id'];
    $nested[$pid]['kotkab'][$reg['id']] = [
        'nama' => $reg['name'],
        'kecamatan' => [],
    ];
}

foreach ($districts as $dist) {
    $pid = $dist['province_id'];
    $rid = $dist['regency_id'];
    // adm4 code: district code + .1001 as representative village
    $adm4 = $dist['id'] . '.1001';
    $nested[$pid]['kotkab'][$rid]['kecamatan'][$dist['id']] = [
        'nama' => $dist['name'],
        'adm4' => $adm4,
    ];
}

// Generate PHP file content
$output = "<?php\n\n";
$output .= "/**\n";
$output .= " * Data Wilayah Indonesia untuk BMKG Weather API\n";
$output .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
$output .= " * Sumber: emsifa.github.io/api-wilayah-indonesia (BPS codes)\n";
$output .= " * Format adm4: {provinsi}.{kotkab}.{kecamatan}.{desa}\n";
$output .= " */\n\n";
$output .= "return " . exportArray($nested, 1) . ";\n";

file_put_contents(__DIR__ . '/regions.php', $output);
$size = filesize(__DIR__ . '/regions.php');
echo "  File disimpan: regions.php (" . number_format($size) . " bytes)\n";
echo "\n=== Selesai! ===\n";

function exportArray($arr, $indent = 0) {
    $pad = str_repeat('    ', $indent);
    $padInner = str_repeat('    ', $indent + 1);
    $lines = ["[\n"];
    foreach ($arr as $key => $value) {
        $keyStr = is_string($key) ? "'$key'" : $key;
        if (is_array($value)) {
            $lines[] = $padInner . "$keyStr => " . exportArray($value, $indent + 1) . ",\n";
        } elseif (is_string($value)) {
            $escaped = str_replace("'", "\\'", $value);
            $lines[] = $padInner . "$keyStr => '$escaped',\n";
        } else {
            $lines[] = $padInner . "$keyStr => $value,\n";
        }
    }
    $lines[] = $pad . "]";
    return implode('', $lines);
}
