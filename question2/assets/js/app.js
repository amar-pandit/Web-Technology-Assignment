// assets/js/app.js - client-side helpers (server-side validation is still the final authority)
"use strict";

document.addEventListener("DOMContentLoaded", function () {

  // ---------- Confirm before deleting ----------
  document.querySelectorAll("form[data-confirm]").forEach(function (form) {
    form.addEventListener("submit", function (event) {
      if (!window.confirm(form.getAttribute("data-confirm"))) {
        event.preventDefault();
      }
    });
  });

  // ---------- Student form validation ----------
  const form = document.getElementById("studentForm");
  if (!form) return;

  const MAX_IMAGE = 2 * 1024 * 1024;   // 2 MB
  const MAX_DOC = 5 * 1024 * 1024;     // 5 MB

  function fail(input, message, messages) {
    input.classList.add("invalid");
    messages.push(message);
  }

  form.addEventListener("submit", function (event) {
    const messages = [];
    form.querySelectorAll(".invalid").forEach(function (el) { el.classList.remove("invalid"); });

    const name = form.student_name;
    const roll = form.roll_number;
    const email = form.email;
    const course = form.course;
    const image = form.profile_image.files[0];
    const doc = form.document.files[0];

    if (!/^[A-Za-z][A-Za-z .'-]{2,99}$/.test(name.value.trim())) fail(name, "Enter a valid name (3-100 letters).", messages);
    if (!/^[0-9]{2}[A-Za-z]{2,4}[0-9]{1,4}$/.test(roll.value.trim())) fail(roll, "Roll number must look like 24CS255.", messages);
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.value.trim())) fail(email, "Enter a valid email address.", messages);
    if (form.phone.value.trim() !== "" && !/^[0-9+ -]{7,15}$/.test(form.phone.value.trim())) fail(form.phone, "Phone must be 7-15 digits.", messages);
    if (course.value === "") fail(course, "Please select a course.", messages);

    if (image) {
      if (!/\.(jpe?g|png)$/i.test(image.name)) fail(form.profile_image, "Profile image must be JPG or PNG.", messages);
      else if (image.size > MAX_IMAGE) fail(form.profile_image, "Profile image must be under 2 MB.", messages);
    }
    if (doc) {
      if (!/\.pdf$/i.test(doc.name)) fail(form.document, "Document must be a PDF.", messages);
      else if (doc.size > MAX_DOC) fail(form.document, "Document must be under 5 MB.", messages);
    }

    if (messages.length > 0) {
      event.preventDefault();
      window.alert(messages.join("\n"));
      const firstBad = form.querySelector(".invalid");
      if (firstBad) firstBad.focus();
    }
  });
});
