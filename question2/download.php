<?php
/**
 * download.php - streams an uploaded file.
 *   download.php?id=5              -> forces download
 *   download.php?id=5&inline=1     -> shows images inside <img> tags
 * Files live in a protected folder, so this script is the only way to read them.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
if ($id <= 0) {
    http_response_code(400);
    die('Invalid file id.');
}

try {
    $stmt = $conn->prepare('SELECT file_type, original_name, stored_name, mime_type FROM student_files WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $file = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (mysqli_sql_exception $ex) {
    error_log($ex->getMessage());
    http_response_code(500);
    die('Database error.');
}

if (!$file) {
    http_response_code(404);
    die('File not found.');
}

// Path traversal safe: stored_path() validates the name and uses realpath()
$path = stored_path($file['file_type'], $file['stored_name']);
if ($path === '' || !is_file($path)) {
    http_response_code(404);
    die('File is missing on the server.');
}

// Only images may be displayed inline; everything else is downloaded
$inline = isset($_GET['inline']) && $file['file_type'] === 'image';
$disposition = $inline ? 'inline' : 'attachment';

// Clean filename for the Content-Disposition header
$downloadName = str_replace(['"', "\r", "\n"], '', $file['original_name']);

header('Content-Type: ' . $file['mime_type']);
header('Content-Length: ' . filesize($path));
header('Content-Disposition: ' . $disposition . '; filename="' . $downloadName . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=3600');
readfile($path);
exit;
