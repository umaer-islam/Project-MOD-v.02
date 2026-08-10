# Mamun's Ortho Dental

**Full-stack dental clinic website & management system**

[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![License](https://img.shields.io/badge/License-Proprietary-red?style=flat-square)](#license)
[![Developer](https://img.shields.io/badge/Developer-Umaer%20Islam-ea741b?style=flat-square)](https://umaerislam.com)

> A complete digital platform for **Mamun's Ortho Dental** — a leading orthodontic and dental care clinic in Lalmatia, Dhaka, Bangladesh. Built by [Umaer Islam](https://umaerislam.com) for Dr. Mohammad Shamim Al Mamun (FCPS, Orthodontics).

---

## Live

**[mamunorthodental.com](https://mamunorthodental.com)**

---

## Features

### Public Website
- **Homepage** — Hero with interactive background (canvas dot grid, aurora blobs, cursor trail particles), services grid, gallery lightbox, testimonials, contact form
- **Doctor Profile** — Dr. Shamim Al Mamun's qualifications, experience, case studies
- **4 Service Pages** — Orthodontic Braces, Root Canal, Teeth Whitening, Scaling & Polishing — each with FAQ, sidebar booking widget, cross-links
- **3 Blog Posts** — Best Age for Braces, Braces Cost in Dhaka 2026, Dental Care Tips for Bangladeshis
- **Contact Page** — Form, clinic location, WhatsApp integration, Open/Closed status
- **Legal Pages** — Privacy Policy, Terms & Conditions
- **Custom Error Pages** — 403, 404, 500 with animated designs
- **PWA Support** — Web manifest, theme color, shortcuts for Book Appointment / Services / Doctor Profile

### Clinic Management System
- **Dashboard** — KPIs (today's patients, revenue, pending reviews), revenue charts (Chart.js), gender distribution, recent patients, today's appointments
- **Patient Management** — Full CRUD, auto-generated patient IDs (`MOD-XXXX`), QR code generation, search by name/phone/ID
- **Appointment Scheduling** — Book, view, cancel appointments with date/time picker
- **Prescription Builder** — Create prescriptions with diagnosis, medicines (name/dose/duration), advice; print with QR code linking to patient record
- **Payment Tracking** — Record payments, filter by date/method, revenue reports
- **Cash Memos** — Create, print, and manage cash memos
- **Staff Management** — Admin, Doctor, Receptionist roles with per-page access control
- **Announcements** — Post notices for staff and patients (Public/Internal visibility)
- **Gallery** — Upload, reorder, delete clinic photos with captions
- **Before & After Cases** — Upload case study images for the public website
- **Testimonials** — Manage patient reviews (Published/Hidden/Pending status)
- **Messages** — View and manage contact form inquiries
- **Reports** — Patient counts, payment totals, monthly revenue charts
- **Activity Monitor** — Hidden admin page tracking all user actions (login, CRUD operations, IP, user agent)
- **Profile Management** — Update own profile, prescription template, signature image

### Special Features
- **Patient QR Codes** — Each patient gets a QR code linking to their public record page
- **Print Prescriptions** — Print-ready prescriptions with clinic header, doctor signature, patient QR code, and medicine list
- **Print Bills & Cash Memos** — Print-ready financial documents
- **Patient Record Portal** — Public-facing page showing prescriptions and appointments (accessible via QR code)
- **Digital Identity Cards** — Doctor identity card pages
- **Global AJAX Search** — Search patients from any page in the admin panel
- **Custom UI Components** — Reusable dropdown, date picker, time picker (vanilla JS, no dependencies)

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.2 (procedural), PDO |
| **Database** | MySQL 8.0 |
| **Frontend** | HTML5, Tailwind CSS (CDN), Vanilla JavaScript |
| **Charts** | Chart.js |
| **Icons** | Font Awesome 6.4 |
| **Fonts** | Outfit (sans-serif), Playfair Display (serif), Poppins (prescription print) |
| **UI Components** | Flowbite (admin panel) |
| **Server** | Apache (XAMPP for local dev) |
| **QR Codes** | api.qrserver.com (external) |

---

## Project Structure

```
site/
├── index.php                    # Public homepage (hero, services, gallery, testimonials, contact)
├── dr-shamim-al-mamun.php       # Doctor profile page
├── contact.php                  # Contact page with form
├── privacy-policy.php           # Privacy policy
├── terms.php                    # Terms & conditions
├── login_page.php               # Admin login page
├── login.php                    # Login handler (POST)
├── logout.php                   # Session destroy + redirect
│
├── # ── Admin Panel ──
├── dashboard.php                # Dashboard with KPIs, charts, recent activity
├── patients.php                 # Patient directory (CRUD, search, QR)
├── appointments.php             # Appointment scheduling
├── prescriptions.php            # Prescription list
├── create_prescription.php      # Prescription builder (983 lines)
├── payments.php                 # Payment tracking + Cash Memos (tabbed)
├── users.php                    # Staff management (admin only)
├── announcements.php            # Notice board (admin only)
├── gallery.php                  # Clinic gallery (admin only)
├── cases.php                    # Before & After cases (admin only)
├── testimonials.php             # Patient reviews
├── messages.php                 # Contact form inquiries
├── reports.php                  # Analytics & reports
├── profile.php                  # User profile settings
│
├── # ── Print Pages ──
├── print_prescription.php       # Print-ready prescription with QR code
├── print_bill.php               # Print-ready bill/invoice
├── print_cash_memo.php          # Print-ready cash memo
├── patient_record.php           # Public patient record portal (via QR)
│
├── # ── Error Pages ──
├── 403.php                      # Access forbidden
├── 404.php                      # Page not found
├── 500.php                      # Server error
│
├── # ── Public Pages ──
├── services/                    # Individual service pages
│   ├── orthodontic-braces-treatment-dhaka.php
│   ├── root-canal-treatment-dhaka.php
│   ├── teeth-whitening-dhaka.php
│   └── scaling-polishing-dhaka.php
│
├── blog/                        # Blog posts
│   ├── best-age-for-braces.php
│   ├── braces-cost-in-dhaka.php
│   └── dental-care-tips-bangladesh.php
│
├── Identity/                    # Digital identity cards
│   ├── dr-shamim-al-mamun.php
│   └── dr-kohinoor-sabnam.php
│
├── # ── API Layer (30 endpoints) ──
├── api/
│   ├── add_patient.php          # Create patient + QR code
│   ├── update_patient.php       # Update patient record
│   ├── delete_patient.php       # Delete patient
│   ├── search_patient.php       # AJAX patient search (JSON)
│   ├── add_appointment.php      # Book appointment
│   ├── add_prescription.php     # Create prescription
│   ├── save_prescription.php    # Save prescription settings
│   ├── delete_prescription.php  # Delete prescription
│   ├── add_payment.php          # Record payment
│   ├── delete_payment.php       # Delete payment
│   ├── save_cash_memo.php       # Create cash memo
│   ├── delete_cash_memo.php     # Delete cash memo
│   ├── add_case.php             # Add before/after case
│   ├── update_case.php          # Update case
│   ├── delete_case.php          # Delete case
│   ├── add_gallery.php          # Upload gallery image
│   ├── update_gallery.php       # Update gallery caption/order
│   ├── delete_gallery.php       # Delete gallery image
│   ├── add_announcement.php     # Post announcement
│   ├── update_announcement.php  # Update announcement
│   ├── delete_announcement.php  # Delete announcement
│   ├── add_guest_review.php     # Submit public review
│   ├── save_testimonial.php     # Save testimonial
│   ├── delete_message.php       # Delete contact inquiry
│   ├── save_user.php            # Create/update staff account
│   ├── upload_rx_settings.php   # Upload prescription template
│   ├── generate_qr.php          # QR code generator
│   ├── get_dashboard_stats.php  # Dashboard statistics API
│   ├── get_patient_vitals.php   # Patient vitals API
│   └── update_patient.php       # Update patient
│
├── # ── Components ──
├── components/
│   ├── auth_guard.php           # Session auth + role-based access control
│   ├── activity_logger.php      # Activity logging (34 action types)
│   ├── header.php               # Admin HTML head + Tailwind config
│   ├── sidebar.php              # Sidebar navigation with role-based items
│   ├── topbar.php               # Top bar with search, clock, notifications
│   └── footer.php               # Footer with developer credit
│
├── # ── Database ──
├── database/
│   └── connection.php           # PDO connection (mamunort_clinic_db)
│
├── # ── Assets ──
├── assets/
│   ├── css/
│   │   ├── landing.css          # Public website CSS (hero, cursor, animations)
│   │   ├── style.css            # Admin panel CSS (cards, forms, components)
│   │   └── loader.css           # Page loader animation
│   ├── js/
│   │   ├── main.js              # AJAX search, global UI helpers
│   │   ├── charts.js            # Chart.js configurations
│   │   └── loader.js            # Loader lifecycle
│   ├── logo.svg                 # SVG logo
│   └── qr/                      # QR code storage
│
├── uploads/                     # User-uploaded files (gallery, cases, signatures)
│
├── # ── SEO / AEO / GEO ──
├── sitemap.xml                  # XML sitemap (13 URLs)
├── robots.txt                   # Crawler directives (blocks admin/api)
├── llms.txt                     # AI system optimization
├── humans.txt                   # Developer credits
├── browserconfig.xml            # Microsoft tile config
├── site.webmanifest             # PWA manifest
├── Logo.png                     # Clinic logo
├── .htaccess                    # Error pages + file protection
│
├── # ── Misc ──
├── hero-preview.html            # Standalone hero section preview
├── announcements.php            # Public announcements page
├── SKILL.md                     # Apple design principles reference
└── mamunorthodental-indexnow-key-2026.txt  # IndexNow key
```

---

## Getting Started

### Prerequisites

- **XAMPP** / **WAMP** / **LAMP** stack (Apache + PHP 8.2+ + MySQL 8.0+)
- **Composer** (optional, not required — no third-party PHP dependencies)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-repo/mamunorthodental.git
   # OR copy the `site/` folder to your htdocs directory
   ```

2. **Place in web root**
   ```
   C:\xampp\htdocs\mamunorthodental\    (Windows)
   /opt/lampp\htdocs/mamunorthodental/  (Linux)
   ```

3. **Create the database**
   - Open phpMyAdmin (`http://localhost/phpmyadmin`)
   - Create a database named `mamunort_clinic_db`
   - Import the SQL file (if provided) or let the app auto-create tables on first run

4. **Configure database connection**
   Edit `database/connection.php` if your credentials differ:
   ```php
   $host = 'localhost';
   $dbname = 'mamunort_clinic_db';
   $username = 'mamunort_manager';
   $password = 'your_password';
   ```

5. **Set up the admin user**
   ```sql
   INSERT INTO users (name, email, password_hash, role)
   VALUES ('Admin', 'admin@mamunorthodental.com', '$2y$10$...', 'admin');
   ```
   Or use `password_hash('your_password', PASSWORD_DEFAULT)` to generate the hash.

6. **Start the server**
   ```
   http://localhost/mamunorthodental/
   ```

### Default Admin Access

| Field | Value |
|-------|-------|
| URL | `http://localhost/mamunorthodental/login_page.php` |
| Email | (set during user creation) |
| Password | (set during user creation) |

---

## Usage

### Public Website

| Page | URL | Description |
|------|-----|-------------|
| Homepage | `/index.php` | Hero, services, gallery, testimonials, contact |
| Doctor Profile | `/dr-shamim-al-mamun.php` | Dr. Shamim's qualifications and experience |
| Contact | `/contact.php` | Contact form, location, WhatsApp |
| Braces Treatment | `/services/orthodontic-braces-treatment-dhaka.php` | Service page with FAQ |
| Root Canal | `/services/root-canal-treatment-dhaka.php` | Service page with FAQ |
| Teeth Whitening | `/services/teeth-whitening-dhaka.php` | Service page with FAQ |
| Scaling & Polishing | `/services/scaling-polishing-dhaka.php` | Service page with FAQ |
| Blog: Best Age for Braces | `/blog/best-age-for-braces.php` | Educational article |
| Blog: Braces Cost | `/blog/braces-cost-in-dhaka.php` | Price guide |
| Blog: Dental Care Tips | `/blog/dental-care-tips-bangladesh.php` | Health tips |
| Patient Record | `/patient_record.php?pid=MOD-0001` | Public patient portal (QR accessible) |

### Admin Panel

| Page | URL | Access |
|------|-----|--------|
| Dashboard | `/dashboard.php` | All |
| Patients | `/patients.php` | All |
| Appointments | `/appointments.php` | All |
| Prescriptions | `/prescriptions.php` | Admin, Doctor |
| Create Prescription | `/create_prescription.php` | Admin, Doctor |
| Payments | `/payments.php` | All |
| Messages | `/messages.php` | All |
| Testimonials | `/testimonials.php` | All |
| Reports | `/reports.php` | All |
| Announcements | `/announcements.php` | Admin |
| Staff | `/users.php` | Admin |
| Gallery | `/gallery.php` | Admin |
| Before & After | `/cases.php` | Admin |
| Activity Monitor | `/admin/all-activities-monitor.php` | Admin |

### Roles

| Role | Access |
|------|--------|
| **Admin** | Full access to all pages and features |
| **Doctor** | Clinical pages (prescriptions, patients, appointments, payments, reports) |
| **Receptionist** | Front-desk pages (patients, appointments, payments, messages, testimonials, reports) |

---

## API Endpoints

All endpoints are in the `/api/` directory and require authentication via session cookies.

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `add_patient.php` | All | Create patient + generate QR code |
| POST | `update_patient.php` | All | Update patient record |
| POST | `delete_patient.php` | Admin | Delete patient |
| GET | `search_patient.php?q=` | All | AJAX patient search (JSON) |
| POST | `add_appointment.php` | All | Book appointment |
| POST | `add_prescription.php` | Admin, Doctor | Create prescription |
| POST | `save_prescription.php` | Admin, Doctor | Save RX template settings |
| POST | `delete_prescription.php` | Admin, Doctor | Delete prescription |
| POST | `add_payment.php` | All | Record payment |
| POST | `delete_payment.php` | Admin | Delete payment |
| POST | `save_cash_memo.php` | All | Create cash memo |
| POST | `delete_cash_memo.php` | Admin | Delete cash memo |
| POST | `add_case.php` | Admin | Add before/after case |
| POST | `update_case.php` | Admin | Update case |
| POST | `delete_case.php` | Admin | Delete case |
| POST | `add_gallery.php` | Admin | Upload gallery image |
| POST | `update_gallery.php` | Admin | Update gallery caption/order |
| POST | `delete_gallery.php` | Admin | Delete gallery image |
| POST | `add_announcement.php` | Admin | Post announcement |
| POST | `update_announcement.php` | Admin | Update announcement |
| POST | `delete_announcement.php` | Admin | Delete announcement |
| POST | `add_guest_review.php` | Public | Submit public review |
| POST | `save_testimonial.php` | All | Save testimonial |
| POST | `delete_message.php` | All | Delete contact inquiry |
| POST | `save_user.php` | Admin | Create/update staff account |
| POST | `upload_rx_settings.php` | Admin, Doctor | Upload RX template/signature |
| GET | `generate_qr.php` | — | QR code generator |
| GET | `get_dashboard_stats.php` | All | Dashboard statistics |
| GET | `get_patient_vitals.php` | All | Patient vitals data |

---

## SEO / AEO / GEO

### Schema.org Structured Data

| Schema Type | Where | Purpose |
|-------------|-------|---------|
| `LocalBusiness` + `MedicalBusiness` | Homepage | Clinic info, hours, location, rating |
| `Physician` | Homepage, Doctor page | Dr. Shamim's credentials |
| `FAQPage` | Homepage, all service pages, blog posts | 36 Q&A pairs for featured snippets |
| `BlogPosting` | All 3 blog posts | Article metadata |
| `MedicalWebPage` | Service pages | Medical procedure info |
| `BreadcrumbList` | Blog posts, service pages | Navigation structure |
| `WebDesign` | Homepage | Developer credit (Umaer Islam) |
| `SoftwareApplication` | Homepage | Clinic management system |

### Meta Tags (per page)

- `description`, `keywords`, `author`, `robots`
- `developer`, `designer`, `copyright` — Developer attribution
- `ai-content-declaration: human-authored` — AI system signal
- Open Graph (`og:type`, `og:title`, `og:description`, `og:url`, `og:image`)
- Twitter Cards
- `canonical` URLs
- `google-site-verification`

### AI Optimization

| File | Purpose |
|------|---------|
| `llms.txt` | Structured info for AI systems (clinic, doctor, developer, services, contact) |
| `humans.txt` | Human-readable credits and tech stack |
| `robots.txt` | Crawler directives (allows public, blocks admin/api/database) |
| `sitemap.xml` | 13 URLs with priorities and lastmod dates |

### Target Keywords

"Best Dental Clinic in Dhaka", "Best Dental in Dhaka", "Best Clinic in Dhaka" — injected into H1, meta description, schema description, body content, FAQ answers, llms.txt, and service page titles.

---

## Design System

### Brand Colors

| Token | Hex | Usage |
|-------|-----|-------|
| Navy | `#004591` | Primary brand color, headings, buttons |
| Gold / Orange | `#ea741b` | Accent, CTAs, highlights |
| Dark | `#080c14` | Backgrounds, footer |
| Spring | Custom CSS var | Smooth transitions |
| Bounce | Custom CSS var | Elastic animations |

### Typography

| Font | Usage |
|------|-------|
| **Outfit** | Body text, UI elements (sans-serif) |
| **Playfair Display** | Headings, hero text (serif) |
| **Poppins** | Prescription print layout |

### Custom Components

- **Cursor system** — Dot, ring, glow, light cone, trail particles (desktop only)
- **Interactive background** — Canvas dot grid, aurora blobs, cursor-repel orbs, floating particles, pulse rings
- **Page loader** — Animated slide-up reveal with `loader:done` CustomEvent
- **Animated counters** — Count up on scroll reveal
- **Custom dropdown** — Vanilla JS, no dependencies
- **Custom date picker** — Calendar with month navigation
- **Custom time picker** — Hour/minute/AM-PM with scroll
- **Gallery lightbox** — Click-to-expand with caption
- **Glass morphism** — Admin panel cards and topbar

---

## Security

| Measure | Implementation |
|---------|---------------|
| **Authentication** | Session-based with `session_regenerate_id(true)` on login |
| **Password Hashing** | `password_hash()` / `password_verify()` (bcrypt) |
| **Role-Based Access** | `restrict_access(['admin', 'doctor'])` per page |
| **Auth Guard** | `auth_guard.php` included in all protected pages and API endpoints |
| **SQL Injection Prevention** | PDO prepared statements throughout |
| **XSS Prevention** | `htmlspecialchars()` on all user output |
| **Session Fixation** | `session_regenerate_id(true)` on successful login |
| **File Protection** | `.htaccess` blocks `.env`, `.log`, `.sql`, `.bak`, `.ini`, `.conf` |
| **Error Pages** | Custom 403/404/500 with `noindex` meta |
| **Failed Login Logging** | IP address, user agent, timestamp recorded |
| **CSRF** | Form-level validation (POST method checks) |

---

## Database Schema

### Core Tables

| Table | Purpose |
|-------|---------|
| `patients` | Patient records (auto-ID: MOD-XXXX, name, phone, age, gender, address, notes, QR) |
| `appointments` | Appointment schedule (patient_id, date, time, status) |
| `prescriptions` | Prescriptions (patient_id, doctor_id, diagnosis, medicines JSON, advice) |
| `payments` | Payment records (patient_id, amount, payment_method) |
| `cash_memos` | Cash memo records |
| `users` | Staff accounts (name, email, password_hash, role, degrees, rx_template_path, signature_path) |
| `services` | Service listings (name, description, icon, status) |
| `testimonials` | Patient reviews (patient_name, location, stars, review, status) |
| `announcements` | Notices (title, description, visibility, expiry_date) |
| `gallery` | Clinic photos (image_path, caption, sort_order) |
| `before_after_cases` | Case studies (title, description, before_image, after_image) |
| `contact_inquiries` | Contact form submissions (name, phone, service, message, status) |
| `activity_logs` | Activity audit trail (user_id, action, details, ip_address, user_agent) |

### Auto-Created Tables

The app auto-creates missing tables on first run via `index.php` runtime checks:
- `contact_inquiries`
- `gallery`
- `before_after_cases`
- `activity_logs` (via `activity_logger.php`)

---

## Developer

**Umaer Islam** — Full-Stack Web Developer & Designer

- Website: [umaerislam.com](https://umaerislam.com)
- Specialties: PHP, MySQL, JavaScript, Tailwind CSS, Schema.org, SEO, AEO/GEO

### Developer Credit Layers

1. **Schema.org** — `WebDesign`, `SoftwareApplication`, `WebSite.contributor`, `FAQPage` answer
2. **Meta tags** — `developer`, `designer`, `copyright` on every page
3. **HTML comments** — `<!-- Developed by Umaer Islam -->`
4. **Footer** — "Designed & Developed by Umaer Islam" with dofollow link
5. **llms.txt** — Credits section + developer project description
6. **humans.txt** — Full developer profile

---

## License

This is a proprietary project. All rights reserved by Mamun's Ortho Dental and Dr. Mohammad Shamim Al Mamun.

Website designed and developed by [Umaer Islam](https://umaerislam.com).

---

*Built with care for Mamun's Ortho Dental, Lalmatia, Dhaka-1207, Bangladesh.*
