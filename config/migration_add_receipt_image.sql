USE skin_clinic;

-- Add receipt_image column to appointments table
ALTER TABLE appointments ADD COLUMN receipt_image VARCHAR(255) NULL AFTER status;
