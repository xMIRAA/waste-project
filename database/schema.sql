CREATE DATABASE IF NOT EXISTS waste_db;
USE waste_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','resident') NOT NULL DEFAULT 'resident',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

<<<<<<< HEAD
INSERT INTO users (username, password, role) 
VALUES ('ucsc', '$2y$10$5Jg5H0n61N3QVyFD1e53g.F.XvysjBBUvgi0Lq/80IDREPfezryG.', 'resident')
ON DUPLICATE KEY UPDATE id=id;
=======
INSERT INTO users (username,password,role)
VALUES
('ucsc','$2y$10$5Jg5H0n61N3QVyFD1e53g.F.XvysjBBUvgi0Lq/80IDREPfezryG.','resident')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO users (username,password,role)
VALUES
('admin','$2y$10$SxNNnTu1RDzhPxXOAYK9c.5/WO.mMnx2QHEFek8OSPvT6Sxe5EQCi','admin')
ON DUPLICATE KEY UPDATE id=id;


-- Pickup Requests Table

CREATE TABLE IF NOT EXISTS pickup_requests (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    waste_type VARCHAR(50) NOT NULL,

    pickup_date DATE NOT NULL,

    time_slot VARCHAR(50) NOT NULL,

    notes TEXT,

    status ENUM('pending','approved','rejected')
    DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(user_id)
    REFERENCES users(id)
    ON DELETE CASCADE

);


-- Pickup Schedule Table

CREATE TABLE IF NOT EXISTS pickup_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pickup_date DATE NOT NULL,
    waste_type VARCHAR(50) NOT NULL,
    area VARCHAR(50) NOT NULL,
    collection_time VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Complaints Table

CREATE TABLE IF NOT EXISTS complaints (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    complaint_type VARCHAR(50) NOT NULL,

    complaint_subject VARCHAR(100) NOT NULL,

    complaint_text TEXT NOT NULL,

    status ENUM('pending','resolved')
    DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(user_id)
    REFERENCES users(id)
    ON DELETE CASCADE

);
>>>>>>> 9bc31ad (resident pages work in progress)
