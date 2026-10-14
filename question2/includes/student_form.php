<?php
/**
 * includes/student_form.php - form shared by add_student.php and edit_student.php
 * Expects: $student (array of current values), $isEdit (bool), $files (array)
 */
?>
<form method="post" enctype="multipart/form-data" id="studentForm" novalidate>
  <?= csrf_field() ?>

  <div class="grid">
    <div class="form-group">
      <label for="student_name">Student Name *</label>
      <input type="text" id="student_name" name="student_name" maxlength="100"
             value="<?= e($student['student_name'] ?? '') ?>" required>
    </div>

    <div class="form-group">
      <label for="roll_number">Roll Number *</label>
      <input type="text" id="roll_number" name="roll_number" maxlength="20"
             placeholder="24CS255" value="<?= e($student['roll_number'] ?? '') ?>" required>
    </div>

    <div class="form-group">
      <label for="email">Email *</label>
      <input type="email" id="email" name="email" maxlength="120"
             value="<?= e($student['email'] ?? '') ?>" required>
    </div>

    <div class="form-group">
      <label for="phone">Phone</label>
      <input type="text" id="phone" name="phone" maxlength="15"
             value="<?= e($student['phone'] ?? '') ?>">
    </div>

    <div class="form-group full">
      <label for="course">Course *</label>
      <select id="course" name="course" required>
        <option value="">-- Select Course --</option>
        <?php foreach (course_options() as $c): ?>
          <option value="<?= e($c) ?>" <?= (($student['course'] ?? '') === $c) ? 'selected' : '' ?>>
            <?= e($c) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="profile_image">Profile Image (JPG/PNG, max 2 MB)</label>
      <input type="file" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png">
      <?php if ($isEdit && !empty($files['image'])): ?>
        <small>Current: <?= e($files['image']['original_name']) ?> (upload a new one to replace it)</small>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label for="document">Document (PDF, max 5 MB)</label>
      <input type="file" id="document" name="document" accept=".pdf">
      <?php if ($isEdit && !empty($files['document'])): ?>
        <small>Current: <?= e($files['document']['original_name']) ?> (upload a new one to replace it)</small>
      <?php endif; ?>
    </div>
  </div>

  <div class="actions">
    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update Student' : 'Add Student' ?></button>
    <a href="students.php" class="btn btn-light">Cancel</a>
  </div>
</form>
