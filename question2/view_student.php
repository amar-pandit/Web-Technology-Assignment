<?php
/** view_student.php - details page with profile image and document download */
$pageTitle = 'Student Details';
require_once __DIR__ . '/includes/header.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
if ($id <= 0) {
    flash('error', 'Invalid student id.');
    redirect('students.php');
}

$stmt = $conn->prepare('SELECT * FROM students WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$s) {
    flash('error', 'Student not found.');
    redirect('students.php');
}

$files = [];
$stmt = $conn->prepare('SELECT * FROM student_files WHERE student_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $f) {
    $files[$f['file_type']] = $f;
}
$stmt->close();
?>
<div class="page-head">
  <h1>Student Details</h1>
  <div>
    <a class="btn btn-primary" href="edit_student.php?id=<?= (int)$s['id'] ?>">Edit</a>
    <a class="btn btn-light" href="students.php">Back to list</a>
  </div>
</div>

<div class="card profile">
  <div class="photo">
    <?php if (!empty($files['image'])): ?>
      <img src="download.php?id=<?= (int)$files['image']['id'] ?>&amp;inline=1"
           alt="Profile photo of <?= e($s['student_name']) ?>">
    <?php else: ?>
      <div class="no-photo">No profile image</div>
    <?php endif; ?>
  </div>

  <table class="details">
    <tr><th>Name</th><td><?= e($s['student_name']) ?></td></tr>
    <tr><th>Roll Number</th><td><?= e($s['roll_number']) ?></td></tr>
    <tr><th>Email</th><td><?= e($s['email']) ?></td></tr>
    <tr><th>Phone</th><td><?= $s['phone'] !== null ? e($s['phone']) : '&mdash;' ?></td></tr>
    <tr><th>Course</th><td><?= e($s['course']) ?></td></tr>
    <tr><th>Registered</th><td><?= e(date('d M Y, h:i A', strtotime($s['created_at']))) ?></td></tr>
    <tr>
      <th>Document</th>
      <td>
        <?php if (!empty($files['document'])): $d = $files['document']; ?>
          <?= e($d['original_name']) ?> (<?= e(human_size((int)$d['file_size'])) ?>)
          &nbsp;<a href="download.php?id=<?= (int)$d['id'] ?>">Download</a>
        <?php else: ?>No document uploaded<?php endif; ?>
      </td>
    </tr>
    <?php if (!empty($files['image'])): $im = $files['image']; ?>
    <tr>
      <th>Image file</th>
      <td>
        <?= e($im['original_name']) ?> (<?= e(human_size((int)$im['file_size'])) ?>)
        &nbsp;<a href="download.php?id=<?= (int)$im['id'] ?>">Download</a>
      </td>
    </tr>
    <?php endif; ?>
  </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
