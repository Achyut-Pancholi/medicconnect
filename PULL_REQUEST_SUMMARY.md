# Pull Request: MediConnect EHR & Referral System

**Branch:** `feature/ehr-core` → `main`

---

## Overview

This PR implements a production-ready **Electronic Health Records (EHR) and Inter-Hospital Referral Management System** in Laravel 11 / PHP 8.2+, fulfilling all technical assessment requirements.

---

## Technical Decisions

### 1. Architecture: Service Layer Pattern
A `ReferralManagementService` was extracted from the controller to encapsulate urgency escalation logic. This keeps controllers thin and makes the service independently testable and schedulable.

### 2. Encryption: Custom Eloquent Cast
Instead of encrypting manually in each controller, a custom `EncryptedFieldCast` was implemented. Any model field can be encrypted at rest by simply declaring:
```php
protected function casts(): array {
    return ['emergency_contact_enc' => EncryptedFieldCast::class];
}
```
This ensures encryption is applied consistently and transparently via the Eloquent ORM layer.

### 3. HIPAA Audit Trail: Observer Pattern
Using a Laravel Observer (`HIPAAAuditObserver`) on the `MedicalRecord` model ensures every `retrieved` event logs automatically without any controller intervention. This is far more reliable than manual logging calls.

### 4. Data Privacy: Role-Scoped Queries
A Specialist logging in cannot see or search for patients that haven't been referred to them. This is enforced at two levels:
- **Search API**: `PatientController::search()` filters results via `whereHas('referrals', ...)` for Specialists
- **Route Gate**: Direct URL access to `/patients/{id}/records` returns 403 via `abort_unless()`

### 5. Policies vs. Middleware
Authorization uses Laravel Policies (`MedicalRecordPolicy`, `ReferralPolicy`) rather than middleware, as policies allow fine-grained, model-level access control per role — more appropriate for EHR data than broad route-level guards.

### 6. File Storage: Private Disk
The `lab_reports` disk is configured without a `url` or `visibility` key, ensuring it is unreachable via any public URL. All retrieval is handled through `Storage::disk('lab_reports')->response()` behind an authenticated, policy-checked route.

---

## Key Files Changed

| File | Purpose |
|---|---|
| `app/Casts/EncryptedFieldCast.php` | AES-256 transparent encryption for model fields |
| `app/Observers/HIPAAAuditObserver.php` | HIPAA-style audit logging on every record access |
| `app/Services/ReferralManagementService.php` | Urgency escalation logic (Routine → Urgent → Emergency) |
| `app/Http/Middleware/ForcePasswordChange.php` | Default password enforcement middleware |
| `app/Policies/MedicalRecordPolicy.php` | Role-based access control for medical records |
| `app/Http/Controllers/LabReportController.php` | Secure PDF/DICOM upload and retrieval |
| `config/filesystems.php` | Private `lab_reports` disk definition |
| `database/seeders/DatabaseSeeder.php` | 3 GPs, 3 Specialists, 10 patients with varied medical histories |

---

## Testing

See `TESTING_GUIDE.md` for full step-by-step validation instructions.

**Quick test credentials:**
- GP: `gp1@mediconnect.test` / `ChangeMe@123`
- Specialist: `spec1@mediconnect.test` / `ChangeMe@123`
- Admin: `admin@mediconnect.test` / `ChangeMe@123`
