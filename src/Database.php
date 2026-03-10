<?php
namespace App\Config;

class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = ""; // Kosongkan jika pakai XAMPP standar
    private $db   = "db_pengaduan";
    protected $conn;

    public function __construct() {
        $this->conn = new \mysqli($this->host, $this->user, $this->pass, $this->db);
        if ($this->conn->connect_error) {
            die("Koneksi Database Gagal: " . $this->conn->connect_error);
        }
    }
}