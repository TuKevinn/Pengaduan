<?php
require_once 'src/Auth.php';
$auth = new App\Config\Auth();
$error = "";

if (isset($_POST['login'])) {
    if ($auth->login($_POST['user'], $_POST['pass'])) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login E-Lapor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    <div class="container card p-4 shadow-sm" style="max-width: 400px;">
        <h3 class="text-center mb-3">Login</h3>
        
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'registrasi_berhasil'): ?>
            <div class="alert alert-success py-2">Registrasi berhasil! Silakan login.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="user" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="pass" class="form-control" required>
            </div>
            <button name="login" class="btn btn-primary w-100">Masuk</button>
            <p class="text-center mt-3 mb-0">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </form>
    </div>
</body>
</html>