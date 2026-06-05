1. Daftar Anggota Kelompok
- Agustinus Janssen Simamora - 2410631170055
- Taqi Hamizan - 2410631170113
- Haikal Quds - 2410631170141

2. Deskripsi dan Tujuan Website
   ApotekCare adalah sistem informasi apotek berbasis web yang dikembangkan untuk membantu proses pengelolaan data obat, kategori obat, stok, transaksi penjualan, serta pemesanan obat secara online oleh customer.
   Sistem ini memiliki dua sisi utama, yaitu sisi staff/apoteker untuk mengelola data dan transaksi, serta sisi customer untuk melihat katalog obat, menambahkan produk ke keranjang, dan membuat pesanan.
   Tujuan dari website ini adalah mempermudah pengelolaan operasional apotek secara digital, mengurangi pencatatan manual, serta memberikan kemudahan bagi customer dalam pengecekan obat yang tersedia,
   sehingga tidak perlu repot untuk berkunjung ke apotek langsung ketika stok obat tidak tersedia dan mempermudah melakukan pemesanan obat melalui website.

3. Fitur-fitur Utama Website
   Fitur Admin/Apoteker
   a. Login admin/apoteker.
   b. Dashboard informasi apotek.
   c. Mengelola data obat.
   d. Melakukan transaksi kasir.
   e. Mengelola pesanan customer online.
   f. Mengubah Profile
   g. Logout.

   Fitur Customer
   a. Registrasi akun customer.
   b. Login customer.
   c. Melihat katalog obat.
   d. Mencari dan memilih obat.
   e. Menambahkan obat ke keranjang.
   f. Membuat pesanan.
   g. Melihat daftar pesanan.
   h. Logout.

4. Struktur Project
   UAS Project/
    ├── assets/
    │   ├── css/
    │   ├── js/
    │   └── images/
    ├── includes/
    │   ├── database.php
    │   ├── guard.php
    │   └── helper.php
    ├── admin/
    │   ├── dashboard.php
    │   ├── obat.php
    │   ├── kategori.php
    │   ├── transaksi.php
    │   └── pesanan.php
    ├── customer/
    │   ├── katalog.php
    │   ├── keranjang.php
    │   └── pesanan.php
    ├── auth.php
    ├── index.php
    ├── apotekcare.sql
    └── README.md

   Penjelasan folder/file penting
   a. index.php: halaman awal website.
   b. auth.php: file untuk proses login dan logout.
   c. includes/database.php: file koneksi database.
   d. includes/guard.php: file untuk membatasi akses berdasarkan role user.
   e. apoteker/: folder halaman untuk staff atau apoteker.
   f. customer/: folder halaman untuk customer.
   g. foto/: folder penyimpanan file gambar.
   h. apotekcare.sql: file database yang harus di-import ke phpMyAdmin.
   i. README.md: file dokumentasi project.

5.Cara Menjalankan Aplikasi
  a. Download atau clone repository ini.
  b. Pindahkan folder project ke dalam folder htdocs jika menggunakan XAMPP atau ke folder www jika menggunakan laragon.
  c. Jalankan Apache dan MySQL melalui XAMPP/Laragon Control Panel.
  d. Buka phpMyAdmin melalui browser:
        http://localhost/phpmyadmin
  e. Buat database baru dengan nama:
        apotek_db
  f. Import file database:
        apotek_db.sql
  g. Sesuaikan konfigurasi database pada file:
        includes/database.php
  
  Contoh konfigurasi:
  
  $host = "localhost";
  $user = "root";
  $password = "";
  $database = "apotek_db";
  
  Jalankan website melalui browser:
  http://localhost/UAS Project/

6. Link Video Presentasi
   
