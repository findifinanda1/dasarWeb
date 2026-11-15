<?php
$page_title = "Beranda";
require __DIR__ . '/includes/koneksi.php';

$totalBuku       = (int) $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();
$totalAnggota    = (int) $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
$peminjamanAktif = (int) $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam'")->fetchColumn();
$keterlambatan   = (int) $pdo->query(
    "SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam' AND CURRENT_DATE - tanggal_pinjam > 14"
)->fetchColumn();

include __DIR__ . '/includes/header.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php if ($flash): ?>
    <div class="flash flash-<?php echo e($flash['type']); ?>">
        <?php echo e($flash['pesan']); ?>
    </div>
<?php endif; ?>

<section class="card mb-4">
    <h2>Selamat Datang di SIMPUS-Mini</h2>
    <p>Sistem Informasi Perpustakaan Mini berbasis Web.</p>
</section>

<section class="card">
    <h2>Ringkasan</h2>
    <div class="stats-grid">
        <article class="stat-card">
            <h3>Total Buku</h3>
            <p class="stat-number"><?php echo $totalBuku; ?></p>
        </article>
        <article class="stat-card">
            <h3>Total Anggota</h3>
            <p class="stat-number"><?php echo $totalAnggota; ?></p>
        </article>
        <article class="stat-card">
            <h3>Peminjaman Aktif</h3>
            <p class="stat-number"><?php echo $peminjamanAktif; ?></p>
        </article>
        <article class="stat-card">
            <h3>Keterlambatan</h3>
            <p class="stat-number"><?php echo $keterlambatan; ?></p>
        </article>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>