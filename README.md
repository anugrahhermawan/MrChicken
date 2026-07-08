# 🐔 MrChicken

Sistem Informasi Penjualan dan Manajemen MrChicken

Project ini merupakan tugas **Ujian Akhir Semester (UAS)** mata kuliah **Rekayasa Perangkat Lunak (RPL)**. Aplikasi dikembangkan menggunakan **PHP Native** dengan arsitektur **MVC (Model-View-Controller)** dan menggunakan **MySQL** sebagai database.

---

## Fitur

* Login pengguna
* Manajemen produk
* Manajemen stok
* Manajemen transaksi
* Manajemen hutang
* Manajemen pengguna
* Dashboard sesuai hak akses

---

## Teknologi yang Digunakan

* PHP 8.x
* MySQL / MariaDB
* HTML, CSS, JavaScript
* Laragon (disarankan sebagai web server)

---

# Instalasi

## 1. Clone Repository

```bash
git clone https://github.com/anugrahhermawan/MrChicken.git
```

atau download project dalam bentuk ZIP dari GitHub.

---

## 2. Letakkan Project

Pindahkan folder project ke direktori web server, misalnya:

```
D:\laragon\www\MrChicken
```

---

## 3. Konfigurasi Database

### Buat database baru

```
mrchicken
```

### Import database

Import file berikut ke database yang telah dibuat:

```
MrChicken.sql
```

---

## 4. Konfigurasi Koneksi Database

Buka file:

```
config/database.php
```

Sesuaikan konfigurasi database apabila diperlukan.

Contoh konfigurasi default:

```php
Host     : 127.0.0.1
Database : mrchicken
Username : root
Password : (kosong)
```

Apabila MySQL menggunakan port selain default atau memiliki password, sesuaikan konfigurasi pada file tersebut.

---

## 5. Jalankan Aplikasi

Pastikan Apache dan MySQL telah berjalan.

Kemudian buka browser:

```
http://localhost/MrChicken/
```

atau jika menggunakan Virtual Host Laragon:

```
http://mrchicken.test/
```

---

# Struktur Project

```
MrChicken
│
├── assets/
├── config/
├── controllers/
├── core/
├── models/
├── views/
├── index.php
├── seed.php
├── MrChicken.sql
├── PRD.md
└── Design.md
```

---

# Catatan

* Project ini menggunakan PHP Native (bukan Laravel atau framework PHP lainnya).
* Composer dan NPM tidak diperlukan untuk menjalankan aplikasi.
* Disarankan menggunakan Laragon agar konfigurasi lebih mudah.

---

# Kontributor

**Anugrah Hermawan**

---

# Lisensi

Project ini dibuat untuk keperluan pembelajaran dan tugas akademik.