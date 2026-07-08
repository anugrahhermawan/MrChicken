<?php
// models/Transaksi.php

class Transaksi {
    private PDO $conn;
    private string $table_name = "transaksi";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // Mendapatkan total berat akumulatif untuk tanggal dan slot waktu tertentu (hanya menghitung status 'Pending' yang aktif jalan)
    public function getBeratAkumulatif(string $tanggal, string $slot_waktu): float {
        $query = "SELECT SUM(total_berat_akumulatif) as total_berat 
                  FROM " . $this->table_name . " 
                  WHERE tanggal = :tanggal AND slot_waktu = :slot_waktu AND status_pengiriman = 'Pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':slot_waktu', $slot_waktu);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row && $row->total_berat ? (float)$row->total_berat : 0.0;
    }

    // Menyimpan transaksi utama
    public function create(array $data): int|false {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_pelanggan, id_user, tanggal, waktu, slot_waktu, total_berat_akumulatif, metode_pembayaran, status_pengiriman, total_harga) 
                  VALUES (:id_pelanggan, :id_user, :tanggal, :waktu, :slot_waktu, :total_berat_akumulatif, :metode_pembayaran, :status_pengiriman, :total_harga)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_pelanggan', $data['id_pelanggan'], PDO::PARAM_INT);
        $stmt->bindParam(':id_user', $data['id_user'], PDO::PARAM_INT);
        $stmt->bindParam(':tanggal', $data['tanggal']);
        $stmt->bindParam(':waktu', $data['waktu']);
        $stmt->bindParam(':slot_waktu', $data['slot_waktu']);
        $stmt->bindParam(':total_berat_akumulatif', $data['total_berat_akumulatif']);
        $stmt->bindParam(':metode_pembayaran', $data['metode_pembayaran']);
        $stmt->bindParam(':status_pengiriman', $data['status_pengiriman']);
        $stmt->bindParam(':total_harga', $data['total_harga'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return (int)$this->conn->lastInsertId();
        }
        return false;
    }

    // Menyimpan detail transaksi
    public function createDetail(array $data): bool {
        $query = "INSERT INTO detail_transaksi 
                  (id_transaksi, id_produk, jumlah_berat_kg, harga_satuan, subtotal) 
                  VALUES (:id_transaksi, :id_produk, :jumlah_berat_kg, :harga_satuan, :subtotal)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_transaksi', $data['id_transaksi'], PDO::PARAM_INT);
        $stmt->bindParam(':id_produk', $data['id_produk'], PDO::PARAM_INT);
        $stmt->bindParam(':jumlah_berat_kg', $data['jumlah_berat_kg']);
        $stmt->bindParam(':harga_satuan', $data['harga_satuan'], PDO::PARAM_INT);
        $stmt->bindParam(':subtotal', $data['subtotal'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Mendapatkan semua transaksi pengiriman harian (termasuk yang menggantung dari hari sebelumnya jika belum selesai)
    public function getPengirimanHarian(string $tanggal = null): array {
        if ($tanggal === null) {
            $tanggal = date('Y-m-d');
        }
        $query = "SELECT t.*, p.nama_pelanggan, p.no_hp, p.alamat, u.nama_pengguna 
                  FROM " . $this->table_name . " t
                  JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                  JOIN users u ON t.id_user = u.id_user
                  WHERE t.tanggal = :tanggal OR (t.tanggal < :tanggal AND t.status_pengiriman != 'Selesai')
                  ORDER BY t.tanggal ASC, t.waktu DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan detail items dari sebuah transaksi
    public function getDetails(int $id_transaksi): array {
        $query = "SELECT dt.*, p.nama_produk 
                  FROM detail_transaksi dt
                  JOIN produk p ON dt.id_produk = p.id_produk
                  WHERE dt.id_transaksi = :id_transaksi";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_transaksi', $id_transaksi, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan transaksi berdasarkan ID
    public function getById(int $id_transaksi): object|false {
        $query = "SELECT t.*, p.nama_pelanggan, p.no_hp, p.alamat 
                  FROM " . $this->table_name . " t
                  JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                  WHERE t.id_transaksi = :id_transaksi LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_transaksi', $id_transaksi, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Mengubah status pengiriman
    public function updateStatusPengiriman(int $id_transaksi, string $status): bool {
        $query = "UPDATE " . $this->table_name . " 
                  SET status_pengiriman = :status 
                  WHERE id_transaksi = :id_transaksi";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id_transaksi', $id_transaksi, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Menghapus transaksi (koreksi oleh Owner)
    public function delete(int $id_transaksi): bool {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_transaksi = :id_transaksi";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_transaksi', $id_transaksi, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Laporan harian (7 hari terakhir dari tanggal terpilih)
    public function getOmzetStats(string $tanggal): array {
        $query = "SELECT tanggal, SUM(total_harga) as omzet, SUM(total_berat_akumulatif) as total_kg 
                  FROM " . $this->table_name . " 
                  WHERE status_pengiriman = 'Selesai' AND tanggal <= :tanggal
                  GROUP BY tanggal 
                  ORDER BY tanggal DESC LIMIT 7";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Laporan Mingguan (6 minggu terakhir dari tanggal terpilih)
    public function getWeeklyOmzetStats(string $tanggal): array {
        $query = "SELECT DATE_FORMAT(tanggal, 'Wk %v (%Y)') AS label, SUM(total_harga) as omzet, SUM(total_berat_akumulatif) as total_kg 
                  FROM " . $this->table_name . " 
                  WHERE status_pengiriman = 'Selesai' AND tanggal <= :tanggal
                  GROUP BY DATE_FORMAT(tanggal, 'Wk %v (%Y)'), YEARWEEK(tanggal, 1) 
                  ORDER BY YEARWEEK(tanggal, 1) DESC LIMIT 6";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Laporan Bulanan (6 bulan terakhir dari tanggal terpilih)
    public function getMonthlyOmzetStats(string $tanggal): array {
        $query = "SELECT DATE_FORMAT(tanggal, '%M %Y') AS label, SUM(total_harga) as omzet, SUM(total_berat_akumulatif) as total_kg 
                  FROM " . $this->table_name . " 
                  WHERE status_pengiriman = 'Selesai' AND tanggal <= :tanggal
                  GROUP BY DATE_FORMAT(tanggal, '%M %Y'), YEAR(tanggal), MONTH(tanggal) 
                  ORDER BY YEAR(tanggal) DESC, MONTH(tanggal) DESC LIMIT 6";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Distribusi penjualan produk berdasarkan periode hari dari tanggal terpilih
    public function getProductSalesStatsByPeriod(int $days, string $tanggal): array {
        $query = "SELECT p.nama_produk, COALESCE(SUM(d.jumlah_berat_kg), 0) AS total_kg 
                  FROM produk p
                  LEFT JOIN detail_transaksi d ON p.id_produk = d.id_produk
                  LEFT JOIN transaksi t ON d.id_transaksi = t.id_transaksi 
                       AND t.status_pengiriman = 'Selesai' 
                       AND t.tanggal <= :tanggal
                       AND t.tanggal >= DATE_SUB(:tanggal2, INTERVAL :days DAY)
                  GROUP BY p.id_produk, p.nama_produk
                  ORDER BY total_kg DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':tanggal2', $tanggal);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
