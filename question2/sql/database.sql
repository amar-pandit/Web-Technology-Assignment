-- database.sql : Student Management System (Question 2)
-- Run with:  mysql -u root -p < sql/database.sql

CREATE DATABASE IF NOT EXISTS student_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE student_management;

-- ---------------------------------------------------------
-- Table 1: students (core student data)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_name  VARCHAR(100) NOT NULL,
  roll_number   VARCHAR(20)  NOT NULL,
  email         VARCHAR(120) NOT NULL,
  phone         VARCHAR(15)  DEFAULT NULL,
  course        VARCHAR(60)  NOT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_students_roll (roll_number),
  UNIQUE KEY uq_students_email (email)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table 2: student_files (metadata of uploaded files)
-- One student can have one profile image and one document.
-- The file itself lives on disk; only metadata is stored here.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS student_files (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_id     INT UNSIGNED NOT NULL,
  file_type      ENUM('image','document') NOT NULL,
  original_name  VARCHAR(255) NOT NULL,   -- name shown to the user
  stored_name    VARCHAR(100) NOT NULL,   -- unique random name on disk
  mime_type      VARCHAR(100) NOT NULL,
  file_size      INT UNSIGNED NOT NULL,   -- bytes
  uploaded_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_stored_name (stored_name),
  UNIQUE KEY uq_student_filetype (student_id, file_type),
  CONSTRAINT fk_files_student
    FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;
