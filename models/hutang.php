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
                  WHERE h.sisa_hutang > 0 AND h.status = '" . STATUS_HUTANG_AKTIF . "'
                  ORDER BY h.tanggal_hutang ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan semua daftar hutang, termasuk yang lunas & write-off (untuk rekap Owner)
    public function getAllHutangHistory(): array {
        $query = "SELECT h.*, p.nama_pelanggan, t.total_harga, t.tanggal 
                  FROM hutang h 
                  JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan 
                  JOIN transaksi t ON h.id_transaksi = t.id_transaksi 
                  ORDER BY h.tanggal_hutang DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan hutang aktif untuk satu pelanggan tertentu
    public function getHutangByPelanggan(int $id_pelanggan): array {
        $query = "SELECT h.*, t.total_harga, t.tanggal 
                  FROM hutang h
                  JOIN transaksi t ON h.id_transaksi = t.id_transaksi
                  WHERE h.id_pelanggan = :id_pelanggan AND h.sisa_hutang > 0 AND h.status = '" . STATUS_HUTANG_AKTIF . "'
                  ORDER BY h.tanggal_hutang ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_pelanggan', $id_pelanggan, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan detail pembayaran/cicilan berdasarkan ID
    public function getPembayaranById(int $id_pembayaran): object|false {
        $query = "SELECT pb.*, h.id_transaksi, h.jumlah_hutang, h.sisa_hutang, h.tanggal_hutang, p.nama_pelanggan, u.nama_pengguna
                  FROM pembayaran_hutang pb
                  JOIN hutang h ON pb.id_hutang = h.id_hutang
                  JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan
                  JOIN users u ON pb.created_by = u.id_user
                  WHERE pb.id_pembayaran = :id_pembayaran LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_pembayaran', $id_pembayaran, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Mendapatkan semua riwayat pembayaran cicilan
    public function getRiwayatPembayaran(): array {
        $query = "SELECT pb.*, h.id_transaksi, p.nama_pelanggan, u.nama_pengguna
                  FROM pembayaran_hutang pb
                  JOIN hutang h ON pb.id_hutang = h.id_hutang
                  JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan
                  JOIN users u ON pb.created_by = u.id_user
                  ORDER BY pb.tanggal_bayar DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan total nominal berdasarkan tipe pembayaran (Bayar, Adjustment, Write-Off)
    public function getTotalByTipe(string $tipe): int {
        $query = "SELECT COALESCE(SUM(nominal_bayar), 0) as total FROM pembayaran_hutang WHERE tipe = :tipe";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipe', $tipe);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? (int)$row->total : 0;
    }

    // Mendapatkan total nominal berdasarkan tipe pembayaran pada tanggal transaksi tertentu
    public function getTotalByTipeDanTanggal(string $tipe, string $tanggal): int {
        $query = "SELECT COALESCE(SUM(pb.nominal_bayar), 0) as total 
                  FROM pembayaran_hutang pb
                  JOIN hutang h ON pb.id_hutang = h.id_hutang
                  JOIN transaksi t ON h.id_transaksi = t.id_transaksi
                  WHERE pb.tipe = :tipe AND t.tanggal = :tanggal";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipe', $tipe);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? (int)$row->total : 0;
    }

    // Mendapatkan total nominal berdasarkan tipe pembayaran pada rentang tanggal transaksi tertentu
    public function getTotalByTipeDanRentangTanggal(string $tipe, string $startDate, string $endDate): int {
        $query = "SELECT COALESCE(SUM(pb.nominal_bayar), 0) as total 
                  FROM pembayaran_hutang pb
                  JOIN hutang h ON pb.id_hutang = h.id_hutang
                  JOIN transaksi t ON h.id_transaksi = t.id_transaksi
                  WHERE pb.tipe = :tipe AND t.tanggal >= :startDate AND t.tanggal <= :endDate";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipe', $tipe);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? (int)$row->total : 0;
    }

    // Mencatat hutang baru saat transaksi dibuat dengan metode 'Hutang'
    public function tambahHutang(int $id_pelanggan, int $id_transaksi, int $jumlah_hutang, ?string $due_date = null): bool {
        try {
            $this->conn->beginTransaction();

            $tanggal = date('Y-m-d');
            $query = "INSERT INTO hutang (id_pelanggan, id_transaksi, jumlah_hutang, sisa_hutang, tanggal_hutang, due_date, status) 
                      VALUES (:id_pelanggan, :id_transaksi, :jumlah_hutang, :sisa_hutang, :tanggal_hutang, :due_date, '" . STATUS_HUTANG_AKTIF . "')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_pelanggan', $id_pelanggan, PDO::PARAM_INT);
            $stmt->bindParam(':id_transaksi', $id_transaksi, PDO::PARAM_INT);
            $stmt->bindParam(':jumlah_hutang', $jumlah_hutang, PDO::PARAM_INT);
            $stmt->bindParam(':sisa_hutang', $jumlah_hutang, PDO::PARAM_INT);
            $stmt->bindParam(':tanggal_hutang', $tanggal);
            $stmt->bindValue(':due_date', $due_date);
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

    // Mencatat pembayaran cicilan hutang dengan logging dan audit trail
    public function bayarCicilan(int $id_hutang, int $nominal_pembayaran, int $created_by, string $tipe = 'Bayar'): int|false {
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

            // Log pembayaran ke pembayaran_hutang
            $tanggal_bayar = date('Y-m-d H:i:s');
            $queryLog = "INSERT INTO pembayaran_hutang (id_hutang, nominal_bayar, tipe, tanggal_bayar, created_by) 
                         VALUES (:id_hutang, :nominal_bayar, :tipe, :tanggal_bayar, :created_by)";
            $stmtLog = $this->conn->prepare($queryLog);
            $stmtLog->bindParam(':id_hutang', $id_hutang, PDO::PARAM_INT);
            $stmtLog->bindParam(':nominal_bayar', $nominal_bayar, PDO::PARAM_INT);
            $stmtLog->bindParam(':tipe', $tipe);
            $stmtLog->bindParam(':tanggal_bayar', $tanggal_bayar);
            $stmtLog->bindParam(':created_by', $created_by, PDO::PARAM_INT);
            $stmtLog->execute();
            $id_pembayaran = (int)$this->conn->lastInsertId();

            // Check if sisa_hutang becomes 0, then update status to 'Lunas'
            $queryCheck = "SELECT sisa_hutang FROM hutang WHERE id_hutang = :id_hutang";
            $stmtCheck = $this->conn->prepare($queryCheck);
            $stmtCheck->bindParam(':id_hutang', $id_hutang, PDO::PARAM_INT);
            $stmtCheck->execute();
            $updatedHutang = $stmtCheck->fetch();

            if ($updatedHutang && $updatedHutang->sisa_hutang <= 0) {
                // If it is fully adjusted, we can set status to 'Lunas' as well, or keep status
                $status = ($tipe === STATUS_HUTANG_WRITEOFF) ? STATUS_HUTANG_WRITEOFF : STATUS_HUTANG_LUNAS;
                $queryStatus = "UPDATE hutang SET status = :status WHERE id_hutang = :id_hutang";
                $stmtStatus = $this->conn->prepare($queryStatus);
                $stmtStatus->bindParam(':status', $status);
                $stmtStatus->bindParam(':id_hutang', $id_hutang, PDO::PARAM_INT);
                $stmtStatus->execute();
            }

            // Update saldo hutang di tabel pelanggan
            $queryUpdatePelanggan = "UPDATE pelanggan SET saldo_hutang = saldo_hutang - :nominal_bayar WHERE id_pelanggan = :id_pelanggan";
            $stmtUpdatePelanggan = $this->conn->prepare($queryUpdatePelanggan);
            $stmtUpdatePelanggan->bindParam(':nominal_bayar', $nominal_bayar, PDO::PARAM_INT);
            $stmtUpdatePelanggan->bindParam(':id_pelanggan', $hutang->id_pelanggan, PDO::PARAM_INT);
            $stmtUpdatePelanggan->execute();

            $this->conn->commit();
            return $id_pembayaran;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Melakukan penyesuaian/pemotongan sisa hutang (Adjustment)
    public function adjustmentHutang(int $id_hutang, int $nominal_adjustment, int $created_by): int|false {
        return $this->bayarCicilan($id_hutang, $nominal_adjustment, $created_by, 'Adjustment');
    }

    // Melakukan Write-Off (penghapusan piutang macet)
    public function writeOffHutang(int $id_hutang, int $created_by): bool {
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

            $sisa_macet = $hutang->sisa_hutang;

            if ($sisa_macet <= 0) {
                throw new Exception("Hutang sudah lunas");
            }

            // Set sisa_hutang menjadi 0 dan status = 'Write-Off'
            $query = "UPDATE hutang SET sisa_hutang = 0, status = '" . STATUS_HUTANG_WRITEOFF . "' WHERE id_hutang = :id_hutang";
            $stmtUpdateHutang = $this->conn->prepare($query);
            $stmtUpdateHutang->bindParam(':id_hutang', $id_hutang, PDO::PARAM_INT);
            $stmtUpdateHutang->execute();

            // Log ke pembayaran_hutang sebagai pemotongan write-off
            $tanggal_bayar = date('Y-m-d H:i:s');
            $query = "INSERT INTO pembayaran_hutang (id_hutang, nominal_bayar, tipe, tanggal_bayar, created_by) 
                         VALUES (:id_hutang, :nominal_bayar, '" . STATUS_HUTANG_WRITEOFF . "', :tanggal_bayar, :created_by)";
            $stmtLog = $this->conn->prepare($query);
            $stmtLog->bindParam(':id_hutang', $id_hutang, PDO::PARAM_INT);
            $stmtLog->bindParam(':nominal_bayar', $sisa_macet, PDO::PARAM_INT);
            $stmtLog->bindParam(':tanggal_bayar', $tanggal_bayar);
            $stmtLog->bindParam(':created_by', $created_by, PDO::PARAM_INT);
            $stmtLog->execute();

            // Update saldo hutang pelanggan
            $queryUpdatePelanggan = "UPDATE pelanggan SET saldo_hutang = saldo_hutang - :sisa_macet WHERE id_pelanggan = :id_pelanggan";
            $stmtUpdatePelanggan = $this->conn->prepare($queryUpdatePelanggan);
            $stmtUpdatePelanggan->bindParam(':sisa_macet', $sisa_macet, PDO::PARAM_INT);
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

                // Hapus entri hutang (cascade akan menghapus detail pembayaran jika ada)
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

    // Membatalkan transaksi pembayaran cicilan / hapus buku / adjustment
    public function batalPembayaran(int $id_pembayaran): bool {
        try {
            $this->conn->beginTransaction();

            // 1. Dapatkan info pembayaran_hutang
            $queryPay = "SELECT p.*, h.id_pelanggan 
                         FROM pembayaran_hutang p
                         JOIN hutang h ON p.id_hutang = h.id_hutang
                         WHERE p.id_pembayaran = :id_pembayaran";
            $stmtPay = $this->conn->prepare($queryPay);
            $stmtPay->bindParam(':id_pembayaran', $id_pembayaran, PDO::PARAM_INT);
            $stmtPay->execute();
            $pay = $stmtPay->fetch();

            if (!$pay) {
                throw new Exception("Pembayaran tidak ditemukan.");
            }

            $id_hutang = (int)$pay->id_hutang;
            $nominal = (int)$pay->nominal_bayar;
            $id_pelanggan = (int)$pay->id_pelanggan;

            // 2. Kembalikan sisa_hutang di tabel hutang
            $queryHutang = "UPDATE hutang 
                            SET sisa_hutang = sisa_hutang + :nominal,
                                status = '" . STATUS_HUTANG_AKTIF . "' 
                            WHERE id_hutang = :id_hutang";
            $stmtHutang = $this->conn->prepare($queryHutang);
            $stmtHutang->bindParam(':nominal', $nominal, PDO::PARAM_INT);
            $stmtHutang->bindParam(':id_hutang', $id_hutang, PDO::PARAM_INT);
            $stmtHutang->execute();

            // 3. Kembalikan saldo_hutang di tabel pelanggan
            $queryPelanggan = "UPDATE pelanggan 
                               SET saldo_hutang = saldo_hutang + :nominal 
                               WHERE id_pelanggan = :id_pelanggan";
            $stmtPelanggan = $this->conn->prepare($queryPelanggan);
            $stmtPelanggan->bindParam(':nominal', $nominal, PDO::PARAM_INT);
            $stmtPelanggan->bindParam(':id_pelanggan', $id_pelanggan, PDO::PARAM_INT);
            $stmtPelanggan->execute();

            // 4. Hapus record pembayaran_hutang
            $queryDel = "DELETE FROM pembayaran_hutang WHERE id_pembayaran = :id_pembayaran";
            $stmtDel = $this->conn->prepare($queryDel);
            $stmtDel->bindParam(':id_pembayaran', $id_pembayaran, PDO::PARAM_INT);
            $stmtDel->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
