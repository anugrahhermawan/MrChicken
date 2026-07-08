<?php
// config/database.php

class Database {
    private string $host = "127.0.0.1";
    private string $db_name = "mrchicken";
    private string $username = "root";
    private string $password = "";
    public ?PDO $conn = null; // Menambahkan tipe data PDO atau null

    public function getConnection(): ?PDO {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch(PDOException $exception) {
            echo "Koneksi database gagal: " . $exception->getMessage();
        }
        return $this->conn;
    }
}