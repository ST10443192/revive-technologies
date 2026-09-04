# Revive Technologies
### Integrated Event Decorating and Freelance Management System

A full PHP + MySQL web application built to match your Figma design:
a public marketing site, a Client portal, a Freelancer portal, and an Admin portal.

---

## 1. What's inside

```
revive-technologies/
├── index.php                  Public home page (hero, services, gallery, testimonials, contact)
├── config/
│   └── database.php           MySQL connection settings (edit this if your DB details differ)
├── includes/
│   ├── functions.php           Shared helper functions (escaping, redirects, flash messages)
│   ├── auth_check.php          Protects dashboard pages by role (client/freelancer/admin)
│   ├── header_public.php / footer_public.php     Shared layout for the public site
│   └── header_dashboard.php / footer_dashboard.php  Shared layout for all 3 portals
├── auth/
│   ├── login.php               Sign in (with demo credential auto-fill)
│   ├── register.php            Register as Client or Freelancer
│   └── logout.php
├── client/                     Client Portal
│   ├── dashboard.php            "Portfolio" - packages + booking summary
│   ├── submit-design.php        Submit a custom design brief
│   ├── invitations.php          Request invitation cards
│   ├── request-quote.php        Choose a package + request a quotation
│   ├── quotations.php           View & accept/reject quotations
│   ├── messages.php             Chat with the Revive Technologies team
│   └── notifications.php
├── freelancer/                 Freelancer Portal
│   ├── dashboard.php            Earnings, jobs completed, rating overview
│   ├── available-jobs.php       Browse & apply for open jobs
│   ├── my-jobs.php               Track assigned jobs, mark as completed
│   ├── messages.php
│   ├── alerts.php
│   └── earnings.php              Payout history + submit payment claims
├── admin/                       Admin Portal
│   ├── dashboard.php             Business overview (revenue, bookings, stock alerts)
│   ├── quotations.php            Create quotations for pending bookings
│   ├── inventory.php             Full inventory CRUD (add/restock/delete items)
│   ├── calendar.php              Month view of all confirmed events
│   ├── freelancers.php           Manage freelancers + approve/reject payment claims
│   ├── messages.php
│   └── alerts.php
├── assets/
│   └── css/style.css           Site-wide styling (navy + gold theme)
└── database/
    └── revive_technologies.sql  Full database schema + demo data
```

---

## 2. Installation (XAMPP)

1. **Copy the project folder** into your XAMPP htdocs directory, e.g.:
   ```
   C:\xampp\htdocs\revive-technologies\
   ```

2. **Start Apache and MySQL** from the XAMPP Control Panel.

3. **Create the database:**
   - Open `http://localhost/phpmyadmin`
   - Click "Import"
   - Choose the file `database/revive_technologies.sql`
   - Click "Go"

   This creates the `revive_technologies` database with all 8 tables and demo data.

4. **Check your database settings** in `config/database.php`.
   The defaults match a standard XAMPP install (`root` user, no password), so
   in most cases you won't need to change anything.

5. **Visit the site:**
   ```
   http://localhost/revive-technologies/index.php
   ```

---

## 3. Demo login accounts

Every demo account uses the password: **Password123**

| Role       | Email                      |
|------------|-----------------------------|
| Admin      | admin@revive.co.za          |
| Client     | thandiwe@client.co.za       |
| Freelancer | sipho@freelance.co.za       |

You can also register brand new Client or Freelancer accounts from `auth/register.php`.

---

## 4. Database design (summary)

| Table         | Purpose                                                              |
|---------------|-----------------------------------------------------------------------|
| `users`       | Every person who can log in (client / freelancer / admin)             |
| `freelancers` | Extra profile info for freelancer accounts (skill, rate, rating)      |
| `inventory`   | Décor stock items the business owns                                   |
| `bookings`    | Event/décor requests submitted by clients                             |
| `quotations`  | Price quotes admin sends for a booking                                |
| `payments`    | Freelancer payment claims for completed jobs                          |
| `receipts`    | Payment receipts issued to clients                                    |
| `messages`    | In-app chat between clients, freelancers and admin                    |

All passwords are hashed with PHP's `password_hash()` / verified with `password_verify()`.
All SQL queries use prepared statements (PDO) to prevent SQL injection.

---

## 5. How the code is organised (for learning/understanding)

- **`config/database.php`** — the *only* place the DB connection is defined. Every other
  file does `require_once '../config/database.php';` to get the `$pdo` object.
- **`includes/functions.php`** — small reusable helpers, imported almost everywhere.
- **`includes/auth_check.php`** — call `require_role('client')` (or `'freelancer'`/`'admin'`)
  at the top of any page that should be restricted to a logged-in role.
- **`includes/header_dashboard.php`** — one shared navbar used by all three portals; it
  reads `$dash_role` and `$active` (set at the top of each page) to know which links to
  highlight and which portal label to show.
- Every page follows the same pattern:
  1. Require the shared files
  2. Run role check
  3. Handle any `$_POST` actions (forms) — update the database
  4. Run `SELECT` queries to load the data needed for display
  5. Include the header, print the HTML, include the footer

---

## 6. Notes / things you may want to extend

- File uploads (e.g. actual mood board images) are not implemented — the "Reference
  Images / Mood Board URL" field just stores a link/text for now.
- Emails/SMS notifications are not sent - all notifications are shown in-app only.
- The messaging system is a simple 1-to-1 thread with the first admin account found;
  extend `messages.php` if you want multiple admins with separate assignments.
- Feel free to adjust colours in `assets/css/style.css` (`:root` variables at the top).

