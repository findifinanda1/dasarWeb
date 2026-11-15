# Jobsheet 12 — Integrasi Modul Peminjaman

Sub-CPMK: Mengintegrasikan front-end dan back-end proyek secara utuh.

## Perubahan dari Jobsheet 11
- Tambah `sql/03_peminjaman.sql`: tabel `peminjaman` (relasi ke `buku` dan `anggota`).
- Modul Peminjaman:
  - `peminjaman/tambah.php` + `proses_tambah.php`: pilih anggota + buku (stok > 0), simpan transaksi dan kurangi stok dalam satu transaction dengan `SELECT ... FOR UPDATE`.
  - `peminjaman/kembali.php` + `proses_kembali.php`: daftar transaksi aktif, tombol Kembalikan menambah stok dalam transaction.
  - `peminjaman/riwayat.php`: histori peminjaman per anggota (JOIN).
- `includes/header.php`: menu Peminjaman Baru, Pengembalian, Riwayat (hanya saat login).
- `index.php`: kartu "Sedang Dipinjam" dihitung dari database.

## Cara menjalankan
psql -d simpus_mini -f sql/03_peminjaman.sql
php -S localhost:8000

## Pengujian end-to-end
Registrasi, Login, Tambah Buku dan Anggota, Peminjaman Baru (stok berkurang, kartu bertambah), Pengembalian (stok kembali), Riwayat (status Selesai), Logout.