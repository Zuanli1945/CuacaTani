# CuacaTani — Architecture

## Stack
- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** Blade + Tailwind CSS
- **Database:** MySQL / MariaDB
- **External API:** OpenWeatherMap (Current Weather + 5-Day Forecast)
- **Auth:** Laravel Breeze (session-based)

---

## Module Boundaries (per anggota)

| Modul | PIC | Tanggung Jawab |
|---|---|---|
| Auth & Lahan | Mhs 1 | Register/login petani, CRUD lahan (komoditas, luas) |
| Cuaca | Mhs 2 | Fetch OWM API, simpan cache, tampilkan forecast 5 hari |
| Rekomendasi | Mhs 3 | Rule-based engine dari data cuaca → saran aktivitas |

---

## Database Schema

```
users               lahan                   weather_cache
─────────           ─────────────────       ──────────────────────
id (PK)             id (PK)                 id (PK)
name                user_id (FK→users)      city
email               nama_lahan              data (JSON)
password            komoditas (enum)        fetched_at
city                luas_hektar             
timestamps          timestamps              
```

> `weather_cache` TTL = 1 jam (cek `fetched_at` sebelum re-fetch).

---

## Data Flow

```
[Petani login]
      │
      ▼
[Pilih Lahan] → city dari users.city
      │
      ▼
[WeatherService] → cek cache → HIT: return cache
                             → MISS: call OWM API → simpan cache
      │
      ▼
[RecommendationService] → terima array forecast
      │
      ▼
[Blade View] → tampilkan tabel 5 hari + kartu rekomendasi
```

---

## Service Classes

```
app/Services/
├── WeatherService.php          # HTTP call ke OWM, parse response
└── RecommendationService.php   # Rule engine, return array saran
```

---

## OWM Endpoint yang Dipakai

```
GET https://api.openweathermap.org/data/2.5/forecast
    ?q={city}&appid={API_KEY}&units=metric&lang=id&cnt=40
```

Response diparsing per-hari (groupBy date), ambil: `weather[0].main`, `main.temp`, `wind.speed`, `rain.3h`.

---

## Rekomendasi Rule (ringkasan)

| Kondisi | Rekomendasi |
|---|---|
| rain > 0 atau weather = Rain/Thunderstorm | Tunda pemupukan |
| temp > 32°C dan rain = 0 | Lakukan penyiraman pagi/sore |
| wind > 20 km/h | Hindari penyemprotan pestisida |
| Cuaca cerah, temp normal | Jadwal tanam/pupuk aman |

> Detail lengkap di `RecommendationService.php`.
