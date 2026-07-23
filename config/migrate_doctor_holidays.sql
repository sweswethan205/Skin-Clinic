-- =====================================
-- DOCTOR HOLIDAYS (Doctor Leave/Vacation)
-- =====================================

CREATE TABLE doctor_holidays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason VARCHAR(100) NOT NULL DEFAULT 'Vacation',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (doctor_id)
    REFERENCES doctors(id)
    ON DELETE CASCADE,

    CHECK (end_date >= start_date)
);

CREATE INDEX idx_doctor_holidays_doctor_date ON doctor_holidays (doctor_id, start_date, end_date);
