# Checklist Keamanan — Jobsheet 11

Audit menyeluruh terhadap kode Jobsheet 7-10, dengan bukti before/after.

| # | Kerentanan | Ditemukan di | Sebelum | Sesudah (perbaikan) |
|---|---|---|---|---|
| 1 | SQL Injection | Semua query di `buku/`, `anggota/`, `auth/` | Sejak Jobsheet 8 sudah memakai prepared statement PDO (`:parameter`) | Diaudit ulang, sudah aman. Uji input `' OR '1'='1` di form login tidak berhasil bypass (muncul "Username atau password salah"). |
| 2 | XSS (Cross-Site Scripting) | `buku/list.php`, `buku/edit.php`, `anggota/list.php`, `anggota/edit.php`, `includes/header.php` (nama petugas), pesan flash | Judul, pengarang, isbn, kategori, nama, alamat, no_hp, kata kunci pencarian (`q`), dan nama petugas dicetak langsung tanpa escaping | Dibungkus fungsi `e()` (`includes/helpers.php`, `htmlspecialchars` dengan `ENT_QUOTES`). Uji judul buku `<script>alert(1)</script>` tampil sebagai teks biasa, bukan dieksekusi. |
| 3 | CSRF (Cross-Site Request Forgery) | Form Tambah/Edit/Hapus Buku & Anggota, Login, Register | Form POST tidak memiliki token verifikasi, bisa dipicu dari situs lain | Ditambah `includes/csrf.php` (`csrf_field()` + `csrf_verify()`), token disimpan di `$_SESSION['csrf_token']` dan diverifikasi di setiap `proses_*.php` dan `hapus.php` sebelum query dijalankan. Uji curl tanpa token pada sesi login menghasilkan HTTP 403. |
| 4 | Validasi & Sanitasi Input | `proses_tambah.php`, `proses_edit.php`, `hapus.php` (buku & anggota), `edit.php` | Sudah ada validasi tipe (`is_numeric`) dan wajib-isi sejak Jobsheet 7-9. `id` dari `$_GET`/`$_POST` belum di-cast. | Validasi dipertahankan, ditambah cast eksplisit `(int)` pada `id` agar nilai non-numerik tidak masuk. |
| 5 | Session Fixation | `auth/proses_login.php` | Session ID tidak diperbarui setelah login | `session_regenerate_id(true)` dipanggil tepat setelah `password_verify()` berhasil. Uji: `PHPSESSID` berubah dari `8cc4e2bb...` menjadi `eaba86ae...` setelah login. |
| 6 | Broken Access Control (temuan tambahan) | `buku/edit.php`, `buku/hapus.php`, `anggota/edit.php` | Tidak memanggil guard `auth.php`, sehingga tamu bisa membuka form edit dan `buku/hapus.php` bisa menghapus tanpa login | Ditambah `require includes/auth.php` di awal file. Akses tanpa login diarahkan ke halaman Login. |
| 7 | Kebocoran pesan error | `buku/test_list.php`, `includes/koneksi.php` | File debug dengan `display_errors = 1` dapat dibuka tanpa login, dan error PHP/PDO berpotensi tampil ke pengguna. | `test_list.php` dihapus. `display_errors` dimatikan dan `log_errors` dinyalakan sehingga error dicatat di log, bukan ditampilkan. |

## Catatan Implementasi

- Guard `includes/auth.php` selalu dijalankan sebelum `includes/csrf.php` di halaman proses, memastikan pengguna yang belum login tidak bisa memicu pengecekan CSRF sama sekali (langsung di-redirect ke login). Diuji dengan curl tanpa cookie sesi: hasilnya HTTP 302 ke `login.php`.
- Fungsi `csrf_verify()` mengembalikan HTTP 403 dan menghentikan eksekusi (`die()`) bila token tidak cocok atau tidak ada. Diuji dengan curl POST tanpa `csrf_token` ke `proses_tambah.php` pada sesi login.
- Panggilan `session_start()` ganda di `proses_tambah.php` dan `proses_edit.php` (buku & anggota) dihapus karena sesi sudah dimulai oleh `auth.php`.