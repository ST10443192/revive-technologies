-- =========================================================
-- REVIVE TECHNOLOGIES
-- Integrated Event Decorating and Freelance Management System
-- Database: revive_technologies
-- Import this file in phpMyAdmin (or run: mysql -u root -p < revive_technologies.sql)
-- =========================================================

CREATE DATABASE IF NOT EXISTS revive_technologies CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE revive_technologies;

-- ---------------------------------------------------------
-- 1. USERS TABLE
-- Every person who can log in: client, freelancer or admin
-- ---------------------------------------------------------
CREATE TABLE users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(120)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    phone       VARCHAR(30)   DEFAULT NULL,
    password    VARCHAR(255)  NOT NULL,           -- hashed with password_hash()
    role        ENUM('client','freelancer','admin') NOT NULL DEFAULT 'client',
    status      ENUM('active','suspended')  NOT NULL DEFAULT 'active',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 2. FREELANCER PROFILE TABLE
-- Extra details only freelancers have (linked 1-to-1 to users)
-- ---------------------------------------------------------
CREATE TABLE freelancers (
    freelancer_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    skill           VARCHAR(150) NOT NULL,          -- e.g. "Floral Design", "Lighting"
    bio             TEXT,
    hourly_rate     DECIMAL(10,2) DEFAULT 0.00,
    rating          DECIMAL(3,2) DEFAULT 0.00,      -- 0.00 - 5.00
    portfolio_files TEXT DEFAULT NULL,              -- comma-separated paths to uploaded work samples
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- If you already imported this database before the portfolio-upload feature
-- was added, run this line by hand to add the new column:
-- ALTER TABLE freelancers ADD COLUMN portfolio_files TEXT DEFAULT NULL AFTER rating;

-- ---------------------------------------------------------
-- 3. INVENTORY TABLE
-- Décor items the company owns and rents/uses for events
-- ---------------------------------------------------------
CREATE TABLE inventory (
    item_id         INT AUTO_INCREMENT PRIMARY KEY,
    item_name       VARCHAR(150) NOT NULL,
    category        VARCHAR(80)  DEFAULT 'General',
    total_stock     INT NOT NULL DEFAULT 0,
    available_stock INT NOT NULL DEFAULT 0,
    unit_price      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 4. BOOKINGS TABLE
-- An online event/decor booking request submitted by a client
-- ---------------------------------------------------------
CREATE TABLE bookings (
    booking_id    INT AUTO_INCREMENT PRIMARY KEY,
    client_id     INT NOT NULL,
    freelancer_id INT DEFAULT NULL,                -- assigned once approved
    event_type    VARCHAR(120) NOT NULL,           -- Wedding, Corporate, Birthday...
    event_date    DATE NOT NULL,
    venue         VARCHAR(200) DEFAULT NULL,
    guest_count   INT DEFAULT NULL,
    notes         TEXT,
    status        ENUM('pending','quoted','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (freelancer_id) REFERENCES freelancers(freelancer_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 5. QUOTATIONS TABLE
-- Digital quotation the event manager/admin sends for a booking
-- ---------------------------------------------------------
CREATE TABLE quotations (
    quote_id    INT AUTO_INCREMENT PRIMARY KEY,
    booking_id  INT NOT NULL,
    client_id   INT NOT NULL,
    amount      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    details     TEXT,
    status      ENUM('sent','accepted','rejected') NOT NULL DEFAULT 'sent',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 6. PAYMENTS TABLE
-- Freelancer payment / cash claims for completed work
-- ---------------------------------------------------------
CREATE TABLE payments (
    payment_id    INT AUTO_INCREMENT PRIMARY KEY,
    freelancer_id INT NOT NULL,
    booking_id    INT NOT NULL,
    amount        DECIMAL(10,2) NOT NULL,
    claim_date    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (freelancer_id) REFERENCES freelancers(freelancer_id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 7. RECEIPTS TABLE
-- Issued once a client payment for a booking is finalised
-- ---------------------------------------------------------
CREATE TABLE receipts (
    receipt_id     INT AUTO_INCREMENT PRIMARY KEY,
    client_id      INT NOT NULL,
    booking_id     INT NOT NULL,
    payment_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(60) DEFAULT 'EFT',
    issue_date     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 8. MESSAGES TABLE
-- Centralized messaging between clients, freelancers & admin
-- ---------------------------------------------------------
CREATE TABLE messages (
    message_id  INT AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT NOT NULL,
    receiver_id INT NOT NULL,
    message     TEXT NOT NULL,
    sent_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- SAMPLE / DEMO DATA
-- Demo password for every account below is:  Password123
-- (hash generated with PHP password_hash - see auth/register.php)
-- =========================================================
INSERT INTO users (full_name, email, phone, password, role) VALUES
('Revive Admin',    'admin@revive.co.za',      '0110000000', '$2b$10$52ujA6uJAVzZ5wI.ZYVw2OYQFor4mOSVyv3Y2i3me8jpgQ6TnSAbK', 'admin'),
('Thandiwe Mokoena', 'thandiwe@client.co.za',  '0821112222', '$2b$10$52ujA6uJAVzZ5wI.ZYVw2OYQFor4mOSVyv3Y2i3me8jpgQ6TnSAbK', 'client'),
('Sipho Ndlovu',     'sipho@freelance.co.za',  '0833334444', '$2b$10$52ujA6uJAVzZ5wI.ZYVw2OYQFor4mOSVyv3Y2i3me8jpgQ6TnSAbK', 'freelancer'),
('Amara Dlamini',    'amara@freelance.co.za',  '0845556666', '$2b$10$52ujA6uJAVzZ5wI.ZYVw2OYQFor4mOSVyv3Y2i3me8jpgQ6TnSAbK', 'freelancer');

INSERT INTO freelancers (user_id, skill, bio, hourly_rate, rating) VALUES
(3, 'Floral Design & Table Styling', 'Specialises in wedding florals and elegant table settings.', 350.00, 4.8),
(4, 'Lighting & Draping',            'Expert in ambient lighting design and fabric draping for events.', 300.00, 4.6);

INSERT INTO inventory (item_name, category, total_stock, available_stock, unit_price) VALUES
('Round Tables (10-seater)', 'Furniture', 40, 32, 250.00),
('Chiavari Chairs',          'Furniture', 300, 210, 45.00),
('Fairy Light Backdrop',     'Lighting',  15, 10, 600.00),
('Gold Charger Plates',      'Tableware', 300, 260, 15.00),
('Floral Centerpieces',      'Décor',     50, 38, 180.00);

INSERT INTO bookings (client_id, freelancer_id, event_type, event_date, venue, guest_count, notes, status) VALUES
(2, 1, 'Wedding',   '2026-09-12', 'The Manor House, Johannesburg', 150, 'Blush and gold theme requested.', 'confirmed'),
(2, NULL, 'Corporate Launch', '2026-10-03', 'Sandton Convention Centre', 80, 'Company product launch, minimalist theme.', 'pending');

INSERT INTO quotations (booking_id, client_id, amount, details, status) VALUES
(1, 2, 24500.00, 'Full wedding décor package: florals, lighting, table styling for 150 guests.', 'accepted');

COMMIT;
