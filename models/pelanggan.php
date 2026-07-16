<?php
// models/Pelanggan.php

class Pelanggan {
    private PDO $conn;
    private string $table_name = "pelanggan";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // Mendapatkan semua pelanggan
    public function getAll(): array {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nama_pelanggan ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan pelanggan berdasarkan ID
    public function getById(int $id_pelanggan): object|false {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_pelanggan = :id_pelanggan LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_pelanggan', $id_pelanggan, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Menyimpan pelanggan baru (dari modal di POS)
    public function create(string $nama, string $no_hp, string $alamat): int|false {
        $query = "INSERT INTO " . $this->table_name . " (nama_pelanggan, no_hp, alamat, saldo_hutang, credit_limit) VALUES (:nama, :no_hp, :alamat, 0, 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':no_hp', $no_hp);
        $stmt->bindParam(':alamat', $alamat);
        
        if ($stmt->execute()) {
            return (int)$this->conn->lastInsertId();
        }
        return false;
    }

    // Update credit limit pelanggan (Khusus Owner)
    public function updateCreditLimit(int $id_pelanggan, int $credit_limit): bool {
        $query = "UPDATE " . $this->table_name . " SET credit_limit = :credit_limit WHERE id_pelanggan = :id_pelanggan";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':credit_limit', $credit_limit, PDO::PARAM_INT);
        $stmt->bindParam(':id_pelanggan', $id_pelanggan, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
