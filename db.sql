-- Create the database
CREATE DATABASE loan_management;

-- Use the database
USE loan_management;

-- Admin Table
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Referral Person Table
CREATE TABLE referral_person (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    mobile VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Account Person Table
CREATE TABLE account_person (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    mobile VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Loan Table
CREATE TABLE loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_mobile VARCHAR(20) NOT NULL,
    required_loan_amount DECIMAL(15, 2) NOT NULL,
    approved_loan_amount DECIMAL(15, 2),
    status ENUM('Pending', 'Approved', 'Rejected', 'Completed') DEFAULT 'Pending',
    referral_person_id INT,
    referral_commission_rate DECIMAL(5, 2),
    account_person_id INT,
    account_commission_rate DECIMAL(5, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (referral_person_id) REFERENCES referral_person(id) ON DELETE SET NULL,
    FOREIGN KEY (account_person_id) REFERENCES account_person(id) ON DELETE SET NULL
);

-- Payments Table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_type ENUM('Referral', 'Account') NOT NULL,
    referral_id INT NULL,
    account_id INT NULL,
    loan_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    remarks TEXT,
    FOREIGN KEY (referral_id) REFERENCES referral_person(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES account_person(id) ON DELETE CASCADE,
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE
);


CREATE TABLE expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    expense_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE CASCADE
);

INSERT INTO expense_categories (name) VALUES
('Office Rent'),
('Electricity Bill'),
('Internet & Telephone'),
('Tea & Snacks'),
('Stationery'),
('Travel & Transportation'),
('Maintenance & Repairs'),
('Marketing & Advertising'),
('Salaries & Wages'),
('Software Subscriptions'),
('Bank Charges'),
('Consulting & Professional Fees'),
('Insurance'),
('Office Supplies'),
('Fuel Expenses'),
('Event & Meetings'),
('Legal Fees'),
('Miscellaneous Expenses');


ALTER TABLE loans 
ADD COLUMN bank_name VARCHAR(255) NOT NULL AFTER customer_mobile,
ADD COLUMN commission DECIMAL(15, 2) NOT NULL AFTER bank_name,
CHANGE COLUMN referral_commission_rate referral_commission DECIMAL(15,2),
CHANGE COLUMN account_commission_rate account_commission DECIMAL(15,2);



ALTER TABLE admin 
ADD COLUMN first_name VARCHAR(255) NOT NULL AFTER username,
ADD COLUMN last_name VARCHAR(255) NOT NULL AFTER first_name;
