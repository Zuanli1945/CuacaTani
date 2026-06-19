<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function __construct(
        protected WeatherService $weatherService,
        protected RecommendationService $recommendationService,
    ) {}

    public function forecast(Request $request): JsonResponse
    {
        $data = $request->validate([
            'city' => 'required|string|max:100',
            'komoditas' => 'required|string|in:padi,jagung',
        ]);

        $forecasts = $this->weatherService->getForecast($data['city']);

        if (empty($forecasts)) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data cuaca. Periksa nama kota atau API key.',
            ], 502);
        }

        $recommendations = $this->recommendationService->generate($forecasts, $data['komoditas']);

        return response()->json([
            'success' => true,
            'city' => $data['city'],
            'komoditas' => $data['komoditas'],
            'forecasts' => $forecasts,
            'recommendations' => $recommendations,
        ]);
    }
}
