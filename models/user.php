<?php
// models/User.php

class User {
    private PDO $conn;
    private string $table_name = "users";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // Mendapatkan data user berdasarkan username (untuk login) - hanya yang aktif dan tidak terhapus
    public function findByUsername(string $username): object|false {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE username = :username AND status_aktif = 1 AND is_deleted = 0 LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Mendapatkan seluruh daftar user sistem yang tidak terhapus (is_deleted = 0)
    public function getAll(): array {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_deleted = 0 ORDER BY id_user DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mendapatkan data user berdasarkan ID
    public function getById(int $id_user): object|false {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_user = :id_user LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Menambah user baru dengan password yang telah di-hash
    public function create(string $username, string $password, string $nama_pengguna, string $role): bool {
        $query = "INSERT INTO " . $this->table_name . " (username, password, nama_pengguna, role, status_aktif, is_deleted) 
                  VALUES (:username, :password, :nama_pengguna, :role, 1, 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':nama_pengguna', $nama_pengguna);
        $stmt->bindParam(':role', $role);
        return $stmt->execute();
    }

    // Memperbarui data user
    public function update(int $id_user, string $username, string $nama_pengguna, string $role, ?string $password = null): bool {
        if ($password) {
            $query = "UPDATE " . $this->table_name . " 
                      SET username = :username, password = :password, nama_pengguna = :nama_pengguna, role = :role 
                      WHERE id_user = :id_user";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $password);
        } else {
            $query = "UPDATE " . $this->table_name . " 
                      SET username = :username, nama_pengguna = :nama_pengguna, role = :role 
                      WHERE id_user = :id_user";
            $stmt = $this->conn->prepare($query);
        }
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':nama_pengguna', $nama_pengguna);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Toggle status_aktif user (aktif/non-aktif)
    public function toggleStatus(int $id_user): bool {
        $user = $this->getById($id_user);
        if (!$user) return false;

        $newStatus = $user->status_aktif == 1 ? 0 : 1;
        $query = "UPDATE " . $this->table_name . " SET status_aktif = :status WHERE id_user = :id_user";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $newStatus, PDO::PARAM_INT);
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Melakukan Soft Delete dengan menandai is_deleted = 1 (menyembunyikan dari UI, tetapi relasi DB tetap aman)
    public function delete(int $id_user): bool {
        $query = "UPDATE " . $this->table_name . " SET is_deleted = 1 WHERE id_user = :id_user";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        return $stmt->execute();
    }
}