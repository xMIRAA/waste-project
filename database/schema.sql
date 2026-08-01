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
    pickup_type VARCHAR(50) NOT NULL,
    preferred_date DATE NOT NULL,
    status ENUM('pending', 'done') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    complaint_text TEXT NOT NULL,
    status ENUM('pending', 'done') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (username, password, role) 
VALUES ('ucsc', '$2y$10$5Jg5H0n61N3QVyFD1e53g.F.XvysjBBUvgi0Lq/80IDREPfezryG.', 'resident')
ON DUPLICATE KEY UPDATE id=id;
INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$SxNNnTu1RDzhPxXOAYK9c.5/WO.mMnx2QHEFek8OSPvT6Sxe5EQCi', 'admin')
ON DUPLICATE KEY UPDATE id=id;