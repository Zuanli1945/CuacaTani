# CuacaTani — Agent Scaffolding Guide

Panduan ini digunakan sebagai konteks untuk AI coding assistant (Copilot, Claude, Cursor, dll).

---

## Project Context

Laravel 11 app untuk petani. Fitur utama:
1. Auth + manajemen lahan (komoditas padi/jagung, luas, kota)
2. Prakiraan cuaca 5 hari via OpenWeatherMap API
3. Rekomendasi aktivitas bertani berbasis rule sederhana

---

## Konvensi Kode

- Bahasa komentar: **Indonesia**
- Gunakan **Service class** untuk logika bisnis, bukan di Controller
- Controller hanya: validasi → panggil Service → return view/response
- Selalu gunakan `$request->validated()` setelah `FormRequest`
- Nama variabel: camelCase, nama tabel: snake_case
- **Never fabricate functions/APIs.** Jika ragu, tambahkan komentar `// unverified` dan beri tahu.
- **DRY:** Ekstrak logika berulang ke fungsi bernama yang bisa dipakai ulang. Satu fungsi satu tugas.
- **Pilih algoritma yang tepat.** Tulis Big-O untuk logika non-sepele.
- **Tulis kode idiomatis dan mudah dibaca** — tidak perlu over-engineering.
- **Selalu verifikasi contoh output** dengan menelusuri logika sebelum menuliskannya.
- **Patuhi constraint dengan ketat.** Jika tidak bisa, katakan — jangan diam-diam mencari jalan alternatif.
- **Jika perintah ambigu, tanya dulu.**

---

## File Kunci

```
app/
├── Http/Controllers/
│   ├── LahanController.php       # CRUD lahan milik petani
│   ├── WeatherController.php     # Tampilkan forecast + rekomendasi
│   └── Auth/                     # Breeze default
├── Models/
│   ├── User.php                  # tambahkan field: city
│   └── Lahan.php                 # belongsTo User
├── Services/
│   ├── WeatherService.php        # fetch + cache OWM
│   └── RecommendationService.php # rule engine
resources/views/
├── lahan/                        # index, create, edit
└── cuaca/                        # show (forecast + rekomendasi)
```

---

## WeatherService — Interface yang Diharapkan

```php
// Kembalikan array forecast per hari, sudah diproses
$forecasts = $weatherService->getForecast(string $city): array;

// Struktur tiap elemen:
// [
//   'tanggal'   => 'Senin, 16 Jun',
//   'cuaca'     => 'Rain',           // dari weather[0].main
//   'suhu_max'  => 32,
//   'suhu_min'  => 24,
//   'angin_kmh' => 15,
//   'hujan_mm'  => 2.5,              // rain.3h, default 0
// ]
```

---

## RecommendationService — Interface yang Diharapkan

```php
// Terima array forecast dari WeatherService
// Kembalikan array rekomendasi per hari
$recs = $recService->generate(array $forecasts, string $komoditas): array;

// Struktur tiap elemen:
// [
//   'tanggal'     => 'Senin, 16 Jun',
//   'saran'       => 'Tunda pemupukan karena hujan diprediksi.',
//   'level'       => 'warning', // 'info' | 'warning' | 'danger'
// ]
```

---

## Cache Strategy

```php
// Di WeatherService, gunakan Laravel Cache
$key = 'weather_' . Str::slug($city);
return Cache::remember($key, now()->addHour(), function () use ($city) {
    // fetch dari OWM
});
```

---

## Environment Variables (.env)

```
OWM_API_KEY=your_key_here
OWM_BASE_URL=https://api.openweathermap.org/data/2.5
```

---

## Prompt Template untuk AI Assistant

Ketika minta bantuan AI, gunakan prefix ini:

```
Konteks: Laravel 11, project CuacaTani.
File target: [nama file]
Tugas: [deskripsi singkat]
Constraint: Ikuti konvensi di agent.md. Jangan ubah file lain.
```
