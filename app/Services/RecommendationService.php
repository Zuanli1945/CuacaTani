<?php

namespace App\Services;

class RecommendationService
{
    // Threshold berbeda per komoditas
    private array $config = [
        'padi'   => ['suhu_kritis' => 35, 'butuh_air_tinggi' => true],
        'jagung' => ['suhu_kritis' => 38, 'butuh_air_tinggi' => false],
    ];

    public function generate(array $forecasts, string $komoditas): array
    {
        return array_map(fn($f) => [
            'tanggal' => $f['tanggal'],
            'level'   => $this->determineLevel($f, $komoditas),
            'saran'   => $this->buildSaran($f, $komoditas),
        ], $forecasts);
    }

    private function buildSaran(array $f, string $komoditas): string
    {
        $cfg = $this->config[$komoditas] ?? $this->config['padi'];

        if (in_array($f['cuaca'], ['Thunderstorm'])) {
            return "Hentikan semua aktivitas lahan $komoditas, risiko petir tinggi.";
        }
        if ($f['hujan_mm'] > 0 || $f['cuaca'] === 'Rain') {
            return "Tunda pemupukan $komoditas, hujan diprediksi.";
        }
        if ($f['angin_kmh'] > 20) {
            return "Hindari penyemprotan pestisida $komoditas, angin terlalu kencang.";
        }
        if ($f['suhu_max'] >= $cfg['suhu_kritis']) {
            $waktu = $cfg['butuh_air_tinggi'] ? 'pagi, siang, dan sore' : 'pagi dan sore';
            return "Suhu ekstrem, siram $komoditas $waktu.";
        }
        if ($f['suhu_max'] > 32) {
            return "Cuaca panas, lakukan penyiraman $komoditas pagi/sore.";
        }

        return "Kondisi aman, jadwal tanam/pupuk $komoditas dapat dilanjutkan.";
    }

    private function determineLevel(array $f, string $komoditas): string
    {
        $cfg = $this->config[$komoditas] ?? $this->config['padi'];

        if (in_array($f['cuaca'], ['Thunderstorm'])) return 'danger';
        if ($f['hujan_mm'] > 5)                      return 'danger';
        if ($f['suhu_max'] >= $cfg['suhu_kritis'])    return 'danger';

        if ($f['hujan_mm'] > 0 || $f['cuaca'] === 'Rain') return 'warning';
        if ($f['angin_kmh'] > 20)                          return 'warning';
        if ($f['suhu_max'] > 32)                           return 'warning';

        return 'info';
    }
}
