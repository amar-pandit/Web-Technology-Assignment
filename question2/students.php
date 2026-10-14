<?php
/** students.php - list of all students with search and file links */
$pageTitle = 'Students';
require_once __DIR__ . '/includes/header.php';

$search = trim($_GET['q'] ?? '');

try {
    // Prepared statement with LIKE search (parameters are never concatenated)
    $sql = "SELECT s.id, s.student_name, s.roll_number, s.email, s.course,
                   img.id AS image_id, doc.id AS doc_id
              FROM students s
              LEFT JOIN student_files img ON img.student_id = s.id AND img.file_type = 'image'
              LEFT JOIN student_files doc ON doc.student_id = s.id AND doc.file_type = 'document'
             WHERE s.student_name LIKE ? OR s.roll_number LIKE ? OR s.email LIKE ?
             ORDER BY s.student_name ASC";
    $like = '%' . $search . '%';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $ex) {
    error_log($ex->getMessage());
    $students = [];
    echo '<div class="alert alert-error">Could not load students.</div>';
}
?>
<div class="page-head">
  <h1>Students</h1>
  <a class="btn btn-primary" href="add_student.php">+ Add Student</a>
</div>

<form method="get" class="search">
  <input type="text" name="q" placeholder="Search by name, roll number or email" value="<?= e($search) ?>">
  <button class="btn btn-primary" type="submit">Search</button>
  <?php if ($search !== ''): ?><a class="btn btn-light" href="students.php">Clear</a><?php endif; ?>
</form>

<div class="card">
<?php if (!$students): ?>
  <p>No students found.</p>
<?php else: ?>
  <table>
    <thead>
      <tr><th>#</th><th>Photo</th><th>Name</th><th>Roll No.</th><th>Email</th><th>Course</th><th>Document</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($students as $i => $s): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td>
          <?php if ($s['image_id']): ?>
            <img class="thumb" src="download.php?id=<?= (int)$s['image_id'] ?>&amp;inline=1" alt="Photo of <?= e($s['student_name']) ?>">
          <?php else: ?><span class="thumb placeholder">N/A</span><?php endif; ?>
        </td>
        <td><?= e($s['student_name']) ?></td>
        <td><?= e($s['roll_number']) ?></td>
        <td><?= e($s['email']) ?></td>
        <td><?= e($s['course']) ?></td>
        <td>
          <?php if ($s['doc_id']): ?>
            <a href="download.php?id=<?= (int)$s['doc_id'] ?>">Download</a>
          <?php else: ?>&mdash;<?php endif; ?>
        </td>
        <td class="row-actions">
          <a href="view_student.php?id=<?= (int)$s['id'] ?>">View</a>
          <a href="edit_student.php?id=<?= (int)$s['id'] ?>">Edit</a>
          <!-- Delete uses POST + CSRF token, never a plain link -->
          <form method="post" action="delete_student.php" class="inline"
                data-confirm="Delete <?= e($s['student_name']) ?> and all uploaded files?">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
            <button type="submit" class="link danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
