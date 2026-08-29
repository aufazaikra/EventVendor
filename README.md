<div align="center">

# 🎉 SPK Vendor Acara Banda Aceh
### Sistem Pendukung Keputusan Pemilihan Vendor Acara Terbaik di Banda Aceh

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE)

> Aplikasi web berbasis **Laravel 12** yang membantu masyarakat Kota Banda Aceh menemukan vendor jasa acara terbaik menggunakan algoritma **SAW (Simple Additive Weighting)**. Dibangun sebagai proyek skripsi dengan pendekatan multi-criteria decision making (MCDM).

</div>

---

## 📋 Deskripsi

**SPK Vendor Acara Banda Aceh** adalah sebuah platform digital yang dirancang untuk menyelesaikan permasalahan kesulitan masyarakat dalam memilih vendor jasa acara (pernikahan, khitanan, seminar, dll.) yang tepat di Kota Banda Aceh. Sistem ini mengimplementasikan algoritma SAW untuk meranking vendor berdasarkan tiga kriteria utama: **harga**, **rating**, dan **pengalaman** (jumlah transaksi selesai), dengan bobot yang dapat dikustomisasi sendiri oleh pengguna.

Platform ini terinspirasi dari konsep marketplace vendor acara modern (seperti Bridestory), namun fokus pada kebutuhan lokal masyarakat Banda Aceh dengan mempertimbangkan filter berdasarkan kategori layanan dan kecamatan.

---

## ✨ Fitur Utama

### 🔍 Untuk Pengunjung / Calon Pelanggan

| Fitur | Deskripsi |
|---|---|
| **Rekomendasi Cerdas SAW** | Pencarian vendor dengan hasil terurut berdasarkan algoritma SAW yang dapat disesuaikan bobotnya |
| **Filter Multi-Kriteria** | Filter berdasarkan kategori layanan (7 kategori), kecamatan (9 kecamatan), dan anggaran maksimum |
| **Bobot Dinamis** | Pengguna dapat mengatur prioritas antara Harga, Rating, dan Pengalaman sesuai kebutuhan |
| **Profil Vendor Detail** | Halaman detail vendor dengan portofolio proyek, paket harga, ulasan pelanggan, dan kontak WhatsApp |
| **Ulasan & Rating** | Sistem ulasan bintang 1-5 dengan komentar dan balasan dari vendor |

### 🏢 Untuk Vendor

| Fitur | Deskripsi |
|---|---|
| **Registrasi Vendor** | Pendaftaran akun bisnis dengan profil lengkap (logo, banner, deskripsi, kontak) |
| **Dashboard Vendor** | Ringkasan statistik paket, rata-rata rating, dan status verifikasi |
| **Manajemen Paket Harga** | CRUD paket layanan dengan harga, fitur, dan opsional upload brosur PDF |
| **Manajemen Portofolio** | Upload foto dan deskripsi proyek/event yang pernah dikerjakan |
| **Kelola Pesanan** | Catat transaksi pelanggan dan perbarui status pesanan |
| **Balas Ulasan** | Merespons ulasan yang ditulis pelanggan |

### 🛡️ Untuk Admin

| Fitur | Deskripsi |
|---|---|
| **Dashboard Admin** | Statistik total pengguna, vendor, dan kategori |
| **Verifikasi Vendor** | Menyetujui atau mencabut verifikasi vendor agar tampil di halaman publik |
| **Manajemen Pengguna** | Melihat daftar pelanggan beserta riwayat transaksi |

---

## 🛠️ Tech Stack

| Layer | Teknologi |
|---|---|
| **Backend Framework** | [Laravel 12](https://laravel.com) (PHP 8.2+) |
| **Frontend Styling** | [Tailwind CSS v4](https://tailwindcss.com) |
| **Build Tool** | [Vite](https://vitejs.dev) |
| **Auth Scaffolding** | [Laravel Breeze](https://laravel.com/docs/breeze) |
| **Database** | MySQL (XAMPP) / SQLite (development) |
| **Email** | [Resend](https://resend.com) via `resend/resend-laravel` |
| **Testing** | [Pest PHP](https://pestphp.com) |
| **Deployment** | [Railway](https://railway.app) (via `nixpacks.toml`) |
| **Font** | Google Fonts (Plus Jakarta Sans, Inter) |

### Paket Composer Utama

```json
"laravel/framework": "^12.0",
"resend/resend-laravel": "^1.4",
"laravel/breeze": "^2.3"
```

---

## 🧮 Cara Kerja Sistem Rekomendasi (Algoritma SAW)

Sistem rekomendasi menggunakan metode **Simple Additive Weighting (SAW)**, salah satu metode MCDM (*Multi-Criteria Decision Making*) yang paling umum digunakan. Seluruh logika dikapsulasi dalam kelas [`SawService`](app/Services/SawService.php).

### Kriteria Penilaian

| Kode | Kriteria | Jenis | Cara Normalisasi |
|---|---|---|---|
| **C1** | Harga Minimum Paket | **Cost** (semakin murah semakin baik) | `r = Harga_min_kolom / Harga_vendor` |
| **C2** | Rating Rata-rata Ulasan | **Benefit** (semakin tinggi semakin baik) | `r = Rating_vendor / 5.0` |
| **C3** | Pengalaman (Jumlah Transaksi) | **Benefit** (semakin banyak semakin baik) | `r = Transaksi_vendor / Max_transaksi` |

### Alur Kalkulasi

```
1. INPUT PENGGUNA
   └── Filter: Kategori, Kecamatan, Anggaran Maksimum
   └── Bobot: Harga (%), Rating (%), Pengalaman (%)

2. NORMALISASI BOBOT
   └── w1 = bobotHarga / total
   └── w2 = bobotRating / total
   └── w3 = bobotPengalaman / total

3. QUERY DATABASE (Eager Loading, Anti N+1)
   └── withMin('pricelists', 'harga')   → C1
   └── withAvg('reviews', 'rating')     → C2
   └── withCount('transactions')        → C3

4. NORMALISASI MATRIKS PER VENDOR
   └── n1 = minHarga / hargaVendor      (Cost)
   └── n2 = ratingVendor / 5.0          (Benefit)
   └── n3 = transaksiVendor / maxTrans  (Benefit)

5. HITUNG NILAI PREFERENSI
   └── Skor = (n1 x w1) + (n2 x w2) + (n3 x w3)

6. PERANGKINGAN
   └── Vendor diurutkan dari skor tertinggi ke terendah
```

### Optimasi Performa

- **Eager Loading** dengan `withMin`, `withAvg`, `withCount` memastikan seluruh kalkulasi dilakukan di sisi **database**, bukan PHP, menghindari masalah N+1 query.
- **Query Result Caching** menggunakan `Cache::remember()` selama 15 menit berdasarkan hash unik dari kombinasi filter dan bobot pengguna.
- **Guard divide-by-zero** pada semua pembagian untuk mencegah error saat data masih kosong.

---

## 🗄️ Struktur Database

```
cities           → Kota (Banda Aceh)
categories       → Kategori layanan vendor (Katering, Dekorasi, MUA, dll.)
users            → Akun (admin / vendor / user/pelanggan)
vendors          → Profil bisnis vendor
vendor_addresses → Alamat detail vendor per kecamatan
pricelists       → Paket harga vendor          ← Sumber data C1 (Harga)
reviews          → Ulasan dan rating pelanggan  ← Sumber data C2 (Rating)
transactions     → Riwayat pesanan              ← Sumber data C3 (Pengalaman)
projects         → Album portofolio vendor
```

### Kategori Layanan yang Tersedia

1. 🍽️ Katering
2. 🌸 Dekorasi
3. 💄 Makeup Artist (MUA)
4. 📷 Fotografer
5. 🎵 Hiburan (Band/DJ)
6. 🎤 MC
7. 🏛️ Venue/Gedung

### Cakupan Wilayah (9 Kecamatan Banda Aceh)

`Baiturrahman` · `Banda Raya` · `Jaya Baru` · `Kuta Alam` · `Kuta Raja` · `Lueng Bata` · `Meuraxa` · `Syiah Kuala` · `Ulee Kareng`

---

## 🖥️ Tampilan Aplikasi

### Halaman Utama & Sistem Rekomendasi
- Form pencarian dengan filter kategori, kecamatan, dan anggaran
- Slider bobot dinamis untuk Harga, Rating, dan Pengalaman
- Kartu hasil rekomendasi vendor dengan skor SAW, rating bintang, dan harga terendah

### Halaman Detail Vendor
- Banner dan logo bisnis
- Deskripsi lengkap "Tentang Kami"
- Galeri foto portofolio proyek
- Daftar paket harga beserta fitur detail
- Ulasan pelanggan dengan sistem bintang
- Tombol kontak langsung via WhatsApp

### Dashboard Vendor
- Ringkasan statistik (total paket, rata-rata rating)
- Manajemen paket harga (CRUD)
- Manajemen portofolio (upload foto proyek)
- Halaman pesanan dan manajemen status transaksi

### Panel Admin
- Statistik platform (total user, vendor, kategori)
- Daftar vendor dengan tombol verifikasi / batalkan verifikasi
- Daftar pelanggan dengan riwayat transaksi

---

## 🚀 Cara Instalasi

### Prasyarat

Pastikan lingkungan pengembangan Anda memiliki:
- PHP `^8.2`
- Composer
- Node.js & NPM
- MySQL (XAMPP / Laragon / dll.)
- Git

### Langkah Instalasi

**1. Clone repository**

```bash
git clone https://github.com/aufazaikra/EventVendor.git
cd EventVendor
```

**2. Install dependensi PHP & JavaScript**

```bash
composer install
npm install
```

**3. Salin dan konfigurasi file `.env`**

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spk_vendor_aceh
DB_USERNAME=root
DB_PASSWORD=

APP_NAME="SPK Vendor Acara Banda Aceh"
APP_URL=http://localhost:8000
```

**4. Jalankan migrasi database**

```bash
php artisan migrate
```

**5. Seed data dummy (opsional)**

Buka browser dan akses URL berikut secara berurutan:

```
http://localhost:8000/debug-admin        → Buat akun admin
http://localhost:8000/seed-dummy-data    → Seed data vendor & pelanggan (70+ vendor)
http://localhost:8000/seed-transactions  → Seed data transaksi & ulasan
http://localhost:8000/seed-portfolio     → Seed data portofolio vendor
```

**6. Build aset frontend**

```bash
npm run build
```

**7. Jalankan server pengembangan**

```bash
composer run dev
# atau jalankan secara terpisah:
php artisan serve
npm run dev
```

Akses aplikasi di: **http://localhost:8000**

---

## 🔐 Akun Default

| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@eventvendor.com` | `admin123` |
| **Vendor** | Lihat endpoint `/list-vendor-credentials` | `[namapertama]123` |

---

## 📁 Struktur Proyek

```
skripsi-vendor/
├── app/
│   ├── Http/Controllers/
│   │   ├── RecommendationController.php  ← Orkestrasi SAW + caching
│   │   ├── AdminController.php           ← Panel admin
│   │   ├── VendorAuthController.php      ← Registrasi vendor
│   │   ├── VendorProfileController.php   ← Edit profil vendor
│   │   ├── PricelistController.php       ← CRUD paket harga
│   │   ├── ProjectController.php         ← CRUD portofolio
│   │   ├── TransactionController.php     ← Manajemen pesanan
│   │   └── ReviewController.php          ← Ulasan & balasan
│   ├── Models/
│   │   ├── Vendor.php, User.php, Pricelist.php
│   │   ├── Review.php, Transaction.php, Project.php
│   │   └── Category.php, City.php, VendorAddress.php
│   └── Services/
│       └── SawService.php                ← Inti algoritma SAW
├── database/
│   ├── migrations/                       ← Skema database
│   └── seeders/
│       └── VendorAndCustomerSeeder.php   ← 70+ vendor dummy Banda Aceh
├── resources/views/
│   ├── home.blade.php                    ← Halaman utama + hasil rekomendasi
│   ├── detail_vendor.blade.php           ← Profil vendor publik
│   ├── vendor/                           ← Dashboard area vendor
│   ├── admin/                            ← Panel admin
│   └── auth/                             ← Halaman login & register
└── routes/web.php                        ← Definisi seluruh rute
```

---

## 🧪 Menjalankan Pengujian

```bash
composer run test
# atau
php artisan test
```

---

## 📖 Referensi Algoritma

> **SAW (Simple Additive Weighting)** — Kusumadewi, S., Hartati, S., Harjoko, A., & Wardoyo, R. (2006). *Fuzzy Multi-Attribute Decision Making (MADM)*. Graha Ilmu.

Metode SAW dipilih karena:
- Mudah dipahami dan diimplementasikan
- Memungkinkan bobot preferensi dikustomisasi oleh pengguna
- Efektif untuk kasus pemilihan vendor dengan kriteria campuran (*cost* dan *benefit*)
- Cocok untuk dataset skala kecil-menengah seperti vendor lokal

---

## 👤 Author

**Muhammad Aufa Zaikra**
Mahasiswa Informatika — Universitas Syiah Kuala (USK), Banda Aceh

- 📧 Email: `muhammad.aufa2018@gmail.com`
- 🐙 GitHub: [@aufazaikra](https://github.com/aufazaikra)

> *Proyek ini dibuat sebagai tugas akhir (skripsi) dengan topik: "Sistem Pendukung Keputusan Pemilihan Vendor Jasa Acara di Kota Banda Aceh Menggunakan Metode Simple Additive Weighting (SAW)"*

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

```
MIT License (c) 2026 Muhammad Aufa Zaikra

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
```

---

<div align="center">

Made in Banda Aceh, Aceh, Indonesia

*"Menghubungkan masyarakat Banda Aceh dengan vendor acara terbaik, satu keputusan cerdas pada satu waktu."*

</div>
