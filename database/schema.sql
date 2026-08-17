CREATE DATABASE IF NOT EXISTS waste_db;
USE waste_db;

-- Create the users table to store login credentials, role, profile details, and account creation time.
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'resident') NOT NULL DEFAULT 'resident',
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create the pickup_requests table to store each resident's request for waste collection and its current status.
CREATE TABLE IF NOT EXISTS pickup_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    waste_type VARCHAR(50) NOT NULL,
    pickup_date DATE NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    notes TEXT DEFAULT NULL,
    states ENUM('pending', 'done', 'declined') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create the complaints table to store resident complaints and their resolution state.
CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    complaint_type VARCHAR(50) NOT NULL,
    complaint_subject VARCHAR(150) NOT NULL,
    complaint_text TEXT NOT NULL,
    states ENUM('pending', 'done', 'declined') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create the pickup_schedule table to store the weekly collection plan shown to residents.
CREATE TABLE IF NOT EXISTS pickup_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pickup_date DATE NOT NULL,
    waste_type VARCHAR(50) NOT NULL,
    area VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create the default resident account so a resident can log in during testing.
INSERT INTO users (username, password, role) 
VALUES ('ucsc', '$2y$10$5Jg5H0n61N3QVyFD1e53g.F.XvysjBBUvgi0Lq/80IDREPfezryG.', 'resident')
ON DUPLICATE KEY UPDATE id=id;
-- Create the default admin account and set the role to admin because admin-only access must be restricted by role.
INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$SxNNnTu1RDzhPxXOAYK9c.5/WO.mMnx2QHEFek8OSPvT6Sxe5EQCi', 'admin')
ON DUPLICATE KEY UPDATE id=id;