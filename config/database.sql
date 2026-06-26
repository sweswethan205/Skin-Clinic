CREATE DATABASE IF NOT EXISTS skin_clinic;
USE skin_clinic;

-- =====================================
-- ADMINS
-- =====================================

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- =====================================
-- USERS
-- =====================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    phone VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================
-- DOCTORS
-- =====================================

CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50) UNIQUE,
    password VARCHAR(255) NOT NULL,
    specialization VARCHAR(100),
    experience INT,
    phone VARCHAR(50),
    photo VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active'
);

-- =====================================
-- CATEGORIES
-- =====================================

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active','inactive') DEFAULT 'active'
);

-- =====================================
-- TREATMENTS
-- =====================================

CREATE TABLE treatments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    treatment_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration INT,
    image VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',

    FOREIGN KEY (category_id)
    REFERENCES categories(id)
    ON DELETE CASCADE
);

-- =====================================
-- DOCTOR_TREATMENTS
-- =====================================

CREATE TABLE doctor_treatments (
    doctor_id INT NOT NULL,
    treatment_id INT NOT NULL,

    PRIMARY KEY (doctor_id, treatment_id),

    FOREIGN KEY (doctor_id)
    REFERENCES doctors(id)
    ON DELETE CASCADE,

    FOREIGN KEY (treatment_id)
    REFERENCES treatments(id)
    ON DELETE CASCADE
);

-- =====================================
-- SCHEDULES
-- =====================================

CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    available_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_booked ENUM('yes','no') DEFAULT 'no',

    FOREIGN KEY (doctor_id)
    REFERENCES doctors(id)
    ON DELETE CASCADE
);

-- =====================================
-- APPOINTMENTS
-- =====================================

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    treatment_id INT NOT NULL,
    schedule_id INT NOT NULL,

    status ENUM(
        'pending',
        'confirmed',
        'completed',
        'cancelled'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE,

    FOREIGN KEY (treatment_id)
    REFERENCES treatments(id),

    FOREIGN KEY (schedule_id)
    REFERENCES schedules(id)
);

-- =====================================
-- PAYMENT METHODS
-- =====================================

CREATE TABLE payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    method_name VARCHAR(50) NOT NULL
);

INSERT INTO payment_methods (method_name)
VALUES
('KBZ Pay'),
('AYA Pay'),
('Wave Pay'),
('Cash');

-- =====================================
-- PAYMENTS
-- =====================================

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,

    appointment_id INT NOT NULL,
    payment_method_id INT NOT NULL,

    amount DECIMAL(10,2) NOT NULL,

    payment_status ENUM(
        'pending',
        'paid',
        'failed'
    ) DEFAULT 'pending',

    payment_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (appointment_id)
    REFERENCES appointments(id)
    ON DELETE CASCADE,

    FOREIGN KEY (payment_method_id)
    REFERENCES payment_methods(id)
);

-- =====================================
-- NOTIFICATIONS
-- =====================================

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,
    appointment_id INT NOT NULL,

    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE,

    FOREIGN KEY (appointment_id)
    REFERENCES appointments(id)
    ON DELETE CASCADE
);