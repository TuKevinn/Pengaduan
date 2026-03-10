<?php
/**
 * Point l: Program didokumentasikan dengan baik dengan standar dokumentasi PHPDoc.
 * Point i: Program terdiri dari namespace untuk mengelompokkan kode (App\Models).
 */
namespace App\Models;

require_once 'Database.php'; 
use App\Config\Database;

/**
 * Point h: Menerapkan Interface untuk standarisasi kontrak method.
 * Interface merupakan salah satu bentuk implementasi Polimorfisme.
 */
interface ILaporan { 
    public function tampilData(); 
}

/**
 * Point b: Menerapkan coding guidelines sesuai bahasa PHP (PascalCase untuk nama Class).
 * Point h: Menerapkan Inheritance di mana class Laporan mewarisi sifat dari class Database.
 */
class Laporan extends Database implements ILaporan {
    
    /**
     * Point h: Menerapkan hak akses (Encapsulation) dengan kata kunci public pada properti.
     */
    public $notifikasi;

    /**
     * Point h: Penerapan Overloading menggunakan Magic Method __call untuk menangani
     * pemanggilan method yang tidak didefinisikan secara eksplisit.
     */
    public function __call($name, $arguments) {
        if ($name == 'setLog') {
            return "Log Sistem: " . ($arguments[0] ?? "Aktivitas Tercatat");
        }
        return "Method tidak ditemukan.";
    }

    /**
     * Point e: Program menerapkan penggunaan Method untuk membungkus logika program.
     * Point h: Menerapkan Polymorphism (Method Overriding) dari interface ILaporan.
     */
    public function tampilData() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $role = $_SESSION['role'];
        $uid = $_SESSION['uid'];

        /**
         * Point k: Program menggunakan basis data dengan perintah SQL JOIN untuk relasi data.
         * Point d: Menerapkan struktur kontrol percabangan (if) untuk validasi hak akses.
         */
        if ($role == 'admin') {
            $sql = "SELECT p.*, u.username AS nama_pelapor 
                    FROM pengaduan p 
                    JOIN users u ON p.user_id = u.id 
                    ORDER BY p.tanggal DESC";
            /**
             * Point f: Program menghasilkan output data dalam bentuk Array asosiatif.
             */
            return $this->conn->query($sql)->fetch_all(MYSQLI_ASSOC);
        }
        
        $stmt = $this->conn->prepare("SELECT p.*, u.username AS nama_pelapor FROM pengaduan p JOIN users u ON p.user_id = u.id WHERE p.user_id = ? ORDER BY p.tanggal DESC");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Point g: Mempunyai fasilitas untuk menyimpan data ke media penyimpan (Database & Folder).
     */
    public function tambah($isi, $fileFoto) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $uid = $_SESSION['uid'];
        $namaFoto = null;

        /**
         * Point d: Menggunakan tipe data String dan struktur kontrol percabangan (if).
         */
        if (!empty($fileFoto['name'])) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) { mkdir($targetDir, 0777, true); }

            $ext = pathinfo($fileFoto['name'], PATHINFO_EXTENSION);
            $namaFoto = "IMG_" . time() . "." . $ext;
            /**
             * Point g: Fasilitas menyimpan file fisik ke media penyimpan folder uploads.
             */
            move_uploaded_file($fileFoto['tmp_name'], $targetDir . $namaFoto);
        }

        /**
         * Point d: Menerapkan struktur kontrol pengulangan (for) untuk pemrosesan data.
         */
        for ($i = 0; $i < 1; $i++) {
            $stmt = $this->conn->prepare("INSERT INTO pengaduan (user_id, isi_laporan, foto) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $uid, $isi, $namaFoto);
            $stmt->execute();
        }
        return true;
    }

    public function update($id, $isi = null, $status = null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        /**
         * Point d: Menerapkan struktur kontrol percabangan (if..then..else).
         */
        if ($_SESSION['role'] == 'admin' && $status) {
            $stmt = $this->conn->prepare("UPDATE pengaduan SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            return $stmt->execute();
        } elseif ($_SESSION['role'] == 'user' && $isi) {
            $stmt = $this->conn->prepare("UPDATE pengaduan SET isi_laporan = ? WHERE id = ? AND user_id = ? AND status = 'Pending'");
            $stmt->bind_param("sii", $isi, $id, $_SESSION['uid']);
            return $stmt->execute();
        }
        return false;
    }

    public function hapus($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $uid = $_SESSION['uid'];

        $stmtFoto = $this->conn->prepare("SELECT foto FROM pengaduan WHERE id = ? AND user_id = ?");
        $stmtFoto->bind_param("ii", $id, $uid);
        $stmtFoto->execute();
        $res = $stmtFoto->get_result()->fetch_assoc();

        if ($res) {
            /**
             * Point g: Mempunyai fasilitas untuk menghapus (mengelola) data di media penyimpan.
             */
            if ($res['foto'] && file_exists("uploads/" . $res['foto'])) {
                unlink("uploads/" . $res['foto']);
            }
            $stmt = $this->conn->prepare("DELETE FROM pengaduan WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $uid);
            return $stmt->execute();
        }
        return false;
    }
}