<?php
/**
 * delete_student.php - deletes a student and their files.
 * Only accepts POST requests protected by a CSRF token.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}
csrf_check();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
if ($id <= 0) {
    flash('error', 'Invalid student id.');
    redirect('students.php');
}

try {
    // 1. Remember the stored filenames before the rows disappear
    $stmt = $conn->prepare('SELECT file_type, stored_name FROM student_files WHERE student_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $files = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // 2. Delete the student (ON DELETE CASCADE removes student_files rows)
    $stmt = $conn->prepare('DELETE FROM students WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $deleted = $stmt->affected_rows;
    $stmt->close();

    // 3. Remove the physical files only after the database delete succeeded
    if ($deleted > 0) {
        foreach ($files as $f) {
            delete_stored_file($f['file_type'], $f['stored_name']);
        }
        flash('success', 'Student deleted successfully.');
    } else {
        flash('error', 'Student not found.');
    }
} catch (mysqli_sql_exception $ex) {
    error_log($ex->getMessage());
    flash('error', 'Database error: the student could not be deleted.');
}
redirect('students.php');
