<?php
/** index.php - dashboard with summary statistics and recent students */
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

try {
    // Summary counts (no user input, so a plain query is safe here)
    $totalStudents = (int)$conn->query('SELECT COUNT(*) FROM students')->fetch_row()[0];
    $totalImages   = (int)$conn->query("SELECT COUNT(*) FROM student_files WHERE file_type='image'")->fetch_row()[0];
    $totalDocs     = (int)$conn->query("SELECT COUNT(*) FROM student_files WHERE file_type='document'")->fetch_row()[0];

    // Five most recently added students with their profile image id (if any)
    $recent = $conn->query(
        "SELECT s.id, s.student_name, s.roll_number, s.course, s.created_at, f.id AS image_id
           FROM students s
           LEFT JOIN student_files f ON f.student_id = s.id AND f.file_type = 'image'
          ORDER BY s.id DESC LIMIT 5"
    )->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $ex) {
    error_log($ex->getMessage());
    echo '<div class="alert alert-error">Could not load dashboard data. Did you import sql/database.sql?</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
?>
<h1>Dashboard</h1>

<div class="cards">
  <div class="card stat"><span class="num"><?= $totalStudents ?></span><span>Total Students</span></div>
  <div class="card stat"><span class="num"><?= $totalImages ?></span><span>Profile Images</span></div>
  <div class="card stat"><span class="num"><?= $totalDocs ?></span><span>Documents</span></div>
</div>

<div class="card">
  <h2>Recently Added Students</h2>
  <?php if (!$recent): ?>
    <p>No students yet. <a href="add_student.php">Add the first student</a>.</p>
  <?php else: ?>
  <table>
    <thead><tr><th>Photo</th><th>Name</th><th>Roll No.</th><th>Course</th><th>Added</th></tr></thead>
    <tbody>
    <?php foreach ($recent as $r): ?>
      <tr>
        <td>
          <?php if ($r['image_id']): ?>
            <img class="thumb" src="download.php?id=<?= (int)$r['image_id'] ?>&amp;inline=1" alt="Photo of <?= e($r['student_name']) ?>">
          <?php else: ?><span class="thumb placeholder">N/A</span><?php endif; ?>
        </td>
        <td><a href="view_student.php?id=<?= (int)$r['id'] ?>"><?= e($r['student_name']) ?></a></td>
        <td><?= e($r['roll_number']) ?></td>
        <td><?= e($r['course']) ?></td>
        <td><?= e(date('d M Y', strtotime($r['created_at']))) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
