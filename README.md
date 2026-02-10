# Tokopedia to Shopee Scraper 🛒

Aplikasi sederhana berbasis PHP untuk mengambil data produk dari Tokopedia dan mengubahnya menjadi format Excel (.xlsx) yang siap di-upload ke Shopee (Mass Upload).

**Versi PHP yang dibutuhkan: 8.2 atau lebih baru.**

## Fitur ✨

1.  **Scraping Otomatis**: Masukkan link produk Tokopedia untuk mengambil data secara otomatis.
2.  **Scraping Manual (Anti-Banned)**: Jika hosting Anda diblokir oleh Tokopedia (Error 403), Anda bisa copy-paste _source code HTML_ halaman produk.
3.  **Excel Generator**: Output langsung berupa file `.xlsx` dengan kolom standar Shopee (Kategori, Nama, Deskripsi, Harga, Stok, Berat, Foto, dll).
4.  **Tanpa Database**: Ringan dan mudah dideploy di shared hosting manapun.

## Cara Install di cPanel (Shared Hosting) 🚀

1.  **Siapkan File**:
    - Download atau zip seluruh folder `scraping` ini (pastikan folder `vendor` ikut terbawa).
2.  **Upload ke Hosting**:
    - Masuk ke cPanel -> File Manager -> `public_html`.
    - Upload file zip tadi dan ekstrak. Misalnya diekstrak ke folder `public_html/scraper`.

3.  **Cek Versi PHP**:
    - Di cPanel, cari menu **"Select PHP Version"** atau **"MultiPHP Manager"**.
    - Pastikan domain Anda diset menggunakan **PHP 8.2**.
    - Pastikan ekstensi PHP berikut aktif (biasanya sudah default): `json`, `mbstring`, `xml`, `zip`.

4.  **Selesai**:
    - Akses aplikasi di browser Anda: `http://domain-anda.com/scraper`.

## Cara Menggunakan 💡

### Metode 1: Otomatis (Cepat)

1.  Buka aplikasi.
2.  Pilih tab **"Automatic Scraper"**.
3.  Masukkan link produk Tokopedia (satu link per baris).
4.  Klik tombol **"Processing & Download Shopee Excel"**.

### Metode 2: Manual (Solusi jika gagal scraping otomatis)

Metode ini 100% berhasil karena mem-bypass proteksi Cloudflare.

1.  Buka halaman produk Tokopedia yang ingin discrape di browser (Chrome/Firefox).
2.  Klik kanan di halaman -> **View Page Source** (atau tekan `Ctrl+U`).
3.  Di tab source code yang muncul, tekan `Ctrl+A` (Select All) lalu `Ctrl+C` (Copy).
4.  Kembali ke aplikasi scraper, pilih tab **"Manual HTML"**.
5.  Paste (`Ctrl+V`) kode tadi ke kotak yang tersedia.
6.  Klik tombol **"Processing & Download Shopee Excel"**.

## Struktur Folder 📂

- `index.php`: Halaman utama aplikasi (UI).
- `process.php`: Script pemroses data dan pembuat file Excel.
- `lib/`:
  - `Scraper.php`: Logika untuk mengambil data dari Tokopedia.
  - `ExcelGenerator.php`: Logika untuk membuat file Excel format Shopee.
- `vendor/`: Library pendukung (Guzzle & PhpSpreadsheet). **Jangan dihapus.**

---

**Catatan Penting**:
Aplikasi ini bergantung pada struktur halaman Tokopedia. Jika Tokopedia mengubah tampilan websitenya secara drastis, scraper mungkin perlu diperbarui.
