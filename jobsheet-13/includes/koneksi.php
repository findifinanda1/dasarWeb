<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

$config  = require __DIR__ . '/config.php';
$sslmode = getenv('DB_SSLMODE') ?: 'prefer';

try {
    $pdo = new PDO(
        "pgsql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};sslmode={$sslmode};connect_timeout=2",
        $config['db_user'],
        $config['db_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 2,
        ]
    );
} catch (PDOException $e) {
    // Detail error hanya dicatat di log server, tidak ditampilkan ke pengguna.
    error_log('Koneksi database gagal: ' . $e->getMessage());
    die('Koneksi database gagal. Hubungi administrator.');
}