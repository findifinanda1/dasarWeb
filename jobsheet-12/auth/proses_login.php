<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/csrf.php';

// Jobsheet 11: verifikasi token CSRF paling awal
csrf_verify();

// Pengaturan batas percobaan (Jobsheet 10, Latihan 3)
$maks   = 3;    // batas percobaan gagal
$durasi = 60;   // lama dikunci (detik)

// Cek apakah sedang dikunci
if (isset($_SESSION['blokir_sampai']) && time() < $_SESSION['blokir_sampai']) {
    $sisa = $_SESSION['blokir_sampai'] - time();
    $_SESSION['flash'] = [
        'type'  => 'error',
        'pesan' => "Terlalu banyak percobaan gagal. Coba lagi dalam $sisa detik."
    ];
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ===== Login berhasil =====
if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);

    unset($_SESSION['gagal_login'], $_SESSION['blokir_sampai']);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama']    = $user['nama'];
    $_SESSION['role']    = $user['role'];

    // Ingat Saya (Jobsheet 10, Latihan 2)
    if (isset($_POST['ingat'])) {
        $token = bin2hex(random_bytes(32));

        $upd = $pdo->prepare("UPDATE users SET remember_token = :t WHERE id = :id");
        $upd->execute(['t' => hash('sha256', $token), 'id' => $user['id']]);

        setcookie('remember', $user['id'] . ':' . $token, [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    header('Location: ../index.php');
    exit;
}

// ===== Login gagal, hitung percobaan =====
$_SESSION['gagal_login'] = ($_SESSION['gagal_login'] ?? 0) + 1;

if ($_SESSION['gagal_login'] >= $maks) {
    $_SESSION['blokir_sampai'] = time() + $durasi;
    $_SESSION['gagal_login']   = 0;
    $pesan = "Terlalu banyak percobaan gagal. Login dikunci $durasi detik.";
} else {
    $sisa  = $maks - $_SESSION['gagal_login'];
    $pesan = "Username atau password salah. Sisa percobaan: $sisa.";
}

$_SESSION['flash'] = ['type' => 'error', 'pesan' => $pesan];
header('Location: login.php');
exit;