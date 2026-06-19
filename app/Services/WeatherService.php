<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WeatherService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.openweather.url');
        $this->apiKey = config('services.openweather.key');
    }

    public function getForecast(string $city): array
    {
        if ($this->apiKey === 'masukan api key') {
            return $this->dummyForecast();
        }

        $key = 'weather_' . Str::slug($city);

        return Cache::remember($key, now()->addHour(), function () use ($city) {
            $response = Http::get("{$this->baseUrl}/forecast", [
                'q' => $city,
                'appid' => $this->apiKey,
                'units' => 'metric',
                'lang' => 'id',
                'cnt' => 40,
            ]);

            if ($response->failed()) {
                return [];
            }

            return $this->parseResponse($response->json());
        });
    }

    private function dummyForecast(): array
    {
        return [
            [
                'tanggal' => 'Senin, 17 Jun',
                'cuaca' => 'Rain',
                'suhu_max' => 29,
                'suhu_min' => 23,
                'angin_kmh' => 15.2,
                'hujan_mm' => 8.5,
            ],
            [
                'tanggal' => 'Selasa, 18 Jun',
                'cuaca' => 'Clouds',
                'suhu_max' => 31,
                'suhu_min' => 24,
                'angin_kmh' => 10.0,
                'hujan_mm' => 0,
            ],
            [
                'tanggal' => 'Rabu, 19 Jun',
                'cuaca' => 'Clear',
                'suhu_max' => 34,
                'suhu_min' => 25,
                'angin_kmh' => 8.7,
                'hujan_mm' => 0,
            ],
            [
                'tanggal' => 'Kamis, 20 Jun',
                'cuaca' => 'Thunderstorm',
                'suhu_max' => 28,
                'suhu_min' => 22,
                'angin_kmh' => 22.5,
                'hujan_mm' => 25.0,
            ],
            [
                'tanggal' => 'Jumat, 21 Jun',
                'cuaca' => 'Clouds',
                'suhu_max' => 30,
                'suhu_min' => 24,
                'angin_kmh' => 11.3,
                'hujan_mm' => 2.0,
            ],
        ];
    }

    private function parseResponse(array $data): array
    {
        $grouped = [];
        foreach ($data['list'] ?? [] as $item) {
            $date = date('Y-m-d', $item['dt']);
            $grouped[$date][] = $item;
        }

        $hari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];

        $result = [];
        foreach ($grouped as $date => $items) {
            $t = strtotime($date);
            $namaHari = $hari[date('l', $t)];

            $cuaca = $items[0]['weather'][0]['main'] ?? 'Clear';
            $suhuMax = -INF;
            $suhuMin = INF;
            $anginKmh = 0;
            $hujan = 0;

            foreach ($items as $item) {
                if ($item['main']['temp_max'] > $suhuMax) {
                    $suhuMax = $item['main']['temp_max'];
                }
                if ($item['main']['temp_min'] < $suhuMin) {
                    $suhuMin = $item['main']['temp_min'];
                }
                $s = $item['wind']['speed'] * 3.6;
                if ($s > $anginKmh) {
                    $anginKmh = $s;
                }
                $hujan += $item['rain']['3h'] ?? 0;
            }

            $result[] = [
                'tanggal' => $namaHari . ', ' . date('d M', $t),
                'cuaca' => $cuaca,
                'suhu_max' => round($suhuMax),
                'suhu_min' => round($suhuMin),
                'angin_kmh' => round($anginKmh, 1),
                'hujan_mm' => round($hujan, 1),
            ];
        }

        return $result;
    }
}
