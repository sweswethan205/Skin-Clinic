-- Migration: Dynamic Treatment-Based Scheduling
-- Run this on the existing skin_clinic database

USE skin_clinic;

-- 1. Add duration column to treatments (30, 60, or 90 minutes)
ALTER TABLE treatments ADD COLUMN duration INT NOT NULL DEFAULT 60 COMMENT 'Duration in minutes' AFTER price;

-- 2. Add working hours to doctors
ALTER TABLE doctors ADD COLUMN work_start TIME DEFAULT '09:00:00' AFTER status;
ALTER TABLE doctors ADD COLUMN work_end TIME DEFAULT '19:00:00' AFTER work_start;
ALTER TABLE doctors ADD COLUMN lunch_start TIME DEFAULT '12:00:00' AFTER work_end;
ALTER TABLE doctors ADD COLUMN lunch_end TIME DEFAULT '13:00:00' AFTER lunch_start;

-- 3. Add appointment time tracking to appointments
ALTER TABLE appointments ADD COLUMN appointment_start TIME NOT NULL DEFAULT '09:00:00' AFTER payment_method_id;
ALTER TABLE appointments ADD COLUMN appointment_end TIME NOT NULL DEFAULT '10:00:00' AFTER appointment_start;

-- 4. Remove is_booked from schedules (now tracked via appointments time-range overlap)
ALTER TABLE schedules DROP COLUMN is_booked;

-- 5. Migrate existing schedules: collapse multiple per-day slots into single availability entries
-- For each doctor+date that has multiple entries, keep the earliest start_time and latest end_time

CREATE TEMPORARY TABLE IF NOT EXISTS consolidated_schedules AS
SELECT 
    doctor_id,
    available_date,
    MIN(start_time) AS start_time,
    MAX(end_time) AS end_time
FROM schedules
GROUP BY doctor_id, available_date;

DELETE FROM schedules;

INSERT INTO schedules (doctor_id, available_date, start_time, end_time)
SELECT doctor_id, available_date, start_time, end_time
FROM consolidated_schedules;

DROP TEMPORARY TABLE IF EXISTS consolidated_schedules;

-- 6. Add unique constraint to prevent duplicate availability entries per doctor per date
ALTER TABLE schedules ADD UNIQUE KEY unique_doctor_date (doctor_id, available_date);

-- 7. Create time_slots table with 30-minute intervals
CREATE TABLE IF NOT EXISTS time_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_time TIME NOT NULL UNIQUE
);

INSERT IGNORE INTO time_slots (slot_time) VALUES
('09:00:00'), ('09:30:00'), ('10:00:00'), ('10:30:00'), ('11:00:00'),
('11:30:00'), ('12:00:00'), ('12:30:00'), ('13:00:00'), ('13:30:00'),
('14:00:00'), ('14:30:00'), ('15:00:00'), ('15:30:00'), ('16:00:00'),
('16:30:00'), ('17:00:00'), ('17:30:00'), ('18:00:00'), ('18:30:00');
