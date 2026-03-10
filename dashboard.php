<?php
session_start();
require_once 'src/Laporan.php';

// Proteksi Halaman: Jika belum login, tendang ke index.php
if (!isset($_SESSION['uid'])) {
    header("Location: index.php");
    exit();
}

$lp = new App\Models\Laporan();

/**
 * LOGIKA KRUSIAL: Post/Redirect/Get (PRG)
 * Setelah setiap aksi (Tambah/Update/Hapus), kita melakukan redirect kembali ke 
 * dashboard.php agar data POST di memori browser dibersihkan.
 */

// Menangani Tambah Laporan
if (isset($_POST['kirim'])) {
    $lp->tambah($_POST['isi'], $_FILES['foto']);
    header("Location: dashboard.php");
    exit();
}

// Menangani Update Status oleh Admin
if (isset($_POST['upd_id_admin'])) {
    $lp->update($_POST['upd_id_admin'], null, $_POST['st']);
    header("Location: dashboard.php");
    exit();
}

// Menangani Ubah Isi Laporan oleh User
if (isset($_POST['upd_id_user'])) {
    $lp->update($_POST['upd_id_user'], $_POST['isi_baru'], null);
    header("Location: dashboard.php");
    exit();
}

// Menangani Hapus Laporan
if (isset($_GET['hapus'])) {
    $lp->hapus($_GET['hapus']);
    header("Location: dashboard.php");
    exit();
}

// Ambil data terbaru untuk ditampilkan di tabel
$data = $lp->tampilData();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Lapor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS agar tabel Rapi dan Rata Kiri sesuai permintaan */
        .table thead th { 
            vertical-align: middle; 
            text-align: left; 
            white-space: nowrap; 
            padding-left: 15px;
        }
        .table tbody td {
            text-align: left; 
            padding-left: 15px;
        }
        .col-no { width: 50px; text-align: center !important; }
        .col-pelapor { width: 130px; }
        .col-tanggal { width: 160px; }
        .col-bukti { width: 100px; }
        .col-status { width: 110px; }
        .col-aksi { width: 140px; }
        .img-bukti { width: 70px; height: 55px; object-fit: cover; border-radius: 4px; display: block; }
    </style>
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 shadow-sm rounded">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-start">Halo, <?= htmlspecialchars($_SESSION['name']) ?></h4>
            <a href="logout.php" class="btn btn-danger btn-sm">Keluar</a>
        </div>

        <?php if ($_SESSION['role'] == 'user'): ?>
            <div class="mb-4 p-3 border rounded bg-light">
                <h6 class="text-start mb-3">Buat Pengaduan Baru</h6>
                <form method="POST" enctype="multipart/form-data">
                    <textarea name="isi" class="form-control mb-2" placeholder="Detail laporan..." required></textarea>
                    <input type="file" name="foto" class="form-control mb-2" accept="image/*">
                    <button name="kirim" class="btn btn-primary w-100">Kirim Aduan</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover border align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="col-no text-center">No</th>
                        <th class="col-pelapor">Pelapor</th>
                        <th class="col-tanggal">Tanggal</th>
                        <th>Isi Aduan</th>
                        <th class="col-bukti">Bukti</th>
                        <th class="col-status">Status</th>
                        <th class="col-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr><td colspan="7" class="text-center">Belum ada data pengaduan.</td></tr>
                    <?php else: ?>
                        <?php 
                        $no = 1; // Inisialisasi nomor urut
                        foreach ($data as $r): // Point d & f: Pengulangan Array
                        ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $no++ ?></td>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($r['nama_pelapor']) ?></td>
                            <td class="small"><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td>
                            <td class="px-3"><?= htmlspecialchars($r['isi_laporan']) ?></td>
                            <td>
                                <?php if ($r['foto']): ?>
                                    <img src="uploads/<?= $r['foto'] ?>" class="img-bukti border shadow-sm">
                                <?php else: ?>
                                    <div class="text-muted small">No Photo</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php $c = $r['status']=='Selesai'?'success':($r['status']=='Proses'?'warning':'danger'); ?>
                                <span class="badge bg-<?= $c ?> d-inline-block" style="width: 80px; text-align: center;"><?= $r['status'] ?></span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="upd_id_admin" value="<?= $r['id'] ?>">
                                        <select name="st" onchange="this.form.submit()" class="form-select form-select-sm">
                                            <option value="Pending" <?= $r['status']=='Pending'?'selected':'' ?>>Pending</option>
                                            <option value="Proses" <?= $r['status']=='Proses'?'selected':'' ?>>Proses</option>
                                            <option value="Selesai" <?= $r['status']=='Selesai'?'selected':'' ?>>Selesai</option>
                                        </select>
                                    </form>
                                <?php elseif ($r['status'] == 'Pending'): ?>
                                    <button class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#edit<?= $r['id'] ?>">Ubah</button>
                                    <a href="?hapus=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus laporan?')">Hapus</a>

                                    <div class="modal fade" id="edit<?= $r['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form method="POST" class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Ubah Laporan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="upd_id_user" value="<?= $r['id'] ?>">
                                                    <textarea name="isi_baru" class="form-control" rows="4" required><?= htmlspecialchars($r['isi_laporan']) ?></textarea>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>