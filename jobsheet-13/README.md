# Jobsheet 13 — Deployment & Dokumentasi (SIMPUS-Mini)

Sub-CPMK: Mendeploy dan mendokumentasikan aplikasi.

Snapshot akhir proyek SIMPUS-Mini setelah Jobsheet 1-12, siap didemokan pada UAS.

## Deskripsi Aplikasi

SIMPUS-Mini adalah aplikasi web sederhana untuk mengelola perpustakaan: data buku, anggota,
serta transaksi peminjaman/pengembalian, dengan autentikasi petugas.

Stack: HTML5, CSS3, JavaScript, PHP native, PostgreSQL (PDO_PGSQL).

## ERD Final

```
buku            anggota          users             peminjaman
------          --------         ------            -----------
id (PK)         id (PK)          id (PK)           id (PK)
judul           nama             nama              buku_id (FK -> buku.id)
pengarang       no_anggota (UQ)  username (UQ)     anggota_id (FK -> anggota.id)
tahun           alamat           password (hash)   tanggal_pinjam
isbn            no_hp            role              tanggal_jatuh_tempo
stok                             remember_token    tanggal_kembali
kategori                                           status
```

## Fitur per Role

| Fitur | Tamu (tanpa login) | Petugas (login) |
|---|---|---|
| Lihat Beranda & statistik | Ya | Ya |
| Lihat Daftar Buku | Ya | Ya |
| Tambah/Edit Buku | Tidak | Ya |
| Tambah/Edit Anggota | Tidak | Ya |
| Hapus Anggota | Tidak | Hanya role `admin` |
| Peminjaman Baru | Tidak | Ya |
| Pengembalian | Tidak | Ya |
| Riwayat Peminjaman | Tidak | Ya |

Akun hasil registrasi selalu berperan `petugas`. Untuk menjadikan akun sebagai admin:

```sql
UPDATE users SET role = 'admin' WHERE username = 'nama_pengguna';
```

## Instalasi & Menjalankan

1. Salin folder ini ke server (lokal atau hosting yang mendukung PHP + PostgreSQL).
2. Buat database dan impor skema (urutan penting karena `peminjaman` mereferensikan `buku` / `anggota`):

   ```
   createdb simpus_mini
   psql -d simpus_mini -f sql/01_buku_anggota.sql
   psql -d simpus_mini -f sql/02_users.sql
   psql -d simpus_mini -f sql/03_peminjaman.sql
   ```

3. Konfigurasi koneksi: kredensial dibaca dari `includes/config.php`, yang mengambil environment variable
   (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`) dengan fallback ke nilai default lokal.
   Daftar variabel ada di `.env.example`. Set sebelum menjalankan server bila kredensial berbeda:

   ```
   DB_HOST=127.0.0.1 DB_NAME=simpus_mini DB_USER=produser DB_PASS=rahasia php -S localhost:8000
   ```

   > Jangan mengubah nilai default di `includes/config.php` menjadi kredensial asli lalu meng-commit-nya ke repository publik.

4. Jalankan. Path CSS/JS/link/redirect dihitung relatif otomatis di `includes/header.php` dan `includes/auth.php`.
   - Opsi 1 — PHP built-in server: `php -S localhost:8000`
   - Opsi 2 — Laragon (Apache): virtual host langsung ke folder `jobsheet-13/` (mis. `http://jobsheet13.test/`),
     atau bersarang di bawah domain proyek (mis. `http://dp2026.test/kode-praktikum/jobsheet-13/`).
5. Buka `http://localhost:8000/index.php` (atau URL Laragon), lalu registrasi akun petugas pertama
   lewat `auth/register.php`.

> Hosting Linux bersifat case-sensitive: nama file harus persis sama dengan yang ditautkan
> (mis. `auth/logout.php` huruf kecil semua).

## Struktur Folder

```
jobsheet-13/
├── index.php                 Beranda (statistik real-time dari DB)
├── includes/                 config.php, koneksi.php, header.php, footer.php, auth.php,
│                             csrf.php, helpers.php, remember.php
├── assets/css, assets/js     styling & interaktivitas
├── buku/                     CRUD Buku
├── anggota/                  CRUD Anggota
├── auth/                     Register, Login, Logout
├── peminjaman/               Peminjaman, Pengembalian, Riwayat
├── sql/                      skema database (01-03, jalankan berurutan)
└── docs/                     wireframe.md, security-checklist.md, manual-pengguna.md
```

## Dokumen Pendukung

- `docs/wireframe.md` — rancangan UX (Jobsheet 4)
- `docs/security-checklist.md` — audit keamanan (Jobsheet 11)
- `docs/manual-pengguna.md` — panduan penggunaan aplikasi

## Catatan

Sebelum demo UAS, jalankan `php -l` pada berkas PHP dan uji alur end-to-end di lingkungan lab
yang memiliki PostgreSQL aktif. Siapkan penjelasan alasan desain teknis: struktur tabel dan alur
transaksi peminjaman (lihat README Jobsheet 12 untuk detail transaksi stok).