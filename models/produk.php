<?php
// models/Produk.php

class Produk {
    private PDO $conn;
    private string $table_name = "produk";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // Mendapatkan semua produk beserta jumlah stoknya
    public function getAll(): array {
        $query = "SELECT p.*, s.jumlah_kg, s.jumlah_kg AS stok_kg FROM " . $this->table_name . " p 
                  LEFT JOIN stok s ON p.id_produk = s.id_produk 
                  ORDER BY p.id_produk ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan produk berdasarkan ID
    public function getById(int $id_produk): object|false {
        $query = "SELECT p.*, s.jumlah_kg, s.jumlah_kg AS stok_kg FROM " . $this->table_name . " p 
                  LEFT JOIN stok s ON p.id_produk = s.id_produk 
                  WHERE p.id_produk = :id_produk LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_produk', $id_produk, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Menambahkan produk baru beserta stok awalnya
    public function insert(string $nama_produk, int $harga_per_kg, float $stok_awal): bool {
        try {
            $this->conn->beginTransaction();
            
            $query = "INSERT INTO " . $this->table_name . " (nama_produk, harga_per_kg, satuan) 
                      VALUES (:nama_produk, :harga_per_kg, 'Kg')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nama_produk', $nama_produk);
            $stmt->bindParam(':harga_per_kg', $harga_per_kg, PDO::PARAM_INT);
            $stmt->execute();
            
            $id_produk = (int)$this->conn->lastInsertId();
            
            $queryStok = "INSERT INTO stok (id_produk, jumlah_kg) VALUES (:id_produk, :stok_awal)";
            $stmtStok = $this->conn->prepare($queryStok);
            $stmtStok->bindParam(':id_produk', $id_produk, PDO::PARAM_INT);
            $stmtStok->bindParam(':stok_awal', $stok_awal);
            $stmtStok->execute();
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Memperbarui data produk dan stoknya
    public function update(int $id_produk, string $nama_produk, int $harga_per_kg, float $stok_kg): bool {
        try {
            $this->conn->beginTransaction();
            
            $query = "UPDATE " . $this->table_name . " 
                      SET nama_produk = :nama_produk, harga_per_kg = :harga_per_kg 
                      WHERE id_produk = :id_produk";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nama_produk', $nama_produk);
            $stmt->bindParam(':harga_per_kg', $harga_per_kg, PDO::PARAM_INT);
            $stmt->bindParam(':id_produk', $id_produk, PDO::PARAM_INT);
            $stmt->execute();
            
            $queryStok = "UPDATE stok SET jumlah_kg = :stok_kg WHERE id_produk = :id_produk";
            $stmtStok = $this->conn->prepare($queryStok);
            $stmtStok->bindParam(':stok_kg', $stok_kg);
            $stmtStok->bindParam(':id_produk', $id_produk, PDO::PARAM_INT);
            $stmtStok->execute();
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Menghapus data produk (menggunakan proteksi FK check)
    public function delete(int $id_produk): bool {
        // Cek apakah produk sudah pernah ditransaksikan
        $queryCheck = "SELECT COUNT(*) FROM detail_transaksi WHERE id_produk = :id_produk";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->bindParam(':id_produk', $id_produk, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ((int)$stmtCheck->fetchColumn() > 0) {
            // Produk sudah pernah ditransaksikan, tidak boleh dihapus secara fisik
            return false;
        }

        try {
            $this->conn->beginTransaction();

            // Hapus data stok
            $queryStok = "DELETE FROM stok WHERE id_produk = :id_produk";
            $stmtStok = $this->conn->prepare($queryStok);
            $stmtStok->bindParam(':id_produk', $id_produk, PDO::PARAM_INT);
            $stmtStok->execute();

            // Hapus data produk
            $query = "DELETE FROM " . $this->table_name . " WHERE id_produk = :id_produk";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_produk', $id_produk, PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Mengurangi stok produk ketika status pengiriman selesai
    public function kurangiStok(int $id_produk, float $jumlah_berat_kg): bool {
        $query = "UPDATE stok SET jumlah_kg = jumlah_kg - :jumlah_berat_kg 
                  WHERE id_produk = :id_produk AND jumlah_kg >= :jumlah_berat_kg";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':jumlah_berat_kg', $jumlah_berat_kg);
        $stmt->bindParam(':id_produk', $id_produk, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Menambah stok produk (fitur restock oleh Owner)
    public function tambahStok(int $id_produk, float $jumlah_berat_kg): bool {
        $query = "UPDATE stok SET jumlah_kg = jumlah_kg + :jumlah_berat_kg WHERE id_produk = :id_produk";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':jumlah_berat_kg', $jumlah_berat_kg);
        $stmt->bindParam(':id_produk', $id_produk, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
