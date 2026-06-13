# Redistore - E-Commerce Akun Game Online

Redistore adalah Sistem E-Commerce berbasis Web yang dirancang khusus untuk memfasilitasi transaksi top-up dan jual beli akun game online seperti Mobile Legends, Free Fire, dan Genshin Impact. Sistem ini menyediakan platform yang aman, transparan, dan terstruktur untuk pelanggan dan pengelola.

## Teknologi Utama

- **Backend**: Laravel 12 (PHP ^8.2)
- **Frontend**: Vue.js 3 + TypeScript
- **UI Framework**: Vuetify & Materialize Admin Template
- **Database**: SQLite (dapat diubah ke MySQL/PostgreSQL di `.env`)
- **Bundler**: Vite

## Fitur Utama

- **Katalog Produk Real-Time**: Tampilan daftar harga dan stok voucher/akun game yang otomatis diperbarui.
- **Transaksi Aman**: Pengelolaan transaksi terpadu, didukung oleh validasi manual/otomatis.
- **Notifikasi WhatsApp**: (Mendatang) Integrasi menggunakan API Fonnte untuk notifikasi pesanan pelanggan.
- **Sistem Rekomendasi & Diskon**: Dukungan kode voucher diskon dan rekomendasi berdasarkan riwayat pembelian.
- **Dashboard Admin**: Pengelolaan penuh terhadap data pengguna, kategori game, produk voucher, transaksi, dan pembuatan laporan.

## Prasyarat Instalasi

Pastikan sistem Anda telah memiliki:
- PHP >= 8.2
- Composer
- Node.js & NPM
- Git

## Cara Instalasi & Menjalankan Aplikasi

1. **Clone Repositori**
   ```bash
   git clone https://github.com/armadanpagala88-code/redistore.git
   cd redistore
   ```

2. **Install Dependensi Backend (PHP/Laravel)**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**
   Salin file konfigurasi lingkungan:
   ```bash
   cp .env.example .env
   ```
   Generate application key:
   ```bash
   php artisan key:generate
   ```

4. **Migrasi Database & Data Dummy**
   Untuk langsung mendapatkan struktur database beserta data dummy (Kategori, Produk, User Admin):
   ```bash
   touch database/database.sqlite
   php artisan migrate:fresh --seed
   ```

5. **Install Dependensi Frontend (Node.js/Vue)**
   ```bash
   npm install
   ```

6. **Menjalankan Server**
   Jalankan perintah berikut untuk mengaktifkan server PHP dan Vite (Frontend) secara bersamaan:
   ```bash
   npm run dev
   ```
   Aplikasi dapat diakses melalui browser pada `http://localhost:8000`.

## Pengguna Default (Hasil Seeder)

Anda dapat menggunakan akun berikut untuk masuk ke sistem:
- **Admin**: `username`: admin | `password`: password
- **Owner**: `username`: owner | `password`: password

## Lisensi

Proyek ini dibuat sebagai syarat kelulusan Program Sarjana (S1) Sistem Informasi di Universitas Bina Sarana Informatika. Dilarang keras menggandakan sebagian atau seluruh isi aplikasi dan karya tulis tanpa seizin pengembang.
