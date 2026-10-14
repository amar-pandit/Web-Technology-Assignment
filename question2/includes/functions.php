<?php
/**
 * includes/functions.php - helpers: escaping, CSRF, flash messages,
 * validation and secure file upload handling.
 */

// ---------- Output escaping (prevents XSS) ----------
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// ---------- CSRF protection ----------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(403);
        die('Invalid or missing security token. Please go back and try again.');
    }
}

// ---------- Flash messages (shown once after a redirect) ----------
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function show_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $f) {
            echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['message']) . '</div>';
        }
        unset($_SESSION['flash']);
    }
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// ---------- Course list used by the forms ----------
function course_options(): array
{
    return [
        'B.E. Computer Science and Engineering',
        'B.Tech Information Technology',
        'B.E. Electronics and Communication',
        'B.E. Electrical and Electronics',
        'B.E. Mechanical Engineering',
    ];
}

// ---------- Validation of text fields ----------
// Returns array of error messages (empty array = valid)
function validate_student(array $data, mysqli $conn, int $ignoreId = 0): array
{
    $errors = [];

    $name   = trim($data['student_name'] ?? '');
    $roll   = trim($data['roll_number'] ?? '');
    $email  = trim($data['email'] ?? '');
    $phone  = trim($data['phone'] ?? '');
    $course = trim($data['course'] ?? '');

    if ($name === '' || !preg_match("/^[A-Za-z][A-Za-z .'-]{2,99}$/", $name)) {
        $errors[] = 'Enter a valid student name (3-100 letters).';
    }
    if (!preg_match('/^[0-9]{2}[A-Za-z]{2,4}[0-9]{1,4}$/', $roll)) {
        $errors[] = 'Roll number format is invalid (example: 24CS255).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($phone !== '' && !preg_match('/^[0-9+ -]{7,15}$/', $phone)) {
        $errors[] = 'Phone number must be 7-15 digits (+, space and - allowed).';
    }
    if (!in_array($course, course_options(), true)) {
        $errors[] = 'Please select a valid course.';
    }

    // Uniqueness checks with prepared statements
    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM students WHERE roll_number = ? AND id <> ?');
        $stmt->bind_param('si', $roll, $ignoreId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'This roll number is already registered.';
        }
        $stmt->close();

        $stmt = $conn->prepare('SELECT id FROM students WHERE email = ? AND id <> ?');
        $stmt->bind_param('si', $email, $ignoreId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'This email is already registered.';
        }
        $stmt->close();
    }
    return $errors;
}

// ---------- Secure file upload ----------
/**
 * Validates and moves one uploaded file.
 *
 * @param string $field  name of the <input type="file">
 * @param string $kind   'image' or 'document'
 * @param array  $errors collects error messages (by reference)
 * @return array|null    file info on success, null if nothing uploaded / error
 */
function process_upload(string $field, string $kind, array &$errors): ?array
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;   // the file is optional
    }
    $file  = $_FILES[$field];
    $label = $kind === 'image' ? 'Profile image' : 'Document';

    // 1. PHP upload error codes
    $phpErrors = [
        UPLOAD_ERR_INI_SIZE   => 'exceeds the server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'exceeds the form size limit.',
        UPLOAD_ERR_PARTIAL    => 'was only partially uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'could not be saved (missing temp folder).',
        UPLOAD_ERR_CANT_WRITE => 'could not be written to disk.',
        UPLOAD_ERR_EXTENSION  => 'was blocked by a PHP extension.',
    ];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = $label . ' ' . ($phpErrors[$file['error']] ?? 'upload failed.');
        return null;
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        $errors[] = $label . ' upload was not valid.';
        return null;
    }

    // 2. Rules for each kind
    if ($kind === 'image') {
        $allowed = ['jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png']];
        $maxSize = MAX_IMAGE_SIZE;
    } else {
        $allowed = ['pdf' => ['application/pdf']];
        $maxSize = MAX_DOC_SIZE;
    }

    // 3. Size check
    if ($file['size'] <= 0 || $file['size'] > $maxSize) {
        $errors[] = $label . ' must be smaller than ' . ($maxSize / 1024 / 1024) . ' MB.';
        return null;
    }

    // 4. Extension check (case-insensitive whitelist)
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!isset($allowed[$ext])) {
        $errors[] = $label . ' type not allowed. Allowed: ' . strtoupper(implode(', ', array_keys($allowed))) . '.';
        return null;
    }

    // 5. Real MIME type check (does not trust the browser-supplied type)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed[$ext], true)) {
        $errors[] = $label . ' content does not match its extension.';
        return null;
    }
    // Extra check: images must be decodable
    if ($kind === 'image' && @getimagesize($file['tmp_name']) === false) {
        $errors[] = $label . ' is not a valid image.';
        return null;
    }

    // 6. Unique, unpredictable filename; original name is never used on disk
    $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
    $subDir     = $kind === 'image' ? 'images' : 'documents';
    $targetDir  = UPLOAD_DIR . '/' . $subDir;
    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
        $errors[] = 'Upload folder could not be created.';
        return null;
    }
    if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $storedName)) {
        $errors[] = $label . ' could not be saved.';
        return null;
    }

    // Original name: strip path parts and control characters
    $original = preg_replace('/[^\w .()-]/u', '_', basename($file['name']));

    return [
        'file_type'     => $kind,
        'original_name' => mb_substr($original, 0, 255),
        'stored_name'   => $storedName,
        'mime_type'     => $mime,
        'file_size'     => (int)$file['size'],
    ];
}

// Absolute path of a stored file ('' if unsafe / missing)
function stored_path(string $kind, string $storedName): string
{
    if (!preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|pdf)$/', $storedName)) {
        return '';
    }
    $sub  = $kind === 'image' ? 'images' : 'documents';
    $path = realpath(UPLOAD_DIR . '/' . $sub . '/' . $storedName);
    $base = realpath(UPLOAD_DIR . '/' . $sub);
    if ($path === false || $base === false || strpos($path, $base . DIRECTORY_SEPARATOR) !== 0) {
        return '';
    }
    return $path;
}

function delete_stored_file(string $kind, string $storedName): void
{
    $path = stored_path($kind, $storedName);
    if ($path !== '' && is_file($path)) {
        @unlink($path);
    }
}

// Inserts a file metadata row (replaces an existing file of the same type)
function save_file_record(mysqli $conn, int $studentId, array $info): void
{
    // Find old file (if any) so it can be removed from disk after the swap
    $stmt = $conn->prepare('SELECT stored_name FROM student_files WHERE student_id = ? AND file_type = ?');
    $stmt->bind_param('is', $studentId, $info['file_type']);
    $stmt->execute();
    $old = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare('DELETE FROM student_files WHERE student_id = ? AND file_type = ?');
    $stmt->bind_param('is', $studentId, $info['file_type']);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare(
        'INSERT INTO student_files (student_id, file_type, original_name, stored_name, mime_type, file_size)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('issssi', $studentId, $info['file_type'], $info['original_name'],
                      $info['stored_name'], $info['mime_type'], $info['file_size']);
    $stmt->execute();
    $stmt->close();

    if ($old) {
        delete_stored_file($info['file_type'], $old['stored_name']);
    }
}

function human_size(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}
