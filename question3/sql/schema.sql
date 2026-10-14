-- schema.sql : Student Profile Portal (Question 3)
-- Run with:  mysql -u root -p < sql/schema.sql

CREATE DATABASE IF NOT EXISTS student_portal
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE student_portal;

-- ---------------------------------------------------------
-- users: portal login accounts (passwords are stored hashed)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username       VARCHAR(50)  NOT NULL,
  full_name      VARCHAR(100) NOT NULL,
  password_hash  VARCHAR(255) NOT NULL,   -- pbkdf2$iterations$saltHex$hashHex
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- students: profile data. photo_file / document_file hold only a
-- file NAME; the file lives in <upload.dir>/photos or /documents.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  roll_number    VARCHAR(20)  NOT NULL,
  name           VARCHAR(100) NOT NULL,
  email          VARCHAR(120) NOT NULL,
  course         VARCHAR(80)  NOT NULL,
  photo_file     VARCHAR(100) DEFAULT NULL,
  document_file  VARCHAR(100) DEFAULT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_students_roll (roll_number),
  UNIQUE KEY uq_students_email (email)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Sample login:  username = admin   password = Admin@123
-- (hash generated with com.kpr.portal.util.PasswordUtil)
-- Change this password for any real use.
-- ---------------------------------------------------------
INSERT INTO users (username, full_name, password_hash) VALUES
('admin', 'Portal Administrator',
 'pbkdf2$120000$a34be83e19f28dd598426968b90f7005$0be6c1d40fdecb5f05826775c7ea94f12a93691f9659f0fbd309b833d8a1d67d');

-- ---------------------------------------------------------
-- Sample students (no files yet - see README for attaching your own
-- photos / PDFs with the UPDATE statements below)
-- ---------------------------------------------------------
INSERT INTO students (roll_number, name, email, course) VALUES
('24CS255', 'Amar Kumar Pandit', 'amar.pandit@example.com',  'B.E. Computer Science and Engineering'),
('24CS101', 'Priya Sharma',      'priya.sharma@example.com', 'B.E. Computer Science and Engineering'),
('24IT042', 'Rahul Verma',       'rahul.verma@example.com',  'B.Tech Information Technology');

-- To attach files: copy them into <upload.dir>/photos and <upload.dir>/documents, then e.g.
--   UPDATE students SET photo_file = 'amar.jpg', document_file = 'amar_resume.pdf'
--    WHERE roll_number = '24CS255';
-- File names may contain only letters, digits, dot, underscore, hyphen and must
-- end in .jpg, .jpeg, .png (photos) or .pdf (documents).
