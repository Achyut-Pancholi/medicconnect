# MediConnect — Master Testing & Validation Guide

This guide provides exhaustive, step-by-step instructions to verify every technical requirement of the MediConnect system.

---

## 🔑 Test Credentials
| Account | Email | Default Password | Role |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@mediconnect.test` | `ChangeMe@123` | Master access to all records |
| **GP 1** | `gp1@mediconnect.test` | `ChangeMe@123` | Family Medicine Specialist |
| **Spec 1** | `spec1@mediconnect.test` | `ChangeMe@123` | Cardiology Specialist |
| **Spec 2** | `spec2@mediconnect.test` | `ChangeMe@123` | Neurology Specialist |

---

## 📂 Phase 1: Authentication & Account Security

### 1.1 Forced Password Change (Middleware)
*   **Feature:** Enforces a password reset for all users logging in with the default seeded password.
*   **Pre-condition:** Use a fresh database or a user who hasn't changed their password.
*   **Steps:**
    1.  Navigate to `http://localhost:8000`.
    2.  Enter Email: `gp1@mediconnect.test` and Password: `ChangeMe@123`.
    3.  Click "Login".
*   **Expected Result:** 
    *   System redirects immediately to `/password/change`.
    *   A warning message "Please change your default password to continue" is visible.
    *   Access to `/dashboard` is blocked until the form is submitted.
*   **Validation:** Submit the form with a new password. You should be redirected to the Dashboard with a success message.

---

## 🛡️ Phase 2: Data Privacy & Encryption

### 2.1 PII Encryption (At-Rest)
*   **Feature:** `EncryptedFieldCast` ensures sensitive data is scrambled in the database.
*   **Steps:**
    1.  Open your terminal.
    2.  Run Laravel Tinker: `php artisan tinker`.
    3.  Fetch a patient's raw database attributes:
        ```php
        $patient = \App\Models\Patient::first();
        echo $patient->getAttributes()['emergency_contact_enc'];
        ```
*   **Expected Result:** You will see a long alphanumeric string (e.g., `eyJpdiI6Ilp...`). This is the **encrypted** value.
*   **Steps (Verification):**
    1.  In Tinker, access the property normally:
        ```php
        echo $patient->emergency_contact_enc;
        ```
*   **Expected Result:** You see the plain-text phone number (e.g., `+1-555-0101`). This confirms the **Cast** is working correctly.

---

## 🔎 Phase 3: Patient Discovery & Visibility

### 3.1 Role-Scoped Search (Specialist Restriction)
*   **Feature:** Specialists can only discover patients they are authorized to treat.
*   **Pre-condition:** Log in as **Spec 2** (`spec2@mediconnect.test`). Note: Spec 2 is a Neurologist.
*   **Steps:**
    1.  Focus the "Patient Search" bar on the top-right of the dashboard.
    2.  Type "James" (James Carter, MRN-0001).
*   **Expected Result:** No results found. (James is referred to Cardiology/Spec 1, not Neurology/Spec 2).
*   **Steps (GP/Admin Check):**
    1.  Log in as **Admin** or **GP 1**.
    2.  Search for "James".
*   **Expected Result:** James Carter appears in the dropdown. (GPs/Admins need global search to create new referrals).

### 3.2 Unauthorized View Blocking (403 Forbidden)
*   **Feature:** Hard-blocking direct URL access to patient records if no referral exists.
*   **Steps:**
    1.  Log in as **Spec 2**.
    2.  Manually type this URL in your browser: `http://localhost:8000/patients/1/records` (James Carter).
*   **Expected Result:** A standard **403 Forbidden** page appears with the message "Unauthorized access to patient record."

---

## 🩺 Phase 4: Electronic Health Records (EHR) & Auditing

### 4.1 HIPAA-Style Access Audit Logs
*   **Feature:** Automatically logs every time a user views a patient's medical records.
*   **Steps:**
    1.  Log in as **GP 1**.
    2.  Navigate to James Carter's records via the search or Dashboard.
    3.  Once the page loads, open Tinker: `php artisan tinker`.
    4.  Run: `\App\Models\AuditLog::latest()->first();`
*   **Expected Result:** A log entry exists matching your current session:
    *   `user_id`: Your ID.
    *   `record_accessed`: Patient 1's records.
    *   `ip_address`: `127.0.0.1`.
    *   `accessed_at`: Current timestamp.

### 4.2 PDF Prescription Export
*   **Steps:**
    1.  On any Patient Record page, find a "Prescription" or "Visit Card".
    2.  Click the "Export as PDF" button.
*   **Expected Result:** 
    *   A PDF downloads immediately.
    *   Open it to verify the professional layout (Header, Patient Info, Diagnosis, and Treatment Plan).

---

## 🔄 Phase 5: Referral Pipeline & Automation

### 5.1 Kanban Referral Board
*   **Steps:**
    1.  View the Dashboard as **GP** or **Specialist**.
    2.  Locate the 3 columns: **Pending**, **In Review**, **Completed**.
*   **Expected Result:** 
    *   Cards are colored based on urgency (Red for Emergency, Yellow for Urgent).
    *   Status updates can be performed by Specialists using the "Update Status" dropdown on the card.

### 5.2 Automated Urgency Escalation
*   **Feature:** Promotes overdue referrals to ensure timely care.
*   **Steps:**
    1.  In Tinker, pick a 'Routine' referral with **'Pending'** status and back-date it:
        ```php
        $r = \App\Models\Referral::where('urgency_level', 'Routine')
            ->where('status', 'Pending')
            ->first();
        $r->created_at = now()->subDays(5);
        $r->save();
        ```
    2.  Trigger the escalation service:
        ```php
        app(\App\Services\ReferralManagementService::class)->escalateUrgency();
        ```
*   **Expected Result:** The `urgency_level` is now **Urgent**. If run again, it becomes **Emergency**. (Note: Escalation ignores 'Completed' or 'In Review' referrals).

---

## 📤 Phase 6: Lab Resources & Storage

### 6.1 Secure Lab Report Upload
*   **Steps:**
    1.  Go to a Patient Record page.
    2.  Find the "Upload Lab Report" form.
    3.  Upload any PDF file.
*   **Expected Result:** 
    *   Success message appears.
    *   **A new link appears** under "Lab Reports" for that visit card (e.g., `📄 report_name.pdf`).
    *   Clicking the link opens the file securely in a new tab.
*   **Privacy Check:** Attempting to access the file via a direct public path (e.g., `http://localhost:8000/storage/lab_reports/...`) will result in a **404** or **Access Denied**. It is **only** retrievable via the secure link you just clicked.
