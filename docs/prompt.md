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

# 6 - Lanjutkan implementasi CampusEvent dari progres repository saat ini.

Baca TASKS.md, periksa progres aktual, lalu langsung kerjakan task berikutnya yang belum selesai.

Gunakan Build agent. Jangan mengulang analisis, jangan membuat laporan panjang, jangan membuat ulang proyek Laravel/frontend/backend, dan jangan mengubah arsitektur yang telah disepakati.

Implementasikan fitur, jalankan pengujian, perbaiki error, dan perbarui TASKS.md sesuai progres.

Jangan commit, push, merge, atau membuat PR. Git saya kelola manual.

Jika tidak ada keputusan penting yang menghalangi, langsung coding sampai task selesai. Laporkan hasil secara singkat dan lanjutkan ke task berikutnya.

# 7 - Lanjutkan pengembangan CampusEvent dari kondisi repository terakhir.

Baca TASKS.md untuk mengetahui task terakhir yang selesai dan task berikutnya yang belum dikerjakan. Periksa kode terkait seperlunya, lalu langsung implementasikan task berikutnya.

ATURAN:
1. Gunakan arsitektur dan ketentuan dalam REQUIREMENTS.md, ARCHITECTURE.md, RULES.md, dan TASKS.md.
2. Jangan mengulang analisis, inspeksi, atau membuat dokumentasi baru yang tidak diperlukan.
3. Jangan membuat ulang proyek Laravel, backend, maupun frontend yang sudah tersedia.
4. Jangan mengubah teknologi atau arsitektur yang telah disepakati.
5. Implementasikan fitur sampai berfungsi, jalankan pengujian, dan perbaiki error.
6. Perbarui TASKS.md berdasarkan pekerjaan yang benar-benar selesai.
7. Jangan mengerjakan task yang sudah selesai atau membuat implementasi duplikat.
8. Jangan commit, push, merge, atau membuat Pull Request. Semua Git dikelola manual oleh saya.
9. Jika ada keputusan penting yang belum ditentukan dan benar-benar menghalangi implementasi, ajukan satu pertanyaan singkat. Selain itu, lanjutkan tanpa meminta persetujuan untuk setiap task kecil.

FOKUS:
- Prioritaskan implementasi kode dan fungsionalitas aplikasi.
- Kerjakan roadmap secara berurutan.
- Jangan berhenti hanya untuk memberikan rekomendasi atau rencana.
- Jangan menghabiskan token untuk laporan panjang.

MULAI SEKARANG:
Identifikasi task berikutnya yang belum selesai, langsung coding, uji hasilnya, perbaiki jika gagal, kemudian lanjutkan pekerjaan sesuai roadmap.

Berikan laporan singkat setelah satu bagian pekerjaan selesai: fitur, file yang berubah, hasil tes, dan task berikutnya.

# 8 - Lakukan FINAL REVIEW terhadap seluruh implementasi CampusEvent setelah Phase 01–11 selesai.

Fokus pada:
1. Periksa apakah seluruh hasil implementasi sesuai REQUIREMENTS.md, ARCHITECTURE.md, RULES.md, dan TASKS.md.
2. Verifikasi hasil pengujian yang sudah dilaporkan. Jangan mengklaim tes berhasil jika tidak dijalankan ulang atau tidak ada bukti hasilnya.
3. Periksa keamanan, RBAC, transaksi, concurrency, idempotency, dan kemungkinan bug kritis.
4. Periksa konfigurasi environment dan pastikan tidak ada file rahasia yang akan masuk Git.
5. Periksa frontend dan backend dapat dijalankan.
6. Periksa dokumentasi dan README sesuai kondisi aplikasi aktual.

Jangan menambahkan fitur baru, mengubah arsitektur, atau melakukan refactoring besar.

Jangan commit, push, merge, atau membuat Pull Request. Git dikelola manual oleh pengguna.

Berikan laporan singkat berisi:
- Temuan kritis dan tingkat risikonya.
- Masalah yang perlu diperbaiki.
- Hasil validasi.
- Status kesiapan aplikasi.

Jika ditemukan masalah, perbaiki hanya masalah yang jelas dan tidak membutuhkan keputusan arsitektur baru. Jika tidak ada masalah kritis, nyatakan siap untuk approval pengguna.

Setelah selesai, berhenti dan tunggu instruksi.

# 9 - Lanjutkan pengembangan CampusEvent dari kondisi repository terbaru.

Konteks:
- Project: Integrated Campus Event & Ticketing Management System.
- Repository: CampusEvent.
- Branch: docs/architecture-amendment.
- Lokasi project: D:\laragon\www\campusevent.
- Phase 01–11 sudah selesai.
- Final Review sudah dilakukan.
- Pull Request menuju main sudah dibuat.
- Backend: Laravel REST API.
- Frontend: Vue.js 3 + Vite.
- Database: MySQL InnoDB.

INSTRUKSI:

1. Baca REQUIREMENTS.md, ARCHITECTURE.md, RULES.md, dan docs/TASKS.md.
2. Periksa implementasi terbaru dan identifikasi pekerjaan yang masih belum selesai, kelemahan teknis, atau peningkatan yang memang diperlukan.
3. Jangan mengulang fitur yang sudah selesai.
4. Prioritaskan peningkatan berdasarkan keamanan, integritas transaksi, stabilitas backend, konsistensi API, dan kualitas frontend.
5. Pilih satu pekerjaan dengan prioritas tertinggi yang dapat langsung diimplementasikan tanpa keputusan tambahan dari user.
6. Implementasikan sampai selesai, termasuk validasi dan penanganan error.
7. Tambahkan atau perbarui automated tests.
8. Jalankan test backend dan frontend yang relevan.
9. Perbaiki seluruh kegagalan yang disebabkan perubahan.
10. Perbarui docs/TASKS.md sesuai hasil implementasi.

BATASAN:
- Jangan mengubah arsitektur utama tanpa alasan kuat.
- Jangan menghapus fitur yang sudah berjalan.
- Jangan menjalankan migrate:fresh atau db:wipe.
- Jangan mengubah atau menghapus data database.
- Jangan melakukan commit, push, merge, atau membuat Pull Request.
- Jangan menghabiskan waktu membuat laporan analisis panjang.

Fokus utama: IMPLEMENTASI, TESTING, DAN PERBAIKAN.

Di akhir, laporkan secara singkat:
- Pekerjaan yang dipilih.
- File yang dibuat/diubah.
- Hasil testing.
- Pekerjaan berikutnya yang masih tersedia.

# 9 - Lakukan AUDIT TAHAP 0 untuk proyek CampusEvent sebelum pengembangan UI/UX dan email OTP dimulai.

Tujuan:
Memastikan kondisi repository, source code, dokumentasi, dan environment saat ini.

Tugas:
1. Periksa `git status`, branch aktif, dan 5 commit terakhir. Jangan melakukan commit, push, merge, checkout, reset, atau menghapus perubahan.
2. Identifikasi apakah ada perubahan lokal yang belum di-commit.
3. Periksa versi Laravel, PHP, Composer, Node.js, npm, Vue, dan dependency frontend.
4. Bandingkan implementasi aktual dengan:
   - REQUIREMENTS.md
   - ARCHITECTURE.md
   - RULES.md
   - TASKS.md
5. Periksa struktur autentikasi, registrasi, login, Sanctum, routing, layout, navigasi, dan CSS responsif.
6. Periksa konfigurasi email tanpa membuka atau menampilkan isi rahasia `.env`.
7. Identifikasi perbedaan antara source code aktual dan dokumentasi.
8. Pastikan apakah source lokal sudah mencakup UI refinement terakhir.

Keputusan pengembangan yang sudah disetujui:
- Redesign menggunakan warna teal.
- Tampilan mahasiswa bergaya aplikasi modern.
- Dashboard admin bergaya SaaS.
- Mendukung mobile dan desktop.
- Gmail SMTP untuk verifikasi email.
- OTP hanya untuk registrasi akun baru.
- Pengunjung boleh melihat event tanpa login.
- Pemesanan tiket wajib login.
- Akun lama tetap bisa login tanpa OTP ulang.
- Registrasi baru harus verifikasi OTP sebelum bisa login.

BATASAN:
- JANGAN mengubah file apa pun.
- JANGAN menjalankan migration.
- JANGAN mengubah database.
- JANGAN menginstal atau menghapus dependency.
- JANGAN melakukan Git operations yang mengubah repository.
- Jangan menampilkan password, API key, token, atau kredensial.

OUTPUT:
Buat laporan ringkas dengan:
A. Kondisi Git.
B. Versi environment.
C. Kondisi frontend dan backend.
D. Kesesuaian dokumentasi dan implementasi.
E. Temuan yang perlu diperbaiki.
F. Bagian yang sudah benar dan tidak perlu diubah.
G. Rekomendasi urutan pengerjaan.

Berikan kesimpulan apakah proyek siap masuk Tahap 1. Jangan mulai implementasi sebelum mendapat persetujuan.

# 10 - TAHAP 1 — SECURITY HARDENING & EMAIL OTP

Kerjakan berdasarkan hasil audit read-only sebelumnya.

Urutan:

1. Perbaiki kebocoran email petugas pada endpoint event publik. Pastikan data sensitif hanya tersedia untuk role yang berwenang.
2. Pindahkan kredensial database hardcoded dari backend/phpunit.xml ke konfigurasi environment lokal/CI. Jangan menampilkan atau mengubah kredensial produksi.
3. Implementasikan email OTP enam digit menggunakan Laravel Mail dan Gmail SMTP.
4. Simpan hash OTP, masa berlaku, batas percobaan, dan resend cooldown.
5. Registrasi akun baru tidak boleh langsung login.
6. Akun baru harus memverifikasi email sebelum bisa login.
7. Akun lama tetap bisa login tanpa OTP ulang. Siapkan strategi backfill email_verified_at yang aman.
8. Buat endpoint register, verify OTP, dan resend OTP sesuai pola API yang sudah ada.
9. Tangani kegagalan pengiriman email tanpa meninggalkan akun dalam kondisi tidak konsisten.
10. Tambahkan pengujian untuk registrasi, OTP valid/salah/kedaluwarsa, resend, rate limit, login akun belum terverifikasi, dan akun lama.

ATURAN:
- Pertahankan Sanctum cookie-based SPA.
- Jangan mengubah sistem transaksi tiket, pembayaran, kuota, QR, dan check-in.
- Jangan menjalankan migrate:fresh atau db:wipe.
- Jangan menghapus data database.
- Jangan mengubah file .env atau menampilkan secret tanpa izin.
- Jangan melakukan commit, push, merge, atau reset.
- Pertahankan perubahan lokal docs/prompt.md.
- Perbarui dokumentasi yang memang terdampak.

Setelah selesai, laporkan file yang berubah, migration baru, hasil pengujian, dan risiko yang masih tersisa. Jangan mulai redesign UI sebelum Tahap 1 diperiksa.

# 11 - Saya ingin melanjutkan pengerjaan CampusEvent yang sebelumnya dikerjakan menggunakan OpenCode di terminal VS Code.

Silakan periksa kondisi source code terbaru secara langsung. Jangan mengulang pekerjaan dari awal.

Lakukan audit progres:
1. Periksa git status dan seluruh perubahan lokal.
2. Identifikasi implementasi Tahap 1 yang sudah selesai dan belum selesai.
3. Periksa file yang telah diubah, migration, konfigurasi, dan test.
4. Jangan menimpa pekerjaan yang sudah ada.
5. Lanjutkan hanya pekerjaan yang belum selesai sesuai rencana Tahap 1 Security Hardening & Email OTP.

Jangan commit, push, reset, menghapus perubahan, atau menjalankan migrate:fresh/db:wipe.

Laporkan kondisi aktual sebelum melanjutkan implementasi.

# 11 - Lanjutkan review Tahap 1 CampusEvent berdasarkan implementasi yang sudah ada.

JANGAN mengulang implementasi OTP dari awal.

Prioritas:
1. Audit mekanisme resend OTP ketika Gmail SMTP gagal. Pastikan cooldown dan rate limit tetap berlaku meskipun pengiriman gagal.
2. Audit konsistensi penyimpanan OTP, expiry, attempt limit, dan idempotency.
3. Audit strategi backfill email_verified_at agar seluruh akun lama tetap dapat login.
4. Periksa migration baru dan statusnya. Jangan menjalankan migration tanpa memastikan dampaknya dan meminta persetujuan.
5. Jalankan test yang relevan menggunakan database test yang benar. Jangan menyentuh database development atau production.
6. Jalankan test autentikasi, registrasi, OTP, resend, event publik, dan keamanan.
7. Jalankan frontend build.
8. Periksa apakah seluruh acceptance criteria Tahap 1 terpenuhi.

PERATURAN:
- Jangan mengulang atau menimpa implementasi yang sudah ada.
- Jangan menjalankan migrate:fresh atau db:wipe.
- Jangan menghapus data.
- Jangan mengubah .env atau menampilkan secret.
- Jangan commit, push, merge, reset, atau membuang perubahan lokal.
- Pertahankan seluruh perubahan yang sudah dibuat.
- Jangan mulai redesign UI.

Jika menemukan masalah, jelaskan dan perbaiki hanya jika aman serta tidak membutuhkan perubahan database yang belum disetujui.

LAPORAN AKHIR:
- Hasil setiap pengujian.
- Temuan dan perbaikan.
- Status migration.
- Status Gmail SMTP.
- Acceptance criteria yang terpenuhi dan belum terpenuhi.
- Kesimpulan apakah Tahap 1 benar-benar siap dinyatakan selesai.

# 12 - Lakukan FINAL PRE-DEPLOYMENT REVIEW Tahap 1 CampusEvent.

Jangan implementasikan fitur baru.

1. Review kedua migration OTP secara mendalam, terutama keamanan backfill email_verified_at untuk akun lama.
2. Periksa apakah migration aman diterapkan pada database yang sudah berisi pengguna, organisasi, event, dan transaksi.
3. Periksa kemungkinan dampak terhadap akun demo, multi-role, dan administrator.
4. Periksa konfigurasi Gmail SMTP dan dokumentasi setup tanpa membuka atau menampilkan secret.
5. Buat panduan konfigurasi Gmail App Password melalui .env.
6. Pastikan pengiriman OTP menggunakan konfigurasi yang sesuai dan tidak mencatat OTP plaintext.
7. Periksa ulang seluruh perubahan Git dan pastikan tidak ada pekerjaan pengguna yang hilang.

BATASAN:
- Jangan jalankan migration.
- Jangan mengubah database.
- Jangan mengubah .env.
- Jangan commit, push, merge, reset, atau menghapus perubahan.
- Jangan mulai redesign UI.

Berikan laporan akhir berisi:
A. Risiko migration.
B. Strategi penerapan aman.
C. Panduan Gmail SMTP.
D. File yang perlu diperbaiki jika ada.
E. Apakah Tahap 1 siap diterapkan ke development.

Jangan menyatakan deployment siap jika masih ada risiko yang belum diselesaikan.

# 13 - Lakukan FINAL PRE-DEPLOYMENT REVIEW Tahap 1 CampusEvent.

Jangan implementasikan fitur baru.

1. Review kedua migration OTP secara mendalam, terutama keamanan backfill email_verified_at untuk akun lama.
2. Periksa apakah migration aman diterapkan pada database yang sudah berisi pengguna, organisasi, event, dan transaksi.
3. Periksa kemungkinan dampak terhadap akun demo, multi-role, dan administrator.
4. Periksa konfigurasi Gmail SMTP dan dokumentasi setup tanpa membuka atau menampilkan secret.
5. Buat panduan konfigurasi Gmail App Password melalui .env.
6. Pastikan pengiriman OTP menggunakan konfigurasi yang sesuai dan tidak mencatat OTP plaintext.
7. Periksa ulang seluruh perubahan Git dan pastikan tidak ada pekerjaan pengguna yang hilang.

BATASAN:
- Jangan jalankan migration.
- Jangan mengubah database.
- Jangan mengubah .env.
- Jangan commit, push, merge, reset, atau menghapus perubahan.
- Jangan mulai redesign UI.

Berikan laporan akhir berisi:
A. Risiko migration.
B. Strategi penerapan aman.
C. Panduan Gmail SMTP.
D. File yang perlu diperbaiki jika ada.
E. Apakah Tahap 1 siap diterapkan ke development.

Jangan menyatakan deployment siap jika masih ada risiko yang belum diselesaikan.

# 14 - Lakukan pengujian Gmail SMTP CampusEvent.

Kondisi:
- Gmail pengirim sudah dikonfigurasi di backend/.env.
- Implementasi EmailVerificationService dan EmailVerificationCode sudah tersedia.
- Jangan mengulang implementasi OTP.

Tugas:
1. Periksa konfigurasi mail yang dibaca Laravel tanpa menampilkan MAIL_PASSWORD.
2. Pastikan MAIL_MAILER=smtp, host smtp.gmail.com, port 587, dan alamat pengirim sesuai konfigurasi.
3. Buat pengujian pengiriman email nyata menggunakan Mailable yang sudah tersedia.
4. Kirim satu email tes ke alamat Gmail milik developer yang sudah dikonfirmasi secara lokal. Jangan menebak alamat penerima.
5. Jangan menampilkan App Password atau OTP dalam terminal, log, maupun laporan.
6. Jangan membuat akun pengguna atau mengubah database.
7. Jangan menjalankan migration.
8. Jangan commit atau push.

Jika alamat penerima belum tersedia, tanyakan sebelum mengirim.

Laporkan apakah koneksi dan pengiriman SMTP berhasil.

# 15 - Lakukan investigasi masalah registrasi akun baru CampusEvent.

Masalah:

Pengiriman email OTP melalui Gmail SMTP sudah berhasil diuji secara manual menggunakan Mailable App\Mail\EmailVerificationCode.

Namun, saat pengguna mengisi formulir registrasi dan menekan tombol Daftar, frontend menampilkan pesan: "Tidak dapat membuat akun. Periksa koneksi ke server."

Registrasi seharusnya membuat akun pending/unverified, mengirim OTP, lalu mengarahkan pengguna ke halaman verifikasi OTP.

Tugas investigasi:

Periksa alur registrasi frontend hingga backend.

Periksa endpoint dan route registrasi yang dipanggil.

Periksa respons HTTP dari request registrasi dan error di browser Network/Console jika tersedia.

Periksa backend/storage/logs/laravel.log untuk error terbaru.

Periksa validasi request, database, konfigurasi autentikasi, dan pemanggilan EmailVerificationService.

Tentukan penyebab utama kegagalan berdasarkan bukti, bukan asumsi.

Jelaskan apakah kegagalan terjadi sebelum atau sesudah proses pengiriman OTP.

Batasan:

Jangan mengubah kode atau konfigurasi.

Jangan menjalankan migration.

Jangan mengubah atau menghapus data database.

Jangan menampilkan MAIL_PASSWORD, OTP, atau informasi rahasia.

Jangan commit atau push.

Laporkan: penyebab utama, file dan baris terkait, bukti error, serta rekomendasi perbaikan.

# 16 - Tinjau kedua migration OTP yang masih Pending pada database `campusevent`.

Fokus:
1. Periksa dampak migration backfill `email_verified_at` terhadap seluruh akun yang sudah ada, khususnya akun yang belum terverifikasi.
2. Periksa apakah migration aman dijalankan pada database development yang sudah berisi data.
3. Periksa kemungkinan akun pending yang seharusnya belum terverifikasi justru ditandai verified.
4. Susun urutan penerapan migration yang aman.
5. Jika diperlukan perubahan migration, tampilkan usulan perubahan terlebih dahulu.

Batasan:
- Jangan menjalankan migration.
- Jangan mengubah database atau data.
- Jangan mengubah kode.
- Jangan menghapus akun.
- Jangan commit atau push.

Berikan rekomendasi dan tunggu persetujuan sebelum melakukan tindakan.

# 17 - KOREKSI KEPUTUSAN PRODUK CAMPUS EVENT:

Kita hanya membutuhkan DUA jenis akun:

1. **Mahasiswa**
   - Registrasi mandiri melalui halaman registrasi.
   - Role STUDENT.
   - Wajib verifikasi email OTP sebelum dapat login.

2. **Admin Pengelola CampusEvent**
   - Dibuat khusus melalui seeder yang idempotent.
   - Role SUPER_ADMIN.
   - Email langsung verified.
   - Tidak dibuat melalui registrasi publik.

Ketentuan:
- Gunakan satu halaman login untuk kedua jenis akun.
- Setelah login, arahkan mahasiswa ke dashboard mahasiswa dan admin ke dashboard pengelola.
- Tidak ada akun demo dengan dua role.
- Tidak ada registrasi publik untuk role administrator.
- Hapus seluruh 5 akun lama pada database development setelah backup dan pemeriksaan foreign key.
- Pertahankan organisasi, event, dan data non-akun yang tidak perlu dihapus.
- Jangan gunakan migrate:fresh atau db:wipe.
- Perbaiki strategi migration backfill agar tidak otomatis memverifikasi mahasiswa yang belum melakukan OTP.
- Pastikan tabel OTP tersedia sebelum registrasi publik diaktifkan.

Jangan commit atau push. Laporkan rencana dan perubahan, serta hentikan penghapusan jika berisiko menghapus data penting.

# 18 - Sebelum melakukan perubahan, buat backup database `campusevent` dan pastikan backup dapat diverifikasi.

Klasifikasikan seluruh 5 akun lama berdasarkan ID dan role. Hapus akun beserta data yang memang terkait setelah memastikan foreign key dan dampaknya. Pertahankan organisasi, event, serta data non-akun yang tidak perlu dihapus.

Buat satu akun SUPER_ADMIN khusus melalui seeder yang idempotent. Kredensial harus dikonfigurasi secara aman melalui environment lokal, bukan ditulis sebagai password plaintext di repository.

Mahasiswa tetap mendaftar sendiri dan wajib verifikasi OTP.

PENTING:
- Tahap ini hanya untuk pemeriksaan dan persiapan rencana.
- Jangan menghapus akun.
- Jangan menjalankan migration.
- Jangan mengubah database atau kode.
- Laporkan rencana backup, klasifikasi akun, strategi penghapusan, dan pembuatan akun admin.
- Tunggu persetujuan eksplisit sebelum menerapkan perubahan.

# 19 - Saya menyetujui penghapusan seluruh 5 akun lama pada database development `campusevent`, termasuk registrasi CONFIRMED dan tiket milik akun ID 8.

Silakan lanjutkan implementasi dengan urutan berikut:

1. Pastikan backup yang sudah dibuat disalin ke lokasi permanen, misalnya `D:\Backup-CampusEvent`, lalu verifikasi ukuran, hash, dan kelengkapan SQL. Jika backup tidak dapat diverifikasi, HENTIKAN proses.
2. Hapus kelima akun lama beserta relasi dan data turunannya yang memang terkait akun tersebut.
3. Pertahankan organisasi, event, dan data non-akun lainnya.
4. Ubah `DatabaseSeeder` agar membuat satu akun SUPER_ADMIN yang idempotent dan langsung terverifikasi. Kredensial wajib berasal dari environment lokal, bukan plaintext di repository.
5. Pastikan registrasi publik hanya membuat role STUDENT.
6. Ubah migration backfill agar tidak otomatis memverifikasi mahasiswa.
7. Pastikan migration tabel OTP tersedia dan urutan penerapannya aman.
8. Pastikan satu halaman login mengarahkan SUPER_ADMIN ke dashboard pengelola dan STUDENT ke dashboard mahasiswa.
9. Jalankan pengujian backend dan frontend yang relevan.

BATASAN:
- Jangan gunakan migrate:fresh, db:wipe, atau menghapus database.
- Jangan menghapus organisasi maupun event.
- Jangan mengubah business logic transaksi dan concurrency di luar kebutuhan.
- Jangan menampilkan password, OTP, atau secret.
- Jangan commit atau push.
- Jangan menjalankan migration sebelum backup terverifikasi dan dampaknya diperiksa.
- Jika ada kegagalan atau risiko penghapusan data di luar cakupan, hentikan proses dan laporkan.

Setelah selesai, laporkan jumlah akun tersisa, role akun admin baru, data organisasi/event yang dipertahankan, status migration, hasil pengujian, serta file yang berubah.

# 20 - 