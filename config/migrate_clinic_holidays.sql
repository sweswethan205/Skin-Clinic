-- =====================================
-- CLINIC HOLIDAYS (Clinic-wide closures)
-- =====================================

CREATE TABLE clinic_holidays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    holiday_date DATE NOT NULL UNIQUE,
    reason VARCHAR(100) NOT NULL DEFAULT 'Clinic Closed',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_clinic_holidays_date ON clinic_holidays (holiday_date);
