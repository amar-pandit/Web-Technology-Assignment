// script.js - client-side validation for the Student Registration Form
"use strict";

// ---------- Cached DOM references ----------
const form = document.getElementById("registrationForm");
const fields = {
  studentName: document.getElementById("studentName"),
  rollNumber: document.getElementById("rollNumber"),
  email: document.getElementById("email"),
  course: document.getElementById("course"),
  password: document.getElementById("password"),
  confirmPassword: document.getElementById("confirmPassword"),
  fileUpload: document.getElementById("fileUpload")
};
const successBox = document.getElementById("successMessage");
const strengthFill = document.getElementById("strengthFill");
const strengthText = document.getElementById("strengthText");

// ---------- Validation rules ----------
const NAME_REGEX = /^[A-Za-z][A-Za-z .'-]{2,49}$/;             // letters/spaces, 3-50 chars
const ROLL_REGEX = /^[0-9]{2}[A-Za-z]{2,4}[0-9]{1,4}$/;        // e.g. 24CS255
const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;           // simple email pattern
const MAX_FILE_SIZE = 2 * 1024 * 1024;                          // 2 MB
const ALLOWED_TYPES = ["image/jpeg", "image/png", "application/pdf"];
const ALLOWED_EXTENSIONS = ["jpg", "jpeg", "png", "pdf"];

// ---------- Helper functions to show / clear messages ----------
function showError(fieldName, message) {
  const input = fields[fieldName];
  document.getElementById(fieldName + "Error").textContent = message;
  input.classList.add("invalid");
  input.classList.remove("valid");
  return false;
}

function showValid(fieldName) {
  const input = fields[fieldName];
  document.getElementById(fieldName + "Error").textContent = "";
  input.classList.remove("invalid");
  input.classList.add("valid");
  return true;
}

// ---------- Individual field validators (each returns true/false) ----------
function validateName() {
  const value = fields.studentName.value.trim();
  if (value === "") return showError("studentName", "Student name is required.");
  if (!NAME_REGEX.test(value)) {
    return showError("studentName", "Use 3-50 letters (spaces, . ' - allowed).");
  }
  return showValid("studentName");
}

function validateRoll() {
  const value = fields.rollNumber.value.trim();
  if (value === "") return showError("rollNumber", "Roll number is required.");
  if (!ROLL_REGEX.test(value)) {
    return showError("rollNumber", "Invalid roll number format (example: 24CS255).");
  }
  return showValid("rollNumber");
}

function validateEmail() {
  const value = fields.email.value.trim();
  if (value === "") return showError("email", "Email is required.");
  if (!EMAIL_REGEX.test(value)) {
    return showError("email", "Enter a valid email address (name@domain.com).");
  }
  return showValid("email");
}

function validateCourse() {
  if (fields.course.value === "") {
    return showError("course", "Please select a course.");
  }
  return showValid("course");
}

// Returns a score from 0 to 5 based on the rules satisfied
function passwordScore(pwd) {
  let score = 0;
  if (pwd.length >= 8) score++;
  if (/[a-z]/.test(pwd)) score++;
  if (/[A-Z]/.test(pwd)) score++;
  if (/[0-9]/.test(pwd)) score++;
  if (/[^A-Za-z0-9]/.test(pwd)) score++;
  return score;
}

function validatePassword() {
  const pwd = fields.password.value;
  if (pwd === "") return showError("password", "Password is required.");
  if (pwd.length < 8) return showError("password", "Password must be at least 8 characters.");
  if (!/[a-z]/.test(pwd)) return showError("password", "Add at least one lowercase letter.");
  if (!/[A-Z]/.test(pwd)) return showError("password", "Add at least one uppercase letter.");
  if (!/[0-9]/.test(pwd)) return showError("password", "Add at least one digit.");
  if (!/[^A-Za-z0-9]/.test(pwd)) return showError("password", "Add at least one special character.");
  return showValid("password");
}

function validateConfirmPassword() {
  const confirm = fields.confirmPassword.value;
  if (confirm === "") return showError("confirmPassword", "Please confirm your password.");
  if (confirm !== fields.password.value) {
    return showError("confirmPassword", "Passwords do not match.");
  }
  return showValid("confirmPassword");
}

function validateFile() {
  const file = fields.fileUpload.files[0];
  if (!file) return showError("fileUpload", "Please choose a file to upload.");

  const extension = file.name.split(".").pop().toLowerCase();
  if (!ALLOWED_EXTENSIONS.includes(extension) || !ALLOWED_TYPES.includes(file.type)) {
    return showError("fileUpload", "Only JPG, PNG or PDF files are allowed.");
  }
  if (file.size > MAX_FILE_SIZE) {
    return showError("fileUpload", "File is too large (maximum 2 MB).");
  }
  return showValid("fileUpload");
}

// ---------- Password strength meter ----------
function updateStrengthMeter() {
  const pwd = fields.password.value;
  const score = pwd === "" ? 0 : passwordScore(pwd);
  const levels = [
    { text: "",            color: "#e5e7eb", width: "0%" },
    { text: "Very Weak",   color: "#dc2626", width: "20%" },
    { text: "Weak",        color: "#f97316", width: "40%" },
    { text: "Fair",        color: "#eab308", width: "60%" },
    { text: "Strong",      color: "#65a30d", width: "80%" },
    { text: "Very Strong", color: "#16a34a", width: "100%" }
  ];
  const level = levels[score];
  strengthFill.style.width = level.width;
  strengthFill.style.background = level.color;
  strengthText.textContent = level.text ? "Strength: " + level.text : "";
  strengthText.style.color = level.color;
}

// ---------- Live validation while the user types ----------
fields.studentName.addEventListener("input", validateName);
fields.rollNumber.addEventListener("input", validateRoll);
fields.email.addEventListener("input", validateEmail);
fields.course.addEventListener("change", validateCourse);
fields.password.addEventListener("input", function () {
  updateStrengthMeter();
  validatePassword();
  if (fields.confirmPassword.value !== "") validateConfirmPassword();
});
fields.confirmPassword.addEventListener("input", validateConfirmPassword);
fields.fileUpload.addEventListener("change", validateFile);

// ---------- Form submission ----------
form.addEventListener("submit", function (event) {
  event.preventDefault();           // stop the page from reloading
  successBox.hidden = true;

  // Run every validator (no short-circuit, so all errors are shown at once)
  const results = [
    validateName(),
    validateRoll(),
    validateEmail(),
    validateCourse(),
    validatePassword(),
    validateConfirmPassword(),
    validateFile()
  ];

  if (results.every(Boolean)) {
    successBox.textContent =
      "Registration successful for " + fields.studentName.value.trim() + "!";
    successBox.hidden = false;
    // In a real application the data would now be sent to a server.
  } else {
    // Move focus to the first invalid field
    const firstInvalid = form.querySelector(".invalid");
    if (firstInvalid) firstInvalid.focus();
  }
});

// ---------- Reset handler: clear messages and styles ----------
form.addEventListener("reset", function () {
  Object.keys(fields).forEach(function (name) {
    document.getElementById(name + "Error").textContent = "";
    fields[name].classList.remove("invalid", "valid");
  });
  successBox.hidden = true;
  // Wait until the browser clears the values, then refresh the meter
  setTimeout(updateStrengthMeter, 0);
});
