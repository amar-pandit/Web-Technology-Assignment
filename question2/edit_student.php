<?php
/** edit_student.php - update a student; uploads replace existing files */
$pageTitle = 'Edit Student';
require_once __DIR__ . '/includes/header.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
if ($id <= 0) {
    flash('error', 'Invalid student id.');
    redirect('students.php');
}

// Load the existing student (prepared statement)
$stmt = $conn->prepare('SELECT * FROM students WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    flash('error', 'Student not found.');
    redirect('students.php');
}

// Load current files, indexed by type ('image' / 'document')
$files = [];
$stmt = $conn->prepare('SELECT * FROM student_files WHERE student_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $f) {
    $files[$f['file_type']] = $f;
}
$stmt->close();

$errors = [];
$isEdit = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $student = array_merge($student, [
        'student_name' => trim($_POST['student_name'] ?? ''),
        'roll_number'  => strtoupper(trim($_POST['roll_number'] ?? '')),
        'email'        => trim($_POST['email'] ?? ''),
        'phone'        => trim($_POST['phone'] ?? ''),
        'course'       => trim($_POST['course'] ?? ''),
    ]);

    $errors = validate_student($student, $conn, $id);   // $id is ignored in the unique check

    $image = $doc = null;
    if (!$errors) {
        $image = process_upload('profile_image', 'image', $errors);
        $doc   = process_upload('document', 'document', $errors);
    }

    if (!$errors) {
        try {
            $conn->begin_transaction();

            $phone = $student['phone'] === '' ? null : $student['phone'];
            $stmt = $conn->prepare(
                'UPDATE students SET student_name=?, roll_number=?, email=?, phone=?, course=? WHERE id=?'
            );
            $stmt->bind_param('sssssi', $student['student_name'], $student['roll_number'],
                              $student['email'], $phone, $student['course'], $id);
            $stmt->execute();
            $stmt->close();

            // save_file_record() swaps the old file for the new one
            foreach ([$image, $doc] as $info) {
                if ($info) save_file_record($conn, $id, $info);
            }
            $conn->commit();

            flash('success', 'Student updated successfully.');
            redirect('view_student.php?id=' . $id);
        } catch (mysqli_sql_exception $ex) {
            $conn->rollback();
            foreach ([$image, $doc] as $info) {
                if ($info) delete_stored_file($info['file_type'], $info['stored_name']);
            }
            error_log($ex->getMessage());
            $errors[] = 'Database error: the student could not be updated.';
        }
    } else {
        foreach ([$image ?? null, $doc ?? null] as $info) {
            if ($info) delete_stored_file($info['file_type'], $info['stored_name']);
        }
    }
}
?>
<h1>Edit Student</h1>
<?php foreach ($errors as $err): ?>
  <div class="alert alert-error"><?= e($err) ?></div>
<?php endforeach; ?>
<div class="card">
  <?php require __DIR__ . '/includes/student_form.php'; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
