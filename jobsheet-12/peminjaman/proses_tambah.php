<?php
// Urutan penting: guard login, token CSRF, baru database
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah.php');
    exit;
}

csrf_verify();

$anggotaId = (int) ($_POST['anggota_id'] ?? 0);
$bukuId    = (int) ($_POST['buku_id'] ?? 0);

if ($anggotaId <= 0 || $bukuId <= 0) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Anggota dan buku wajib dipilih.'];
    header('Location: tambah.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Kunci baris buku supaya stok tidak bisa diubah proses lain
    $cek = $pdo->prepare("SELECT stok FROM buku WHERE id = :id FOR UPDATE");
    $cek->execute(['id' => $bukuId]);
    $buku = $cek->fetch(PDO::FETCH_ASSOC);

    if (!$buku || $buku['stok'] < 1) {
        throw new Exception('Stok buku tidak tersedia.');
    }

    // LATIHAN 1: tolak anggota yang punya pinjaman terlambat lebih dari 14 hari
    $telat = $pdo->prepare(
        "SELECT COUNT(*) FROM peminjaman
         WHERE anggota_id = :a
           AND status = 'dipinjam'
           AND CURRENT_DATE - tanggal_pinjam > 14"
    );
    $telat->execute(['a' => $anggotaId]);
    if ($telat->fetchColumn() > 0) {
        throw new Exception('Anggota memiliki peminjaman terlambat lebih dari 14 hari.');
    }

    // LATIHAN 2: isi tanggal_jatuh_tempo = hari ini + 14 hari
    $insert = $pdo->prepare(
        "INSERT INTO peminjaman (buku_id, anggota_id, tanggal_pinjam, tanggal_jatuh_tempo, status)
         VALUES (:buku_id, :anggota_id, CURRENT_DATE, CURRENT_DATE + 14, 'dipinjam')"
    );
    $insert->execute(['buku_id' => $bukuId, 'anggota_id' => $anggotaId]);

    $update = $pdo->prepare("UPDATE buku SET stok = stok - 1 WHERE id = :id");
    $update->execute(['id' => $bukuId]);

    $pdo->commit();

    $_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Peminjaman berhasil dicatat.'];
    header('Location: ../buku/list.php');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Pesan database mentah tidak ditampilkan ke pengguna
    $pesan = ($e instanceof PDOException)
        ? 'Terjadi kesalahan pada database.'
        : $e->getMessage();
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Gagal mencatat peminjaman: ' . $pesan];
    header('Location: tambah.php');
    exit;
}