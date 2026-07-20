-- Add description column to doctors table if it doesn't exist
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'doctors'
    AND COLUMN_NAME = 'description'
);

SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE doctors ADD COLUMN description TEXT AFTER password',
    'SELECT "description column already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
