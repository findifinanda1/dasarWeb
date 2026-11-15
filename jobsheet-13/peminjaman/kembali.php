<?php
require __DIR__ . '/../includes/auth.php';
$page_title = "Pengembalian Buku";
include __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/koneksi.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$q = trim($_GET['q'] ?? '');

// LATIHAN 4: tambah a.no_hp
// LATIHAN 2: tambah jatuh tempo dan penanda terlambat
$sql = "SELECT p.id, b.judul, a.nama, a.no_hp, p.tanggal_pinjam, p.tanggal_jatuh_tempo,
               CASE WHEN p.tanggal_jatuh_tempo < CURRENT_DATE THEN 1 ELSE 0 END AS terlambat
        FROM peminjaman p
        JOIN buku b ON b.id = p.buku_id
        JOIN anggota a ON a.id = p.anggota_id
        WHERE p.status = 'dipinjam'";

if ($q !== '') {
    $sql .= " AND (a.nama ILIKE :kw1 OR b.judul ILIKE :kw2)";
}
$sql .= " ORDER BY p.tanggal_pinjam DESC, p.id DESC";

$stmt = $pdo->prepare($sql);
if ($q !== '') {
    $stmt->bindValue('kw1', '%' . $q . '%');
    $stmt->bindValue('kw2', '%' . $q . '%');
}
$stmt->execute();
$daftarAktif = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<section>
<h2>Pengembalian Buku</h2>

<?php if ($flash): ?>
<p class="flash flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['pesan']); ?></p>
<?php endif; ?>

<form method="get" action="kembali.php" class="search-box">
<div class="search-field">
<label for="search-input">Cari transaksi aktif (nama anggota / judul buku)</label>
<input type="text" id="search-input" name="q" placeholder="Ketik nama anggota atau judul buku..." value="<?php echo e($q); ?>">
</div>
<button type="submit">Cari</button>
<?php if ($q !== ''): ?>
<a href="kembali.php" class="btn-reset">Reset</a>
<?php endif; ?>
</form>

<div class="table-responsive">
<table>
<thead>
<tr>
<th>Anggota</th>
<th>No. HP</th>
<th>Buku</th>
<th>Tgl Pinjam</th>
<th>Jatuh Tempo</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>
<?php if (empty($daftarAktif)): ?>
<tr>
<td colspan="6">Tidak ada transaksi peminjaman aktif.</td>
</tr>
<?php else: ?>
<?php foreach ($daftarAktif as $t): ?>
<tr>
<td><?php echo e($t['nama']); ?></td>
<td><?php echo e($t['no_hp']); ?></td>
<td><?php echo e($t['judul']); ?></td>
<td><?php echo date('d/m/Y', strtotime($t['tanggal_pinjam'])); ?></td>
<td>
<?php echo $t['tanggal_jatuh_tempo'] ? date('d/m/Y', strtotime($t['tanggal_jatuh_tempo'])) : '-'; ?>
<?php if ((int) $t['terlambat'] === 1): ?>
<strong>(Terlambat)</strong>
<?php endif; ?>
</td>
<td>
<form method="post" action="proses_kembali.php">
<?php echo csrf_field(); ?>
<input type="hidden" name="id" value="<?php echo (int) $t['id']; ?>">
<button type="submit">Kembalikan</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>