<?php
header('Content-Type: text/plain; charset=utf-8');
ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "PHP: " . PHP_VERSION . "\n";
echo "pdo_pgsql: " . (extension_loaded('pdo_pgsql') ? 'ADA' : 'TIDAK ADA') . "\n";

foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_SSLMODE'] as $k) {
    $v = getenv($k);
    if ($v === false) {
        echo "$k: TIDAK TERISI\n";
    } elseif ($k === 'DB_PASS') {
        echo "$k: terisi (" . strlen($v) . " karakter)\n";
    } else {
        echo "$k: $v\n";
    }
}

$t = microtime(true);
try {
    $dsn = "pgsql:host=" . getenv('DB_HOST') . ";port=" . getenv('DB_PORT')
         . ";dbname=" . getenv('DB_NAME') . ";sslmode=" . (getenv('DB_SSLMODE') ?: 'require')
         . ";connect_timeout=8";
    $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "KONEKSI: BERHASIL (" . round((microtime(true) - $t) * 1000) . " ms)\n";
    foreach (['buku', 'anggota', 'users', 'peminjaman'] as $tb) {
        echo "$tb: " . $pdo->query("SELECT COUNT(*) FROM $tb")->fetchColumn() . " baris\n";
    }
} catch (Throwable $e) {
    echo "KONEKSI: GAGAL\n" . $e->getMessage() . "\n";
}