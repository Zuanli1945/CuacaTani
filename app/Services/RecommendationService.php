<?php

namespace App\Services;

class RecommendationService
{
    private array $config = [
        "padi" => ["suhu_kritis" => 35, "butuh_air_tinggi" => true],
        "jagung" => ["suhu_kritis" => 38, "butuh_air_tinggi" => false],
    ];

    public function generate(array $forecasts, string $komoditas): array
    {
        $result = [];
        foreach ($forecasts as $f) {
            $r = $this->recommend($f, $komoditas);
            $result[] = [
                "tanggal" => $f["tanggal"],
                "level" => $r["level"],
                "saran" => $r["saran"],
            ];
        }
        return $result;
    }

    private function recommend(array $f, string $komoditas): array
    {
        $cfg = $this->config[$komoditas] ?? $this->config["padi"];

        if (in_array($f["cuaca"], ["Thunderstorm"])) {
            return [
                "level" => "danger",
                "saran" => "Hentikan semua aktivitas lahan $komoditas, risiko petir tinggi.",
            ];
        }
        if ($f["hujan_mm"] > 5) {
            return [
                "level" => "danger",
                "saran" => "Hindari aktivitas lahan $komoditas, curah hujan tinggi.",
            ];
        }
        if ($f["suhu_max"] >= $cfg["suhu_kritis"]) {
            $waktu = $cfg["butuh_air_tinggi"] ? "pagi, siang, dan sore" : "pagi dan sore";
            return [
                "level" => "danger",
                "saran" => "Suhu ekstrem, siram $komoditas $waktu.",
            ];
        }
        if ($f["hujan_mm"] > 0 || $f["cuaca"] === "Rain") {
            return [
                "level" => "warning",
                "saran" => "Tunda pemupukan $komoditas, hujan diprediksi.",
            ];
        }
        if ($f["angin_kmh"] > 20) {
            return [
                "level" => "warning",
                "saran" => "Hindari penyemprotan pestisida $komoditas, angin terlalu kencang.",
            ];
        }
        if ($f["suhu_max"] > 32) {
            return [
                "level" => "warning",
                "saran" => "Cuaca panas, lakukan penyiraman $komoditas pagi/sore.",
            ];
        }
        return [
            "level" => "info",
            "saran" => "Kondisi aman, jadwal tanam/pupuk $komoditas dapat dilanjutkan.",
        ];
    }
}
