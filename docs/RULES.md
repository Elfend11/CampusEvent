# RULES.md
# CAMPUS EVENT — CODING RULES & ENGINEERING STANDARDS

**Project:** CampusEvent  
**Project Name:** Integrated Campus Event & Ticketing Management System  
**Document:** Coding Rules & Engineering Standards  
**Version:** 1.1  
**Status:** Approved (Amendment 1)  
**Purpose:** Menjadi pedoman teknis dan standar pengembangan yang wajib dipatuhi selama proses implementasi CampusEvent.

---

## 1. GENERAL ENGINEERING PRINCIPLES

Seluruh proses pengembangan CampusEvent wajib mengikuti prinsip berikut:

1. Mengutamakan Clean Code, modularitas, konsistensi, dan kemudahan pemeliharaan.
2. Mengikuti kebutuhan sistem dalam `REQUIREMENTS.md`.
3. Mengikuti rancangan teknis dan struktur sistem dalam `ARCHITECTURE.md`.
4. Mengikuti seluruh standar pengkodean dalam dokumen ini.
5. Menghindari implementasi yang tidak diperlukan atau berada di luar ruang lingkup proyek.
6. Mengutamakan keamanan, integritas data, dan konsistensi proses bisnis.
7. Setiap perubahan kode harus dapat diuji dan dijelaskan.
8. Tidak boleh mengorbankan integritas transaksi hanya demi menyederhanakan implementasi.

### 1.1 Source of Truth

Urutan acuan pengembangan:

- `REQUIREMENTS.md`: menentukan kebutuhan dan perilaku sistem.
- `ARCHITECTURE.md`: menentukan arsitektur, teknologi, komponen, dan aliran data.
- `RULES.md`: menentukan standar dan batasan implementasi.
- `TASKS.md`: menentukan tahapan serta urutan pengerjaan.

Apabila ditemukan ketidaksesuaian antardokumen, AI wajib melaporkannya dan meminta persetujuan sebelum mengubah keputusan yang telah disepakati.

---

## 2. CLEAN CODE STANDARDS

Kode harus ditulis agar mudah dibaca, dipahami, diuji, dan dikembangkan kembali.

### 2.1 General Rules

1. Gunakan nama variabel, fungsi, class, dan file yang menjelaskan tujuan penggunaannya.
2. Hindari kode duplikat.
3. Hindari fungsi atau method yang memiliki terlalu banyak tanggung jawab.
4. Pisahkan logika berdasarkan fungsi dan tanggung jawabnya.
5. Hindari penggunaan angka atau string konstan secara berulang tanpa penjelasan.
6. Gunakan komentar hanya untuk menjelaskan logika yang tidak langsung terlihat.
7. Hindari nested condition yang terlalu dalam.
8. Hapus kode tidak terpakai, debugging sementara, dan komentar yang sudah tidak relevan.
9. Hindari penggunaan global state yang tidak diperlukan.
10. Jangan membuat abstraksi berlebihan untuk kebutuhan yang sederhana.

### 2.2 Modularity

Setiap modul harus memiliki tanggung jawab yang jelas.

Contoh pemisahan modul:

- Authentication Module
- User Management Module
- Organization Module
- Event Management Module
- Registration Module
- Payment Module
- Ticket Module
- Check-in Module
- Reporting Module

Setiap modul harus berinteraksi melalui mekanisme yang terstruktur dan tidak memiliki ketergantungan yang tidak diperlukan terhadap modul lain.

### 2.3 Maintainability

- Gunakan struktur folder yang konsisten.
- Hindari menempatkan seluruh logika dalam satu file.
- Gunakan komponen yang dapat digunakan kembali.
- Perubahan pada satu modul tidak boleh merusak modul lain.
- Setiap fitur harus memiliki tanggung jawab dan batasan yang jelas.

---

## 3. NAMING CONVENTIONS

Penamaan harus konsisten sesuai bahasa dan teknologi yang digunakan.

### 3.1 PHP — Laravel

Gunakan standar PSR-12 dan konvensi Laravel.

| Elemen | Convention | Contoh |
|---|---|---|
| Class | PascalCase | `EventService` |
| Method | camelCase | `createEvent()` |
| Variable | camelCase | `$eventData` |
| Constant | UPPER_SNAKE_CASE | `MAX_TICKET_LIMIT` |
| Controller | PascalCase + Controller | `EventController` |
| Model | Singular PascalCase | `Registration` |
| Migration | Laravel convention | `create_events_table` |

### 3.2 JavaScript — Vue.js

Gunakan camelCase untuk variabel dan fungsi, serta PascalCase untuk nama komponen.

| Elemen | Convention | Contoh |
|---|---|---|
| Variable | camelCase | `eventData` |
| Function | camelCase | `fetchEvents()` |
| Component | PascalCase | `EventCard.vue` |
| Constant | UPPER_SNAKE_CASE | `API_BASE_URL` |
| Composable | camelCase dengan use | `useAuth.js` |
| Store | camelCase | `authStore.js` |

### 3.3 Database — MySQL

Gunakan snake_case.

| Elemen | Convention | Contoh |
|---|---|---|
| Table | snake_case, plural | `events` |
| Column | snake_case | `event_date` |
| Foreign Key | nama entitas + `_id` | `organization_id` |
| Pivot Table | nama tabel terkait | `organization_members` |
| Index | nama deskriptif | `idx_events_status` |

Hindari nama tabel atau kolom yang tidak menjelaskan makna datanya.

---

## 4. BACKEND DEVELOPMENT RULES

Backend menggunakan Laravel dengan pola MVC dan REST API.

### 4.1 Controller Rules

Controller harus dibuat tipis (*thin controller*).

Controller bertanggung jawab untuk:

1. Menerima HTTP request.
2. Memanggil validasi request.
3. Memanggil Service Layer yang sesuai.
4. Mengembalikan response API.

Controller tidak boleh menjadi tempat utama untuk:

- Logika bisnis kompleks.
- Perhitungan kuota tiket.
- Pengelolaan transaksi yang kompleks.
- Proses pembayaran.
- Logika concurrency.
- Validasi hak akses yang seharusnya ditangani middleware atau policy.

### 4.2 Service Layer

Business logic harus ditempatkan pada Service Layer.

Contoh:

- `EventService`
- `RegistrationService`
- `PaymentService`
- `TicketService`
- `CheckInService`
- `OrganizationService`

Setiap service harus memiliki tanggung jawab yang spesifik.

Contoh pembagian:

- `RegistrationService`: menangani proses reservasi tiket.
- `PaymentService`: menangani simulasi dan konfirmasi pembayaran.
- `TicketService`: menangani penerbitan dan pengelolaan tiket.
- `CheckInService`: menangani validasi QR Code dan check-in.

### 4.3 Request Validation

1. Validasi input wajib dilakukan di backend.
2. Gunakan Laravel Form Request apabila sesuai.
3. Jangan menganggap validasi frontend sebagai pengganti validasi backend.
4. Validasi format, tipe data, panjang input, dan batas nilai.
5. Validasi relasi data dan kepemilikan resource.
6. Tolak input yang tidak sesuai dengan aturan bisnis.

### 4.4 API Rules

1. Gunakan REST API dengan format JSON.
2. Gunakan HTTP method sesuai tujuan operasi.
3. Gunakan HTTP status code secara konsisten.
4. Gunakan struktur response yang seragam.
5. Endpoint yang membutuhkan autentikasi wajib dilindungi.
6. Jangan mengirimkan informasi sensitif melalui response API.
7. Jangan mengandalkan identitas atau role yang dikirim langsung oleh frontend.

---

## 5. FRONTEND DEVELOPMENT RULES

Frontend menggunakan Vue.js 3 dan Vite.

### 5.1 Vue Development Standards

1. Gunakan Composition API.
2. Gunakan `<script setup>` untuk komponen Vue.
3. Pisahkan komponen berdasarkan tanggung jawab.
4. Gunakan reusable components untuk elemen UI yang digunakan berulang.
5. Hindari menempatkan seluruh logika aplikasi dalam satu komponen.
6. Gunakan props dan emits untuk komunikasi antarkomponen.
7. Hindari manipulasi DOM secara langsung jika dapat ditangani melalui mekanisme Vue.

### 5.2 State Management

Gunakan Pinia untuk pengelolaan state global.

State yang dapat dikelola melalui Pinia meliputi:

- Authentication state.
- User information.
- Role dan permission yang ditampilkan pada UI.
- Data global yang digunakan lintas halaman.

Data penting seperti kuota tiket, status pembayaran, dan validitas tiket tetap harus diverifikasi melalui backend.

### 5.3 Axios Service

1. Gunakan Axios sebagai HTTP client.
2. Buat konfigurasi Axios terpusat.
3. Gunakan service API terpisah untuk setiap domain atau modul.
4. Hindari pemanggilan endpoint secara berulang dengan konfigurasi berbeda-beda.
5. Tangani loading state, error response, dan timeout secara konsisten.
6. Jangan menyimpan secret key atau kredensial sensitif pada frontend.

### 5.4 UI Components

Gunakan komponen yang konsisten untuk:

- Button
- Input
- Form
- Modal
- Table
- Card
- Alert
- Loading indicator
- Pagination

Tampilan harus menyesuaikan role pengguna tanpa menjadikan pembatasan UI sebagai satu-satunya mekanisme keamanan.

---

## 6. DATABASE RULES

Database menggunakan MySQL dengan InnoDB sebagai storage engine.

### 6.1 Migration

1. Seluruh perubahan struktur database wajib menggunakan Laravel Migration.
2. Hindari perubahan skema secara manual tanpa migration.
3. Migration harus memiliki fungsi `up()` dan `down()` yang sesuai.
4. Migration harus dapat dijalankan secara berurutan.
5. Jangan menghapus atau mengubah data produksi secara tidak sengaja melalui migration.

### 6.2 Relationships and Constraints

Gunakan Foreign Key untuk menjaga integritas relasi data.

Gunakan Unique Constraint untuk data yang tidak boleh memiliki duplikasi.

Contoh:

- Email pengguna harus unik.
- Kombinasi pengguna dan role pada `user_roles` harus unik.
- Kombinasi event dan mahasiswa pada registrasi harus mengikuti aturan satu registrasi aktif per event.
- Kombinasi event dan petugas pada `event_officers` harus unik.
- Setiap registrasi hanya boleh memiliki satu record pembayaran.
- Token tiket harus unik.
- Satu tiket hanya boleh memiliki satu catatan check-in yang berhasil.

### 6.3 Indexing

Gunakan index pada kolom yang sering digunakan untuk:

- Pencarian.
- Filtering.
- Sorting.
- Relasi tabel.
- Pemeriksaan status.
- Pengambilan data berdasarkan foreign key.

Index harus dibuat berdasarkan kebutuhan query, bukan ditambahkan secara berlebihan.

### 6.4 Database Integrity

1. Gunakan tipe data yang sesuai.
2. Tentukan nullable atau non-nullable berdasarkan kebutuhan.
3. Gunakan default value apabila diperlukan.
4. Gunakan Foreign Key dan Unique Constraint sebagai perlindungan integritas.
5. Hindari menyimpan data yang sama secara tidak perlu.
6. Gunakan database sebagai sumber kebenaran (*source of truth*).

### 6.5 Event Inventory

Pengelolaan kuota event menggunakan tiga nilai:

- `capacity`: kapasitas total event.
- `reserved_count`: jumlah tiket yang sedang direservasi.
- `sold_count`: jumlah tiket yang telah berhasil dibayar.

Rumus ketersediaan:

`available = capacity - reserved_count - sold_count`

Invariant wajib:

`reserved_count + sold_count <= capacity`

AI tidak boleh membuat implementasi yang memungkinkan nilai kuota menjadi negatif atau melebihi kapasitas.

Untuk event gratis, konfirmasi dilakukan dalam transaksi reservasi yang sama: `sold_count` bertambah satu, `reserved_count` tidak berubah, tiket berstatus `ACTIVE` langsung dibuat, dan tidak ada record pembayaran.

Masa berlaku reservasi disimpan pada `registrations.expires_at` dengan batas waktu 10 menit.

---

## 7. ACID & TRANSACTION RULES

Semua operasi kritis yang memengaruhi integritas data wajib menggunakan database transaction.

### 7.1 ACID Principles

**Atomicity**

Seluruh operasi dalam satu transaksi harus berhasil bersama-sama atau dibatalkan seluruhnya.

**Consistency**

Setiap transaksi harus menjaga aturan dan integritas database.

**Isolation**

Transaksi yang berjalan bersamaan tidak boleh menghasilkan kondisi data yang tidak valid.

**Durability**

Data yang sudah berhasil di-commit harus tersimpan secara permanen sesuai jaminan database.

### 7.2 Critical Operations

Operasi berikut wajib menggunakan transaksi:

1. Reservasi tiket.
2. Konfirmasi pembayaran.
3. Pelepasan kuota reservasi yang kedaluwarsa.
4. Penerbitan tiket.
5. Proses check-in.
6. Operasi lain yang mengubah beberapa data terkait dan harus konsisten.

### 7.3 Rollback

1. Jika operasi kritis gagal, transaksi harus di-rollback.
2. Jangan membiarkan sebagian perubahan tersimpan ketika operasi seharusnya bersifat atomik.
3. Error harus ditangani dan dicatat.
4. Jangan mengembalikan status sukses apabila transaksi gagal.
5. Jangan melakukan commit sebelum seluruh perubahan dalam transaksi selesai.

### 7.4 Transaction Boundaries

1. Batasi transaksi pada proses database yang benar-benar membutuhkan konsistensi bersama.
2. Jangan menahan database lock selama pengguna melakukan pembayaran.
3. Jangan menjalankan proses eksternal yang lambat di dalam transaksi kritis.
4. Pastikan urutan penguncian data konsisten untuk mengurangi risiko deadlock.
5. Gunakan mekanisme retry secara terbatas hanya untuk kondisi yang memang aman diulang.

---

## 8. SECURITY STANDARDS

Keamanan harus diterapkan pada seluruh lapisan sistem.

### 8.1 Authentication

1. Password wajib disimpan menggunakan hashing yang aman melalui mekanisme Laravel.
2. Password tidak boleh disimpan dalam bentuk plaintext.
3. Endpoint yang membutuhkan autentikasi harus dilindungi.
4. Proses login harus memiliki validasi dan penanganan kegagalan yang sesuai.
5. Session atau token autentikasi harus dikelola dengan aman.
6. Autentikasi API menggunakan Laravel Sanctum dengan Cookie-Based SPA Authentication.
7. Frontend tidak boleh menyimpan token autentikasi di localStorage.
8. Setiap request yang mengubah data wajib dilindungi CSRF.
9. Session harus diregenerasi setelah login dan diinvalidasi saat logout.
10. Cookie autentikasi menggunakan `HttpOnly`, `Secure` pada deployment HTTPS, dan `SameSite` yang sesuai.

### 8.2 Authorization and RBAC

CampusEvent memiliki empat aktor utama:

- Super Admin.
- Admin Organisasi.
- Petugas Check-in.
- Mahasiswa.

Setiap aktor hanya boleh mengakses fungsi sesuai hak aksesnya.

Satu akun dapat memiliki lebih dari satu role. Role disimpan terpisah dari data akun dan tidak boleh menimbulkan duplikasi akun. Hak akses mahasiswa tetap tersedia meskipun akun tersebut memiliki role pengelola.

Aturan:

1. Terapkan Role-Based Access Control (RBAC).
2. Validasi role dan permission pada backend.
3. Gunakan middleware, policy, atau mekanisme otorisasi Laravel yang sesuai.
4. Admin Organisasi hanya boleh mengelola organisasi dan event yang menjadi tanggung jawabnya, yaitu organisasi yang tercatat pada `organization_members`.
5. Petugas Check-in hanya boleh melakukan fungsi check-in untuk event yang tercatat pada `event_officers`.
6. Mahasiswa tidak boleh mengakses fungsi administrasi.
7. Super Admin memiliki kewenangan pengelolaan platform sesuai kebutuhan sistem.
8. Role tidak boleh ditentukan oleh data yang dikirim frontend.

### 8.3 Input and Endpoint Protection

1. Validasi seluruh input pada backend.
2. Lindungi endpoint dari akses tanpa izin.
3. Terapkan rate limiting pada endpoint yang rentan terhadap penyalahgunaan.
4. Gunakan proteksi terhadap SQL Injection melalui mekanisme query Laravel.
5. Terapkan perlindungan XSS dan CSRF sesuai mekanisme autentikasi dan penggunaan aplikasi.
6. Jangan mengekspos kredensial, secret, atau informasi internal.
7. Jangan mempercayai ID resource yang dikirim frontend tanpa memeriksa hak akses pengguna.

### 8.4 Sensitive Data

1. Jangan menyimpan password dalam bentuk asli.
2. Jangan mencatat password, token autentikasi, atau data rahasia ke log.
3. Batasi informasi sensitif pada response API.
4. Simpan token QR tiket dalam bentuk hash pada database apabila mengikuti rancangan arsitektur.
5. Gunakan HTTPS pada lingkungan deployment.

---

## 9. ERROR HANDLING & LOGGING

Penanganan error harus terpusat, konsisten, dan aman.

### 9.1 Error Handling

1. Gunakan mekanisme exception handling Laravel.
2. Hindari penanganan error yang berbeda-beda tanpa alasan pada setiap fungsi.
3. Gunakan HTTP status code sesuai jenis kesalahan.
4. Berikan pesan error yang dapat dipahami pengguna.
5. Jangan menampilkan stack trace atau detail internal sistem kepada pengguna.
6. Pastikan kegagalan operasi kritis tidak menghasilkan response sukses.

### 9.2 HTTP Status Code

Gunakan status code secara konsisten.

| Status | Penggunaan |
|---|---|
| 200 | Request berhasil |
| 201 | Resource berhasil dibuat |
| 204 | Berhasil tanpa response body |
| 400 | Request tidak valid |
| 401 | Belum terautentikasi |
| 403 | Tidak memiliki izin |
| 404 | Resource tidak ditemukan |
| 409 | Konflik data atau status |
| 422 | Validasi input gagal |
| 429 | Terlalu banyak request |
| 500 | Kesalahan internal server |

### 9.3 Logging

1. Catat error penting menggunakan Laravel Logging.
2. Catat kegagalan transaksi dan proses kritis.
3. Catat kejadian yang diperlukan untuk audit.
4. Jangan mencatat password, secret, dan token sensitif.
5. Gunakan informasi log yang membantu proses debugging.
6. Hindari mencatat informasi pribadi secara berlebihan.

---

## 10. TESTING STANDARDS

Setiap fitur wajib melalui pengujian sebelum dianggap selesai.

### 10.1 Unit Testing

Menguji fungsi atau komponen secara terpisah.

Contoh:

- Validasi input event.
- Perhitungan kuota.
- Pemeriksaan status registrasi.
- Validasi role.
- Pemeriksaan status tiket.

### 10.2 Integration Testing

Menguji interaksi beberapa komponen.

Contoh:

- Event Service dengan database.
- Registration Service dengan Event Inventory.
- Payment Service dengan Registration.
- Ticket Service dengan Payment.
- Check-in Service dengan Ticket.

### 10.3 Feature Testing

Menguji alur fitur dari sudut pandang pengguna melalui endpoint atau proses aplikasi.

Contoh:

- Mahasiswa melakukan registrasi.
- Mahasiswa melakukan simulasi pembayaran.
- Sistem menerbitkan tiket.
- Petugas melakukan check-in.
- Admin melihat laporan event.

### 10.4 Concurrency Testing

Pengujian concurrency wajib dilakukan untuk membuktikan sistem mampu menangani permintaan bersamaan.

Pengujian utama:

- Kapasitas tersisa: 5 tiket.
- Jumlah permintaan bersamaan: 100 mahasiswa.
- Hasil yang diharapkan: tepat 5 reservasi berhasil dan 95 permintaan ditolak karena kuota habis.

Kriteria keberhasilan:

1. Tidak terjadi overselling.
2. Tidak ada kuota bernilai negatif.
3. Jumlah reservasi dan tiket terjual tidak melebihi kapasitas.
4. Tidak ada registrasi ganda yang melanggar aturan.
5. Seluruh transaksi menjaga konsistensi data.
6. Kegagalan transaksi tidak meninggalkan perubahan parsial.

Pengujian wajib menggunakan MySQL InnoDB yang sesungguhnya, bukan hanya SQLite atau mock database, untuk membuktikan perilaku locking dan transaksi.

### 10.5 Testing Evidence

Setiap pengujian penting harus memiliki bukti, seperti:

- Test result.
- Log pengujian.
- Jumlah request berhasil dan gagal.
- Kondisi database sebelum dan sesudah pengujian.
- Dokumentasi error yang ditemukan dan diperbaiki.

AI tidak boleh menyatakan pengujian berhasil apabila pengujian belum benar-benar dijalankan.

---

## 11. CONCURRENCY CONTROL RULES

Bagian ini merupakan aturan khusus untuk menangani tantangan utama CampusEvent, yaitu perebutan kuota tiket oleh banyak mahasiswa secara bersamaan.

### 11.1 Reservation Rules

1. Reservasi tiket harus diproses melalui backend.
2. Gunakan database transaction.
3. Gunakan mekanisme row-level locking, seperti `SELECT ... FOR UPDATE`, pada data inventori event.
4. Periksa kembali ketersediaan tiket setelah lock diperoleh.
5. Jika kuota tidak tersedia, batalkan proses reservasi.
6. Jika kuota tersedia, buat atau perbarui registrasi PENDING dan tambahkan `reserved_count`.
7. Setiap reservasi memiliki batas waktu 10 menit yang disimpan pada `registrations.expires_at`.
8. Jangan mengurangi kuota berdasarkan data frontend.
9. Event gratis tidak membuat reservasi sementara; konfirmasi dilakukan dalam transaksi yang sama sesuai RULES §11.2 butir 10.

### 11.2 Payment Confirmation Rules

1. Pembayaran dilakukan melalui simulasi, tanpa integrasi payment gateway nyata.
2. Proses konfirmasi harus memeriksa status registrasi.
3. Registrasi yang sudah kedaluwarsa tidak boleh dikonfirmasi sebagai pembayaran berhasil.
4. Konfirmasi pembayaran wajib transactional.
5. Ketika berhasil, sistem memperbarui status registrasi, mengurangi `reserved_count`, menambah `sold_count`, dan menerbitkan tiket dalam transaksi yang konsisten.
6. Proses yang sama tidak boleh menghasilkan penerbitan tiket ganda.
7. Satu registrasi hanya memiliki satu record pembayaran.
8. Retry pembayaran menggunakan record pembayaran yang sama selama reservasi masih aktif dan belum berstatus `PAID`.
9. Pembayaran berstatus `PAID` tidak boleh diproses ulang dan tidak boleh menghasilkan tiket tambahan.
10. Event gratis tidak membuat record pembayaran; status registrasi langsung `CONFIRMED`, `sold_count` bertambah, `reserved_count` tidak berubah, dan tiket `ACTIVE` diterbitkan dalam transaksi yang sama.

### 11.3 Reservation Expiration Rules

1. Reservasi yang melewati batas waktu 10 menit harus dapat kedaluwarsa.
2. Laravel Scheduler digunakan untuk menjalankan proses pemeriksaan dan pelepasan reservasi.
3. Proses kedaluwarsa harus memeriksa ulang status registrasi di dalam transaksi.
4. Kuota hanya boleh dilepaskan satu kali.
5. Jangan melepaskan kuota untuk registrasi yang sudah berhasil dibayar.
6. Proses pembayaran dan kedaluwarsa harus menangani kemungkinan berjalan bersamaan.

### 11.4 Locking Rules

1. Gunakan urutan penguncian yang konsisten pada proses pembayaran dan kedaluwarsa.
2. Periksa kembali status data setelah memperoleh lock.
3. Hindari proses database yang tidak perlu selama lock masih aktif.
4. Tangani deadlock secara aman apabila terjadi.
5. Jangan menganggap urutan request masuk selalu sama dengan urutan commit database.

### 11.5 Idempotency

Operasi kritis harus aman ketika request yang sama dikirim ulang.

Contoh:

- Konfirmasi pembayaran tidak boleh menerbitkan dua tiket.
- Pelepasan reservasi tidak boleh mengurangi kuota dua kali.
- Check-in tidak boleh tercatat dua kali untuk tiket yang sama.
- Request berulang harus menghasilkan respons yang sesuai dengan status terakhir data.

### 11.6 Administrative Cancellation Rules

1. Pembatalan mandiri oleh mahasiswa tidak termasuk cakupan versi 1.
2. Pembatalan hanya dapat dilakukan oleh Super Admin atau Admin Organisasi yang berwenang atas event terkait.
3. Pembatalan wajib menggunakan transaksi database.
4. Urutan locking konsisten: registration terlebih dahulu, kemudian event inventory.
5. Pembatalan registrasi `PENDING` melepaskan `reserved_count` sebanyak satu.
6. Pembatalan registrasi `CONFIRMED` melepaskan `sold_count` sebanyak satu dan mengubah tiket menjadi `VOID`.
7. Registrasi `EXPIRED` atau `CANCELLED` tidak boleh dilepas kembali.
8. Kuota tidak boleh menjadi negatif akibat pembatalan.

---

## 12. GIT & REPOSITORY RULES

Pengembangan harus menggunakan Git untuk menjaga riwayat perubahan kode.

### 12.1 Branching

1. Gunakan branch `main` sebagai branch utama.
2. Pengembangan fitur dilakukan pada branch terpisah.
3. Hindari mengerjakan seluruh fitur langsung pada `main`.
4. Gunakan nama branch yang menjelaskan tujuan perubahan.

Contoh:

- `feature/authentication`
- `feature/event-management`
- `feature/ticket-registration`
- `fix/concurrency-issue`
- `docs/project-documentation`

### 12.2 Commit Rules

Gunakan pesan commit yang jelas dan menjelaskan perubahan.

Contoh:

- `feat: add event registration`
- `fix: prevent ticket overselling`
- `refactor: move registration logic to service`
- `test: add concurrency test`
- `docs: update API documentation`

### 12.3 Repository Integrity

1. Jangan memasukkan file `.env` ke repository.
2. Jangan memasukkan secret atau kredensial.
3. Gunakan `.gitignore` dengan benar.
4. Jangan menghapus perubahan milik anggota tim tanpa persetujuan.
5. Periksa perubahan sebelum melakukan commit.
6. Jangan melakukan force push pada branch bersama tanpa persetujuan.
7. Pastikan kode dapat dijalankan berdasarkan dokumentasi proyek.

---

## 13. AI CODING RESTRICTIONS

AI merupakan alat bantu pengembangan, bukan pengambil keputusan arsitektur secara mandiri.

### 13.1 Mandatory Documentation Reading

Sebelum menghasilkan atau mengubah kode, AI wajib membaca:

1. `REQUIREMENTS.md`
2. `ARCHITECTURE.md`
3. `RULES.md`
4. `TASKS.md`

AI juga harus memeriksa struktur repository dan kode yang sudah tersedia.

### 13.2 Architecture Restrictions

AI dilarang mengubah hal berikut tanpa persetujuan:

- Framework dan teknologi utama.
- Arsitektur frontend dan backend.
- Struktur database yang telah disepakati.
- Pola komunikasi API.
- Mekanisme transaksi dan concurrency.
- Aturan bisnis utama.
- Pembagian role dan hak akses.

Jika perubahan diperlukan, AI harus menjelaskan:

1. Masalah yang ditemukan.
2. Alasan perubahan diperlukan.
3. Dampak terhadap sistem.
4. Alternatif solusi.
5. Bagian dokumentasi yang perlu diperbarui.

### 13.3 Code Generation Restrictions

1. Jangan menghasilkan kode yang bertentangan dengan dokumentasi.
2. Jangan membuat fitur di luar ruang lingkup tanpa persetujuan.
3. Jangan mengganti implementasi yang sudah berfungsi tanpa alasan teknis.
4. Jangan menghapus file atau fungsi yang masih digunakan.
5. Jangan membuat data dummy sebagai pengganti proses backend yang seharusnya nyata.
6. Jangan menggunakan SQLite atau mock database sebagai bukti akhir pengujian concurrency.
7. Jangan menyatakan fitur selesai hanya karena kode sudah dihasilkan.

### 13.4 Testing Responsibilities

Setelah menghasilkan atau mengubah kode, AI wajib:

1. Menjalankan pemeriksaan kode yang relevan.
2. Menjalankan test yang tersedia.
3. Memeriksa error dan warning.
4. Memperbaiki error yang ditemukan sesuai ruang lingkup.
5. Melaporkan test yang berhasil dan gagal.
6. Menjelaskan bagian yang belum dapat diuji.
7. Tidak membuat klaim keberhasilan tanpa bukti.

### 13.5 Communication Rules

Jika menemukan informasi yang belum jelas atau bertentangan, AI harus bertanya terlebih dahulu.

AI tidak boleh:

- Mengarang kebutuhan sistem.
- Mengasumsikan keputusan teknis yang belum disepakati.
- Mengubah aturan bisnis secara diam-diam.
- Menyembunyikan keterbatasan implementasi.
- Melaporkan pekerjaan yang belum dilakukan sebagai pekerjaan selesai.

---

## 14. CODE REVIEW & VALIDATION

Setiap fitur harus melalui pemeriksaan sebelum dinyatakan selesai.

### 14.1 Code Review Checklist

- [ ] Kode mengikuti Clean Code.
- [ ] Penamaan sesuai konvensi.
- [ ] Struktur kode modular.
- [ ] Controller tetap tipis.
- [ ] Business logic berada di Service Layer.
- [ ] Validasi backend tersedia.
- [ ] Hak akses telah diperiksa.
- [ ] Database menggunakan migration dan constraint yang sesuai.
- [ ] Operasi kritis menggunakan transaction.
- [ ] Error handling dan logging tersedia.
- [ ] Tidak ada data sensitif yang terekspos.
- [ ] Tidak ada kode debugging yang tertinggal.
- [ ] Tidak merusak fitur yang sudah ada.

### 14.2 Functional Validation

- [ ] Fitur sesuai REQUIREMENTS.md.
- [ ] Implementasi sesuai ARCHITECTURE.md.
- [ ] Tidak melanggar RULES.md.
- [ ] Alur pengguna berjalan sesuai kebutuhan.
- [ ] Integrasi frontend dan backend berhasil.
- [ ] Data tersimpan dengan benar.
- [ ] Error dan kondisi tidak normal ditangani.

### 14.3 Critical System Validation

- [ ] Reservasi tiket menggunakan transaction dan locking.
- [ ] Kuota tidak dapat melebihi kapasitas.
- [ ] Konfirmasi pembayaran tidak menghasilkan tiket ganda.
- [ ] Reservasi kedaluwarsa melepaskan kuota tepat satu kali.
- [ ] QR Code divalidasi melalui backend.
- [ ] Check-in tidak dapat dilakukan berulang untuk tiket yang sama.
- [ ] Concurrency test 100 request dengan 5 tiket tersisa berhasil memenuhi kriteria.

### 14.4 Definition of Done

Sebuah fitur hanya dapat dinyatakan selesai apabila:

1. Implementasi sesuai kebutuhan yang disepakati.
2. Kode mengikuti standar dalam dokumen ini.
3. Pengujian yang relevan telah dijalankan.
4. Tidak terdapat error kritis yang belum diselesaikan.
5. Integrasi dengan modul terkait berfungsi.
6. Dokumentasi diperbarui jika terdapat perubahan yang relevan.
7. Hasil pekerjaan dapat dijelaskan dan diverifikasi.

---

## 15. DOCUMENT CHANGE LOG

| Versi | Bagian | Perubahan | Dasar |
|---|---|---|---|
| 1.1 | §6.2 | Unique constraint `user_roles` dan `event_officers` | F-03, F-07 |
| 1.1 | §6.5 | Perilaku event gratis dan `registrations.expires_at` | F-02, F-04 |
| 1.1 | §8.1 | Autentikasi Sanctum, CSRF, session, dan cookie | F-09 |
| 1.1 | §8.2 | Multi-role dan cakupan akses berdasarkan penugasan | F-07 |
| 1.1 | §11.1, §11.2 | `expires_at`, retry pembayaran, dan event gratis | F-02, F-04, F-08 |
| 1.1 | §11.6 | Aturan pembatalan administratif | F-06 |

---

## FINAL RULE

Seluruh aturan dalam dokumen ini berlaku untuk semua proses pengembangan CampusEvent.

Apabila AI menemukan konflik antara kebutuhan, arsitektur, dan standar implementasi, AI wajib menghentikan perubahan yang berpotensi mengubah keputusan proyek, menjelaskan konflik tersebut, dan meminta persetujuan pengembang.

**Prinsip utama: Generate code berdasarkan konteks yang benar, jaga integritas data, uji setiap proses kritis, dan jangan mengubah keputusan proyek tanpa persetujuan.**