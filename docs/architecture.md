# ARCHITECTURE.md

# CampusEvent
## System Architecture & Technical Design

**Document Type:** Software Architecture Specification  
**Version:** 1.0  
**Project:** CampusEvent  
**Status:** Initial Approved Architecture  
**Architecture Pattern:** 3-Tier Architecture + MVC REST API

---

# 1. ARCHITECTURE OVERVIEW

## 1.1 Architecture Description

CampusEvent menggunakan arsitektur 3-Tier yang memisahkan aplikasi menjadi tiga lapisan utama:

1. Client Application (Presentation Layer).
2. Business Logic (Application Layer).
3. Relational Database (Data Layer).

Pemisahan ini bertujuan untuk menjaga struktur aplikasi tetap modular, mempermudah pemeliharaan, serta memastikan proses bisnis dan pengelolaan data berjalan secara terorganisasi.

Frontend dan backend berkomunikasi menggunakan REST API dengan format pertukaran data JSON.

## 1.2 Architecture Objectives

- Memisahkan tanggung jawab frontend, backend, dan database.
- Menerapkan struktur MVC pada backend.
- Menjaga integritas data menggunakan transaksi ACID.
- Mencegah ticket overselling dalam kondisi permintaan bersamaan.
- Menyediakan mekanisme reservasi tiket sementara.
- Menjamin validitas tiket digital dan proses check-in.
- Mempermudah pengembangan dan pengujian setiap modul.

---

# 2. TECHNOLOGY STACK

| Layer | Technology | Responsibility |
|---|---|---|
| Frontend | Vue.js 3 | User Interface |
| Build Tool | Vite | Frontend Development |
| HTTP Client | Axios | REST API Communication |
| Backend | Laravel | Application Backend |
| Architecture | MVC | Business Logic Organization |
| API | REST API | Client-Server Communication |
| Database | MySQL InnoDB | Relational Data Storage |
| QR Code | QR Code Library | Digital Ticket Generation |
| Scheduler | Laravel Scheduler | Reservation Expiration |
| Runtime | PHP, Node.js | Application Execution |
| Version Control | Git | Source Code Management |

Frontend dan backend dikembangkan sebagai aplikasi terpisah dalam satu repository atau struktur proyek yang terorganisasi.

---

# 3. THREE-TIER ARCHITECTURE

## 3.1 Tier 1 — Client Application

**Technology:** Vue.js 3 + Vite

Client Application merupakan lapisan yang berinteraksi langsung dengan pengguna.

Responsibilities:

- Menampilkan halaman aplikasi.
- Mengelola komponen antarmuka.
- Menangani navigasi halaman.
- Melakukan validasi input awal.
- Mengelola state aplikasi.
- Mengirim request ke REST API menggunakan Axios.
- Menampilkan respons dari backend.
- Menampilkan informasi status pendaftaran dan tiket.

Frontend tidak memiliki kewenangan untuk menentukan ketersediaan kuota secara final.

Seluruh validasi kritis harus dilakukan kembali oleh backend dan database.

## 3.2 Tier 2 — Business Logic

**Technology:** Laravel MVC + REST API

Lapisan ini bertanggung jawab terhadap seluruh proses bisnis CampusEvent.

Components:

| Component | Responsibility |
|---|---|
| Routes | Mendefinisikan endpoint API |
| Controllers | Menerima dan mengatur request |
| Models | Merepresentasikan data database |
| Services | Menangani logika bisnis |
| Middleware | Mengatur autentikasi dan otorisasi |
| Form Requests | Memvalidasi input |
| Jobs / Scheduler | Menangani proses terjadwal |

Business logic utama:

- Authentication dan authorization.
- Organization management.
- Event management.
- Registration management.
- Temporary reservation.
- Payment simulation.
- Ticket generation.
- QR validation.
- Check-in.
- Reporting.

Proses kritis seperti alokasi kuota dan konfirmasi reservasi harus dikelola melalui service khusus dan transaksi database.

## 3.3 Tier 3 — Relational Database

**Technology:** MySQL InnoDB

Database merupakan lapisan penyimpanan seluruh data aplikasi.

Responsibilities:

- Menyimpan data pengguna.
- Menyimpan data organisasi.
- Menyimpan data event.
- Menyimpan pendaftaran.
- Menyimpan reservasi tiket.
- Menyimpan pembayaran.
- Menyimpan tiket digital.
- Menyimpan kehadiran.
- Menjamin konsistensi relasi dan integritas data.

Database menggunakan MySQL InnoDB karena mendukung transaksi ACID, foreign key, dan row-level locking.

---

# 4. SYSTEM ARCHITECTURE DIAGRAM

```mermaid
flowchart TB
    U[Users]

    subgraph CLIENT["TIER 1 - CLIENT APPLICATION"]
        V[Vue.js 3 + Vite]
        AX[Axios HTTP Client]
    end

    subgraph APP["TIER 2 - BUSINESS LOGIC"]
        API[Laravel REST API]
        MW[Middleware and Validation]
        CTRL[Controllers]
        SVC[Business Services]
        AUTH[Authentication Service]
        RS[Reservation Service]
        PAY[Payment Service]
        TKT[Ticket Service]
        CI[Check-in Service]
    end

    subgraph DATA["TIER 3 - RELATIONAL DATABASE"]
        DB[(MySQL InnoDB)]
        TX[ACID Transactions]
        LK[Row-Level Locking]
    end

    SCH[Laravel Scheduler]

    U --> V
    V --> AX
    AX <--> API
    API --> MW
    MW --> CTRL
    CTRL --> SVC

    SVC --> AUTH
    SVC --> RS
    SVC --> PAY
    SVC --> TKT
    SVC --> CI

    RS --> TX
    PAY --> TX
    TKT --> TX
    CI --> TX

    TX --> LK
    LK <--> DB
    SCH --> RS
```

---

# 5. DATABASE ARCHITECTURE

## 5.1 Database Design Principles

Database CampusEvent dirancang menggunakan relational database dengan prinsip:

1. Setiap tabel memiliki primary key.
2. Relasi antartabel menggunakan foreign key.
3. Data penting memiliki constraint yang sesuai.
4. Data transaksi harus konsisten.
5. Proses alokasi kuota harus menggunakan mekanisme concurrency control.
6. Informasi waktu reservasi menggunakan timestamp yang konsisten.
7. Indeks digunakan pada kolom yang sering digunakan untuk pencarian dan relasi.

## 5.2 Entity Overview

| Entity | Description |
|---|---|
| users | Data pengguna |
| organizations | Data organisasi |
| organization_members | Relasi pengguna dan organisasi |
| events | Data kegiatan |
| registrations | Data pendaftaran |
| payments | Data pembayaran simulasi |
| tickets | Data tiket digital |
| attendances | Data kehadiran |

## 5.3 Entity Relationship Diagram

```mermaid
erDiagram
    USERS ||--o{ ORGANIZATION_MEMBERS : joins
    ORGANIZATIONS ||--o{ ORGANIZATION_MEMBERS : has
    ORGANIZATIONS ||--o{ EVENTS : organizes
    USERS ||--o{ REGISTRATIONS : creates
    EVENTS ||--o{ REGISTRATIONS : receives
    REGISTRATIONS ||--o| PAYMENTS : has
    REGISTRATIONS ||--o| TICKETS : generates
    TICKETS ||--o| ATTENDANCES : records

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        string role
    }

    ORGANIZATIONS {
        bigint id PK
        string name
        string type
        string status
    }

    ORGANIZATION_MEMBERS {
        bigint id PK
        bigint user_id FK
        bigint organization_id FK
        string role
    }

    EVENTS {
        bigint id PK
        bigint organization_id FK
        string title
        text description
        datetime registration_start
        datetime registration_end
        datetime event_start
        datetime event_end
        int capacity
        int reserved_count
        int sold_count
        decimal ticket_price
        string status
    }

    REGISTRATIONS {
        bigint id PK
        bigint user_id FK
        bigint event_id FK
        string status
        datetime created_at
    }

    PAYMENTS {
        bigint id PK
        bigint registration_id FK
        decimal amount
        string status
        datetime paid_at
    }

    TICKETS {
        bigint id PK
        bigint registration_id FK
        string ticket_code UK
        string token_hash UK
        string status
    }

    ATTENDANCES {
        bigint id PK
        bigint ticket_id FK
        bigint officer_id FK
        datetime checked_in_at
    }
```

Catatan: Diagram merupakan rancangan konseptual. Implementasi final harus memperhatikan unique constraints, indeks, tipe data, dan aturan status yang dijelaskan berikutnya.

## 5.4 Data Integrity Rules

### Events

- `capacity` harus bernilai lebih besar dari nol.
- `reserved_count` tidak boleh negatif.
- `sold_count` tidak boleh negatif.
- `reserved_count + sold_count` tidak boleh melebihi `capacity`.

### Registrations

- Satu pengguna hanya boleh memiliki satu pendaftaran aktif untuk event yang sama.
- Setiap pendaftaran harus terhubung dengan pengguna dan event.
- Status pendaftaran harus mengikuti siklus bisnis yang ditetapkan.

### Payments

- Setiap pembayaran harus terhubung dengan pendaftaran.
- Nominal pembayaran harus sesuai harga tiket.
- Status pembayaran harus tercatat secara konsisten.

### Tickets

- Setiap tiket memiliki kode unik.
- Token tiket tidak boleh dapat ditebak.
- Tiket harus terhubung dengan pendaftaran yang valid.
- Satu tiket hanya dapat digunakan satu kali.

### Attendances

- Satu tiket hanya boleh memiliki satu catatan check-in.
- Check-in harus dilakukan oleh petugas yang memiliki otorisasi.

## 5.5 Recommended Indexes and Constraints

| Table | Index / Constraint | Purpose |
|---|---|---|
| users | UNIQUE(email) | Mencegah email ganda |
| organization_members | UNIQUE(organization_id, user_id) | Mencegah keanggotaan ganda |
| events | INDEX(organization_id, status) | Pencarian event |
| registrations | UNIQUE(event_id, user_id) | Mencegah pendaftaran ganda |
| registrations | INDEX(event_id, status) | Monitoring peserta |
| payments | UNIQUE(registration_id) | Satu catatan pembayaran |
| tickets | UNIQUE(registration_id) | Satu tiket per pendaftaran |
| tickets | UNIQUE(ticket_code) | Kode tiket unik |
| tickets | UNIQUE(token_hash) | Token unik |
| attendances | UNIQUE(ticket_id) | Mencegah check-in ganda |

Untuk mendukung pendaftaran ulang setelah reservasi kedaluwarsa, sistem dapat memperbarui siklus status pada satu record pendaftaran, bukan membuat record aktif kedua.

---

# 6. DATA FLOW ARCHITECTURE

## 6.1 Event Creation Flow

1. Admin Organisasi login.
2. Admin membuka halaman pembuatan event.
3. Frontend mengirim data melalui Axios.
4. Laravel memvalidasi request.
5. Authorization memastikan admin memiliki akses.
6. Event Service memproses data.
7. Data disimpan ke MySQL.
8. Backend mengembalikan respons.
9. Frontend menampilkan event yang berhasil dibuat.

## 6.2 Event Registration Flow

1. Mahasiswa login.
2. Mahasiswa memilih event.
3. Frontend mengirim request pendaftaran.
4. Backend memeriksa identitas dan kelayakan pengguna.
5. Backend memeriksa periode pendaftaran.
6. Registration Service memproses reservasi kuota.
7. Database menjalankan transaksi dan locking.
8. Jika kuota tersedia, sistem mencatat reservasi sementara.
9. Transaksi di-commit.
10. Backend mengembalikan status reservasi.

Jika kuota tidak tersedia, backend mengembalikan respons penolakan.

## 6.3 Payment Flow

1. Mahasiswa memiliki reservasi sementara.
2. Sistem menampilkan informasi pembayaran simulasi.
3. Mahasiswa memilih tindakan pembayaran.
4. Backend memvalidasi status dan masa berlaku reservasi.
5. Payment Service memproses simulasi pembayaran.
6. Jika berhasil, reservasi dikonfirmasi.
7. Sistem mengubah status pendaftaran.
8. Sistem mengubah alokasi kuota dari reserved menjadi sold.
9. Sistem menerbitkan tiket digital.
10. Backend mengembalikan informasi tiket.

Jika pembayaran gagal atau reservasi telah kedaluwarsa, sistem tidak menerbitkan tiket.

## 6.4 Check-in Flow

1. Petugas login.
2. Petugas membuka fitur check-in.
3. Petugas memindai QR Code.
4. Frontend mengirim token tiket ke backend.
5. Backend memverifikasi token.
6. Backend memeriksa status tiket dan event.
7. Backend memeriksa apakah tiket telah digunakan.
8. Jika valid, sistem mencatat kehadiran.
9. Sistem menampilkan status check-in.

---

# 7. REST API ARCHITECTURE

## 7.1 API Convention

Base URL:

`/api/v1`

Communication format:

- Request: JSON.
- Response: JSON.
- Authentication: Token-based authentication.
- HTTP methods: GET, POST, PUT/PATCH, DELETE.

Seluruh endpoint yang membutuhkan autentikasi harus dilindungi middleware yang sesuai.

## 7.2 API Endpoint Design

### Authentication

| Method | Endpoint | Function |
|---|---|---|
| POST | /auth/register | Registrasi |
| POST | /auth/login | Login |
| POST | /auth/logout | Logout |
| GET | /auth/me | Profil pengguna |

### Organizations

| Method | Endpoint | Function |
|---|---|---|
| GET | /organizations | Daftar organisasi |
| POST | /organizations | Membuat organisasi |
| GET | /organizations/{id} | Detail organisasi |
| PUT | /organizations/{id} | Mengubah organisasi |
| DELETE | /organizations/{id} | Menghapus organisasi |

### Events

| Method | Endpoint | Function |
|---|---|---|
| GET | /events | Daftar event |
| POST | /events | Membuat event |
| GET | /events/{id} | Detail event |
| PUT | /events/{id} | Mengubah event |
| DELETE | /events/{id} | Menghapus event |

### Registrations and Reservations

| Method | Endpoint | Function |
|---|---|---|
| POST | /events/{id}/reservations | Membuat reservasi |
| GET | /registrations | Riwayat pendaftaran |
| GET | /registrations/{id} | Detail pendaftaran |
| POST | /registrations/{id}/cancel | Membatalkan reservasi jika diizinkan |

### Payments

| Method | Endpoint | Function |
|---|---|---|
| POST | /payments/{id}/simulate | Simulasi pembayaran |
| GET | /payments/{id} | Status pembayaran |

### Tickets and Check-in

| Method | Endpoint | Function |
|---|---|---|
| GET | /tickets | Daftar tiket |
| GET | /tickets/{id} | Detail tiket |
| POST | /check-in/validate | Validasi dan check-in |

### Reports

| Method | Endpoint | Function |
|---|---|---|
| GET | /reports/dashboard | Statistik dashboard |
| GET | /reports/events/{id} | Laporan event |
| GET | /reports/attendance/{id} | Laporan kehadiran |

Endpoint dapat disesuaikan selama implementasi dengan tetap mempertahankan fungsi dan batas aksesnya.

---

# 8. TRANSACTION AND ACID ARCHITECTURE

## 8.1 Transaction Strategy

Seluruh proses yang melibatkan perubahan data kritis harus menggunakan transaksi database.

Proses tersebut meliputi:

- Alokasi kuota.
- Pembuatan reservasi.
- Konfirmasi pembayaran.
- Penerbitan tiket.
- Pengembalian kuota.
- Pencatatan check-in.

## 8.2 ACID Implementation

### Atomicity

Operasi yang saling bergantung harus berhasil bersama atau dibatalkan bersama.

Contoh:

Pengurangan kuota dan pembuatan reservasi harus berada dalam transaksi yang sama.

### Consistency

Setiap transaksi harus menjaga aturan integritas data.

Contoh:

Jumlah tiket terjual dan reservasi aktif tidak boleh melebihi kapasitas event.

### Isolation

Transaksi bersamaan harus diproses tanpa menyebabkan konflik alokasi kuota.

Implementasi menggunakan row-level locking melalui `SELECT FOR UPDATE`.

### Durability

Transaksi yang berhasil di-commit harus tetap tersimpan dalam database.

---

# 9. HIGH-CONCURRENCY TICKET RESERVATION

## 9.1 Problem

Sistem harus mampu menangani 100 permintaan pendaftaran bersamaan ketika hanya tersedia lima tiket.

Tanpa concurrency control, beberapa permintaan dapat membaca kuota yang sama dan menyebabkan overselling.

## 9.2 Inventory Model

Kapasitas event menggunakan tiga nilai:

- `capacity`: total kapasitas.
- `reserved_count`: jumlah kuota yang sedang direservasi.
- `sold_count`: jumlah kuota yang telah dikonfirmasi.

Rumus:

`available = capacity - reserved_count - sold_count`

Aturan:

`reserved_count + sold_count <= capacity`

## 9.3 Reservation Algorithm

```text
BEGIN TRANSACTION

1. Lock event inventory row using SELECT FOR UPDATE.

2. Read current capacity, reserved_count,
   and sold_count.

3. Calculate available quota.

4. If available <= 0:
      ROLLBACK
      Return SOLD_OUT.

5. Create or update registration as PENDING.

6. Increment reserved_count by 1.

7. Set reservation expiration time.

8. COMMIT TRANSACTION.

9. Return reservation details.
```

Pemeriksaan pendaftaran ganda harus dilakukan secara aman dan didukung unique constraint database.

## 9.4 Temporary Reservation Policy

Setiap reservasi memiliki masa berlaku:

**10 minutes**

Status reservasi:

- PENDING: reservasi masih aktif.
- CONFIRMED: pembayaran berhasil.
- EXPIRED: waktu reservasi habis.
- CANCELLED: reservasi dibatalkan.

Reservasi yang telah kedaluwarsa tidak boleh dikonfirmasi.

## 9.5 Payment Confirmation

```text
BEGIN TRANSACTION

1. Lock registration row.

2. Verify registration status is PENDING.

3. Verify current time is before expires_at.

4. Validate payment simulation result.

5. Lock event inventory row.

6. Update payment status to PAID.

7. Change registration status to CONFIRMED.

8. Decrease reserved_count by 1.

9. Increase sold_count by 1.

10. Generate digital ticket.

11. COMMIT TRANSACTION.
```

Jika salah satu operasi gagal, seluruh perubahan harus di-rollback.

## 9.6 Reservation Expiration

Laravel Scheduler menjalankan proses pemeriksaan reservasi yang telah kedaluwarsa.

```text
BEGIN TRANSACTION

1. Find expired PENDING registrations.

2. Lock the selected registration.

3. Recheck registration status and expiration time.

4. Lock associated event inventory.

5. Update registration status to EXPIRED.

6. Decrease reserved_count by 1.

7. COMMIT TRANSACTION.
```

Proses ini harus idempotent agar satu reservasi tidak mengembalikan kuota lebih dari satu kali.

Jika proses pembayaran dan expiration berjalan bersamaan, keduanya harus menggunakan urutan locking yang konsisten dan memeriksa ulang status reservasi sebelum melakukan perubahan.

---

# 10. CONCURRENCY CONTROL STRATEGY

## 10.1 SELECT FOR UPDATE

Mekanisme ini digunakan untuk mengunci baris inventory event selama transaksi berlangsung.

Tujuannya:

- Mencegah transaksi lain mengubah alokasi kuota pada baris yang sama secara bersamaan.
- Memastikan pembacaan kuota dilakukan terhadap data yang terkunci.
- Menjaga konsistensi proses alokasi.

Lock hanya dipertahankan selama transaksi database berlangsung, bukan selama pengguna melakukan pembayaran.

## 10.2 Idempotency

Sistem harus menangani permintaan berulang agar satu permintaan tidak menghasilkan reservasi atau pembayaran ganda.

Implementasi dapat menggunakan:

- Unique constraints.
- Idempotency key pada request yang memerlukan perlindungan retry.
- Pemeriksaan status transaksi.
- Operasi update yang bersyarat.

## 10.3 Deadlock Handling

Sistem harus:

- Menggunakan urutan locking yang konsisten.
- Menjaga transaksi tetap singkat.
- Menghindari operasi eksternal di dalam transaksi.
- Menangani deadlock dengan retry terbatas.
- Mengembalikan respons yang sesuai jika transaksi tetap gagal.

---

# 11. QR CODE AND TICKET SECURITY

## 11.1 Token Generation

Setiap tiket memiliki token acak yang tidak mudah ditebak.

Token tidak menggunakan ID database secara langsung sebagai identitas keamanan tiket.

Sistem menyimpan hash token untuk kebutuhan validasi.

## 11.2 Validation Process

1. QR Code dipindai.
2. Token dikirim ke backend.
3. Backend mencocokkan hash token.
4. Sistem memeriksa status tiket.
5. Sistem memeriksa kesesuaian event.
6. Sistem memeriksa status check-in.
7. Jika valid, sistem mencatat kehadiran secara transaksional.

## 11.3 Duplicate Check-in Prevention

Tabel attendances harus memiliki unique constraint pada `ticket_id`.

Proses check-in juga harus menggunakan transaksi dan validasi ulang status tiket agar dua petugas tidak dapat menggunakan tiket yang sama secara bersamaan.

---

# 12. ERROR HANDLING ARCHITECTURE

Sistem harus menyediakan penanganan kesalahan yang konsisten.

| Condition | Expected Response |
|---|---|
| Kuota habis | Registration rejected |
| Reservasi kedaluwarsa | Reservation expired |
| Pendaftaran duplikat | Duplicate registration rejected |
| Pembayaran gagal | Payment failed |
| Token tidak valid | Invalid ticket |
| Tiket telah digunakan | Ticket already checked in |
| Akses tidak sah | Unauthorized / Forbidden |
| Database transaction gagal | Rollback |
| Terjadi deadlock | Retry terbatas atau transaksi gagal secara aman |

Backend harus menggunakan HTTP status code yang sesuai dan tidak membocorkan informasi internal database kepada pengguna.

---

# 13. SECURITY ARCHITECTURE

Security diterapkan pada seluruh lapisan aplikasi.

## 13.1 Frontend Security

- Validasi input awal.
- Proteksi akses berdasarkan role pada sisi antarmuka.
- Penggunaan HTTPS pada deployment.
- Tidak menyimpan kredensial sensitif secara sembarangan.

## 13.2 Backend Security

- Authentication middleware.
- Role-based authorization.
- Input validation.
- Password hashing.
- Proteksi CSRF sesuai mekanisme autentikasi.
- Rate limiting pada endpoint sensitif.
- Validasi kepemilikan data.
- Pengelolaan token secara aman.

## 13.3 Database Security

- Penggunaan parameterized query melalui mekanisme ORM.
- Foreign key constraints.
- Unique constraints.
- Pembatasan hak akses database.
- Pengamanan kredensial database.
- Backup data secara berkala.

---

# 14. TESTING ARCHITECTURE

Pengujian dilakukan pada beberapa tingkatan.

## 14.1 Unit Testing

Menguji fungsi individual:

- Reservation calculation.
- Quota validation.
- Payment simulation.
- Ticket generation.
- Token validation.
- Reservation expiration.

## 14.2 Integration Testing

Menguji interaksi antarmodul:

- Registration dengan inventory.
- Payment dengan reservation.
- Ticket generation dengan registration.
- Check-in dengan ticket.
- API dengan database.

## 14.3 Concurrency Testing

Pengujian utama proyek:

**Test ID:** CON-001

| Parameter | Value |
|---|---:|
| Initial capacity | 5 |
| Reserved count | 0 |
| Sold count | 0 |
| Concurrent requests | 100 |
| Expected successful reservations | 5 |
| Expected rejected requests | 95 |
| Final available quota | 0 |
| Overselling | 0 |

Hasil pengujian harus memverifikasi:

1. Hanya lima reservasi yang berhasil dibuat.
2. Sembilan puluh lima permintaan ditolak.
3. Kuota tidak pernah menjadi negatif.
4. Jumlah reservasi dan tiket tidak melebihi kapasitas.
5. Tidak ada data pendaftaran ganda.
6. Tidak ada transaksi parsial yang merusak integritas data.

Pengujian menggunakan database MySQL InnoDB yang sebenarnya, bukan hanya SQLite atau mock database, karena perilaku locking harus diuji pada engine database target.

## 14.4 Reservation Expiration Testing

Skenario:

- Reservasi berhasil dibuat.
- Waktu reservasi mencapai batas kedaluwarsa.
- Scheduler atau proses expiration dijalankan.
- Status reservasi berubah menjadi EXPIRED.
- Kuota dikembalikan satu kali.
- Reservasi yang sudah kedaluwarsa tidak dapat dikonfirmasi.

## 14.5 Duplicate Check-in Testing

Skenario:

- Tiket valid dipindai pertama kali.
- Sistem mencatat kehadiran.
- Tiket yang sama dipindai kembali.
- Sistem menolak check-in kedua.

---

# 15. DEPLOYMENT AND DEVELOPMENT ARCHITECTURE

## 15.1 Development Environment

Komponen pengembangan:

- Visual Studio Code.
- PHP dan Composer.
- Laravel.
- Node.js dan npm.
- Vue.js dan Vite.
- MySQL.
- Git dan GitHub.

## 15.2 Recommended Project Structure

```text
campus-event/
│
├── backend/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Middleware/
│   │   │   └── Requests/
│   │   ├── Models/
│   │   └── Services/
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   ├── routes/
│   │   └── api.php
│   ├── tests/
│   └── .env.example
│
├── frontend/
│   ├── src/
│   │   ├── assets/
│   │   ├── components/
│   │   ├── views/
│   │   ├── layouts/
│   │   ├── router/
│   │   ├── stores/
│   │   ├── services/
│   │   └── composables/
│   └── package.json
│
├── docs/
│   ├── REQUIREMENTS.md
│   ├── ARCHITECTURE.md
│   ├── RULES.md
│   └── TASKS.md
│
└── README.md
```

Struktur tersebut merupakan rancangan awal dan dapat disesuaikan ketika implementasi dimulai.

---

# 16. ARCHITECTURE DECISIONS

| ID | Decision | Rationale |
|---|---|---|
| ADR-01 | Vue.js 3 + Vite | Frontend modern dan modular |
| ADR-02 | Laravel MVC + REST API | Struktur backend terorganisasi |
| ADR-03 | MySQL InnoDB | Mendukung ACID dan row locking |
| ADR-04 | Axios + JSON | Komunikasi frontend dan backend |
| ADR-05 | SELECT FOR UPDATE | Mengendalikan alokasi kuota bersamaan |
| ADR-06 | Temporary Reservation | Menghindari overselling dan lock berkepanjangan |
| ADR-07 | 10-minute expiration | Memberikan batas waktu reservasi |
| ADR-08 | Random QR Token | Meningkatkan keamanan tiket |
| ADR-09 | Laravel Scheduler | Mengelola reservasi kedaluwarsa |

---

# 17. ARCHITECTURE APPROVAL

Dokumen ARCHITECTURE.md menjadi acuan teknis pengembangan CampusEvent.

Seluruh implementasi harus mengikuti prinsip pemisahan 3-Tier, MVC REST API, integritas database, transaksi ACID, dan concurrency control.

Perubahan arsitektur harus dievaluasi terlebih dahulu agar tidak bertentangan dengan kebutuhan sistem yang telah ditetapkan dalam REQUIREMENTS.md.

**Project:** CampusEvent  
**Document:** ARCHITECTURE.md  
**Version:** 1.0  
**Status:** Initial Approved Architecture

---

*End of Document*