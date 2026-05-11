# Refactoring Auth Module Final Report

## Ringkasan Perubahan
Modul autentikasi utama (`core/Auth.php`) telah berhasil direfactor dengan memisahkan logika ke dalam tiga class baru agar memenuhi prinsip Single Responsibility (SRP). Perubahan yang dilakukan:
1. **`core/AuthSecurity.php`**: Menangani fitur keamanan seperti rate limiting (brute-force protection), pembuatan CSRF token, dan verifikasi CSRF.
2. **`core/AccessControl.php`**: Menangani otorisasi (role checker), pengelolaan akses halaman (`requireRole`), deteksi session hijacking via User-Agent, dan routing dashboard berdasarkan role.
3. **`core/AuthService.php`**: Menangani proses otentikasi inti yaitu `login` dan `logout` dengan verifikasi dari database menggunakan bcrypt dan PDO, serta mengelola data user saat ini di dalam session.
4. **`core/Auth.php`**: Diubah menjadi sebuah *facade* yang sangat tipis. File ini sekarang hanya mendefinisikan public API yang sama dengan sebelumnya dan sekadar mendelegasikan pemanggilan (contoh: `Auth::login` meneruskan ke `AuthService::login`). 

## Kenapa Diubah?
- **Menghilangkan monolithic class**: Sebelumnya `Auth.php` menanggung semua beban (koneksi DB, algoritma rate limit, pengelolaan session, CSRF, dll) dalam satu file besar yang membingungkan dan sulit dirawat.
- **Konsistensi Arsitektur**: Dengan memisahkannya, modul-modul lain di aplikasi bisa menggunakan komponen auth yang mereka perlukan.
- **Backward Compatibility**: Dengan mempertahankan `Auth.php` sebagai facade, semua halaman yang ada (seperti `login.php`, dashboard admin/kasir/gudang) tetap berfungsi tanpa harus mengubah baris kode mereka sama sekali.

## Dampak ke File Lain
- **TIDAK ADA file view atau controller lain yang perlu diubah**. Karena API statis dari `Auth::*` (`login`, `logout`, `requireRole`, `isLoggedIn`, dll) dipertahankan utuh.
- `php -l` berhasil dijalankan dan semua file PHP dinyatakan memiliki syntax yang valid.
- Hasil pencarian repositori memastikan bahwa semua pemanggilan seperti `Auth::requireRole` dan `Auth::generateCsrfToken` masih didukung secara native oleh facade.

## Checklist Verifikasi Manual
Silakan lakukan pengecekan berikut di environment Anda untuk memvalidasi:
- [ ] **Login Sukses**: Coba login dengan username dan password yang benar.
- [ ] **Login Gagal & Rate Limiter**: Coba login dengan password salah sebanyak 5 kali. Pastikan pesan error yang muncul menyebutkan bahwa akun terkunci ("Terlalu banyak percobaan gagal").
- [ ] **Role Mismatch (Otorisasi)**: Login sebagai `kasir`, lalu coba ubah URL ke `admin/index.php`. Pastikan Anda otomatis ter-redirect dan ditolak.
- [ ] **Logout**: Klik tombol logout dan pastikan session benar-benar hilang, lalu coba akses halaman dashboard lagi.
- [ ] **CSRF Protection**: Cobalah submit suatu form di aplikasi, pastikan token valid dan proses berhasil masuk database.
