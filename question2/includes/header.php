<?php
/** includes/header.php - common page header and navigation */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'Student Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | SMS</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
  <div class="wrap">
    <span class="brand">Student Management System</span>
    <nav>
      <a href="index.php">Dashboard</a>
      <a href="students.php">Students</a>
      <a href="add_student.php">Add Student</a>
    </nav>
  </div>
</header>
<main class="wrap">
<?php show_flash(); ?>
