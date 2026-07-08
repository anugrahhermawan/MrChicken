<?php
// seed.php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        die("Koneksi ke database gagal. Pastikan MySQL aktif di Laragon.\n");
    }

    echo "Koneksi database berhasil. Mulai memasukkan data awal (seeding)...\n";

    // Jalankan alter table jika is_deleted belum ada
    try {
        $db->exec("ALTER TABLE users ADD COLUMN is_deleted TINYINT DEFAULT 0 AFTER status_aktif");
        echo "- Kolom 'is_deleted' berhasil ditambahkan ke tabel 'users'.\n";
    } catch (PDOException $e) {
        // Kolom mungkin sudah ada, abaikan error
    }

    // 1. Seed Users
    $checkUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($checkUsers == 0) {
        $ownerPass = password_hash('password123', PASSWORD_BCRYPT);
        $kasirPass = password_hash('password123', PASSWORD_BCRYPT);

        $stmt = $db->prepare("INSERT INTO users (username, password, nama_pengguna, role, status_aktif) VALUES (?, ?, ?, ?, 1)");
        
        $stmt->execute(['owner', $ownerPass, 'Budi Owner', 'Owner']);
        $stmt->execute(['kasir', $kasirPass, 'Siti Kasir', 'Karyawan']);
        echo "- Berhasil menambahkan user: 'owner' & 'kasir' (password: password123)\n";
    } else {
        echo "- Tabel 'users' sudah terisi, skip.\n";
    }

    // 2. Seed Produk
    $checkProduk = $db->query("SELECT COUNT(*) FROM produk")->fetchColumn();
    if ($checkProduk == 0) {
        $produkData = [
            ['Dada Fillet', 50000, 'Kg'],
            ['Paha Fillet', 48000, 'Kg'],
            ['Sayap Fillet', 35000, 'Kg'],
            ['Ceker Fillet', 25000, 'Kg']
        ];

        $stmtProduk = $db->prepare("INSERT INTO produk (nama_produk, harga_per_kg, satuan) VALUES (?, ?, ?)");
        $stmtStok = $db->prepare("INSERT INTO stok (id_produk, jumlah_kg) VALUES (?, ?)");

        foreach ($produkData as $p) {
            $stmtProduk->execute($p);
            $id_produk = $db->lastInsertId();

            // Set stok awal berdasarkan jenis ayam
            $stokAwal = 100.00;
            if ($p[0] == 'Sayap Fillet') $stokAwal = 50.00;
            if ($p[0] == 'Ceker Fillet') $stokAwal = 30.00;

            $stmtStok->execute([$id_produk, $stokAwal]);
        }
        echo "- Berhasil menambahkan produk default (Dada, Paha, Sayap, Ceker Fillet) dan stok awal.\n";
    } else {
        echo "- Tabel 'produk' sudah terisi, skip.\n";
    }

    // 3. Seed Pelanggan
    $checkPelanggan = $db->query("SELECT COUNT(*) FROM pelanggan")->fetchColumn();
    if ($checkPelanggan == 0) {
        $stmtPelanggan = $db->prepare("INSERT INTO pelanggan (nama_pelanggan, no_hp, alamat, saldo_hutang) VALUES (?, ?, ?, ?)");
        $stmtPelanggan->execute(['Budi (Pelanggan A)', '08123456789', 'Jl. Merdeka No. 1, Kota A', 0]);
        $stmtPelanggan->execute(['Ani (Pelanggan B)', '08987654321', 'Jl. Sudirman No. 2, Kota B', 0]);
        echo "- Berhasil menambahkan pelanggan default: Budi & Ani.\n";
    } else {
        echo "- Tabel 'pelanggan' sudah terisi, skip.\n";
    }

    echo "Seeding selesai dengan sukses!\n";

} catch (PDOException $e) {
    echo "Terjadi kesalahan database: " . $e->getMessage() . "\n";
}
