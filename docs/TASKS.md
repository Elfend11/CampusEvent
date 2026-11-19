# TASKS.md
# CAMPUS EVENT — DEVELOPMENT ROADMAP & AI EXECUTION PLAN

**Project:** CampusEvent  
**Project Name:** Integrated Campus Event & Ticketing Management System  
**Document:** Development Roadmap & AI Execution Plan  
**Version:** 1.1  
**Status:** Approved (Amendment 1)  
**Development Strategy:** Sequential Task Execution  
**Primary Objective:** Membangun sistem manajemen event dan tiket kampus secara bertahap, terstruktur, teruji, dan sesuai dengan seluruh dokumentasi proyek.

---

# 1. DEVELOPMENT PRINCIPLES

Pengembangan CampusEvent wajib mengikuti prinsip berikut:

1. Pengerjaan dilakukan secara bertahap dan berurutan.
2. Setiap tugas harus diselesaikan dan diuji sebelum melanjutkan ke tugas berikutnya.
3. AI wajib membaca dokumentasi proyek sebelum melakukan implementasi.
4. AI tidak boleh mengubah arsitektur atau aturan bisnis tanpa persetujuan pengembang.
5. Setiap fitur harus memiliki hasil implementasi dan pengujian yang dapat diverifikasi.
6. AI wajib mencoba memperbaiki error yang ditemukan.
7. Setiap perubahan kode harus menggunakan Git dengan branch yang sesuai.
8. Dokumentasi wajib disinkronkan dengan implementasi.
9. Setiap tugas harus disertai laporan progres lengkap.
10. AI harus berhenti dan menunggu persetujuan setelah menyelesaikan satu tugas.
11. Penyelesaian proyek ditentukan berdasarkan seluruh kriteria penerimaan, bukan sekadar fitur yang sudah tampil atau dapat digunakan.

## 1.1 Source of Truth

Sebelum memulai pekerjaan, AI wajib membaca:

- `REQUIREMENTS.md`
- `ARCHITECTURE.md`
- `RULES.md`
- `TASKS.md`

Fungsi masing-masing dokumen:

| Dokumen | Fungsi |
|---|---|
| REQUIREMENTS.md | Menentukan kebutuhan dan perilaku sistem |
| ARCHITECTURE.md | Menentukan arsitektur dan teknologi |
| RULES.md | Menentukan standar engineering |
| TASKS.md | Menentukan urutan pengerjaan dan target penyelesaian |

Jika ditemukan konflik, kekurangan, atau ketidaksesuaian, AI wajib melaporkannya dan meminta persetujuan sebelum mengubah keputusan yang telah ditetapkan.

---

# 2. AI EXECUTION WORKFLOW

AI harus menggunakan pola pengerjaan berikut untuk setiap tugas.

## 2.1 Task Execution Cycle

**Step 1 — Context Review**

- Membaca dokumentasi yang relevan.
- Memeriksa struktur repository.
- Memeriksa kode dan fitur yang sudah tersedia.
- Memastikan tidak ada perubahan sebelumnya yang terabaikan.

**Step 2 — Task Implementation**

- Mengerjakan hanya tugas yang sedang ditetapkan.
- Mengikuti arsitektur dan coding rules.
- Tidak menambahkan fitur di luar ruang lingkup.
- Tidak mengubah keputusan teknis tanpa persetujuan.

**Step 3 — Testing**

- Menjalankan pengujian yang relevan.
- Memeriksa hasil implementasi.
- Menguji kondisi normal dan kondisi error.
- Memastikan fitur sebelumnya tidak rusak.

**Step 4 — Error Resolution**

Jika ditemukan error:

1. Analisis penyebab masalah.
2. Tentukan bagian kode yang bermasalah.
3. Perbaiki sesuai dokumentasi.
4. Jalankan pengujian ulang.
5. Catat error yang belum terselesaikan.

Jika perbaikan membutuhkan perubahan arsitektur, skema database yang disepakati, atau aturan bisnis, AI harus meminta persetujuan terlebih dahulu.

**Step 5 — Git Management**

- Gunakan branch sesuai fitur atau tahap.
- Periksa perubahan sebelum commit.
- Buat commit setelah tugas selesai dan pengujian relevan dijalankan.
- Jangan mengubah branch `main` secara langsung.
- Penggabungan ke `main` menunggu persetujuan pengguna.

**Step 6 — Documentation Synchronization**

- Periksa apakah implementasi memengaruhi dokumentasi.
- Perbarui dokumen terkait apabila perubahan telah disetujui.
- Catat perubahan dan alasannya.
- Jangan mengubah keputusan proyek secara sepihak.

**Step 7 — Progress Report**

AI wajib menyampaikan laporan lengkap setelah tugas selesai.

**Step 8 — Approval Gate**

AI wajib berhenti dan menunggu persetujuan pengguna sebelum memulai tugas berikutnya.

## 2.2 Progress Report Format

Setiap laporan progres harus menggunakan format berikut:

### Task Report

**Task:** Nama tugas yang diselesaikan.

**Status:** Completed / Failed / Blocked.

**Implementation Summary:**
- Ringkasan pekerjaan.
- Fitur atau komponen yang dibuat.
- Penjelasan implementasi.

**Files Created:**
- Daftar file baru beserta lokasinya.

**Files Modified:**
- Daftar file yang diubah beserta alasannya.

**Testing Results:**
- Pengujian yang dijalankan.
- Hasil berhasil.
- Hasil gagal.
- Pengujian yang belum dilakukan.

**Error & Resolution:**
- Error yang ditemukan.
- Penyebab.
- Solusi yang diterapkan.
- Kendala yang masih tersisa.

**Git Information:**
- Branch.
- Commit.
- Ringkasan perubahan.

**Documentation Updates:**
- Dokumen yang diperbarui.
- Alasan pembaruan.

**Next Task:**
- Tugas berikutnya yang direncanakan.

**Approval Required:**
AI harus meminta persetujuan pengguna sebelum melanjutkan.

---

# 3. DEVELOPMENT ROADMAP

Pengembangan CampusEvent dibagi menjadi 11 tahap utama.

## PHASE 01 — PROJECT PREPARATION & ENVIRONMENT SETUP

**Objective:** Menyiapkan lingkungan pengembangan dan fondasi repository CampusEvent.

### Tasks

**Task 1.1 — Documentation Review**
- Membaca seluruh dokumen proyek.
- Memahami kebutuhan, arsitektur, dan aturan pengembangan.
- Mengidentifikasi ketergantungan antarfitur.
- Memeriksa konsistensi dokumentasi.

**Task 1.2 — Repository Inspection**
- Memeriksa struktur repository.
- Mengidentifikasi file dan folder yang sudah tersedia.
- Memeriksa branch dan riwayat Git.
- Menghindari penghapusan file yang masih digunakan.

**Task 1.3 — Environment Setup**
- Memastikan PHP 8.3.x dan Composer tersedia sesuai kebutuhan Laravel.
- Memverifikasi advisory keamanan Composer dan menentukan versi stabil yang digunakan sebelum instalasi dependency.
- Memastikan Node.js dan npm tersedia.
- Menyiapkan Laravel dan Vue.js 3 dengan Vite.
- Menyiapkan konfigurasi lingkungan pengembangan.
- Menyiapkan Axios, Pinia, dan Laravel Sanctum untuk Cookie-Based SPA Authentication.
- Memastikan proyek berada pada folder kerja `C:\laragon\www\campusevent`.

**Task 1.4 — Database Connection**
- Menyiapkan database MySQL.
- Mengatur konfigurasi koneksi melalui environment.
- Memastikan Laravel dapat terhubung ke database.
- Memastikan storage engine menggunakan InnoDB.

**Task 1.5 — Initial Application Setup**
- Menyiapkan struktur frontend dan backend.
- Memastikan aplikasi dapat dijalankan.
- Memastikan frontend dapat berkomunikasi dengan backend.

### Expected Deliverables

- Environment pengembangan siap digunakan.
- Repository terstruktur.
- Laravel berjalan.
- Vue.js dan Vite berjalan.
- Koneksi MySQL berhasil.
- Dokumentasi konfigurasi dasar tersedia.

### Acceptance Criteria

- [ ] Aplikasi dapat dijalankan.
- [ ] Backend berhasil dijalankan.
- [ ] Frontend berhasil dijalankan.
- [ ] Koneksi database berhasil.
- [ ] Tidak ada konfigurasi rahasia yang dimasukkan ke repository.
- [ ] Struktur proyek sesuai ARCHITECTURE.md.

---

## PHASE 02 — DATABASE DESIGN, MIGRATION & SEEDER

**Objective:** Membangun struktur database yang sesuai dengan ERD dan kebutuhan CampusEvent.

### Tasks

**Task 2.1 — Database Structure Review**
- Memeriksa seluruh entitas pada ARCHITECTURE.md.
- Memeriksa atribut dan relasi setiap entitas.
- Mengidentifikasi primary key dan foreign key.
- Memastikan aturan unik dan integritas data.

**Task 2.2 — Migration Development**

Membuat migration secara bertahap untuk:

1. `users`
2. `user_roles`
3. `organizations`
4. `organization_members`
5. `events`
6. `event_officers`
7. `registrations`
8. `payments`
9. `tickets`
10. `attendances`

Migration harus mengikuti urutan ketergantungan relasi.

**Task 2.3 — Model Development**

Membuat model Eloquent untuk setiap entitas.

Setiap model harus memiliki:

- Relasi yang sesuai.
- Fillable atau guarded attributes.
- Cast untuk tipe data yang diperlukan.
- Pengaturan sesuai kebutuhan bisnis.

**Task 2.4 — Database Constraints**

Menerapkan:

- Primary Key.
- Foreign Key.
- Unique Constraint.
- Index.
- Nullable dan default value sesuai kebutuhan.

Constraint wajib:

- `user_roles`: UNIQUE(user_id, role).
- `event_officers`: UNIQUE(event_id, user_id) dan INDEX(user_id).
- `events`: `location` dan `category` wajib terisi; INDEX(status, category).
- `registrations`: `expires_at` nullable; INDEX(status, expires_at).
- `payments`: UNIQUE(registration_id).
- `tickets`: UNIQUE(registration_id), UNIQUE(ticket_code), UNIQUE(token_hash).
- `attendances`: UNIQUE(ticket_id).

**Task 2.5 — Factory and Seeder**

Membuat Factory untuk kebutuhan data pengujian.

Membuat Seeder untuk:

- Role pengguna pada `user_roles` (satu akun dapat memiliki beberapa role).
- Akun pengujian.
- Organisasi contoh.
- Penugasan petugas check-in contoh pada `event_officers`.
- Data event contoh apabila diperlukan.

Data seeder harus bersifat aman dan tidak menggunakan kredensial produksi.

**Task 2.6 — Database Validation**

- Menjalankan migration.
- Menjalankan seeder.
- Memeriksa relasi.
- Memeriksa constraint.
- Memastikan migration dapat dijalankan kembali melalui proses pengujian yang sesuai.

### Expected Deliverables

- Migration lengkap.
- Model dan relasi Eloquent.
- Factory.
- Seeder.
- Database MySQL siap digunakan.

### Acceptance Criteria

- [ ] Semua tabel sesuai rancangan.
- [ ] Foreign Key berfungsi.
- [ ] Unique Constraint berfungsi.
- [ ] Index tersedia sesuai kebutuhan.
- [ ] Relasi Eloquent berjalan.
- [ ] Seeder berhasil dijalankan.
- [ ] Tidak ada struktur database yang bertentangan dengan ARCHITECTURE.md.

---

## PHASE 03 — AUTHENTICATION & ROLE-BASED ACCESS CONTROL

**Objective:** Membangun autentikasi dan pembatasan hak akses pengguna.

### Tasks

**Task 3.1 — Authentication**

Mengimplementasikan:

- Login.
- Logout.
- Pengelolaan autentikasi.
- Pengelolaan identitas pengguna.
- Validasi kredensial.

**Task 3.2 — User Management**

- Pengelolaan data pengguna.
- Pengelolaan profil.
- Pengaturan role.
- Validasi data pengguna.

**Task 3.3 — RBAC Implementation**

Menerapkan empat aktor utama:

1. Super Admin.
2. Admin Organisasi.
3. Petugas Check-in.
4. Mahasiswa.

Satu akun dapat memiliki lebih dari satu role. Hak akses mahasiswa tetap tersedia meskipun akun memiliki role pengelola.

**Task 3.4 — Authorization**

- Membuat middleware atau mekanisme authorization.
- Membatasi akses endpoint berdasarkan role.
- Memastikan admin organisasi hanya mengakses organisasi yang tercatat pada `organization_members`.
- Membatasi petugas check-in pada event yang tercatat pada `event_officers`.
- Memastikan mahasiswa tidak dapat mengakses fungsi administrasi.
- Memastikan penentuan role tidak bergantung pada data yang dikirim frontend.

**Task 3.5 — Frontend Authentication**

- Membuat halaman login.
- Mengelola authentication state menggunakan Pinia.
- Mengatur proteksi halaman.
- Mengatur komunikasi autentikasi melalui Axios menggunakan cookie-based session dan CSRF token.
- Tidak menyimpan token autentikasi di localStorage.
- Menangani kondisi session atau token tidak valid.

### Expected Deliverables

- Authentication API.
- User management.
- RBAC.
- Login interface.
- Proteksi endpoint dan halaman.

### Acceptance Criteria

- [ ] Login berfungsi.
- [ ] Logout berfungsi.
- [ ] Password menggunakan hashing.
- [ ] Setiap role memiliki hak akses yang sesuai.
- [ ] Endpoint terlindungi.
- [ ] Pengguna tidak dapat mengakses resource tanpa izin.
- [ ] Pengujian autentikasi dan authorization berhasil.

---

## PHASE 04 — ORGANIZATION MANAGEMENT

**Objective:** Membangun pengelolaan organisasi kampus.

### Tasks

**Task 4.1 — Organization API**

- Membuat organisasi.
- Melihat daftar organisasi.
- Melihat detail organisasi.
- Memperbarui organisasi.
- Mengelola status organisasi.

**Task 4.2 — Organization Membership**

- Menghubungkan pengguna dengan organisasi.
- Mengelola anggota organisasi.
- Mengatur role anggota sesuai kebutuhan.
- Memeriksa hak akses pengelolaan organisasi.

**Task 4.3 — Frontend Organization Management**

Membuat halaman:

- Daftar organisasi.
- Detail organisasi.
- Form tambah organisasi.
- Form edit organisasi.
- Pengelolaan anggota.

**Task 4.4 — Organization Authorization Testing**

- Menguji akses Super Admin.
- Menguji akses Admin Organisasi.
- Menguji akses pengguna dari organisasi lain.
- Memastikan data organisasi tidak dapat dimodifikasi tanpa izin.

### Expected Deliverables

- Organization API.
- Organization management interface.
- Organization membership management.
- Authorization organisasi.

### Acceptance Criteria

- [ ] Organisasi dapat dibuat.
- [ ] Data organisasi dapat diperbarui.
- [ ] Anggota dapat dikelola.
- [ ] Pembatasan akses organisasi berjalan.
- [ ] Pengujian CRUD dan authorization berhasil.

---

## PHASE 05 — EVENT MANAGEMENT

**Objective:** Membangun fitur pembuatan dan pengelolaan event kampus.

### Tasks

**Task 5.1 — Event API**

- Membuat event.
- Melihat daftar event.
- Melihat detail event.
- Memperbarui event.
- Mengubah status event.
- Mengelola informasi event.

**Task 5.2 — Event Attributes**

Mengelola informasi:

- Nama event.
- Deskripsi.
- Penyelenggara.
- Lokasi.
- Tanggal dan waktu.
- Kategori event.
- Kapasitas.
- Harga tiket.
- Status publikasi.

**Task 5.3 — Event Business Rules**

- Memvalidasi data event.
- Memvalidasi kapasitas.
- Mengatur status publikasi.
- Memastikan hanya pihak berwenang yang mengelola event.
- Mencegah perubahan yang melanggar aturan transaksi dan tiket yang sudah berjalan.

**Task 5.4 — Frontend Event Management**

Membuat:

- Halaman daftar event.
- Halaman detail event.
- Form tambah event.
- Form edit event.
- Halaman pengelolaan event.
- Fitur pencarian dan filter.

**Task 5.5 — Event Testing**

Menguji CRUD, validasi, status event, dan authorization.

### Expected Deliverables

- Event API.
- Event management interface.
- Event listing.
- Event detail.
- Validasi aturan bisnis event.

### Acceptance Criteria

- [ ] Event dapat dibuat.
- [ ] Event dapat diperbarui.
- [ ] Event dapat ditampilkan.
- [ ] Data event tervalidasi.
- [ ] Hak akses penyelenggara berfungsi.
- [ ] Pengujian event berhasil.

---

## PHASE 06 — REGISTRATION & TICKET RESERVATION

**Objective:** Membangun sistem registrasi mahasiswa dan reservasi tiket dengan pengendalian kuota.

Tahap ini merupakan fondasi awal untuk menyelesaikan The 30% Hard Problem.

### Tasks

**Task 6.1 — Registration API**

- Membuat endpoint registrasi.
- Mengelola data registrasi.
- Memeriksa identitas mahasiswa.
- Memeriksa status event.
- Memeriksa aturan satu registrasi aktif per mahasiswa untuk event yang sama.
- Menyediakan pembatalan administratif sesuai aturan RULES §11.6.

**Task 6.2 — Ticket Inventory**

Mengimplementasikan tiga atribut inventori:

- `capacity`
- `reserved_count`
- `sold_count`

Menggunakan rumus:

`available = capacity - reserved_count - sold_count`

Menjaga invariant:

`reserved_count + sold_count <= capacity`

**Task 6.3 — Reservation Service**

Mengimplementasikan reservasi menggunakan:

- Database transaction.
- Row-level locking.
- `SELECT FOR UPDATE`.
- Pemeriksaan kuota terbaru setelah lock diperoleh.
- Pembuatan atau pembaruan registrasi PENDING.
- Penambahan `reserved_count`.
- Batas waktu reservasi 10 menit yang disimpan pada `registrations.expires_at`.
- Alur event gratis dalam transaksi yang sama: status `CONFIRMED`, `sold_count` bertambah, `reserved_count` tidak berubah, tiket `ACTIVE` langsung dibuat, dan tidak ada record pembayaran.

**Task 6.4 — Reservation Expiration Foundation**

Menyiapkan mekanisme kedaluwarsa reservasi.

- Menyimpan waktu kedaluwarsa pada `registrations.expires_at`.
- Memeriksa status registrasi.
- Menyiapkan mekanisme pelepasan kuota.
- Mencegah pelepasan kuota berulang.
- Menggunakan index `(status, expires_at)` untuk pemeriksaan reservasi kedaluwarsa.

**Task 6.5 — Frontend Registration**

Membuat:

- Form registrasi.
- Informasi ketersediaan tiket.
- Status registrasi.
- Informasi waktu kedaluwarsa.
- Riwayat registrasi.

**Task 6.6 — Registration Testing**

Menguji:

- Registrasi berhasil.
- Kuota habis.
- Registrasi ganda.
- Event tidak tersedia.
- Reservasi kedaluwarsa.
- Kegagalan transaksi.
- Permintaan bersamaan pada kuota terbatas.
- Pembatalan administratif tidak melepaskan kuota lebih dari sekali.

### Expected Deliverables

- Registration API.
- Registration Service.
- Ticket inventory management.
- Reservation mechanism.
- Reservation expiration foundation.
- Registration interface.

### Acceptance Criteria

- [ ] Mahasiswa dapat melakukan registrasi.
- [ ] Kuota diperiksa di backend.
- [ ] Reservasi menggunakan transaction dan locking.
- [ ] Kuota tidak melebihi kapasitas.
- [ ] Kuota tidak bernilai negatif.
- [ ] Reservasi memiliki batas waktu 10 menit.
- [ ] Registrasi ganda ditangani sesuai aturan.
- [ ] Pengujian fitur berhasil.

---

## PHASE 07 — SIMULATED PAYMENT

**Objective:** Membangun mekanisme pembayaran simulasi untuk tiket event.

Pembayaran pada CampusEvent tidak menggunakan payment gateway nyata.

### Tasks

**Task 7.1 — Payment API**

- Membuat endpoint pembayaran.
- Mengelola data pembayaran.
- Menghubungkan pembayaran dengan registrasi.
- Mengelola status pembayaran.

**Task 7.2 — Payment Simulation**

Mengimplementasikan simulasi proses pembayaran.

- Menampilkan informasi pembayaran.
- Menyediakan tindakan konfirmasi pembayaran simulasi.
- Memproses status pembayaran.
- Menangani pembayaran yang gagal atau kedaluwarsa.

**Task 7.3 — Payment Confirmation Service**

Menggunakan database transaction untuk:

1. Mengunci data yang diperlukan.
2. Memeriksa status registrasi.
3. Memeriksa waktu kedaluwarsa.
4. Memastikan pembayaran belum diproses.
5. Memperbarui status registrasi.
6. Mengurangi `reserved_count`.
7. Menambah `sold_count`.
8. Membuat tiket.
9. Melakukan commit jika seluruh proses berhasil.

**Task 7.4 — Idempotency**

- Mencegah pembayaran diproses berulang.
- Mencegah penerbitan tiket ganda.
- Memeriksa status transaksi sebelum perubahan.
- Menangani request berulang dengan aman.
- Menggunakan record pembayaran yang sama untuk retry selama reservasi aktif dan belum `PAID`.
- Menolak pembayaran setelah reservasi kedaluwarsa.
- Mengembalikan tiket yang sudah ada tanpa membuat tiket baru ketika pembayaran sudah `PAID`.

**Task 7.5 — Frontend Payment**

Membuat:

- Halaman pembayaran simulasi.
- Informasi biaya.
- Status pembayaran.
- Riwayat pembayaran.
- Informasi pembayaran berhasil atau gagal.

**Task 7.6 — Payment Testing**

Menguji:

- Pembayaran berhasil.
- Pembayaran gagal.
- Pembayaran berulang.
- Pembayaran setelah reservasi kedaluwarsa.
- Kegagalan transaksi.
- Penerbitan tiket setelah pembayaran berhasil.

### Expected Deliverables

- Payment API.
- Payment Service.
- Payment simulation interface.
- Payment status management.
- Transactional payment confirmation.

### Acceptance Criteria

- [ ] Pembayaran simulasi berjalan.
- [ ] Status pembayaran tersimpan.
- [ ] Pembayaran menggunakan transaction.
- [ ] Pembayaran kedaluwarsa ditolak.
- [ ] Tidak ada pembayaran yang menghasilkan tiket ganda.
- [ ] Kuota diperbarui secara konsisten.
- [ ] Pengujian pembayaran berhasil.

---

## PHASE 08 — E-TICKET & QR CODE

**Objective:** Membangun tiket digital dengan QR Code untuk setiap registrasi yang berhasil dibayar.

### Tasks

**Task 8.1 — Ticket Generation**

- Membuat tiket setelah pembayaran berhasil.
- Menghubungkan tiket dengan registrasi.
- Membuat identitas tiket unik.
- Memastikan satu registrasi tidak menghasilkan tiket ganda.

**Task 8.2 — QR Code Generation**

- Menghasilkan token QR yang unik dan acak.
- Menggunakan token sebagai identitas validasi tiket.
- Menyimpan hash token pada database sesuai ARCHITECTURE.md.
- Tidak menampilkan informasi sensitif pada QR Code.

**Task 8.3 — Ticket Validation API**

- Memeriksa token QR.
- Memeriksa keberadaan tiket.
- Memeriksa status tiket.
- Memeriksa kesesuaian event.
- Menolak token yang tidak valid.

**Task 8.4 — Frontend E-Ticket**

Membuat:

- Halaman tiket digital.
- Tampilan QR Code.
- Informasi event.
- Informasi pemilik tiket.
- Status tiket.
- Riwayat tiket.

**Task 8.5 — Ticket Testing**

Menguji:

- Penerbitan tiket.
- Token valid.
- Token tidak valid.
- Token yang tidak ditemukan.
- Tiket yang sudah digunakan.
- Penerbitan tiket berulang.

### Expected Deliverables

- Ticket API.
- Ticket generation service.
- QR Code generation.
- E-ticket interface.
- Ticket validation.

### Acceptance Criteria

- [ ] Tiket dibuat setelah pembayaran berhasil.
- [ ] QR Code unik.
- [ ] Token tidak disimpan sebagai plaintext.
- [ ] Tiket dapat ditampilkan.
- [ ] Validasi QR dilakukan melalui backend.
- [ ] Tidak ada tiket ganda.
- [ ] Pengujian tiket berhasil.

---

## PHASE 09 — CHECK-IN MANAGEMENT

**Objective:** Membangun proses validasi tiket dan pencatatan kehadiran peserta.

### Tasks

**Task 9.1 — Check-in API**

- Membuat endpoint check-in.
- Menerima token QR.
- Memeriksa validitas tiket.
- Memeriksa event terkait.
- Memeriksa bahwa petugas ditugaskan pada event melalui `event_officers`.
- Memeriksa status check-in.

**Task 9.2 — Check-in Service**

Mengimplementasikan:

- Validasi token QR.
- Pemeriksaan status pembayaran atau tiket.
- Pemeriksaan kesesuaian event.
- Pemeriksaan penugasan petugas pada event.
- Pencatatan kehadiran.
- Pencegahan check-in ganda.

**Task 9.3 — Transactional Check-in**

- Menggunakan transaction.
- Mengunci data tiket yang diperlukan.
- Memeriksa kembali status tiket.
- Mencatat attendance.
- Menjamin satu tiket hanya memiliki satu check-in berhasil.

**Task 9.4 — Frontend Check-in**

Membuat:

- Halaman petugas check-in.
- Input atau pemindaian QR Code.
- Informasi valid atau tidak valid.
- Informasi peserta.
- Status kehadiran.

**Task 9.5 — Check-in Testing**

Menguji:

- Tiket valid.
- Tiket tidak valid.
- Tiket dari event lain.
- Tiket yang sudah check-in.
- Dua permintaan check-in bersamaan.

### Expected Deliverables

- Check-in API.
- Check-in Service.
- Attendance management.
- Check-in interface.

### Acceptance Criteria

- [ ] QR Code dapat divalidasi.
- [ ] Tiket yang tidak valid ditolak.
- [ ] Tiket hanya dapat digunakan untuk event yang sesuai.
- [ ] Check-in tercatat.
- [ ] Check-in ganda dicegah.
- [ ] Pengujian check-in berhasil.

---

## PHASE 10 — DASHBOARD & REPORTING

**Objective:** Menyediakan dashboard dan laporan sesuai hak akses pengguna.

### Tasks

**Task 10.1 — Dashboard API**

Menyediakan data ringkasan yang relevan:

- Jumlah event.
- Jumlah organisasi.
- Jumlah registrasi.
- Jumlah tiket terjual.
- Jumlah peserta check-in.
- Statistik event.

Data yang ditampilkan harus sesuai dengan kebutuhan dan hak akses aktor.

**Task 10.2 — Super Admin Dashboard**

Menampilkan:

- Statistik keseluruhan platform.
- Data organisasi.
- Data event.
- Data pengguna.
- Laporan keseluruhan.

**Task 10.3 — Organization Dashboard**

Menampilkan:

- Statistik event milik organisasi.
- Jumlah registrasi.
- Jumlah tiket terjual.
- Jumlah peserta hadir.
- Informasi laporan event.

**Task 10.4 — Student Dashboard**

Menampilkan:

- Event yang tersedia.
- Registrasi mahasiswa.
- Tiket yang dimiliki.
- Riwayat pembayaran.
- Riwayat keikutsertaan.

**Task 10.5 — Reporting**

- Menyediakan laporan event.
- Menyediakan laporan registrasi.
- Menyediakan laporan kehadiran.
- Memastikan hasil laporan sesuai data database.
- Membatasi data laporan berdasarkan hak akses.

**Task 10.6 — Dashboard Testing**

- Menguji setiap dashboard berdasarkan role.
- Menguji kesesuaian angka statistik.
- Menguji filter dan pencarian.
- Menguji pembatasan akses laporan.

### Expected Deliverables

- Dashboard API.
- Dashboard interface.
- Reporting functionality.
- Statistik event dan peserta.

### Acceptance Criteria

- [ ] Dashboard sesuai role.
- [ ] Data statistik sesuai database.
- [ ] Laporan dapat ditampilkan.
- [ ] Data antarorganisasi tidak bocor.
- [ ] Pengujian dashboard dan laporan berhasil.

---

## PHASE 11 — INTEGRATION, THE 30% HARD PROBLEM & FINAL VALIDATION

**Objective:** Mengintegrasikan seluruh modul, menguji kondisi ekstrem, dan membuktikan bahwa CampusEvent memenuhi kriteria penerimaan.

Tahap ini menjadi tahap validasi akhir proyek.

### 11.1 End-to-End Integration

Mengintegrasikan seluruh alur:

1. Pengguna melakukan login.
2. Admin Organisasi mengelola organisasi.
3. Admin Organisasi membuat event.
4. Mahasiswa melihat event.
5. Mahasiswa melakukan registrasi.
6. Sistem melakukan reservasi tiket.
7. Mahasiswa melakukan pembayaran simulasi.
8. Sistem menerbitkan e-ticket.
9. Petugas melakukan pemindaian QR.
10. Sistem mencatat kehadiran.
11. Admin melihat laporan event.

### 11.2 The 30% Hard Problem Diagnostic

Fokus pengujian:

**A. High Concurrency Race Condition**

Sistem harus mampu menangani banyak mahasiswa yang melakukan reservasi secara bersamaan.

**B. ACID Transaction and Rollback**

Sistem harus mampu menjaga konsistensi database ketika terjadi kegagalan proses reservasi atau pembayaran.

**C. Ticket Overselling Prevention**

Sistem tidak boleh menerbitkan atau mereservasi tiket melebihi kapasitas event.

### 11.3 Critical Concurrency Test

**Test Scenario:**

| Parameter | Nilai |
|---|---:|
| Kapasitas tersisa | 5 tiket |
| Jumlah request bersamaan | 100 |
| Reservasi berhasil | 5 |
| Request ditolak | 95 |
| Overselling | 0 |

**Expected Result:**

- Tepat 5 reservasi berhasil.
- 95 request lainnya ditolak karena kuota habis.
- `reserved_count` dan `sold_count` tidak melebihi kapasitas.
- Tidak ada nilai kuota negatif.
- Tidak ada registrasi ganda yang melanggar aturan.
- Tidak ada transaksi parsial yang merusak konsistensi database.

**Testing Requirements:**

1. Gunakan MySQL InnoDB.
2. Jalankan request secara bersamaan, bukan berurutan biasa.
3. Pastikan pengujian benar-benar mencapai database.
4. Catat jumlah request berhasil dan gagal.
5. Periksa kondisi database setelah pengujian.
6. Simpan bukti pengujian.
7. Ulangi pengujian apabila ditemukan masalah setelah perbaikan.

Pengujian menggunakan SQLite atau mock database tidak dapat menggantikan bukti akhir pengujian locking MySQL InnoDB.

### 11.4 ACID and Rollback Testing

Menguji kondisi:

- Reservasi gagal di tengah proses.
- Pembayaran gagal di tengah proses.
- Penerbitan tiket gagal.
- Dua transaksi mengakses inventori yang sama.
- Pembayaran dan kedaluwarsa terjadi hampir bersamaan.
- Check-in dilakukan bersamaan untuk tiket yang sama.

Pastikan:

- Rollback berjalan.
- Tidak ada data parsial yang tidak valid.
- Kuota kembali konsisten.
- Tidak ada tiket ganda.
- Tidak ada check-in ganda.

### 11.5 Deadlock and Idempotency Testing

- Memeriksa kemungkinan deadlock.
- Memastikan urutan locking konsisten.
- Menguji request berulang.
- Memastikan proses kedaluwarsa tidak melepaskan kuota dua kali.
- Memastikan konfirmasi pembayaran tidak menghasilkan tiket ganda.
- Memastikan check-in tidak tercatat berulang.

### 11.6 Security Validation

- Menguji authentication.
- Menguji RBAC.
- Menguji proteksi endpoint.
- Menguji validasi backend.
- Menguji akses resource antarorganisasi.
- Memastikan data sensitif tidak terekspos.

### 11.7 Final Functional Testing

Memeriksa seluruh kebutuhan dalam REQUIREMENTS.md.

Melakukan pengujian:

- Unit testing.
- Integration testing.
- Feature testing.
- End-to-end testing.
- Concurrency testing.

### 11.8 UI and Application Validation

- Memeriksa seluruh halaman.
- Memeriksa navigasi.
- Memeriksa respons frontend.
- Memeriksa komunikasi API.
- Memeriksa tampilan berdasarkan role.
- Memeriksa loading, error, dan empty state.

### 11.9 Documentation and Repository Validation

Memastikan:

- REQUIREMENTS.md tersedia.
- ARCHITECTURE.md tersedia.
- RULES.md tersedia.
- TASKS.md tersedia.
- Dokumentasi API relevan tersedia.
- Repository bersih dari secret.
- Struktur kode terdokumentasi.
- Riwayat Git dapat ditelusuri.

### Expected Deliverables

- Seluruh modul terintegrasi.
- Hasil pengujian end-to-end.
- Bukti concurrency testing.
- Bukti ACID dan rollback testing.
- Hasil security testing.
- Dokumentasi final.
- Repository yang siap diperiksa dan dipresentasikan.

### Acceptance Criteria

- [ ] Seluruh fitur utama berjalan.
- [ ] Seluruh modul terintegrasi.
- [ ] Tidak ada bug kritis yang belum diselesaikan.
- [ ] Pengujian unit berhasil sesuai kriteria.
- [ ] Pengujian integrasi berhasil sesuai kriteria.
- [ ] Pengujian fitur berhasil sesuai kriteria.
- [ ] Pengujian end-to-end berhasil.
- [ ] Pengujian concurrency berhasil.
- [ ] Skenario 100 request dan 5 tiket tersisa menghasilkan tepat 5 reservasi berhasil dan 95 ditolak.
- [ ] Tidak terjadi overselling.
- [ ] Tidak terjadi kuota negatif.
- [ ] Transaction dan rollback berjalan sesuai kebutuhan.
- [ ] Tidak terjadi penerbitan tiket ganda.
- [ ] Check-in ganda dicegah.
- [ ] Dokumentasi lengkap dan sinkron.
- [ ] Repository siap diperiksa.

---

# 4. GIT & VERSION CONTROL EXECUTION

Pengelolaan Git dilakukan sepanjang proses pengembangan.

## 4.1 Branch Strategy

Gunakan branch `main` sebagai branch utama.

Setiap fitur atau pekerjaan menggunakan branch tersendiri.

Contoh:

| Task | Branch |
|---|---|
| Authentication | `feature/authentication` |
| Database | `feature/database-setup` |
| Organization | `feature/organization-management` |
| Event | `feature/event-management` |
| Registration | `feature/ticket-registration` |
| Payment | `feature/payment-simulation` |
| E-Ticket | `feature/qr-ticket` |
| Check-in | `feature/check-in` |
| Dashboard | `feature/dashboard-reporting` |
| Bug Fix | `fix/issue-description` |

## 4.2 Commit Strategy

Gunakan format pesan commit yang konsisten.

Contoh:

- `feat: add authentication`
- `feat: implement event management`
- `fix: prevent ticket overselling`
- `test: add concurrency testing`
- `refactor: improve registration service`
- `docs: update development roadmap`

## 4.3 Git Restrictions

1. AI tidak boleh mengerjakan fitur langsung pada `main`.
2. AI tidak boleh melakukan force push tanpa persetujuan.
3. AI tidak boleh menghapus branch bersama tanpa persetujuan.
4. AI wajib memeriksa perubahan sebelum commit.
5. AI wajib melaporkan branch dan commit.
6. Merge ke `main` membutuhkan persetujuan pengguna.
7. Jangan memasukkan `.env`, password, atau secret ke repository.

---

# 5. DOCUMENTATION SYNCHRONIZATION

Dokumentasi harus selalu sesuai dengan kondisi implementasi.

## 5.1 Documentation Update Rules

Apabila terjadi perubahan yang telah disetujui:

- Perubahan kebutuhan harus tercermin pada REQUIREMENTS.md.
- Perubahan arsitektur harus tercermin pada ARCHITECTURE.md.
- Perubahan standar engineering harus tercermin pada RULES.md.
- Perubahan urutan atau status tugas harus tercermin pada TASKS.md.

## 5.2 Documentation Change Log

Setiap perubahan dokumentasi harus mencatat:

- Dokumen yang diubah.
- Bagian yang diubah.
- Alasan perubahan.
- Persetujuan yang menjadi dasar perubahan.

AI tidak boleh mengubah kebutuhan atau arsitektur secara sepihak.

## 5.3 Amendment Log

| Versi | Bagian | Perubahan | Dasar |
|---|---|---|---|
| 1.1 | Task 1.3 | PHP 8.3.x, Sanctum, verifikasi advisory Composer, lokasi proyek | F-09, F-10, F-11, F-13 |
| 1.1 | Task 2.2, 2.4, 2.5 | Migration dan constraint `user_roles`, `event_officers`, `location`, `category`, `expires_at` | F-01, F-02, F-03, F-07 |
| 1.1 | Task 3.3, 3.4 | Multi-role dan cakupan akses berdasarkan penugasan | F-07 |
| 1.1 | Task 6.3, 6.4 | Event gratis dan `expires_at` | F-02, F-04 |
| 1.1 | Task 7.4 | Retry pembayaran dan idempotency | F-08 |
| 1.1 | Task 9.1, 9.2 | Pemeriksaan penugasan petugas | F-03 |

---

# 6. TASK STATUS MANAGEMENT

Gunakan status berikut untuk memantau perkembangan:

| Status | Arti |
|---|---|
| NOT STARTED | Belum dikerjakan |
| IN PROGRESS | Sedang dikerjakan |
| TESTING | Sedang diuji |
| BLOCKED | Terhambat |
| COMPLETED | Selesai dan memenuhi kriteria tugas |
| NEEDS REVIEW | Menunggu pemeriksaan atau persetujuan |

## 6.1 Master Progress Checklist

### Phase 01 — Project Preparation

- [x] Task 1.1 Documentation Review
- [x] Task 1.2 Repository Inspection
- [x] Task 1.3 Environment Setup
- [x] Task 1.4 Database Connection
- [x] Task 1.5 Initial Application Setup

### Phase 02 — Database

- [x] Task 2.1 Database Structure Review
- [x] Task 2.2 Migration Development
- [x] Task 2.3 Model Development
- [x] Task 2.4 Database Constraints
- [x] Task 2.5 Factory and Seeder
- [x] Task 2.6 Database Validation

### Phase 03 — Authentication & RBAC

- [x] Task 3.1 Authentication
- [x] Task 3.2 User Management
- [x] Task 3.3 RBAC Implementation
- [x] Task 3.4 Authorization
- [x] Task 3.5 Frontend Authentication

### Phase 04 — Organization Management

- [ ] Task 4.1 Organization API
- [ ] Task 4.2 Organization Membership
- [ ] Task 4.3 Frontend Organization Management
- [ ] Task 4.4 Organization Authorization Testing

### Phase 05 — Event Management

- [ ] Task 5.1 Event API
- [ ] Task 5.2 Event Attributes
- [ ] Task 5.3 Event Business Rules
- [ ] Task 5.4 Frontend Event Management
- [ ] Task 5.5 Event Testing

### Phase 06 — Registration & Reservation

- [ ] Task 6.1 Registration API
- [ ] Task 6.2 Ticket Inventory
- [ ] Task 6.3 Reservation Service
- [ ] Task 6.4 Reservation Expiration Foundation
- [ ] Task 6.5 Frontend Registration
- [ ] Task 6.6 Registration Testing

### Phase 07 — Simulated Payment

- [ ] Task 7.1 Payment API
- [ ] Task 7.2 Payment Simulation
- [ ] Task 7.3 Payment Confirmation Service
- [ ] Task 7.4 Idempotency
- [ ] Task 7.5 Frontend Payment
- [ ] Task 7.6 Payment Testing

### Phase 08 — E-Ticket & QR Code

- [ ] Task 8.1 Ticket Generation
- [ ] Task 8.2 QR Code Generation
- [ ] Task 8.3 Ticket Validation API
- [ ] Task 8.4 Frontend E-Ticket
- [ ] Task 8.5 Ticket Testing

### Phase 09 — Check-in Management

- [ ] Task 9.1 Check-in API
- [ ] Task 9.2 Check-in Service
- [ ] Task 9.3 Transactional Check-in
- [ ] Task 9.4 Frontend Check-in
- [ ] Task 9.5 Check-in Testing

### Phase 10 — Dashboard & Reporting

- [ ] Task 10.1 Dashboard API
- [ ] Task 10.2 Super Admin Dashboard
- [ ] Task 10.3 Organization Dashboard
- [ ] Task 10.4 Student Dashboard
- [ ] Task 10.5 Reporting
- [ ] Task 10.6 Dashboard Testing

### Phase 11 — Integration & Final Validation

- [ ] Task 11.1 End-to-End Integration
- [ ] Task 11.2 The 30% Hard Problem Diagnostic
- [ ] Task 11.3 Critical Concurrency Test
- [ ] Task 11.4 ACID and Rollback Testing
- [ ] Task 11.5 Deadlock and Idempotency Testing
- [ ] Task 11.6 Security Validation
- [ ] Task 11.7 Final Functional Testing
- [ ] Task 11.8 UI and Application Validation
- [ ] Task 11.9 Documentation and Repository Validation

---

# 7. DEFINITION OF DONE

Sebuah task hanya boleh dinyatakan COMPLETED apabila:

1. Implementasi sesuai kebutuhan tugas.
2. Tidak bertentangan dengan REQUIREMENTS.md.
3. Tidak bertentangan dengan ARCHITECTURE.md.
4. Mematuhi RULES.md.
5. Pengujian yang relevan telah dijalankan.
6. Error yang ditemukan telah ditangani atau dilaporkan secara jelas.
7. Tidak merusak fitur yang sudah selesai.
8. Dokumentasi terkait telah diperbarui jika diperlukan.
9. Perubahan telah dicatat dalam Git sesuai aturan.
10. Laporan progres lengkap telah diberikan.
11. Persetujuan pengguna telah diperoleh sebelum melanjutkan ke task berikutnya.

Status COMPLETED untuk suatu task tidak otomatis berarti seluruh proyek telah selesai.

---

# 8. FINAL PROJECT ACCEPTANCE CRITERIA

CampusEvent dapat direkomendasikan sebagai proyek selesai apabila seluruh kriteria berikut terpenuhi.

## 8.1 Functional Requirements

- Seluruh kebutuhan fungsional dalam REQUIREMENTS.md terpenuhi.
- Empat aktor utama dapat menggunakan sistem sesuai kewenangannya.
- Pengelolaan organisasi berjalan.
- Pengelolaan event berjalan.
- Registrasi dan reservasi tiket berjalan.
- Pembayaran simulasi berjalan.
- E-ticket dan QR Code berfungsi.
- Check-in berfungsi.
- Dashboard dan laporan berfungsi.

## 8.2 Technical Requirements

- Arsitektur sesuai ARCHITECTURE.md.
- Coding standards sesuai RULES.md.
- Database menggunakan MySQL InnoDB.
- Operasi kritis menggunakan transaction.
- Sistem menerapkan locking untuk pengendalian kuota.
- RBAC diterapkan pada backend.
- Error handling dan logging tersedia.
- Struktur kode modular dan mudah dipelihara.

## 8.3 Testing Requirements

- Unit testing.
- Integration testing.
- Feature testing.
- End-to-end testing.
- Security testing.
- Concurrency testing.

Semua hasil harus memiliki bukti pengujian yang dapat diperiksa.

## 8.4 Hard Problem Requirements

- Tepat 5 reservasi berhasil dari 100 request bersamaan dengan 5 tiket tersisa.
- Sebanyak 95 request ditolak karena kuota habis.
- Tidak terjadi overselling.
- Tidak ada kuota negatif.
- Tidak ada transaksi parsial yang merusak konsistensi data.
- Tidak ada penerbitan tiket ganda.
- Tidak ada check-in ganda.
- Pengujian dilakukan pada MySQL InnoDB.

## 8.5 Documentation and Repository

- REQUIREMENTS.md lengkap.
- ARCHITECTURE.md lengkap.
- RULES.md lengkap.
- TASKS.md lengkap dan diperbarui.
- Dokumentasi tambahan yang relevan tersedia.
- Repository dapat dijalankan berdasarkan petunjuk.
- Tidak terdapat secret atau kredensial sensitif.

## 8.6 Final Approval

AI wajib memberikan laporan akhir yang mencakup:

1. Ringkasan seluruh fitur.
2. Ringkasan arsitektur yang diimplementasikan.
3. Daftar pengujian dan hasilnya.
4. Bukti pengujian The 30% Hard Problem.
5. Daftar kendala yang masih tersisa.
6. Kondisi repository.
7. Status seluruh dokumentasi.
8. Rekomendasi penyelesaian proyek.

Keputusan akhir bahwa CampusEvent telah diterima dan selesai berada di tangan pengguna.

---

# 9. FINAL AI EXECUTION INSTRUCTION

AI harus memperlakukan TASKS.md sebagai roadmap utama selama proses pengembangan CampusEvent.

**Aturan yang tidak boleh dilanggar:**

1. Jangan melewati urutan tahap tanpa persetujuan.
2. Jangan mengerjakan beberapa task sekaligus jika task sebelumnya belum diselesaikan dan diperiksa.
3. Jangan mengklaim task selesai tanpa pengujian.
4. Jangan mengubah arsitektur tanpa persetujuan.
5. Jangan mengabaikan error.
6. Jangan mengubah branch `main` secara langsung.
7. Jangan melanjutkan pekerjaan setelah laporan sebelum pengguna memberikan persetujuan.
8. Jangan mengabaikan pengujian The 30% Hard Problem.
9. Jangan menyatakan proyek selesai tanpa seluruh kriteria penerimaan.
10. Selalu menjaga keselarasan kode, database, dokumentasi, dan kebutuhan sistem.

**Execution Principle:**

Build step by step. Test every feature. Fix every identified issue within the approved scope. Preserve architectural decisions. Validate the hard problem with real evidence. Report every completed task. Wait for approval before proceeding.

**CampusEvent must be built based on documented context, not assumptions.**