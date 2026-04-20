# 🎓 ScholarHub — Portal Beasiswa Indonesia

> **UTS Pemrograman Web | Kelompok 3 | Sistem Informasi D | UPN "Veteran" Jawa Timur | TA 2025/2026**  
> Dosen: Reisa Permatasari, S.T., M.Kom.

Platform beasiswa digital berbasis web yang mendukung **SDG Goal 4 (Quality Education)**.  
Memungkinkan mahasiswa menemukan, mendaftar, dan memantau status beasiswa secara online.

🌐 **Live Website Deployment:** [https://scholarhub.great-site.net/index.php](https://scholarhub.great-site.net/index.php)

---

## ⚡ Tech Stack

| Layer      | Teknologi             |
|------------|----------------------|
| Frontend   | HTML + Tailwind CSS CDN |
| Styling    | Vanilla CSS (`assets/style.css`) |
| Backend    | PHP Native           |
| Database   | MySQL                |
| JS         | Vanilla JavaScript   |

---

## 📁 Struktur Folder

```
Tugas-Pemweb-Kel-3-main/
│
├── index.php              ← Halaman Utama (Homepage)
├── catalog.php            ← Katalog Beasiswa + Filter Kategori
├── detail.php             ← Detail Beasiswa
├── apply.php              ← Form Pengajuan Beasiswa (User)
├── riwayat.php            ← Riwayat & Kelola Pengajuan (User)
├── register.php           ← Halaman Daftar Akun
├── login.php              ← Halaman Masuk (User + Admin)
├── logout.php             ← Proses Logout
├── profile.php            ← Profil Tim & Info SDGs
│
├── admin.php              ← Dashboard Admin
├── admin_beasiswa.php     ← CRUD Beasiswa (Admin)
├── admin_pengajuan.php    ← Kelola Pengajuan (Admin)
├── admin_users.php        ← Data Pengguna (Admin)
│
├── conf.php               ← Konfigurasi Database + Helper Functions
├── portal_beasiswa.sql    ← Schema Database + Data Dummy
│
├── includes/
│   ├── navbar.php         ← Komponen Navigasi
│   └── footer.php         ← Komponen Footer
│
└── assets/
    ├── style.css          ← CSS Global (Design System)
    └── image/
        ├── image.png
        └── profil_icon.jpg
```

---

## 🚀 Cara Menjalankan (Lokal)

### Prasyarat
- XAMPP / Laragon (PHP 7.4+ & MySQL)

### Langkah
1. **Clone / copy** folder ini ke `htdocs/` XAMPP
2. **Buka phpMyAdmin** → Import `portal_beasiswa.sql`
3. **Sesuaikan `conf.php`** jika kredensial berbeda:
   ```php
   $host     = "localhost";
   $username = "root";
   $pass     = "";
   $nama_db  = "portal_beasiswa";
   ```
4. **Buka browser**: `http://localhost/Tugas-Pemweb-Kel-3-main/`

---

## 🔑 Demo Akun

| Role  | Email                         | Password   |
|-------|-------------------------------|------------|
| User  | `budi@gmail.com`              | `password123` |
| Admin | `admin@portalbeasiswa.id`     | `admin123` |

---

## 🎯 Fitur

### POV Pengguna (User)
- ✅ Lihat katalog beasiswa dengan filter 3 kategori (Prestasi / Reguler / Leadership)
- ✅ Pencarian beasiswa real-time (JavaScript)
- ✅ Lihat detail beasiswa lengkap (deskripsi, syarat, kuota, deadline)
- ✅ Form pengajuan beasiswa dengan validasi JS + PHP
- ✅ Pantau riwayat semua pengajuan
- ✅ Hapus pengajuan (modal konfirmasi JavaScript)
- ✅ Register & login akun sendiri

### POV Admin
- ✅ Dashboard statistik (total beasiswa, pengguna, pengajuan, status)
- ✅ CRUD Beasiswa (Tambah / Edit / Hapus)
- ✅ Review & update status pengajuan (Pending → Diterima / Ditolak)
- ✅ Tambah catatan admin untuk pemohon
- ✅ Lihat data semua pengguna + live search

### Database
- ✅ 4 tabel relasional: `admin`, `users`, `beasiswa`, `pengajuan`
- ✅ 12 data user dummy, 9 beasiswa (3 per kategori), 26+ pengajuan
- ✅ Foreign key constraint dengan CASCADE

---

## 🌱 SDGs
Proyek ini mendukung **Sustainable Development Goal 4 — Quality Education**  
dengan mempermudah akses informasi beasiswa bagi seluruh mahasiswa Indonesia.

---

## ☁️ Deploy ke InfinityFree
1. Buat database MySQL di panel InfinityFree
2. Import `portal_beasiswa.sql` via phpMyAdmin InfinityFree
3. Edit `conf.php` → ganti host/username/password/db
4. Upload semua file ke `htdocs/` via File Manager

---

*Kelompok 3 · SI-D · UPN "Veteran" Jawa Timur · 2026*
