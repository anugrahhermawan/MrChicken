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
                  WHERE tanggal = :tanggal AND slot_waktu = :slot_waktu AND status_pengiriman = '" . STATUS_PENGIRIMAN_PENDING . "'";
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
                  (id_pelanggan, id_user, tanggal, due_date, waktu, slot_waktu, total_berat_akumulatif, metode_pembayaran, status_pengiriman, total_harga) 
                  VALUES (:id_pelanggan, :id_user, :tanggal, :due_date, :waktu, :slot_waktu, :total_berat_akumulatif, :metode_pembayaran, :status_pengiriman, :total_harga)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_pelanggan', $data['id_pelanggan'], PDO::PARAM_INT);
        $stmt->bindParam(':id_user', $data['id_user'], PDO::PARAM_INT);
        $stmt->bindParam(':tanggal', $data['tanggal']);
        $stmt->bindValue(':due_date', $data['due_date'] ?? null);
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
        $query = "SELECT t.*, p.nama_pelanggan, p.no_hp, p.alamat, u.nama_pengguna, h.status AS hutang_status,
                         (SELECT COALESCE(SUM(pb.nominal_bayar), 0) 
                          FROM pembayaran_hutang pb 
                          WHERE pb.id_hutang = h.id_hutang AND pb.tipe = 'Write-Off') AS total_writeoff_nota,
                         (SELECT COALESCE(SUM(pb.nominal_bayar), 0) 
                          FROM pembayaran_hutang pb 
                          WHERE pb.id_hutang = h.id_hutang AND pb.tipe = 'Adjustment') AS total_adjustment_nota 
                  FROM " . $this->table_name . " t
                  JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                  JOIN users u ON t.id_user = u.id_user
                  LEFT JOIN hutang h ON t.id_transaksi = h.id_transaksi
                  WHERE t.tanggal = :tanggal OR (t.tanggal < :tanggal AND t.status_pengiriman != '" . STATUS_PENGIRIMAN_SELESAI . "')
                  ORDER BY t.tanggal ASC, t.waktu DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan semua transaksi pengiriman dalam rentang tanggal
    public function getPengirimanRentangTanggal(string $startDate, string $endDate): array {
        $query = "SELECT t.*, p.nama_pelanggan, p.no_hp, p.alamat, u.nama_pengguna, h.status AS hutang_status,
                         (SELECT COALESCE(SUM(pb.nominal_bayar), 0) 
                          FROM pembayaran_hutang pb 
                          WHERE pb.id_hutang = h.id_hutang AND pb.tipe = 'Write-Off') AS total_writeoff_nota,
                         (SELECT COALESCE(SUM(pb.nominal_bayar), 0) 
                          FROM pembayaran_hutang pb 
                          WHERE pb.id_hutang = h.id_hutang AND pb.tipe = 'Adjustment') AS total_adjustment_nota 
                  FROM " . $this->table_name . " t
                  JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                  JOIN users u ON t.id_user = u.id_user
                  LEFT JOIN hutang h ON t.id_transaksi = h.id_transaksi
                  WHERE t.tanggal >= :startDate AND t.tanggal <= :endDate
                  ORDER BY t.tanggal DESC, t.waktu DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
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

    // Laporan harian (penjualan per jam untuk hari ini)
    public function getOmzetStats(string $tanggal): array {
        $query = "SELECT HOUR(waktu) AS jam, SUM(total_harga) as omzet, SUM(total_berat_akumulatif) as total_kg 
                  FROM " . $this->table_name . " 
                  WHERE status_pengiriman = '" . STATUS_PENGIRIMAN_SELESAI . "' AND tanggal = :tanggal
                  GROUP BY HOUR(waktu)
                  ORDER BY HOUR(waktu) ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map to buckets: 08:00, 10:00, 12:00, 14:00, 16:00, 18:00, 20:00
        $buckets = [
            '08:00' => ['omzet' => 0, 'total_kg' => 0],
            '10:00' => ['omzet' => 0, 'total_kg' => 0],
            '12:00' => ['omzet' => 0, 'total_kg' => 0],
            '14:00' => ['omzet' => 0, 'total_kg' => 0],
            '16:00' => ['omzet' => 0, 'total_kg' => 0],
            '18:00' => ['omzet' => 0, 'total_kg' => 0],
            '20:00' => ['omzet' => 0, 'total_kg' => 0]
        ];

        foreach ($rows as $r) {
            $hour = (int)$r['jam'];
            if ($hour < 10) {
                $buckets['08:00']['omzet'] += $r['omzet'];
                $buckets['08:00']['total_kg'] += $r['total_kg'];
            } elseif ($hour < 12) {
                $buckets['10:00']['omzet'] += $r['omzet'];
                $buckets['10:00']['total_kg'] += $r['total_kg'];
            } elseif ($hour < 14) {
                $buckets['12:00']['omzet'] += $r['omzet'];
                $buckets['12:00']['total_kg'] += $r['total_kg'];
            } elseif ($hour < 16) {
                $buckets['14:00']['omzet'] += $r['omzet'];
                $buckets['14:00']['total_kg'] += $r['total_kg'];
            } elseif ($hour < 18) {
                $buckets['16:00']['omzet'] += $r['omzet'];
                $buckets['16:00']['total_kg'] += $r['total_kg'];
            } elseif ($hour < 20) {
                $buckets['18:00']['omzet'] += $r['omzet'];
                $buckets['18:00']['total_kg'] += $r['total_kg'];
            } else {
                $buckets['20:00']['omzet'] += $r['omzet'];
                $buckets['20:00']['total_kg'] += $r['total_kg'];
            }
        }

        $result = [];
        foreach ($buckets as $label => $data) {
            $result[] = [
                'tanggal' => $tanggal,
                'label' => $label,
                'omzet' => $data['omzet'],
                'total_kg' => $data['total_kg']
            ];
        }
        return $result;
    }

    // Laporan Mingguan (penjualan harian untuk 7 hari terakhir)
    public function getWeeklyOmzetStats(string $tanggal): array {
        $startDate = date('Y-m-d', strtotime($tanggal . ' - 6 days'));
        $query = "SELECT tanggal, SUM(total_harga) as omzet, SUM(total_berat_akumulatif) as total_kg 
                  FROM " . $this->table_name . " 
                  WHERE status_pengiriman = '" . STATUS_PENGIRIMAN_SELESAI . "' 
                    AND tanggal BETWEEN :startDate AND :tanggal
                  GROUP BY tanggal 
                  ORDER BY tanggal ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Build list of 7 days
        $daysMap = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime($tanggal . " - $i days"));
            $daysMap[$d] = ['omzet' => 0, 'total_kg' => 0];
        }

        foreach ($rows as $r) {
            $tgl = $r['tanggal'];
            if (isset($daysMap[$tgl])) {
                $daysMap[$tgl]['omzet'] = (int)$r['omzet'];
                $daysMap[$tgl]['total_kg'] = (float)$r['total_kg'];
            }
        }

        $daysNameId = ['Sunday' => 'Min', 'Monday' => 'Sen', 'Tuesday' => 'Sel', 'Wednesday' => 'Rab', 'Thursday' => 'Kam', 'Friday' => 'Jum', 'Saturday' => 'Sab'];

        $result = [];
        foreach ($daysMap as $tgl => $data) {
            $dayEn = date('l', strtotime($tgl));
            $dayId = $daysNameId[$dayEn] ?? $dayEn;
            $result[] = [
                'label' => $dayId . ' (' . date('d/m', strtotime($tgl)) . ')',
                'tanggal' => $tgl,
                'omzet' => $data['omzet'],
                'total_kg' => $data['total_kg']
            ];
        }
        return $result;
    }

    // Laporan Bulanan (penjualan per bulan untuk tahun berjalan)
    public function getMonthlyOmzetStats(string $tanggal): array {
        $year = date('Y', strtotime($tanggal));
        $query = "SELECT MONTH(tanggal) as bulan, SUM(total_harga) as omzet, SUM(total_berat_akumulatif) as total_kg 
                  FROM " . $this->table_name . " 
                  WHERE status_pengiriman = '" . STATUS_PENGIRIMAN_SELESAI . "' 
                    AND YEAR(tanggal) = :year
                  GROUP BY MONTH(tanggal)
                  ORDER BY MONTH(tanggal) ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $monthsMap = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthsMap[$m] = ['omzet' => 0, 'total_kg' => 0];
        }

        foreach ($rows as $r) {
            $bln = (int)$r['bulan'];
            if (isset($monthsMap[$bln])) {
                $monthsMap[$bln]['omzet'] = (int)$r['omzet'];
                $monthsMap[$bln]['total_kg'] = (float)$r['total_kg'];
            }
        }

        $monthNamesId = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $result = [];
        foreach ($monthsMap as $bln => $data) {
            $result[] = [
                'label' => $monthNamesId[$bln],
                'omzet' => $data['omzet'],
                'total_kg' => $data['total_kg']
            ];
        }
        return $result;
    }

    // Distribusi penjualan produk berdasarkan periode hari dari tanggal terpilih
    public function getProductSalesStatsByPeriod(int $days, string $tanggal): array {
        $startDate = date('Y-m-d', strtotime($tanggal . " - $days days"));
        $query = "SELECT p.nama_produk, 
                         COALESCE(SUM(CASE 
                            WHEN t.status_pengiriman = '" . STATUS_PENGIRIMAN_SELESAI . "' 
                                 AND t.tanggal BETWEEN :startDate AND :tanggal 
                            THEN 
                                CASE 
                                    WHEN t.total_harga > 0 THEN
                                        d.jumlah_berat_kg * (1 - (
                                            SELECT COALESCE(SUM(pb.nominal_bayar), 0) 
                                            FROM pembayaran_hutang pb 
                                            WHERE pb.id_hutang = h.id_hutang AND pb.tipe = 'Write-Off'
                                        ) / t.total_harga)
                                    ELSE 0
                                END
                            ELSE 0 
                         END), 0) AS total_kg 
                  FROM produk p
                  LEFT JOIN detail_transaksi d ON p.id_produk = d.id_produk
                  LEFT JOIN transaksi t ON d.id_transaksi = t.id_transaksi
                  LEFT JOIN hutang h ON t.id_transaksi = h.id_transaksi
                  GROUP BY p.id_produk, p.nama_produk
                  HAVING total_kg > 0
                  ORDER BY total_kg DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
