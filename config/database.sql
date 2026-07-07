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
    description TEXT,
    experience INT,
    phone VARCHAR(50),
    photo VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active'
);

-- =====================================
-- TREATMENTS
-- =====================================

CREATE TABLE treatments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    treatment_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
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
    payment_method_id  INT NOT NULL,

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
    REFERENCES schedules(id),

    FOREIGN KEY (payment_method_id)
    REFERENCES payment_methods(id)
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

-- CREATE TABLE payments (
--     id INT AUTO_INCREMENT PRIMARY KEY,

--     appointment_id INT NOT NULL,
--     payment_method_id INT NOT NULL,

--     amount DECIMAL(10,2) NOT NULL,

--     payment_status ENUM(
--         'pending',
--         'paid',
--         'failed'
--     ) DEFAULT 'pending',

--     payment_date DATETIME NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

--     FOREIGN KEY (appointment_id)
--     REFERENCES appointments(id)
--     ON DELETE CASCADE,

--     FOREIGN KEY (payment_method_id)
--     REFERENCES payment_methods(id)
-- );

-- =====================================
-- NOTIFICATIONS
-- =====================================

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,
    appointment_id INT NULL,

    title VARCHAR(255),
    message TEXT,
    type VARCHAR(50),
    is_read BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE,

    FOREIGN KEY (appointment_id)
    REFERENCES appointments(id)
    ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL, -- If they are a registered user
  `name` VARCHAR(100) NOT NULL, -- Patient's name
  `rating` INT CHECK (rating >= 1 AND rating <= 5), -- Star rating (1-5)
  `review_text` TEXT NOT NULL, -- Their feedback message
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending', -- Control status
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,                    
  `name` VARCHAR(100) NOT NULL,          
  `email` VARCHAR(100) NOT NULL, 
  `phone` VARCHAR(20) NULL,         
  `subject` VARCHAR(150) DEFAULT NULL,   
  `message_text` TEXT NOT NULL,          
  `status` ENUM('unread', 'read') DEFAULT 'unread', 
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================
-- CONTACTS (contact form submissions)
-- =====================================

