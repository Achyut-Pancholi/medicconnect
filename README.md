# MediConnect — EHR & Referral Management System

A production-ready **Electronic Health Records** and **Inter-Hospital Referral Management** system built with **Laravel 11** and **PHP 8.2+**, demonstrating advanced backend engineering, data privacy, and HIPAA-style audit compliance.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 11 |
| Language | PHP 8.2+ |
| Database | MySQL 8+ |
| Auth | Laravel Session Auth + Policies |
| PDF | barryvdh/laravel-dompdf |
| Frontend | Vanilla HTML / CSS / JS (AJAX) |

---

## Features

- **Role-Based Access Control** — GP, Specialist, Admin roles with Laravel Policies
- **Encrypted Sensitive Fields** — `emergency_contact` and `treatment_plan` encrypted at rest via custom Eloquent Cast
- **HIPAA-Style Audit Log** — Every `MedicalRecord::retrieved` event is logged (user, IP, timestamp)
- **Referral Pipeline** — Kanban board (Pending → In Review → Completed) with urgency escalation scheduler
- **Real-Time Patient Search** — AJAX-powered search by MRN or patient name
- **Secure Lab Report Storage** — Private-disk uploads, authenticated download-only routes
- **PDF Prescription Export** — dompdf-rendered printable prescriptions per medical record
- **Force Password Change** — Middleware detects default password and forces reset on first login

---

## Quick Start

### Prerequisites

```bash
php -v          # 8.2+
composer -V
mysql --version # 8+
git --version
```

### 1. Clone & Install

```bash
git clone <repo-url>
cd medicconnect
composer install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your DB credentials:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medicconnect
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Migrate & Seed

```bash
php artisan migrate --seed
```

This creates all tables and seeds:
- 1 Admin, 3 GPs, 3 Specialists
- 10 patients with realistic medical records
- 5 referrals across all urgency levels

### 4. Run

```bash
php artisan serve
```

Visit `http://localhost:8000`

---

## Default Credentials

> **All accounts use the default password `ChangeMe@123`.** The `ForcePasswordChange` middleware will prompt a reset on first login.

| Role | Email |
|---|---|
| Admin | admin@mediconnect.test |
| GP 1 | gp1@mediconnect.test |
| GP 2 | gp2@mediconnect.test |
| GP 3 | gp3@mediconnect.test |
| Specialist (Cardiology) | spec1@mediconnect.test |
| Specialist (Neurology) | spec2@mediconnect.test |
| Specialist (Oncology) | spec3@mediconnect.test |

---

## Architecture

```
app/
├── Casts/              # EncryptedFieldCast (Crypt facade, reversible)
├── Http/
│   ├── Controllers/    # DashboardController, PatientController,
│   │                   # PrescriptionController, LabReportController
│   └── Middleware/     # ForcePasswordChange
├── Models/             # User, Patient, MedicalRecord, Referral, AuditLog
├── Observers/          # HIPAAAuditObserver (retrieved event -> audit_logs)
├── Policies/           # MedicalRecordPolicy, ReferralPolicy
└── Services/           # ReferralManagementService (urgency escalation)
```

---

## Security Highlights

| Area | Implementation |
|---|---|
| Field encryption | `EncryptedFieldCast` via Laravel `Crypt` (AES-256-CBC) |
| Access control | Laravel Policies (GP/Specialist/Admin-scoped) |
| HIPAA audit | Observer logs every record retrieval to `audit_logs` |
| File isolation | Lab reports on private disk, never publicly accessible |
| Password policy | Default-password detection + forced change middleware |

---

## Scheduler (Urgency Escalation)

Add to your server's crontab:

```
* * * * * cd /path/to/medicconnect && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler runs hourly and promotes overdue referrals: `Routine -> Urgent -> Emergency`.
