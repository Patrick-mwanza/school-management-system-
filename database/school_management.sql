-- ============================================================
-- School Management System - Database Schema
-- Database: school_management
-- ============================================================

CREATE DATABASE IF NOT EXISTS school_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE school_management;

-- ============================================================
-- TABLE: classes
-- ============================================================
CREATE TABLE IF NOT EXISTS classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: subjects
-- ============================================================
CREATE TABLE IF NOT EXISTS subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: users (admin + teacher login accounts)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher') NOT NULL DEFAULT 'teacher',
    teacher_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: teachers
-- ============================================================
CREATE TABLE IF NOT EXISTS teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_number VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    phone_number VARCHAR(20),
    email VARCHAR(100),
    subject_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
);

-- ============================================================
-- TABLE: students
-- ============================================================
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admission_number VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    date_of_birth DATE,
    phone_number VARCHAR(20),
    class_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);

-- ============================================================
-- TABLE: attendance
-- ============================================================
CREATE TABLE IF NOT EXISTS attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('Present', 'Absent') NOT NULL DEFAULT 'Present',
    marked_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_attendance (student_id, date)
);

-- ============================================================
-- TABLE: examinations
-- ============================================================
CREATE TABLE IF NOT EXISTS examinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    exam_name VARCHAR(100) NOT NULL,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    exam_date DATE NOT NULL,
    max_marks INT NOT NULL DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: results
-- ============================================================
CREATE TABLE IF NOT EXISTS results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    marks_obtained DECIMAL(5,2) NOT NULL DEFAULT 0,
    grade VARCHAR(2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES examinations(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_result (exam_id, student_id)
);

-- ============================================================
-- SEED DATA
-- ============================================================

-- Default Classes
INSERT INTO classes (class_name) VALUES
('Form 1A'), ('Form 1B'), ('Form 2A'), ('Form 2B'),
('Form 3A'), ('Form 3B'), ('Form 4A'), ('Form 4B');

-- Default Subjects
INSERT INTO subjects (subject_name, subject_code) VALUES
('Mathematics', 'MATH'),
('English Language', 'ENG'),
('Kiswahili', 'KIS'),
('Physics', 'PHY'),
('Chemistry', 'CHEM'),
('Biology', 'BIO'),
('History', 'HIST'),
('Geography', 'GEO'),
('Computer Studies', 'COMP'),
('Business Studies', 'BUS');

-- ============================================================
-- USER ACCOUNTS — Run setup.php after import to create accounts
-- with correct bcrypt password hashes. OR manually insert:
-- ============================================================
-- Admin:   username=admin      password=admin123
-- Teacher: username=john.mwangi  password=teacher123
--
-- After importing this SQL, visit:
--   http://localhost/school_management/setup.php
-- to auto-generate correct password hashes.
-- ============================================================

-- Sample Students
INSERT INTO students (admission_number, full_name, gender, date_of_birth, phone_number, class_id) VALUES
('ADM2025001', 'Alice Wanjiku',   'Female', '2010-03-15', '0700111001', 1),
('ADM2025002', 'Brian Otieno',    'Male',   '2010-07-22', '0700111002', 1),
('ADM2025003', 'Carol Achieng',   'Female', '2010-11-08', '0700111003', 1),
('ADM2025004', 'Daniel Kamau',    'Male',   '2009-01-30', '0700111004', 2),
('ADM2025005', 'Esther Muthoni',  'Female', '2009-05-14', '0700111005', 2),
('ADM2025006', 'Francis Ochieng', 'Male',   '2008-09-03', '0700111006', 3),
('ADM2025007', 'Grace Njeri',     'Female', '2008-12-19', '0700111007', 3),
('ADM2025008', 'Henry Kipchoge',  'Male',   '2007-06-25', '0700111008', 4);
