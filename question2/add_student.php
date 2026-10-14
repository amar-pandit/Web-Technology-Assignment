<?php
/** add_student.php - create a new student with optional image and document */
$pageTitle = 'Add Student';
require_once __DIR__ . '/includes/header.php';

$errors  = [];
$student = [];
$files   = [];
$isEdit  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $student = [
        'student_name' => trim($_POST['student_name'] ?? ''),
        'roll_number'  => strtoupper(trim($_POST['roll_number'] ?? '')),
        'email'        => trim($_POST['email'] ?? ''),
        'phone'        => trim($_POST['phone'] ?? ''),
        'course'       => trim($_POST['course'] ?? ''),
    ];

    // 1. Validate text fields
    $errors = validate_student($student, $conn);

    // 2. Validate and move uploaded files (only if the text fields are fine)
    $image = $doc = null;
    if (!$errors) {
        $image = process_upload('profile_image', 'image', $errors);
        $doc   = process_upload('document', 'document', $errors);
    }

    // 3. Save everything in one transaction
    if (!$errors) {
        try {
            $conn->begin_transaction();

            $phone = $student['phone'] === '' ? null : $student['phone'];
            $stmt = $conn->prepare(
                'INSERT INTO students (student_name, roll_number, email, phone, course) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('sssss', $student['student_name'], $student['roll_number'],
                              $student['email'], $phone, $student['course']);
            $stmt->execute();
            $newId = $conn->insert_id;
            $stmt->close();

            foreach ([$image, $doc] as $info) {
                if ($info) save_file_record($conn, $newId, $info);
            }
            $conn->commit();

            flash('success', 'Student added successfully.');
            redirect('view_student.php?id=' . $newId);
        } catch (mysqli_sql_exception $ex) {
            $conn->rollback();
            // Remove files already moved to disk because the database save failed
            foreach ([$image, $doc] as $info) {
                if ($info) delete_stored_file($info['file_type'], $info['stored_name']);
            }
            error_log($ex->getMessage());
            $errors[] = 'Database error: the student could not be saved.';
        }
    } else {
        // Validation failed after files were moved: clean them up
        foreach ([$image ?? null, $doc ?? null] as $info) {
            if ($info) delete_stored_file($info['file_type'], $info['stored_name']);
        }
    }
}
?>
<h1>Add Student</h1>
<?php foreach ($errors as $err): ?>
  <div class="alert alert-error"><?= e($err) ?></div>
<?php endforeach; ?>
<div class="card">
  <?php require __DIR__ . '/includes/student_form.php'; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
