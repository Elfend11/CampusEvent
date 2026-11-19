# 1 - Baca REQUIREMENTS.md, ARCHITECTURE.md, RULES.md, dan TASKS.md.

Lakukan inspeksi awal terhadap repository CampusEvent yang sedang terbuka.

Periksa:
1. Struktur folder dan file.
2. Framework dan teknologi yang sudah digunakan.
3. Konfigurasi proyek dan dependency.
4. Kondisi Git, branch aktif, dan perubahan yang belum di-commit.
5. File atau konfigurasi yang belum tersedia.

Jangan membuat, mengubah, menghapus, atau menginstal apa pun terlebih dahulu.

Berikan laporan hasil inspeksi, temuan, dan rekomendasi langkah berikutnya. Setelah itu berhenti dan tunggu persetujuan saya.

# 2 - https://github.com/Elfend11/CampusEvent.git

# 3 - Sebelum memulai Task 1.3, lakukan evaluasi seluruh temuan F-01 sampai F-13.

Untuk setiap temuan, jelaskan:
1. Masalah yang ditemukan.
2. Dokumen dan bagian yang berkaitan.
3. Apakah benar merupakan kekurangan dokumentasi atau hanya detail implementasi.
4. Dampaknya terhadap sistem CampusEvent.
5. Rekomendasi solusi paling minimal tanpa mengubah keputusan utama proyek.

Berikan perhatian khusus pada skema database, event gratis, status registrasi dan pembayaran, pembatalan, retry pembayaran, Laravel Sanctum, serta penamaan file dokumentasi.

Jangan mengubah file, membuat migration, menginstal dependency, atau menjalankan implementasi apa pun.

Periksa juga status repository lokal dan jelaskan apakah sudah siap membuat branch.

Berikan laporan lengkap, kemudian berhenti dan tunggu persetujuan saya.

# 4 - Baca kembali seluruh dokumen utama CampusEvent:
- REQUIREMENTS.md
- ARCHITECTURE.md
- RULES.md
- TASKS.md

Gunakan seluruh keputusan F-01 sampai F-13 yang telah disepakati sebagai dasar amendemen dokumentasi.

TUGAS:
1. Buat branch baru bernama `docs/architecture-amendment`. Jangan mengubah branch main secara langsung.
2. Perbarui dokumen yang relevan berdasarkan keputusan F-01 sampai F-13.
3. Pastikan REQUIREMENTS.md, ARCHITECTURE.md, RULES.md, dan TASKS.md konsisten satu sama lain.
4. Pertahankan ketentuan lama yang tidak bertentangan dengan keputusan baru.
5. Jangan mengubah arsitektur atau menambahkan keputusan baru di luar persetujuan pengguna.
6. Jangan membuat atau mengubah file implementasi, migration, model, controller, service, maupun konfigurasi aplikasi.
7. Jangan melakukan instalasi dependency atau upgrade versi perangkat lunak.

KEPUTUSAN YANG HARUS DITERAPKAN:
- F-01: Event memiliki atribut location dan category.
- F-02: Registration memiliki expires_at dan index gabungan status serta expires_at.
- F-03: Tambahkan event_officers dengan relasi event_id dan user_id serta unique constraint pada kombinasi keduanya.
- F-04: Event gratis tidak membuat record pembayaran. Reservasi dan konfirmasi dilakukan secara atomik, sold_count bertambah, reserved_count tidak bertambah, dan tiket langsung dibuat.
- F-05: Event: DRAFT, PUBLISHED, CLOSED, CANCELLED. Registration: PENDING, CONFIRMED, EXPIRED, CANCELLED. Payment: PENDING, PAID, FAILED. Ticket: ACTIVE, USED, VOID. Validasi transisi status wajib dilakukan di backend.
- F-06: Pembatalan mandiri mahasiswa di luar cakupan v1. Pembatalan administratif mengikuti aturan transaksi dan pelepasan kuota, tanpa double release.
- F-07: Satu akun dapat memiliki beberapa role. Admin organisasi hanya mengakses organisasi yang ditugaskan. Petugas check-in hanya mengakses event yang ditugaskan. Hak mahasiswa tetap tersedia. Super Admin memiliki cakupan platform.
- F-08: Satu registration memiliki satu payment record. Retry menggunakan record yang sama selama reservasi aktif, bersifat transactional dan idempotent. Pembayaran yang sudah PAID tidak boleh diproses ulang atau menghasilkan tiket duplikat. Pembayaran setelah reservasi kedaluwarsa harus ditolak.
- F-09: Gunakan Laravel Sanctum dengan Cookie-Based SPA Authentication. Terapkan CSRF protection, session security, dan konfigurasi cookie yang sesuai. Jangan menyimpan token autentikasi di localStorage.
- F-10: PHP 8.3.x sebagai standar lingkungan pengembangan.
- F-11: Verifikasi advisory keamanan Composer 2.9.7 dan versi stabil yang direkomendasikan sebelum memutuskan upgrade.
- F-12: Pertahankan nama REQUIREMENTS.md, ARCHITECTURE.md, RULES.md, dan TASKS.md.
- F-13: Folder kerja utama adalah C:\laragon\www\campusevent. Jangan menghapus folder lama di OneDrive sebelum diverifikasi.

ATURAN KERJA:
- Jangan mengarang detail teknis yang belum disepakati.
- Jika ada konflik antar dokumen, identifikasi dan laporkan. Jangan menyelesaikan konflik dengan membuat keputusan sepihak.
- Pastikan aturan reservasi 10 menit, concurrency control menggunakan MySQL InnoDB, transaksi database, SELECT FOR UPDATE, dan batas overselling tetap dipertahankan.
- Pertahankan target pengujian 100 request bersamaan dengan 5 tiket tersisa: tepat 5 berhasil dan 95 ditolak.
- Jangan melakukan implementasi kode pada tahap ini.

LAPORAN AKHIR:
Tampilkan:
1. Branch yang digunakan.
2. Daftar dokumen yang diubah.
3. Ringkasan perubahan setiap dokumen.
4. Pemeriksaan konsistensi antar dokumen.
5. Konflik atau hal yang masih membutuhkan keputusan pengguna.
6. Status Git dan daftar perubahan.

Jangan commit, merge, atau push ke main. Setelah laporan selesai, BERHENTI dan tunggu persetujuan pengguna.

# 5 - Kita akan mengubah pola kerja menjadi IMPLEMENTATION-FIRST.

Baca dokumen proyek yang sudah tersedia, terutama TASKS.md, RULES.md, ARCHITECTURE.md, dan REQUIREMENTS.md.

KONDISI:
- Branch aktif: docs/architecture-amendment.
- Perubahan amendemen dokumentasi sudah dibuat dan di-stage, belum di-commit.
- Belum ada implementasi aplikasi.
- Gunakan keputusan F-01 sampai F-13 yang sudah disepakati.
- Delapan poin terbuka K-1 sampai K-8 dalam laporan inspeksi tidak boleh membuat seluruh implementasi tertunda. Selesaikan keputusan yang benar-benar diperlukan ketika mencapai fitur terkait.

ATURAN KERJA BARU:
1. Jangan mengulang analisis atau inspeksi yang sudah selesai.
2. Jangan membuat laporan panjang untuk setiap task.
3. Kerjakan TASKS.md secara berurutan.
4. Gunakan pendekatan implementasi langsung: buat kode, jalankan tes, perbaiki error.
5. Jangan berhenti hanya untuk meminta persetujuan setelah setiap task kecil.
6. Jika ada keputusan penting yang benar-benar memengaruhi arsitektur, keamanan, atau struktur database dan belum ditentukan, tanyakan satu pertanyaan singkat sebelum melanjutkan bagian terkait.
7. Untuk keputusan teknis kecil yang sudah tercakup dalam dokumen, gunakan keputusan yang ada tanpa bertanya lagi.
8. Jangan mengubah teknologi atau arsitektur yang sudah disepakati.
9. Jaga seluruh aturan keamanan, transaksi, idempotency, dan concurrency.
10. Perbarui TASKS.md seperlunya sesuai progres aktual.
11. Lakukan commit secara bertahap dengan pesan yang jelas. Jangan merge ke main atau push tanpa persetujuan.
12. Berikan laporan singkat: apa yang selesai, tes yang dijalankan, hasilnya, dan task berikutnya.

MULAI SEKARANG:
Lanjutkan dari kondisi repository saat ini. Selesaikan Task 1.3 — Environment Setup sesuai TASKS.md.

Setelah environment berhasil diverifikasi, langsung lanjut ke task berikutnya yang tidak membutuhkan keputusan pengguna.

Jangan membuat analisis ulang. Fokus menghasilkan aplikasi yang benar-benar berjalan.

Jika seluruh Phase 1 selesai, laporkan hasilnya secara singkat dan lanjutkan sesuai roadmap.

# 6 - 