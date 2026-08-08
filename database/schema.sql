CREATE DATABASE IF NOT EXISTS waste_db;
USE waste_db;

-- Table to store user credentials and roles
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'resident') NOT NULL DEFAULT 'resident',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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

-- Table to store the weekly waste collection schedule shown on the
-- resident schedule.php page. Populated by an admin (no admin page
-- for this yet).
CREATE TABLE IF NOT EXISTS pickup_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pickup_date DATE NOT NULL,
    waste_type VARCHAR(50) NOT NULL,
    area VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table to store user detail records entered via the admin
-- "Add User Details" form on manage_users.php. Address is collected
-- as a single free-text field (the form's separate street/home
-- number/city/sub-city inputs were dropped as redundant), and
-- preferred pickup days are stored as a comma-separated list.
CREATE TABLE IF NOT EXISTS add_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    preferred_days VARCHAR(100) DEFAULT NULL,
    entry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, role) 
VALUES ('ucsc', '$2y$10$5Jg5H0n61N3QVyFD1e53g.F.XvysjBBUvgi0Lq/80IDREPfezryG.', 'resident')
ON DUPLICATE KEY UPDATE id=id;
INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$SxNNnTu1RDzhPxXOAYK9c.5/WO.mMnx2QHEFek8OSPvT6Sxe5EQCi', 'admin')
ON DUPLICATE KEY UPDATE id=id;