# REQUIREMENTS.md

# CampusEvent
## Integrated Campus Event & Ticketing Management System

**Document Type:** Software Requirements Specification (SRS)  
**Version:** 1.0  
**Project Type:** Vibe Coding Final Project  
**Development Approach:** AI-Assisted Software Engineering  
**Status:** Initial Approved Specification

---

# 1. PROJECT OVERVIEW

## 1.1 Project Description

CampusEvent merupakan sistem informasi manajemen event kampus berbasis web yang dirancang untuk mengintegrasikan proses publikasi kegiatan, pengelolaan event, pendaftaran peserta, distribusi tiket digital, serta verifikasi kehadiran.

Platform ini ditujukan bagi berbagai organisasi dan penyelenggara kegiatan di lingkungan kampus, seperti BEM, fakultas, Himpunan Mahasiswa Program Studi (HMPS), Unit Kegiatan Mahasiswa (UKM), dan kepanitiaan kegiatan lainnya.

Mahasiswa dapat menggunakan platform untuk menemukan informasi kegiatan, melakukan pendaftaran, memperoleh tiket digital, serta melakukan check-in ketika menghadiri kegiatan.

Sistem juga menyediakan pengelolaan event secara terpusat sehingga setiap organisasi dapat mengelola kegiatan masing-masing tanpa mengganggu pengelolaan organisasi lain.

## 1.2 Background

Penyelenggaraan kegiatan kampus membutuhkan pengelolaan informasi, pendaftaran peserta, pembatasan kuota, dan verifikasi kehadiran yang terorganisasi.

Pengelolaan secara manual dapat menyebabkan beberapa permasalahan, antara lain:

1. Informasi kegiatan tersebar di berbagai media.
2. Proses pendaftaran dan pendataan peserta kurang terorganisasi.
3. Kesulitan memantau ketersediaan kuota secara real-time.
4. Risiko pendaftaran melebihi kapasitas kegiatan.
5. Proses distribusi dan verifikasi tiket kurang efisien.
6. Kesulitan melakukan rekapitulasi data peserta dan kehadiran.

CampusEvent dikembangkan untuk mengatasi permasalahan tersebut melalui sistem pengelolaan event dan tiket yang terintegrasi.

## 1.3 Project Objectives

Tujuan pengembangan CampusEvent adalah:

1. Menyediakan platform terpusat untuk publikasi dan pengelolaan event kampus.
2. Mempermudah mahasiswa dalam menemukan dan mendaftar kegiatan.
3. Memfasilitasi organisasi kampus dalam mengelola event secara mandiri.
4. Mengimplementasikan distribusi tiket digital dengan QR Code.
5. Menjamin konsistensi kuota tiket dalam kondisi permintaan bersamaan.
6. Mencegah terjadinya ticket overselling.
7. Mempermudah proses check-in dan pencatatan kehadiran.
8. Menyediakan laporan pendaftaran dan kehadiran peserta.

## 1.4 Project Scope

### In Scope

- Sistem autentikasi dan otorisasi pengguna.
- Manajemen organisasi kampus.
- Manajemen event.
- Publikasi dan pencarian event.
- Pendaftaran peserta.
- Pengelolaan kuota tiket.
- Simulasi pembayaran tiket.
- Penerbitan tiket digital.
- Pembuatan dan validasi QR Code.
- Sistem check-in peserta.
- Dashboard dan laporan.
- Pengujian transaksi bersamaan.

### Out of Scope

- Integrasi payment gateway dengan transaksi keuangan sungguhan.
- Aplikasi native Android dan iOS.
- Integrasi otomatis dengan sistem akademik kampus.
- Sistem pengelolaan keuangan organisasi.
- Sistem penjualan tiket untuk kebutuhan komersial eksternal di luar cakupan kampus.

---

# 2. BUSINESS REQUIREMENTS

## 2.1 Business Domain

CampusEvent bergerak dalam domain Event Management System yang berfokus pada pengelolaan kegiatan kampus dan distribusi tiket digital.

Penyelenggara dapat membuat dan mengelola kegiatan, menentukan kapasitas peserta, serta memantau pendaftaran.

Mahasiswa dapat melihat kegiatan yang tersedia dan melakukan pendaftaran berdasarkan kuota yang ditetapkan.

Sistem menggunakan mekanisme First Come, First Served (FCFS), yaitu peserta yang berhasil memperoleh alokasi kuota melalui transaksi database terlebih dahulu akan mendapatkan tiket.

## 2.2 Stakeholders

| Stakeholder | Kepentingan |
|---|---|
| Mahasiswa | Mendapatkan informasi dan mendaftar event |
| BEM | Mengelola kegiatan organisasi |
| Fakultas | Mengelola kegiatan fakultas |
| HMPS | Mengelola kegiatan program studi |
| UKM | Mengelola kegiatan unit mahasiswa |
| Panitia | Mengelola peserta dan check-in |
| Super Admin | Mengawasi keseluruhan platform |

## 2.3 Business Problems

| ID | Permasalahan | Solusi Sistem |
|---|---|---|
| BP-01 | Informasi event tersebar | Publikasi terpusat |
| BP-02 | Pendaftaran manual | Registrasi online |
| BP-03 | Kuota sulit dipantau | Pengelolaan kuota otomatis |
| BP-04 | Risiko overselling | Transaksi ACID dan locking |
| BP-05 | Tiket tidak terorganisasi | E-ticket dengan QR Code |
| BP-06 | Verifikasi kehadiran manual | QR check-in |
| BP-07 | Rekapitulasi membutuhkan waktu | Laporan otomatis |

## 2.4 Business Goals

- BG-01: Meningkatkan efisiensi pengelolaan kegiatan kampus.
- BG-02: Menyediakan proses pendaftaran yang terintegrasi.
- BG-03: Menjamin distribusi tiket sesuai kapasitas event.
- BG-04: Meningkatkan akurasi data pendaftaran dan kehadiran.
- BG-05: Menyediakan sistem yang aman dan konsisten.

---

# 3. SYSTEM USERS AND ROLES

CampusEvent memiliki empat role utama.

## 3.1 Super Admin

Super Admin merupakan pengelola utama platform.

Hak akses:
- Login dan logout.
- Melihat dashboard keseluruhan.
- Mengelola akun pengguna.
- Mengelola organisasi kampus.
- Melihat seluruh event.
- Mengelola status organisasi.
- Memantau aktivitas sistem.
- Melihat laporan keseluruhan.

## 3.2 Admin Organisasi

Admin Organisasi bertanggung jawab mengelola kegiatan organisasi yang menjadi kewenangannya.

Hak akses:
- Login dan logout.
- Mengelola profil organisasi sesuai izin.
- Membuat event.
- Mengedit dan menghapus event sesuai status dan aturan.
- Menentukan tanggal, lokasi, kategori, harga, dan kuota.
- Mengatur periode pendaftaran.
- Melihat daftar peserta.
- Memantau transaksi pendaftaran.
- Melihat laporan event.

Admin organisasi hanya dapat mengelola data organisasi dan event yang menjadi tanggung jawabnya.

## 3.3 Petugas Check-in

Petugas Check-in bertanggung jawab memverifikasi tiket peserta.

Hak akses:
- Login.
- Melihat event yang ditugaskan.
- Mengakses fitur pemindaian QR Code.
- Memverifikasi tiket.
- Mencatat kehadiran.
- Melihat status check-in.

Petugas tidak memiliki hak untuk mengubah kuota atau data transaksi pendaftaran.

## 3.4 Mahasiswa

Mahasiswa merupakan pengguna utama yang mendaftar kegiatan.

Hak akses:
- Registrasi akun.
- Login dan logout.
- Melihat daftar event.
- Mencari dan memfilter event.
- Melihat detail event.
- Melakukan pendaftaran.
- Melakukan simulasi pembayaran jika diperlukan.
- Melihat status pendaftaran.
- Mengakses tiket digital.
- Melihat riwayat pendaftaran.

---

# 4. FUNCTIONAL REQUIREMENTS

## 4.1 Authentication and Authorization

| ID | Requirement |
|---|---|
| FR-01 | Sistem menyediakan registrasi akun mahasiswa. |
| FR-02 | Sistem menyediakan login dan logout. |
| FR-03 | Sistem membedakan hak akses berdasarkan role. |
| FR-04 | Sistem membatasi akses halaman berdasarkan otorisasi. |
| FR-05 | Sistem melakukan validasi data autentikasi. |
| FR-06 | Sistem menyimpan password dalam bentuk hash. |

## 4.2 Organization Management

| ID | Requirement |
|---|---|
| FR-07 | Super Admin dapat membuat organisasi. |
| FR-08 | Super Admin dapat mengubah dan mengelola status organisasi. |
| FR-09 | Admin Organisasi dapat melihat profil organisasinya. |
| FR-10 | Setiap event harus terhubung dengan organisasi penyelenggara. |
| FR-11 | Admin Organisasi tidak dapat mengakses data organisasi lain tanpa izin. |

## 4.3 Event Management

| ID | Requirement |
|---|---|
| FR-12 | Admin Organisasi dapat membuat event. |
| FR-13 | Admin dapat mengubah informasi event. |
| FR-14 | Admin dapat menghapus event sesuai ketentuan sistem. |
| FR-15 | Admin dapat mengatur nama dan deskripsi event. |
| FR-16 | Admin dapat menentukan tanggal, waktu, dan lokasi event. |
| FR-17 | Admin dapat menentukan kategori event. |
| FR-18 | Admin dapat menentukan kuota peserta. |
| FR-19 | Admin dapat menentukan harga tiket. |
| FR-20 | Admin dapat mengatur periode pendaftaran. |
| FR-21 | Admin dapat mengatur status publikasi event. |
| FR-22 | Mahasiswa dapat melihat event yang telah dipublikasikan. |
| FR-23 | Mahasiswa dapat mencari dan memfilter event. |

## 4.4 Registration Management

| ID | Requirement |
|---|---|
| FR-24 | Mahasiswa dapat mendaftar pada event yang tersedia. |
| FR-25 | Sistem memeriksa periode pendaftaran. |
| FR-26 | Sistem memeriksa ketersediaan kuota sebelum menerima pendaftaran. |
| FR-27 | Sistem mencegah mahasiswa mendaftar event yang sama lebih dari satu kali secara aktif. |
| FR-28 | Sistem memproses alokasi kuota secara transaksional. |
| FR-29 | Sistem menolak pendaftaran ketika kuota habis. |
| FR-30 | Sistem menampilkan hasil pendaftaran kepada mahasiswa. |
| FR-31 | Admin dapat melihat daftar peserta yang mendaftar. |
| FR-32 | Mahasiswa dapat melihat riwayat pendaftaran. |

## 4.5 Payment Simulation

| ID | Requirement |
|---|---|
| FR-33 | Sistem mendukung event gratis dan berbayar. |
| FR-34 | Sistem menyediakan simulasi pembayaran untuk event berbayar. |
| FR-35 | Sistem menampilkan informasi harga tiket. |
| FR-36 | Sistem mencatat status pembayaran. |
| FR-37 | Sistem hanya menerbitkan tiket berbayar setelah pembayaran simulasi berhasil. |
| FR-38 | Sistem menyediakan status pembayaran berhasil, pending, atau gagal. |

Catatan: Modul pembayaran merupakan simulasi untuk kebutuhan demonstrasi. Sistem tidak memproses uang sungguhan dan tidak terhubung dengan payment gateway eksternal.

## 4.6 Digital Ticket Management

| ID | Requirement |
|---|---|
| FR-39 | Sistem membuat tiket digital untuk pendaftaran yang memenuhi ketentuan. |
| FR-40 | Sistem menghasilkan kode tiket yang unik. |
| FR-41 | Sistem menghasilkan QR Code berdasarkan identitas tiket. |
| FR-42 | Mahasiswa dapat melihat tiket yang dimiliki. |
| FR-43 | Sistem menampilkan informasi event pada tiket. |
| FR-44 | Sistem menampilkan status validitas tiket. |

## 4.7 Check-in and Attendance

| ID | Requirement |
|---|---|
| FR-45 | Petugas dapat melakukan pemindaian QR Code. |
| FR-46 | Sistem memvalidasi identitas tiket. |
| FR-47 | Sistem memeriksa apakah tiket berasal dari event yang benar. |
| FR-48 | Sistem memeriksa apakah tiket telah digunakan. |
| FR-49 | Sistem mencatat waktu check-in. |
| FR-50 | Sistem menolak penggunaan tiket yang sudah digunakan. |
| FR-51 | Sistem menampilkan hasil validasi kepada petugas. |

## 4.8 Dashboard and Reporting

| ID | Requirement |
|---|---|
| FR-52 | Super Admin dapat melihat statistik keseluruhan platform. |
| FR-53 | Admin Organisasi dapat melihat statistik organisasinya. |
| FR-54 | Admin dapat melihat jumlah pendaftar setiap event. |
| FR-55 | Admin dapat melihat jumlah tiket terdistribusi. |
| FR-56 | Admin dapat melihat jumlah peserta yang hadir. |
| FR-57 | Sistem menyediakan rekapitulasi pendaftaran dan kehadiran. |

---

# 5. BUSINESS RULES

Bagian ini merupakan aturan bisnis yang wajib dipatuhi oleh seluruh komponen sistem.

## 5.1 Event Rules

- BR-01: Setiap event harus memiliki penyelenggara.
- BR-02: Setiap event harus memiliki kapasitas peserta yang ditentukan.
- BR-03: Kuota event tidak boleh bernilai negatif.
- BR-04: Event hanya dapat didaftarkan ketika status dan periode pendaftarannya memenuhi ketentuan.
- BR-05: Event yang sudah memiliki transaksi pendaftaran tidak boleh dihapus secara sembarangan sehingga merusak integritas data.
- BR-06: Admin hanya dapat mengelola event yang menjadi kewenangannya.

## 5.2 Registration Rules

- BR-07: Satu mahasiswa hanya dapat memiliki satu pendaftaran aktif untuk event yang sama.
- BR-08: Pendaftaran menggunakan mekanisme First Come, First Served.
- BR-09: Pendaftaran harus melewati validasi kuota pada sisi server dan database.
- BR-10: Pendaftaran tidak boleh berhasil apabila kuota telah habis.
- BR-11: Proses pengurangan kuota dan pembuatan data pendaftaran harus dilakukan secara konsisten dalam transaksi database.
- BR-12: Kegagalan transaksi harus menyebabkan rollback terhadap perubahan yang belum berhasil di-commit.

## 5.3 Ticket Rules

- BR-13: Setiap tiket harus memiliki kode unik.
- BR-14: Tiket harus terhubung dengan pendaftaran yang valid.
- BR-15: Tiket berbayar hanya diterbitkan setelah simulasi pembayaran berhasil.
- BR-16: Tiket hanya berlaku untuk event yang tercantum di dalamnya.
- BR-17: Satu tiket hanya dapat digunakan untuk satu kali check-in.
- BR-18: Tiket yang telah digunakan tidak dapat digunakan kembali.

## 5.4 Payment Rules

- BR-19: Event dapat memiliki harga tiket gratis maupun berbayar.
- BR-20: Pembayaran dalam proyek ini hanya berupa simulasi.
- BR-21: Status pembayaran harus disimpan dan dapat ditelusuri.
- BR-22: Tiket tidak boleh diterbitkan untuk pembayaran yang belum berhasil.

---

# 6. THE 30% HARD PROBLEM: HIGH-CONCURRENCY TICKET OVERSELLING

Bagian ini merupakan tantangan rekayasa utama proyek berdasarkan Tema 2 dalam materi Vibe Coding Final Project.

## 6.1 Problem Description

High-Concurrency Ticket Overselling merupakan kondisi ketika banyak pengguna mencoba memperoleh tiket dengan kuota terbatas pada waktu yang hampir bersamaan.

Jika sistem hanya melakukan pemeriksaan kuota tanpa mekanisme transaksi dan locking yang benar, beberapa permintaan dapat membaca sisa kuota yang sama dan semuanya menganggap tiket masih tersedia.

Hal ini dapat menyebabkan jumlah tiket yang diterbitkan melebihi kapasitas event.

## 6.2 Required Extreme Scenario

Skenario wajib:

| Parameter | Nilai |
|---|---:|
| Sisa kuota awal | 5 tiket |
| Jumlah permintaan | 100 mahasiswa |
| Waktu permintaan | Bersamaan |
| Target transaksi berhasil | 5 |
| Target transaksi gagal | 95 |
| Kuota akhir | 0 |
| Overselling | 0 |

## 6.3 Expected System Behavior

1. Seratus permintaan diterima oleh sistem dalam waktu bersamaan.
2. Sistem memproses alokasi kuota menggunakan mekanisme transaksi database yang aman.
3. Sistem memastikan bahwa setiap alokasi tiket dilakukan berdasarkan ketersediaan kuota terbaru.
4. Hanya lima transaksi yang boleh berhasil.
5. Sembilan puluh lima transaksi lainnya harus ditolak dengan respons yang sesuai.
6. Kuota akhir harus bernilai nol.
7. Tidak boleh terdapat tiket tambahan di luar kapasitas event.
8. Tidak boleh terdapat perubahan kuota yang tidak konsisten akibat transaksi gagal.

## 6.4 Required Engineering Solutions

Sistem harus menerapkan:

- Database transaction.
- ACID properties.
- Database locking atau mekanisme concurrency control yang sesuai.
- Validasi kuota secara atomik.
- Rollback untuk transaksi gagal.
- Penanganan race condition.
- Pengujian permintaan secara bersamaan.

Pemilihan teknik locking dan implementasi teknis akan dijelaskan lebih lanjut dalam `ARCHITECTURE.md`.

## 6.5 ACID Requirements

| Property | Requirement |
|---|---|
| Atomicity | Pengurangan kuota dan pencatatan pendaftaran harus berhasil bersama atau dibatalkan bersama. |
| Consistency | Kuota dan jumlah tiket harus selalu memenuhi aturan kapasitas. |
| Isolation | Transaksi bersamaan tidak boleh menyebabkan alokasi kuota melebihi kapasitas. |
| Durability | Data transaksi yang berhasil di-commit harus tersimpan secara permanen. |

## 6.6 Edge Cases

Sistem wajib menangani kondisi berikut:

1. Dua atau lebih mahasiswa melakukan klaim ketika hanya tersisa satu tiket.
2. Seratus mahasiswa melakukan klaim ketika tersisa lima tiket.
3. Pengguna mengirim permintaan pendaftaran berulang kali.
4. Koneksi terputus saat transaksi berlangsung.
5. Transaksi gagal ketika kuota sedang diproses.
6. Pengguna mencoba mendaftar ketika kuota habis.
7. Pengguna mencoba mengakses event yang sudah ditutup.
8. Pembayaran simulasi gagal setelah proses pendaftaran dimulai.
9. Dua petugas melakukan check-in terhadap tiket yang sama secara bersamaan.
10. Pengguna mencoba menggunakan tiket milik event lain.

---

# 7. NON-FUNCTIONAL REQUIREMENTS

## 7.1 Security

- NFR-01: Password harus disimpan menggunakan mekanisme hashing.
- NFR-02: Sistem harus menerapkan autentikasi dan otorisasi berdasarkan role.
- NFR-03: Endpoint REST API harus dilindungi sesuai kebutuhan akses.
- NFR-04: Input pengguna harus divalidasi dan disanitasi sesuai konteks.
- NFR-05: Sistem harus melindungi proses perubahan data dari akses yang tidak berwenang.
- NFR-06: Data sensitif tidak boleh ditampilkan kepada pengguna yang tidak memiliki izin.

## 7.2 Reliability and Data Integrity

- NFR-07: Sistem harus menjaga konsistensi data pada proses pendaftaran.
- NFR-08: Sistem tidak boleh menghasilkan kuota negatif.
- NFR-09: Sistem harus mencegah penerbitan tiket melebihi kapasitas.
- NFR-10: Sistem harus mencegah penggunaan tiket berulang.
- NFR-11: Kegagalan transaksi harus ditangani dengan mekanisme rollback.

## 7.3 Performance

- NFR-12: Sistem harus dapat menangani skenario 100 permintaan pendaftaran secara bersamaan untuk pengujian kuota lima tiket.
- NFR-13: Pengujian concurrency harus membuktikan tidak terjadi overselling.
- NFR-14: Proses validasi kuota harus dilakukan pada sisi backend dan database.

## 7.4 Usability

- NFR-15: Antarmuka harus mudah dipahami oleh mahasiswa dan pengelola event.
- NFR-16: Website harus responsif pada perangkat desktop dan mobile.
- NFR-17: Sistem harus menampilkan pesan keberhasilan dan kegagalan secara jelas.

## 7.5 Maintainability

- NFR-18: Source code harus memiliki struktur yang terorganisasi.
- NFR-19: Penamaan variabel, fungsi, dan komponen harus konsisten.
- NFR-20: Setiap modul harus memiliki tanggung jawab yang jelas.
- NFR-21: Dokumentasi teknis harus tersedia untuk mendukung pengembangan.

---

# 8. USE CASE SPECIFICATION

## 8.1 Main Use Cases

| Actor | Use Case |
|---|---|
| Super Admin | Login, kelola pengguna, kelola organisasi, pantau platform |
| Admin Organisasi | Login, kelola event, atur kuota, pantau pendaftaran, lihat laporan |
| Petugas Check-in | Login, scan QR Code, validasi tiket, catat kehadiran |
| Mahasiswa | Registrasi, login, cari event, daftar event, bayar simulasi, lihat tiket |

## 8.2 Use Case: Register Event

**Actor:** Mahasiswa

**Preconditions:**
- Mahasiswa telah login.
- Event tersedia.
- Periode pendaftaran masih berlangsung.

**Main Flow:**
1. Mahasiswa memilih event.
2. Sistem menampilkan detail event.
3. Mahasiswa menekan tombol pendaftaran.
4. Sistem memeriksa kelayakan pendaftaran.
5. Sistem memeriksa ketersediaan kuota secara transaksional.
6. Sistem mengalokasikan tiket apabila kuota tersedia.
7. Sistem menyimpan data pendaftaran.
8. Sistem melanjutkan ke proses pembayaran simulasi jika event berbayar.
9. Sistem menerbitkan tiket setelah seluruh persyaratan terpenuhi.
10. Sistem menampilkan hasil pendaftaran.

**Alternative Flow:**
- Jika kuota habis, sistem menolak pendaftaran.
- Jika pengguna telah terdaftar, sistem menolak pendaftaran ganda.
- Jika periode pendaftaran berakhir, sistem menolak pendaftaran.
- Jika transaksi gagal, sistem melakukan rollback.

## 8.3 Use Case: Ticket Check-in

**Actor:** Petugas Check-in

**Preconditions:**
- Petugas telah login.
- Event tersedia.
- Tiket telah diterbitkan.

**Main Flow:**
1. Petugas membuka fitur check-in.
2. Petugas memindai QR Code.
3. Sistem memeriksa identitas tiket.
4. Sistem memeriksa event yang sesuai.
5. Sistem memeriksa status penggunaan tiket.
6. Sistem mencatat kehadiran.
7. Sistem menampilkan status check-in berhasil.

**Alternative Flow:**
- QR Code tidak valid: sistem menolak tiket.
- Tiket berasal dari event berbeda: sistem menolak tiket.
- Tiket sudah digunakan: sistem menampilkan bahwa tiket telah digunakan.
- Tiket belum memenuhi ketentuan pembayaran: sistem menolak check-in.

---

# 9. DATA REQUIREMENTS

Entitas utama yang dibutuhkan:

| Entity | Description |
|---|---|
| Users | Data akun dan role pengguna |
| Organizations | Data organisasi penyelenggara |
| Events | Informasi kegiatan |
| Registrations | Data pendaftaran peserta |
| Tickets | Data tiket digital |
| Payments | Data simulasi pembayaran |
| Attendances | Data kehadiran peserta |

Relasi konseptual:

- Satu organisasi dapat memiliki banyak event.
- Satu pengguna dapat memiliki banyak pendaftaran.
- Satu event dapat memiliki banyak pendaftaran.
- Satu pendaftaran dapat menghasilkan maksimal satu tiket.
- Satu pendaftaran berbayar dapat memiliki catatan pembayaran.
- Satu tiket dapat memiliki maksimal satu catatan check-in.

Detail atribut, tipe data, foreign key, unique constraints, dan rancangan ERD akan dijelaskan dalam `ARCHITECTURE.md`.

---

# 10. ACCEPTANCE CRITERIA

Sistem dinyatakan memenuhi kebutuhan apabila:

| ID | Acceptance Criteria | Expected Result |
|---|---|---|
| AC-01 | Mahasiswa melakukan registrasi | Akun berhasil dibuat |
| AC-02 | Pengguna login | Sistem memberikan akses sesuai role |
| AC-03 | Admin membuat event | Event tersimpan |
| AC-04 | Mahasiswa melihat event | Informasi event tampil |
| AC-05 | Mahasiswa mendaftar event | Pendaftaran diproses |
| AC-06 | Mahasiswa mendaftar dua kali | Pendaftaran duplikat ditolak |
| AC-07 | Kuota event habis | Pendaftaran ditolak |
| AC-08 | Pendaftaran berhasil | Tiket diterbitkan sesuai ketentuan |
| AC-09 | Pembayaran simulasi gagal | Tiket berbayar tidak diterbitkan |
| AC-10 | QR Code valid | Tiket dapat diverifikasi |
| AC-11 | QR Code digunakan kembali | Check-in ditolak |
| AC-12 | Admin melihat laporan | Data pendaftaran dan kehadiran tampil |

## 10.1 Critical Acceptance Test: Concurrency

**Test Case:** TC-CON-001

**Scenario:** 100 mahasiswa melakukan klaim tiket secara bersamaan ketika tersisa lima tiket.

**Initial Condition:**
- Kuota awal: 5.
- Data pendaftaran aktif: 0.
- Jumlah pengguna yang mengirim permintaan: 100.

**Execution:**
- Kirim 100 permintaan pendaftaran secara bersamaan menggunakan alat pengujian beban.
- Tunggu hingga seluruh transaksi selesai.
- Periksa hasil respons dan kondisi database.

**Expected Result:**

| Parameter | Expected |
|---|---:|
| Successful registration | 5 |
| Rejected registration | 95 |
| Final remaining quota | 0 |
| Total issued tickets | 5 |
| Overselling | 0 |

Pengujian harus membuktikan bahwa hanya lima transaksi yang berhasil di-commit, tanpa memperhatikan urutan kedatangan permintaan yang bersamaan.

---

# 11. SYSTEM CONSTRAINTS

1. Sistem dikembangkan sebagai aplikasi berbasis web.
2. Backend menggunakan Laravel.
3. Frontend menggunakan Vue.js atau React, dengan pilihan final ditetapkan dalam dokumen arsitektur.
4. Database menggunakan MySQL.
5. Komunikasi frontend dan backend menggunakan REST API.
6. Sistem mengikuti arsitektur 3-Tier.
7. Database harus mendukung transaksi ACID.
8. Sistem harus menggunakan relational database.
9. Pengembangan dilakukan melalui VS Code dengan bantuan AI coding assistant.
10. Source code harus disimpan dalam repository Git.

---

# 12. PROJECT DELIVERABLES

Hasil akhir proyek meliputi:

## 12.1 Application
Aplikasi CampusEvent yang dapat digunakan untuk:
- Mengelola organisasi dan event.
- Mendaftar kegiatan.
- Mengelola kuota tiket.
- Menerbitkan tiket digital.
- Melakukan check-in.
- Melihat laporan.

## 12.2 Source Code
- Source code frontend.
- Source code backend.
- Database migrations dan konfigurasi.
- Dokumentasi instalasi dan penggunaan.

## 12.3 Documentation
Dokumentasi Markdown yang terdiri dari:

- REQUIREMENTS.md
- ARCHITECTURE.md
- RULES.md
- TASKS.md

## 12.4 Final Presentation
Presentasi akhir harus menunjukkan:
- Gambaran sistem.
- Arsitektur aplikasi.
- Fitur utama.
- Implementasi transaksi ACID.
- Demonstrasi pengujian 100 permintaan dengan lima tiket.
- Hasil pengujian dan evaluasi sistem.

---

# 13. REQUIREMENTS PRIORITY

| Priority | Description |
|---|---|
| P0 - Critical | Fitur utama dan Hard Problem wajib berhasil |
| P1 - High | Fitur pendukung utama |
| P2 - Medium | Penyempurnaan dan peningkatan pengalaman pengguna |

Prioritas P0:
- Authentication dan role management.
- Organization dan event management.
- Registration dan quota management.
- ACID transaction dan concurrency control.
- Digital ticket dan QR Code.
- Check-in validation.

Prioritas P1:
- Dashboard.
- Reporting.
- Payment simulation.
- Event filtering dan pencarian.

Prioritas P2:
- Notifikasi.
- Penyempurnaan UI/UX.
- Fitur tambahan yang tidak mengganggu penyelesaian kebutuhan utama.

---

# 14. REQUIREMENTS APPROVAL

Dokumen ini menjadi dasar kebutuhan bisnis dan fungsional CampusEvent.

Setiap implementasi harus mengacu pada requirement yang telah ditetapkan. Jika ditemukan kebutuhan yang belum terpenuhi atau konflik teknis, perubahan harus dievaluasi dan didokumentasikan sebelum diterapkan.

**Project:** CampusEvent  
**Document:** REQUIREMENTS.md  
**Version:** 1.0  
**Status:** Initial Approved Specification

---

*End of Document*
