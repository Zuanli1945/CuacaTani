<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WeatherService
{
    public function getForecast(string $city): array
    {
        $key = 'weather_' . Str::slug($city);

        return Cache::remember($key, now()->addHour(), function () use ($city) {
            $response = Http::get(config('services.openweather.url') . '/forecast', [
                'q' => $city,
                'appid' => config('services.openweather.key'),
                'units' => 'metric',
                'lang' => 'id',
                'cnt' => 40,
            ]);

            if ($response->failed()) {
                return [];
            }

            $data = $response->json();
            $grouped = collect($data['list'])->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item['dt_txt'])->format('Y-m-d');
            });

            $forecasts = [];
            $days = 0;

            foreach ($grouped as $date => $items) {
                if ($days >= 5) break;

                $first = $items->first();
                $tempMax = round($items->max('main.temp_max'));
                $tempMin = round($items->min('main.temp_min'));
                $weatherMain = $first['weather'][0]['main'];
                $windMax = round($items->max('wind.speed'));
                $rainTotal = $items->sum(function ($item) {
                    return $item['rain']['3h'] ?? 0;
                });

                $forecasts[] = [
                    'tanggal' => \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMM'),
                    'cuaca' => $weatherMain,
                    'suhu_max' => $tempMax,
                    'suhu_min' => $tempMin,
                    'angin_kmh' => $windMax,
                    'hujan_mm' => round($rainTotal, 1),
                ];

                $days++;
            }

            return $forecasts;
        });
    }
}
