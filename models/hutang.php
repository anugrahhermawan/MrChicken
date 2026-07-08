<?php
// models/Hutang.php

class Hutang {
    private PDO $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // Mendapatkan semua pelanggan yang memiliki hutang aktif (saldo_hutang > 0)
    public function getPelangganBerhutang(): array {
        $query = "SELECT * FROM pelanggan WHERE saldo_hutang > 0 ORDER BY nama_pelanggan ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan semua daftar hutang (detail transaksi hutang) yang belum lunas (sisa_hutang > 0)
    public function getHutangAktif(): array {
        $query = "SELECT h.*, p.nama_pelanggan, t.total_harga, t.tanggal 
                  FROM hutang h 
                  JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan 
                  JOIN transaksi t ON h.id_transaksi = t.id_transaksi 
                  WHERE h.sisa_hutang > 0 
                  ORDER BY h.tanggal_hutang ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan hutang aktif untuk satu pelanggan tertentu
    public function getHutangByPelanggan(int $id_pelanggan): array {
        $query = "SELECT h.*, t.total_harga, t.tanggal 
                  FROM hutang h
                  JOIN transaksi t ON h.id_transaksi = t.id_transaksi
                  WHERE h.id_pelanggan = :id_pelanggan AND h.sisa_hutang > 0
                  ORDER BY h.tanggal_hutang ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_pelanggan', $id_pelanggan, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mencatat hutang baru saat transaksi dibuat dengan metode 'Hutang'
    public function tambahHutang(int $id_pelanggan, int $id_transaksi, int $jumlah_hutang): bool {
        try {
            $this->conn->beginTransaction();

            $tanggal = date('Y-m-d');
            $query = "INSERT INTO hutang (id_pelanggan, id_transaksi, jumlah_hutang, sisa_hutang, tanggal_hutang) 
                      VALUES (:id_pelanggan, :id_transaksi, :jumlah_hutang, :sisa_hutang, :tanggal_hutang)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_pelanggan', $id_pelanggan, PDO::PARAM_INT);
            $stmt->bindParam(':id_transaksi', $id_transaksi, PDO::PARAM_INT);
            $stmt->bindParam(':jumlah_hutang', $jumlah_hutang, PDO::PARAM_INT);
            $stmt->bindParam(':sisa_hutang', $jumlah_hutang, PDO::PARAM_INT);
            $stmt->bindParam(':tanggal_hutang', $tanggal);
            $stmt->execute();

            // Tambahkan saldo_hutang di tabel pelanggan
            $queryUpdate = "UPDATE pelanggan SET saldo_hutang = saldo_hutang + :jumlah_hutang WHERE id_pelanggan = :id_pelanggan";
            $stmtUpdate = $this->conn->prepare($queryUpdate);
            $stmtUpdate->bindParam(':jumlah_hutang', $jumlah_hutang, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':id_pelanggan', $id_pelanggan, PDO::PARAM_INT);
            $stmtUpdate->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Mencatat pembayaran cicilan hutang
    public function bayarCicilan(int $id_hutang, int $nominal_pembayaran): bool {
        try {
            $this->conn->beginTransaction();

            // Dapatkan informasi hutang
            $queryGet = "SELECT id_pelanggan, sisa_hutang FROM hutang WHERE id_hutang = :id_hutang";
            $stmtGet = $this->conn->prepare($queryGet);
            $stmtGet->bindParam(':id_hutang', $id_hutang, PDO::PARAM_INT);
            $stmtGet->execute();
            $hutang = $stmtGet->fetch();

            if (!$hutang) {
                throw new Exception("Hutang tidak ditemukan");
            }

            // Validasi cicilan tidak boleh melebihi sisa hutang
            $nominal_bayar = min($nominal_pembayaran, $hutang->sisa_hutang);

            // Update sisa hutang di tabel hutang
            $queryUpdateHutang = "UPDATE hutang SET sisa_hutang = sisa_hutang - :nominal_bayar WHERE id_hutang = :id_hutang";
            $stmtUpdateHutang = $this->conn->prepare($queryUpdateHutang);
            $stmtUpdateHutang->bindParam(':nominal_bayar', $nominal_bayar, PDO::PARAM_INT);
            $stmtUpdateHutang->bindParam(':id_hutang', $id_hutang, PDO::PARAM_INT);
            $stmtUpdateHutang->execute();

            // Update saldo hutang di tabel pelanggan
            $queryUpdatePelanggan = "UPDATE pelanggan SET saldo_hutang = saldo_hutang - :nominal_bayar WHERE id_pelanggan = :id_pelanggan";
            $stmtUpdatePelanggan = $this->conn->prepare($queryUpdatePelanggan);
            $stmtUpdatePelanggan->bindParam(':nominal_bayar', $nominal_bayar, PDO::PARAM_INT);
            $stmtUpdatePelanggan->bindParam(':id_pelanggan', $hutang->id_pelanggan, PDO::PARAM_INT);
            $stmtUpdatePelanggan->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Koreksi transaksi hutang (dibatalkan oleh Owner)
    public function batalkanHutangByTransaksi(int $id_transaksi): bool {
        try {
            // Dapatkan informasi hutang untuk transaksi ini
            $queryGet = "SELECT id_pelanggan, sisa_hutang, jumlah_hutang FROM hutang WHERE id_transaksi = :id_transaksi";
            $stmtGet = $this->conn->prepare($queryGet);
            $stmtGet->bindParam(':id_transaksi', $id_transaksi, PDO::PARAM_INT);
            $stmtGet->execute();
            $hutang = $stmtGet->fetch();

            if ($hutang) {
                // Kurangi saldo hutang pelanggan sebesar sisa_hutang dari transaksi ini
                // (karena sisa hutang yang dibatalkan tidak perlu ditagih lagi)
                $queryUpdatePelanggan = "UPDATE pelanggan SET saldo_hutang = saldo_hutang - :sisa_hutang WHERE id_pelanggan = :id_pelanggan";
                $stmtUpdatePelanggan = $this->conn->prepare($queryUpdatePelanggan);
                $stmtUpdatePelanggan->bindParam(':sisa_hutang', $hutang->sisa_hutang, PDO::PARAM_INT);
                $stmtUpdatePelanggan->bindParam(':id_pelanggan', $hutang->id_pelanggan, PDO::PARAM_INT);
                $stmtUpdatePelanggan->execute();

                // Hapus entri hutang
                $queryDel = "DELETE FROM hutang WHERE id_transaksi = :id_transaksi";
                $stmtDel = $this->conn->prepare($queryDel);
                $stmtDel->bindParam(':id_transaksi', $id_transaksi, PDO::PARAM_INT);
                $stmtDel->execute();
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
