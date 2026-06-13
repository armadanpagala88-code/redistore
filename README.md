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
   ```bash
   git clone https://github.com/armadanpagala88-code/redistore.git
   cd redistore
   ```

2. **Install Dependensi Backend (PHP/Laravel)**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment & Integrasi API**
   Salin file konfigurasi lingkungan dasar:
   ```bash
   cp .env.example .env
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

4. **Migrasi Database & Data Dummy**
   Untuk mendapatkan struktur database sekaligus data dummy (Game, Produk, Voucher Promo, Pengaturan, Akun Default):
   ```bash
   touch database/database.sqlite
   php artisan migrate:fresh --seed
   ```

5. **Install Dependensi Frontend (Node.js/Vue)**
   ```bash
   npm install
   ```

6. **Menjalankan Server**
   Jalankan perintah ini untuk menyalakan server Backend dan merender Frontend secara bersamaan:
   ```bash
   npm run dev
   ```
   Aplikasi siap diakses melalui: `http://localhost:8000`

## 🔑 Pengguna Default (Akses Admin)

Setelah menjalankan Seeder, Anda bisa *Login* ke Panel Admin dengan kredensial:
- **Admin**: `username`: admin | `password`: password
- **Owner**: `username`: owner | `password`: password

## 📝 Lisensi & Hak Cipta

Proyek ini dibangun secara ekstensif sebagai *showcase* teknologi E-Commerce Autopilot. 
Pengembangan ditujukan untuk riset dan kelulusan akademik (S1 Sistem Informasi di Universitas Bina Sarana Informatika). Dilarang menggandakan dan mengkomersialkan kode ini secara massal tanpa memberikan kredit yang sesuai kepada pengembang asli.
