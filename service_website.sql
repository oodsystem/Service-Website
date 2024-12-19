CREATE DATABASE IF NOT EXISTS service_website;
USE service_website;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_number INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    dob DATE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('customer', 'provider') NOT NULL,
    service_type VARCHAR(50) DEFAULT NULL,
    service_charge DECIMAL(10, 2) DEFAULT NULL,
    rating DECIMAL(3, 2) DEFAULT 0,
    availability_status ENUM('available', 'unavailable') DEFAULT 'available'
);

-- Service Requests table
CREATE TABLE IF NOT EXISTS service_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    provider_id INT NOT NULL,
    service_type VARCHAR(50) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    add_ress VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Declined') DEFAULT 'Pending',
    FOREIGN KEY (customer_id) REFERENCES users(user_number),
    FOREIGN KEY (provider_id) REFERENCES users(user_number)
);

-- Ratings table
CREATE TABLE IF NOT EXISTS ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    customer_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    review TEXT DEFAULT NULL,
    FOREIGN KEY (provider_id) REFERENCES users(user_number),
    FOREIGN KEY (customer_id) REFERENCES users(user_number)
);