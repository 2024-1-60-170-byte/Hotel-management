DROP DATABASE IF EXISTS PROJECT;

CREATE DATABASE PROJECT;
USE PROJECT;

CREATE TABLE role (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(30)
);

INSERT INTO role (role_name) VALUES
('Admin'),
('Member'),
('Manager');

CREATE TABLE member (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30),
    email VARCHAR(50),
    password VARCHAR(50),
    phone VARCHAR(20),
    join_date DATE,
    is_active BOOLEAN DEFAULT 1,
    role_id INT,
    spent INT DEFAULT 0,
    available INT DEFAULT 0,
    FOREIGN KEY(role_id) REFERENCES role(role_id)
);

ALTER TABLE member ADD UNIQUE (email);

UPDATE member SET password = MD5(password);

INSERT INTO member (name, email, password, phone, join_date, role_id, spent, available)
VALUES ('Admin', 'admin@admin.com', '123', '01700000000', CURDATE(), 1, 0, 0);

CREATE TABLE room (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_number INT,
    total_seats INT,
    available_seats INT
);

CREATE TABLE member_seat (
    assign_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    room_id INT,
    start_date DATE,
    FOREIGN KEY(member_id) REFERENCES member(member_id),
    FOREIGN KEY(room_id) REFERENCES room(room_id)
);

CREATE TABLE expense (
    expense_id INT AUTO_INCREMENT PRIMARY KEY,
    utility VARCHAR(50),
    food VARCHAR(30),
    market VARCHAR(30),
    amount INT,
    description VARCHAR(100),
    member_id INT,
    paid_status VARCHAR(10) DEFAULT 'No',
    FOREIGN KEY(member_id) REFERENCES member(member_id)
);

CREATE TABLE deposit (
    deposit_id INT AUTO_INCREMENT PRIMARY KEY,
    amount INT,
    member_id INT,
    FOREIGN KEY(member_id) REFERENCES member(member_id)
);

CREATE TABLE meal (
    meal_id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50),
    meal_count INT,
    price INT,
    total_price INT,
    member_id INT,
    FOREIGN KEY(member_id) REFERENCES member(member_id)
);
