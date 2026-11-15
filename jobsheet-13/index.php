<?php
$page_title = "Beranda";
require __DIR__ . '/includes/koneksi.php';

$ringkasan = $pdo->query(
    "SELECT
        (SELECT COUNT(*) FROM buku)    AS total_buku,
        (SELECT COUNT(*) FROM anggota) AS total_anggota,
        (SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam') AS aktif,
        (SELECT COUNT(*) FROM peminjaman
          WHERE status = 'dipinjam' AND CURRENT_DATE - tanggal_pinjam > 14) AS telat"
)->fetch(PDO::FETCH_ASSOC);

$totalBuku       = (int) $ringkasan['total_buku'];
$totalAnggota    = (int) $ringkasan['total_anggota'];
$peminjamanAktif = (int) $ringkasan['aktif'];
$keterlambatan   = (int) $ringkasan['telat'];