-- File to store all the queries
-- 10-Oct-2024 12:56PM
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    attendance_date DATE NOT NULL,
    check_in TIME,
    check_out TIME,
    attendance_status VARCHAR(50), 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_attendance (user_id, attendance_date)  -- Corrected the column name to attendance_date
);

-- 11-Oct-2024 12:30PM
UPDATE permissions SET permission_name = 'mark_attendance' WHERE id = 8;
