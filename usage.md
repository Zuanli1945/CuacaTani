# CuacaTani — Usage Guide

## Setup Awal

```bash
git clone <repo-url> && cd cuacatani
composer install
cp .env.example .env
# Isi DB_*, OWM_API_KEY di .env
php artisan key:generate
php artisan migrate
php artisan serve
```

---

## Alur Penggunaan

### 1. Registrasi
`POST /register` — isi nama, email, password, dan **kota** (dipakai untuk fetch cuaca).

### 2. Kelola Lahan
| Route | Aksi |
|---|---|
| `GET /lahan` | Daftar lahan milik user |
| `GET /lahan/create` | Form tambah lahan |
| `POST /lahan` | Simpan lahan baru |
| `GET /lahan/{id}/edit` | Edit lahan |
| `DELETE /lahan/{id}` | Hapus lahan |

Field: `nama_lahan`, `komoditas` (padi/jagung), `luas_hektar`.

### 3. Lihat Cuaca & Rekomendasi
`GET /cuaca` — otomatis fetch berdasarkan `city` dari profil user.

Tampilan: tabel 5 hari + kartu rekomendasi per hari.

---

## Struktur Rekomendasi Output

```
┌─────────────────────────────────────────────┐
│ Senin, 16 Jun — ☁ Hujan Ringan              │
│ Suhu: 24–29°C  │ Angin: 12 km/h             │
├─────────────────────────────────────────────┤
│ ⚠ Tunda pemupukan karena hujan diprediksi.  │
└─────────────────────────────────────────────┘
```

Level badge: `info` (biru) · `warning` (kuning) · `danger` (merah).

---

## Testing (Pest)

```bash
php artisan test                     # semua test
php artisan test --filter Lahan      # test spesifik
php artisan test --filter Rekomendasi
```

Test files:
```
tests/Feature/
├── LahanTest.php          # CRUD lahan
├── WeatherServiceTest.php # mock OWM response
└── RekomendasiTest.php    # assert output rule engine
```

---

## Troubleshooting

| Masalah | Solusi |
|---|---|
| `OWM 401 Unauthorized` | Cek `OWM_API_KEY` di `.env`, jalankan `php artisan config:clear` |
| Cuaca tidak update | Cache masih aktif, tunggu 1 jam atau `php artisan cache:clear` |
| Kota tidak ditemukan | Gunakan nama kota bahasa Inggris (misal: `Purwakarta`, bukan `Kota Purwakarta`) |
| Migration error | Pastikan DB sudah dibuat, cek `DB_DATABASE` di `.env` |
