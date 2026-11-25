# Trrrible — menjalankan proyek di laptop anggota kelompok

## Persiapan (sekali per komputer)

Instal Docker Desktop, PHP, Composer, Node.js/npm, dan PostgreSQL PHP extension (`pdo_pgsql`). Buka terminal Git Bash (Windows) di folder proyek.

1. Salin `.env.example` menjadi `.env`. Isi `APP_DB_PASSWORD` dan `POSTGRES_SUPERUSER_PASSWORD` dengan **dua password lokal** (jangan kirim password pribadi di ZIP). Pastikan `DB_PORT=5437`, `DB_HOST=127.0.0.1`, `DB_DATABASE=trrrible`, `DB_USERNAME=trrrible_app`, `DB_PASSWORD` sama dengan `APP_DB_PASSWORD`. Jika port 5437 sudah dipakai, sesuaikan `DB_FORWARD_PORT` dan `DB_PORT`.
2. Jalankan `bash setup-new.sh` (Docker Desktop harus sudah aktif). Script akan memasang dependensi, membangun CSS, memulai PostgreSQL, menjalankan migration dan seeder, lalu menerapkan **file RLS asli** dan hak akses.
3. Jika `APP_KEY` masih kosong, jalankan `php artisan key:generate`.
4. Jalankan `php artisan serve`, lalu buka alamat lokal yang muncul.

**Penting:** Script tidak menghapus volume Docker dan tidak menjalankan `migrate:fresh`. Jangan gunakan `docker compose down -v` pada database yang ingin dipertahankan. Jangan bagikan `.env` asli.

**Batasan:** Database PostgreSQL dalam volume Docker tidak ikut ZIP. Database baru menggunakan baseline SQL dan demo produk dari seeder. Data transaksi nyata dari laptop asal memerlukan backup/restore terpisah. Jalankan script hanya untuk setup komputer baru, bukan sebagai cara mengubah database utama yang sudah diuji.

**Keamanan:** RLS diterapkan setelah migration dan tetap memakai `database/postgresql/05_row_level_security.sql` asli. Jika policy sudah ada tetapi jumlahnya berbeda, setup berhenti agar tidak menimpa aturan keamanan. Setelah setup, uji kembali login, keranjang, checkout, dan hak akses pada laptop tujuan sebelum dipakai sebagai sistem produksi.

## Isi paket perubahan

- Palet UI lilac dan gradien pada file presentasi Blade/CSS; controller, middleware dan logika checkout tidak diedit.
- MarketplaceProductsSeeder: 28 produk contoh total, dengan SVG preview unik per judul. Seeder memakai `firstOrCreate` dan tidak mengubah stok/penjualan produk lama.
- `setup-new.sh` dan panduan ini untuk instalasi baru; migration dan SQL RLS asli dipertahankan.
