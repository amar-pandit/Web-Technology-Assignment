# Question 2 - PHP Student Management System

Technology: PHP 7.4+/8.x, MySQL/MariaDB, HTML, CSS, JavaScript.

## Setup
1. Install XAMPP/WAMP/LAMP (Apache + PHP + MySQL) and enable the `fileinfo` and `mysqli` PHP extensions (enabled by default in XAMPP).
2. Copy the `question2` folder into the web root, for example `htdocs/question2`.
3. Import the database:
   `mysql -u root -p < sql/database.sql`
   (or paste `sql/database.sql` into phpMyAdmin's SQL tab).
4. Edit `config/db.php` if your MySQL user/password differ.
5. Make sure `uploads/images` and `uploads/documents` are writable by the web server.
6. Open `http://localhost/question2/` in a browser.

## Structure
```
question2/
  index.php            dashboard
  students.php         student list + search
  add_student.php      add form
  edit_student.php     edit form
  delete_student.php   delete (POST + CSRF)
  view_student.php     details page
  download.php         streams / downloads uploaded files
  config/db.php        connection + settings
  includes/            header, footer, shared form, helper functions
  assets/              css and js
  sql/database.sql     database + tables
  uploads/             private storage (blocked by .htaccess)
```

## Security features
- MySQLi prepared statements for every query that uses input
- Output escaping with `htmlspecialchars` (XSS)
- CSRF token on all POST forms
- File upload: extension whitelist, real MIME check, size limits, random unique filenames, storage outside direct web access
- Files served only through `download.php` (path-traversal-safe)
