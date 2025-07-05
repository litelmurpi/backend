<h1 align="center">
  📚 Jurnal Belajar Digital
</h1>

<div align="center">
  <img src="https://via.placeholder.com/200x200.png?text=Logo" width="200" alt="Logo Jurnal Belajar Digital"/>
</div>

<p align="center">
  Aplikasi web jurnal belajar digital yang membantu pelajar, mahasiswa, dan self-learner membangun konsistensi dan melacak kemajuan belajar mereka dengan visualisasi menarik.
</p>

<p align="center">
    <a href="#">
      <img src="https://img.shields.io/badge/status-development-yellow" alt="Status Proyek">
    </a>
    <a href="#">
      <img src="https://img.shields.io/badge/license-MIT-blue" alt="Lisensi">
    </a>
    <a href="#">
      <img src="https://img.shields.io/badge/PHP-73.7%25-777BB4?logo=php" alt="PHP">
    </a>
    <a href="#">
      <img src="https://img.shields.io/badge/Laravel-Framework-FF2D20?logo=laravel" alt="Laravel">
    </a>
</p>

---

## 🌟 Visi Proyek

Sebuah aplikasi web berupa jurnal belajar digital yang bertujuan untuk membantu pengguna (pelajar, mahasiswa, self-learner) membangun konsistensi dan melacak kemajuan belajar mereka.

## 🔥 Fitur Andalan

### Kalender Heatmap 
Fitur utama yang terinspirasi dari GitHub, di mana warna setiap tanggal akan merepresentasikan total durasi belajar pada hari itu. Ini memberikan bukti visual yang memotivasi pengguna untuk tetap konsisten.

<p align="center">
  <img src="https://via.placeholder.com/600x200.png?text=Heatmap+Calendar+Preview" alt="Heatmap Calendar">
</p>

---

## 🌐 Akses Demo & Akun Pengujian

- **Link Demo**: `[COMING SOON]`
- **Link API Documentation**: `[COMING SOON]`
- **Demo Account**:
  - Email: `demo@jurnalbelajar.com`
  - Password: `demo12345`

---

## ✨ Tampilan Aplikasi

<p align="center">
  <img src="https://via.placeholder.com/200x400.png?text=Dashboard" width="200" alt="Dashboard">
  <img src="https://via.placeholder.com/200x400.png?text=Heatmap" width="200" alt="Heatmap View">
  <img src="https://via.placeholder.com/200x400.png?text=Jurnal+Entry" width="200" alt="Jurnal Entry">
  <img src="https://via.placeholder.com/200x400.png?text=Statistics" width="200" alt="Statistics">
</p>

---

## 📝 Status Development

**Fitur yang Sudah Diimplementasikan:**
- [x] Autentikasi Pengguna (Login & Register)
- [x] Database Schema Design
- [ ] Dashboard Utama
- [ ] Kalender Heatmap
- [ ] Input Jurnal Belajar
- [ ] Tracking Durasi Belajar
- [ ] Statistik & Analytics
- [ ] Goal Setting
- [ ] Reminder System
- [ ] Export Data

---

## 🌟 Fitur Aplikasi

### 1. Tracking & Monitoring
- **Jurnal Harian**: Catat aktivitas belajar harian dengan detail topik, durasi, dan catatan
- **Kalender Heatmap**: Visualisasi konsistensi belajar dengan warna yang merepresentasikan intensitas
- **Timer Belajar**: Built-in timer untuk tracking durasi belajar secara real-time

### 2. Analytics & Insights
- **Statistik Pembelajaran**: Grafik dan insights tentang pola belajar, topik favorit, dan produktivitas
- **Streak Counter**: Hitung hari berturut-turut belajar untuk memotivasi konsistensi
- **Progress Tracking**: Pantau kemajuan belajar per topik/mata pelajaran

### 3. Motivasi & Gamifikasi
- **Achievement System**: Unlock badges dan achievements berdasarkan milestone belajar
- **Goal Setting**: Set target belajar harian/mingguan/bulanan
- **Reminder & Notification**: Pengingat untuk menjaga konsistensi belajar

### 4. Organisasi & Produktivitas
- **Kategorisasi Topik**: Organisir jurnal berdasarkan mata pelajaran atau topik
- **Notes & Resources**: Simpan catatan dan resource pembelajaran
- **Export Feature**: Export jurnal dalam format PDF/CSV untuk dokumentasi

---

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel (PHP)
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **API**: RESTful API

### Frontend
- **Template Engine**: Blade
- **CSS Framework**: Tailwind CSS
- **JavaScript**: Alpine.js / Vanilla JS
- **Charts**: Chart.js untuk visualisasi data

### Tools & Libraries
- **Calendar Heatmap**: Custom implementation atau library seperti cal-heatmap
- **Date Management**: Carbon (PHP)
- **Form Validation**: Laravel Validation
- **File Storage**: Laravel Storage

---

## 🚀 Cara Menjalankan Proyek Secara Lokal

### Prerequisites
- PHP >= 8.0
- Composer
- MySQL
- Node.js & NPM
- Git

### Installation Steps

1. **Clone repository:**
   ```bash
   git clone https://github.com/litelmurpi/backend.git
   cd backend
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

4. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

5. **Configure database di `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=jurnal_belajar
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Jalankan migrasi database:**
   ```bash
   php artisan migrate
   ```

7. **Seed database (optional):**
   ```bash
   php artisan db:seed
   ```

8. **Install frontend dependencies:**
   ```bash
   npm install
   ```

9. **Build assets:**
   ```bash
   npm run dev
   # atau untuk production
   npm run build
   ```

10. **Jalankan server:**
    ```bash
    php artisan serve
    ```
    
    Aplikasi akan berjalan di `http://localhost:8000`

### Development Mode

Untuk development dengan hot-reload:
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run watch
```

---

## 📁 Struktur Proyek

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   └── Services/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── views/
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php
│   └── api.php
└── storage/
```

---

## 🔒 Environment Variables

Berikut adalah environment variables yang perlu dikonfigurasi:

```env
APP_NAME="Jurnal Belajar Digital"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jurnal_belajar
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
```

---

## 🤝 Kontribusi

Kami menerima kontribusi! Silakan ikuti langkah berikut:

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 📝 License

Distributed under the MIT License. See `LICENSE` for more information.

---

## 📧 Kontak

- **Developer**: [@litelmurpi](https://github.com/litelmurpi)
- **Project Link**: [https://github.com/litelmurpi/backend](https://github.com/litelmurpi/backend)

---

<p align="center">
  Made with ❤️ for learners everywhere
</p>
