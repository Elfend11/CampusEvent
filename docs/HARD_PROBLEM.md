# THE 30% HARD PROBLEM — DIAGNOSTIC & TEST EVIDENCE

**Project:** CampusEvent  
**Scope:** High concurrency race condition, ACID/rollback, ticket overselling prevention.

---

## 1. Root Cause Analysis

### A. High Concurrency Race Condition

The race occurs when many students reserve the last tickets at the same time. A naive
sequence of `read available quota -> check -> increment` allows two requests to read the
same free slot and both reserve it.

**Mitigation in CampusEvent:**

1. `RegistrationService::register()` runs inside `DB::transaction()` with three retries.
2. The event row is locked with `SELECT ... FOR UPDATE` (`lockForUpdate()`), forcing all
   concurrent reservations to serialize on the same inventory row.
3. The quota is re-checked **after** the lock is acquired (`assertEventAcceptsRegistration()`
   and `availableQuota()`), so stale reads are impossible.
4. `reserved_count` / `sold_count` are incremented on the locked row only.
5. The database invariant `reserved_count + sold_count <= capacity` is preserved.

### B. ACID Transaction and Rollback

- Reservation, payment confirmation, administrative cancellation, expiration and check-in
  are all wrapped in `DB::transaction()`.
- Related rows are locked in a defined order and status is re-read after locking.
- Any exception (payment record failure, ticket issuance failure, deadlock) rolls back the
  whole transaction; no partial registration, quota change or ticket remains.
- MySQL deadlock errors are retried up to three times (Laravel transaction attempts).

### C. Ticket Overselling Prevention

- A single `events` row owns `capacity`, `reserved_count`, `sold_count`; every mutation is
  performed on that locked row.
- `available = capacity - reserved_count - sold_count`, clamped at zero.
- Payment confirmation moves one unit from `reserved_count` to `sold_count` atomically and
  issues exactly one ticket (unique `registration_id`).
- Expiration/cancellation release quota exactly once because the status is re-checked under
  lock and terminal states (`EXPIRED`, `CANCELLED`) are never released again.

### D. Deadlock & Idempotency

- Payment, expiration and administrative cancellation lock **registration first, then the
  event inventory row** (consistent order, per ARCHITECTURE §9.6 and RULES §11.6).
- Payment confirmation is idempotent: a `PAID` payment returns the existing ticket instead of
  issuing a new one.
- Check-in locks the ticket row and checks the existing attendance, so one ticket produces at
  most one attendance row (unique `ticket_id`).
- The expiration command re-checks `isReservationExpired()` and status under lock.

---

## 2. Evidence (MySQL 8 / InnoDB, real concurrent processes)

All scenarios were executed against a real MySQL InnoDB database using separate OS
processes (not SQLite, not mocks).

| Scenario | Command | Observed result |
|---|---|---|
| 100 concurrent registrations, 5 paid tickets | `php tests/Concurrency/run.php` | paid: 5 success, 95 rejected (409); `reserved_count=5`, `sold_count=0`, 5 payments, 0 tickets |
| 100 concurrent registrations, 5 free tickets | `php tests/Concurrency/run.php` | free: 5 success, 95 rejected (409); `sold_count=5`, `reserved_count=0`, 5 tickets |
| 10 concurrent confirmations of the same payment | `php tests/Concurrency/run_payment.php` | all 10 idempotent OK; exactly 1 ticket, `sold_count=1`, `reserved_count=0`, payment `PAID` |
| 20 concurrent check-ins of the same token | `php tests/Concurrency/run_checkin.php` | 1 success, 19 rejected (409); 1 attendance row; ticket `USED` |
| 10 concurrent `registrations:expire` processes over 5 reservations | `php tests/Concurrency/run_expiry.php` | 5 expired, exactly 5 quota releases, `reserved_count=0`, never negative, 0 tickets |

### ACID rollback evidence (feature tests)

`tests/Feature/Integration/TransactionIntegrityTest.php` proves:

- paid reservation rolls back completely when payment record creation fails;
- free registration rolls back completely when ticket issuance fails;
- expiration releases reserved quota exactly once;
- an expired reservation cannot be paid and does not leak quota.

`tests/Feature/Payment/PaymentTest.php` also proves rollback when ticket issuance fails during
payment confirmation.

---

## 3. How to Reproduce

```bash
cd backend

php tests/Concurrency/run.php
php tests/Concurrency/run_payment.php
php tests/Concurrency/run_checkin.php
php tests/Concurrency/run_expiry.php

php artisan test
```

Each concurrency script creates its own temporary MySQL database
(`campusevent_concurrency_*`), boots real worker processes, prints the measured counters and
drops the database afterwards. A non-zero exit code means an invariant was violated.

---

## 4. Conclusion

The hard problem is addressed by database-enforced serialization on a single inventory row,
transactional state transitions, status re-checks after locking, and idempotent confirmation
paths. The measured results show 0 overselling, no negative quota, no duplicate tickets and no
duplicate check-ins under concurrent load.
