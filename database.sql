CREATE DATABASE IF NOT EXISTS kopeladar_db;
USE kopeladar_db;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Default admin username: admin
-- Default admin password: password
INSERT IGNORE INTO admins (username, password) VALUES ('admin', '$2y$10$B.jR3eL/xK3M2QYlD1.uA.P2Xp4/t.4g.5n.cR7jG5N3l3m8d3e');

CREATE TABLE IF NOT EXISTS workers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    position VARCHAR(150) NOT NULL,
    image_path VARCHAR(255),
    category ENUM('board', 'management', 'org') DEFAULT 'board',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    year INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
