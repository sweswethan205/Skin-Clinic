-- =====================================
-- ROOMS
-- =====================================

CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(100) NOT NULL,
    room_number VARCHAR(20) NOT NULL UNIQUE,
    description TEXT NULL,
    capacity INT DEFAULT 1,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================
-- TREATMENT_ROOMS (which rooms can be used for which treatments)
-- =====================================

CREATE TABLE treatment_rooms (
    treatment_id INT NOT NULL,
    room_id INT NOT NULL,
    PRIMARY KEY (treatment_id, room_id),
    FOREIGN KEY (treatment_id) REFERENCES treatments(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- =====================================
-- ALTER APPOINTMENTS: add room_id column
-- =====================================

ALTER TABLE appointments ADD COLUMN room_id INT NULL AFTER schedule_id;
ALTER TABLE appointments ADD FOREIGN KEY (room_id) REFERENCES rooms(id);

-- =====================================
-- SEED DATA: Default rooms for a skin clinic
-- =====================================

INSERT INTO rooms (room_name, room_number, description, capacity, status) VALUES
('Consultation Room',   'R-001', 'General consultation and dermatology check-ups', 1, 'active'),
('Treatment Suite A',   'R-002', 'Facials, chemical peels, and skin treatments',  1, 'active'),
('Treatment Suite B',   'R-003', 'Laser treatments and advanced procedures',       1, 'active'),
('Procedure Room',      'R-004', 'Minor surgical and extraction procedures',        1, 'active');
