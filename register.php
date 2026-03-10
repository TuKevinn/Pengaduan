<?php
require_once 'src/Auth.php';
$auth = new App\Config\Auth();
$error = "";

if (isset($_POST['register'])) {
    // Memanggil method register dari class Auth
    if ($auth->register($_POST['user'], $_POST['pass'])) {
        // Jika berhasil, otomatis pindah ke halaman login (index.php)
        header("Location: index.php?pesan=registrasi_berhasil");
        exit();
    } else {
        $error = "Username sudah digunakan atau terjadi kesalahan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun E-Lapor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    <div class="container card p-4 shadow-sm" style="max-width: 400px;">
        <h3 class="text-center mb-4">Daftar Warga Baru</h3>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="user" class="form-control" placeholder="Pilih Username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="pass" class="form-control" placeholder="Buat Password" required>
            </div>
            <button name="register" class="btn btn-success w-100">Daftar Sekarang</button>
            <hr>
            <p class="text-center mb-0">Sudah punya akun? <a href="index.php">Login di sini</a></p>
        </form>
    </div>
</body>
</html>