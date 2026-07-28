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

INSERT INTO users (username, password, role) 
VALUES ('ucsc', 'ucsc', 'resident')
ON DUPLICATE KEY UPDATE id=id;