<?php
namespace App\Config;
require_once 'Database.php';

class Auth extends Database {
    public function login($user, $pass) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res && password_verify($pass, $res['password'])) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['uid'] = $res['id'];
            $_SESSION['role'] = $res['role'];
            $_SESSION['name'] = $res['username'];
            return true;
        }
        return false;
    }

    public function register($user, $pass) {
        $cek = $this->conn->prepare("SELECT username FROM users WHERE username = ?");
        $cek->bind_param("s", $user);
        $cek->execute();
        $result = $cek->get_result();
        
        if ($result->num_rows > 0) return false;

        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        $role = 'user';

        $stmt = $this->conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user, $hashed_pass, $role);
        return $stmt->execute();
    }
}