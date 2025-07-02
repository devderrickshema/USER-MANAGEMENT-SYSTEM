-- Create the user_management database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `user_management`;

-- Use the database
USE `user_management`;

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `reg_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add indexes for faster lookups
CREATE INDEX idx_username ON users(username);
CREATE INDEX idx_email ON users(email); 