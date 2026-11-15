<?php
// Set APP_DEBUG=1 di Env Vars hanya saat mencari error, lalu hapus lagi.
ini_set('display_errors', getenv('APP_DEBUG') ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

$config  = require __DIR__ . '/config.php';
$sslmode = getenv('DB_SSLMODE') ?: 'prefer';

try {
    $pdo = new PDO(
        "pgsql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};sslmode={$sslmode};connect_timeout=5",
        $config['db_user'],
        $config['db_pass'],
        [
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true,
        ]
    );
} catch (PDOException $e) {
    error_log('Koneksi database gagal: ' . $e->getMessage());
    die('Koneksi database gagal. Hubungi administrator.');
}