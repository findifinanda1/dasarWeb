<?php
// Urutan penting: guard login dulu, baru cek token CSRF, baru database
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: list.php');
    exit;
}

csrf_verify();

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM anggota WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Anggota berhasil dihapus.'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Gagal menghapus anggota karena masih terikat dengan data peminjaman.'];
    }
}

header('Location: list.php');
exit;