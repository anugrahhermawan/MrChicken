# TECHNICAL DESIGN DOCUMENT (design.md)

**Projek:** Sistem Point of Sale (POS) & Distribusi MR. CHICKEN
**Arsitektur Pemrograman:** Perangkat Lunak Berbasis Objek (OOP) & Modular MVC
**Teknologi:** PHP 8.x, MySQL 8.0, JavaScript (AJAX), Bootstrap 5 (via Admin Template)

---

## 1. Arsitektur & Struktur Direktori Proyek
Proyek ini diorganisasikan menggunakan pola modular sederhana untuk memisahkan logika bisnis (Controller), penyimpanan data (Model), dan tampilan antarmuka (View).

```text
mr-chicken-pos/
│
├── config/
│   └── database.php          # Koneksi database PDO / MySQLi
│
├── core/
│   └── Controller.php        # Base Controller utama
│
├── controllers/
│   ├── AuthController.php    # Logika login & session management
│   ├── TransaksiController.php # Pencatatan order, hitung subtotal, & pre-order
│   ├── StokController.php    # Pembaruan stok masuk & pengurangan stok harian
│   └── HutangController.php  # Pencatatan cicilan & pelacakan piutang
│
├── models/
│   ├── User.php              # Query untuk entitas pengguna
│   ├── Produk.php            # Query data ayam fillet & harga
│   ├── Transaksi.php         # Query simpan nota & detail transaksi
│   └── Hutang.php            # Query manipulasi saldo piutang pelanggan
│
├── views/
│   ├── templates/
│   │   ├── header.php        # Aset CSS Admin Template & Sidebar Navigasi
│   │   └── footer.php        # Aset JS & Script AJAX
│   ├── auth/
│   │   └── login.php         # Tampilan halaman login
│   ├── kasir/
│   │   └── index.php         # Tampilan kasir (Split-Screen POS)
│   ├── logistik/
│   │   └── pengiriman.php    # Tampilan daftar kirim & tombol konfirmasi selesai
│   └── owner/
│       ├── dashboard.php     # Metrik visual & grafik omzet harian
│       └── hutang.php        # Buku piutang aktif & form bayar cicilan
│
└── index.php                 # Front Router (Gerbang utama aplikasi)