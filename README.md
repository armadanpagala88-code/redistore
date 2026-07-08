# Redistore - E-Commerce Top Up Game & Produk Digital

Redistore adalah platform modern berbasis web yang dikembangkan untuk melayani transaksi *top-up* game online (seperti Mobile Legends, Free Fire, Valorant, dll) secara otomatis, instan, dan beroperasi 24 jam nonstop tanpa campur tangan admin (Autopilot). Sistem ini dibangun dengan antarmuka yang sangat responsif, mewah (mengusung *Glassmorphism* dan *Dark Mode*), serta ditenagai teknologi mutakhir.

## 🚀 Fitur Unggulan (Autopilot System)

Redistore tidak hanya memajang produk, melainkan sepenuhnya memproses pesanan secara otomatis:

- **💳 Pembayaran Otomatis (Midtrans Payment Gateway)**
  Pelanggan tidak perlu lagi mengunggah foto bukti transfer manual. Mendukung berbagai metode pembayaran secara instan (QRIS, GoPay, OVO, ShopeePay, Virtual Account Bank Mandiri, BCA, BNI, dll). Transaksi dikonfirmasi otomatis melalui teknologi *Webhook*.

- **🤖 Top Up Otomatis (API Digiflazz)**
  Begitu pembayaran dikonfirmasi oleh Midtrans, sistem akan langsung mengirim *request* ke server Digiflazz. *Diamonds* atau *Voucher* game akan langsung masuk ke ID pelanggan dalam hitungan detik.

- **💬 Notifikasi Cerdas WhatsApp (Fonnte API)**
  Memberikan pengalaman bintang lima kepada pelanggan. Sistem otomatis mengirimkan pesan tagihan pembayaran dan mengirimkan pemberitahuan kesuksesan beserta Bukti/Serial Number (SN) langsung ke nomor WhatsApp pembeli.

- **🧑‍💻 Sistem Member & Saldo Wallet**
  Pelanggan dapat mendaftar akun, melihat riwayat transaksi, serta memantau mutasi sisa Saldo (*Wallet*) melalui *Dashboard Member* yang interaktif. Terdapat juga pembagian hak akses (Level Basic, VIP, dan Reseller).

- **⚙️ Panel Admin Dinamis & Pengaturan Web**
  Mendukung pengelolaan produk, kategori, pembuatan kode voucher promo (diskon persen/nominal), cetak laporan transaksi (*Export PDF*), manajemen *Banner/Slider*, hingga merubah nama dan deskripsi Web secara *real-time* langsung melalui antarmuka Admin (tanpa *coding*).

## 🛠️ Teknologi yang Digunakan

Aplikasi ini mengkombinasikan *framework* terbaik di industri modern:

- **Backend**: Laravel 12 (PHP ^8.2) ditenagai REST API penuh
- **Frontend**: Vue.js 3 + TypeScript (Composition API)
- **UI & Styling**: Vuetify 3 (Material Design), Custom SCSS (Glassmorphism UI)
- **Database**: SQLite (Default) / MySQL / PostgreSQL (Fleksibel via `.env`)
- **API Integrations**: Midtrans (Payment), Digiflazz (Provider TopUp), Fonnte (WhatsApp Gateway)
- **Bundler**: Vite

## 📋 Prasyarat Instalasi

Pastikan sistem/server Anda telah meng-install:
- PHP >= 8.2 (dengan ekstensi cURL, SQLite3/MySQL, PDO)
- Composer
- Node.js & NPM
- Git

## 💻 Cara Instalasi & Menjalankan Aplikasi

1. **Clone Repositori**
   Pertama-tama, clone *source code* ini ke komputer lokal Anda:
   ```bash
   git clone https://github.com/armadanpagala88-code/redistore.git
   cd redistore
   ```

2. **Install Dependensi Backend (PHP/Laravel)**
   Pastikan Anda sudah menginstall Composer, lalu jalankan:
   ```bash
   composer install
   ```

3. **Konfigurasi Environment & Integrasi API**
   Salin file konfigurasi lingkungan dasar menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Lalu hasilkan *Application Key*:
   ```bash
   php artisan key:generate
   ```
   **PENTING:** Buka file `.env` Anda dan isi *Credentials* untuk API pihak ketiga agar fitur otomatis berjalan:
   ```env
   # Pengaturan Midtrans
   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxx
   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxx
   MIDTRANS_IS_PRODUCTION=false

   # Pengaturan Digiflazz
   DIGIFLAZZ_USERNAME=username_anda
   DIGIFLAZZ_API_KEY=apikey_anda

   # Pengaturan Notifikasi Fonnte WA
   FONNTE_TOKEN=token_fonnte_anda
   ```

4. **Konfigurasi Database**
   Buka file `.env` dan atur koneksi database Anda (defaultnya menggunakan MySQL/MariaDB). Buat database kosong terlebih dahulu (misal: `redistore`), lalu sesuaikan konfigurasi berikut:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=redistore
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   *(Catatan: Jika Anda ingin menggunakan SQLite, biarkan `DB_CONNECTION=sqlite` dan hapus konfigurasi `DB_HOST` dkk. Lalu buat file kosong dengan perintah `touch database/database.sqlite`)*

5. **Migrasi Database & Seeding Data**
   Setelah database diatur, buat struktur tabel dan masukkan data dasar (Akun Admin & Owner) dengan menjalankan perintah berikut:
   ```bash
   php artisan migrate:fresh --seed
   ```

   **Generate Dummy Data (Opsional tapi Sangat Disarankan):**
   Untuk memudahkan *testing* atau pratinjau tampilan web, sistem ini dilengkapi dengan data dummy. Anda wajib menjalankan seeder berikut jika ingin web terisi data dummy akun game dan produk:
   ```bash
   # Masukkan 10 dummy pelanggan dan 20 akun game jualan
   php artisan db:seed --class=DummyDataSeeder

   # Masukkan 50 dummy produk voucher beserta kategorinya
   php artisan db:seed --class=DummyVoucherSeeder
   ```

6. **Link Storage (Untuk Gambar/File)**
   Agar file gambar produk dan banner bisa diakses secara publik, jalankan:
   ```bash
   php artisan storage:link
   ```

7. **Install Dependensi Frontend (Node.js/Vue)**
   Jalankan perintah ini untuk menginstall semua paket Node yang dibutuhkan (pastikan Node.js terinstall):
   ```bash
   npm install
   ```

8. **Menjalankan Aplikasi (Development)**
   Buka dua jendela/tab terminal secara bersamaan di dalam folder proyek.
   
   Di terminal pertama, jalankan server backend Laravel:
   ```bash
   php artisan serve
   ```
   
   Di terminal kedua, jalankan *compiler* frontend Vite:
   ```bash
   npm run dev
   ```
   Aplikasi Anda siap diakses melalui browser di alamat: `http://localhost:8000` (atau `http://127.0.0.1:8000`).

## 🔑 Pengguna Default (Akses Admin)

Setelah menjalankan `php artisan migrate:fresh --seed`, Anda bisa *Login* ke Panel Admin dengan kredensial berikut:
- **Admin Utama**: `username`: admin | `password`: password

## 📝 Lisensi & Hak Cipta

Proyek ini dibangun secara ekstensif sebagai *showcase* teknologi E-Commerce Autopilot. 
Pengembangan ditujukan untuk riset dan kelulusan akademik (S1 Sistem Informasi di Universitas Bina Sarana Informatika). Dilarang menggandakan dan mengkomersialkan kode ini secara massal tanpa memberikan kredit yang sesuai kepada pengembang asli.
