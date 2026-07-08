# PRODUCT REQUIREMENT DOCUMENT (PRD)

**Nama Projek:** Sistem Point of Sale (POS) & Distribusi MR. CHICKEN[cite: 1, 2]  
**Status Projek:** Pengembangan Prototype / MVP (Minimum Viable Product)[cite: 1, 2]  
**Pengguna Target:** Internal (Owner & Karyawan Kasir)[cite: 1, 2]  
**Paradigma Sistem:** Modular MVC & Object-Oriented Programming (OOP)[cite: 1, 2]  

---

## 1. Ringkasan Produk & Masalah (Product Overview)
MR. CHICKEN adalah usaha *frozen food* yang menjual variasi potongan ayam fillet (Dada, Paha, Sayap, Ceker) dalam satuan Kilogram (Kg)[cite: 1]. Saat ini operasional pencatatan pesanan dari WhatsApp, pengelolaan hutang, dan pembagian slot pengiriman kurir masih menggunakan Microsoft Excel secara manual[cite: 1, 2]. 

Sistem POS & Distribusi MR. CHICKEN hadir sebagai solusi internal untuk mendigitalisasi pencatatan tersebut tanpa mengubah alur bisnis yang ada[cite: 1]. Sistem difokuskan untuk meminimalkan *human error* pada kalkulasi hutang, mencegah *overbooking* muatan logistik kurir, serta menjaga sinkronisasi data stok fisik secara *real-time*[cite: 1, 2].

---

## 2. Peran Pengguna & Hak Akses (User Persona & Permissions)

Sistem membagi hak akses ke dalam 2 level pengguna internal (Pelanggan adalah entitas luar/tidak memiliki hak akses login)[cite: 1, 2]:

| Peran (Role) | Deskripsi Hak Akses | Fitur Utama |
| :--- | :--- | :--- |
| **Owner**[cite: 1, 2] | **Full Access (CRUD)**[cite: 2] | Kelola akun pengguna, kelola harga produk, batalkan/koreksi transaksi salah, lihat dashboard finansial & rekap laporan otomatis[cite: 1, 2]. |
| **Karyawan / Kasir**[cite: 1, 2] | **Restricted Access**[cite: 2] | Input pelanggan baru, catat pesanan masuk (WhatsApp), kelola slot pengiriman, konfirmasi status kirim selesai, serta *view* stok (read-only)[cite: 1, 2]. |

---

## 3. Fitur Utama & Kebutuhan Fungsional (Core Features)

### M-1: Modul Autentikasi (Authentication)
* **Form Login:** Input `username` dan `password`[cite: 1, 2].
* **Keamanan:** Kata sandi wajib di-*hash* menggunakan algoritma `bcrypt`[cite: 1, 2].
* **Session Management:** Sesi otomatis kedaluwarsa (*auto-logout*) jika tidak ada aktivitas selama 60 menit[cite: 1, 2].
* **Middleware:** Memblokir Karyawan yang mencoba mengakses halaman laporan/manajemen owner secara paksa[cite: 2].

### M-2: Modul Transaksi & Kasir (Split-Screen POS)
* **Pilih/Cari Pelanggan:** Mengambil data dari `Class Pelanggan`[cite: 2]. Jika pelanggan baru, kasir dapat menambahkannya langsung lewat tombol "+" (Muncul modal pop-up: Nama, No HP, Alamat)[cite: 1, 2].
* **Daftar Item Dinamis:**
    * Dropdown pilihan produk (Dada, Paha, Sayap, Ceker Fillet)[cite: 1].
    * Input berat dalam satuan **Kilogram (Tipe data: Desimal/Float)**, mendukung input presisi seperti `1.25` atau `10.5` Kg.
    * Kalkulasi otomatis: `Berat (Kg) x Harga per Kg = Subtotal`[cite: 2].
* **Alokasi Logistik (Slot Pengiriman):**
    * Pilihan *Toggle* atau *Radio Button*: **Slot Pagi** atau **Slot Sore**[cite: 1, 2].
    * Sistem menghitung total berat akumulatif pesanan yang masuk pada hari tersebut. Batas maksimal per slot adalah 60 Kg[cite: 1, 2].
    * Jika muatan slot penuh (>60 Kg), sistem otomatis mengunci pilihan slot tersebut dan mengalihkan pesanan ke status **Pre-Order (Antrian)** untuk slot berikutnya[cite: 1, 2].
* **Metode & Status Pembayaran:**
    * Metode: Pilihan Tunai/Transfer atau **Hutang**[cite: 1, 2].
    * Jika memilih **Hutang**, sistem secara otomatis membuat entri baru pada catatan piutang pelanggan tersebut (Saldo hutang bertambah)[cite: 1, 2].
* **Simpan Transaksi:** Menyimpan data ke database dan mengeluarkan struk digital untuk di-*copy* atau dicetak[cite: 1, 2].

### M-3: Modul Logistik & Pemotongan Stok Dinamis
* **Daftar Pengiriman Harian:** Menampilkan daftar pesanan aktif yang siap dikirim berdasarkan slot waktu[cite: 1, 2].
* **Pemicu Stok (Trigger):** Stok ayam fillet di database **TIDAK berkurang saat order disimpan**, melainkan baru terpotong setelah Karyawan menekan tombol **"Konfirmasi Selesai"** (ketika kurir mengonfirmasi barang telah sampai di tangan pembeli)[cite: 1, 2].

### M-4: Modul Manajemen Piutang (Khusus Owner)
* **Buku Hutang:** Menampilkan daftar nama pelanggan yang memiliki sisa hutang aktif[cite: 1, 2].
* **Pencatatan Cicilan:** Form input nominal pembayaran cicilan hutang yang akan langsung memotong total saldo hutang pelanggan secara *real-time*[cite: 1, 2].
* **Koreksi Eksklusif:** Hanya Owner yang diberikan akses tombol "Batalkan/Koreksi Transaksi" untuk nota yang telanjur disimpan namun salah input[cite: 1, 2].

### M-5: Modul Laporan & Dashboard (Khusus Owner)
* **Metrik Utama (Widget):** Total pendapatan harian (Rupiah), volume penjualan harian (Total Kg ayam), sisa slot pengiriman, dan total piutang yang belum tertagih[cite: 1, 2].
* **Grafik Tren:** Grafik sederhana pergerakan omzet mingguan/bulanan[cite: 1, 2].
* **Rekap Otomatis:** Sistem membekukan data harian menjadi rekap *read-only* setiap akhir hari untuk mencegah manipulasi data[cite: 1, 2].

---

## 4. Kebutuhan Non-Fungsional (Non-Functional Requirements)

* **Performansi (RNF-01):** Waktu respons pemrosesan AJAX (input berat produk ke subtotal) dan penyimpanan transaksi maksimal 3 detik pada kondisi jaringan stabil[cite: 1, 2].
* **Aksesibilitas (RNF-06):** Antarmuka dashboard dan daftar pengiriman harian wajib responsif (*mobile-friendly*) karena kasir/kurir akan sering memantau via smartphone[cite: 1, 2].
* **Konsistensi Data:** Seluruh input berat (Kg) dan nominal uang tidak boleh menerima nilai kosong (*null*) atau minus[cite: 2].

---

## 5. Rencana Skema Basis Data Inti (Paradigma Relasional-Objek)

Untuk mendukung implementasi *prototype/coding*, struktur database minimal wajib memiliki tabel-tabel berikut[cite: 1, 2]:
1. **users** (`id_user`, `username`, `password`, `role`)[cite: 2]
2. **pelanggan** (`id_pelanggan`, `nama_pelanggan`, `no_hp`, `alamat`, `saldo_hutang`)[cite: 2]
3. **produk** (`id_produk`, `nama_produk`, `harga_per_kg`, `satuan`)[cite: 2]
4. **stok** (`id_stok`, `id_produk`, `jumlah_kg`)[cite: 2]
5. **transaksi** (`id_transaksi`, `id_pelanggan`, `id_user`, `tanggal`, `slot_waktu`, `metode_pembayaran`, `status_pengiriman`, `total_harga`)[cite: 2]
6. **detail_transaksi** (`id_detail`, `id_transaksi`, `id_produk`, `jumlah_berat_kg`, `subtotal`)[cite: 2]
7. **hutang** (`id_hutang`, `id_pelanggan`, `id_transaksi`, `jumlah_hutang`, `sisa_hutang`)[cite: 2]

---

## 6. Kriteria Keberhasilan Prototype Tugas Akhir (Acceptance Criteria)

Tugas akhir kalian dinyatakan berhasil dan aman saat demo di depan dosen jika memenuhi skenario berikut:
1. **Skenario Validasi Slot:** Ketika memasukkan orderan dengan berat total 65 Kg pada Slot Pagi, sistem harus menolak dan memunculkan peringatan atau memindahkan orderan ke daftar Pre-Order secara otomatis[cite: 1, 2].
2. **Skenario Validasi Stok:** Ketika status transaksi masih `'pending'` (barang dalam perjalanan), stok produk di menu stok tidak boleh berkurang[cite: 1, 2]. Stok baru berkurang setelah status diubah menjadi `'selesai'`[cite: 1, 2].
3. **Skenario Validasi Piutang:** Transaksi dengan metode `'Hutang'` secara otomatis memperbarui tabel hutang dan menambah nilai `saldo_hutang` di data pelanggan tanpa perlu input manual kedua kalinya[cite: 1, 2].