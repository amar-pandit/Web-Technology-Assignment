# Web Technology Assignment (U21CS501)

Student: Amar Kumar Pandit | Roll No: 24CS255 | CSE, III Year, Semester V
KPR Institute of Engineering and Technology | Academic Year 2026-2027 | Submission: 14 October 2026

## Contents
| Folder | Question | Technology |
|--------|----------|------------|
| `question1/` | Student Registration Form (8 marks) | HTML, CSS, JavaScript |
| `question2/` | PHP Student Management System (16 marks) | PHP, MySQL, HTML, CSS, JS |
| `question3/` | Java Servlet Student Profile Portal (16 marks) | Servlet, JSP, JDBC, MySQL, Maven, Tomcat 9 |
| `docs/`      | `Amar_Kumar_Pandit_24CS255_Web_Technology_Assignment.pdf` - the assignment report | |

Each question folder has its own `README.md` with setup steps.
The code printed in the PDF was read directly from these files.

## Screenshots
The PDF contains bordered, clearly labelled boxes ("PASTE YOUR OUTPUT SCREENSHOT HERE").
Run each project on your own machine and add your own screenshots. No screenshots or
program output were generated or faked.

## What was and was not checked when these files were generated
Checked:
- `node --check` on `question1/script.js` and `question2/assets/js/app.js` - no syntax errors
- `php -l` on all 12 PHP files in `question2/` - no syntax errors
- `javac --release 11` on the utility, model and DAO classes in `question3/` - compiled
- `PasswordUtil` run directly: correct password accepted, wrong password and malformed hash rejected, two hashes of the same password differ (salted)

NOT done (so please test these yourself):
- Opening `question1/index.html` in a browser (layout and validation behaviour not seen live)
- Running the PHP application with Apache + MySQL, and running either SQL script on MySQL
- Building the Maven project: Maven Central was not reachable from the build environment, so `mvn package` was not run
- Compiling the servlet, filter and JSP code (no servlet API jar was available), and deploying on Tomcat 9

If something fails when you run it, check the error message against the setup steps in the
question's README (database credentials, upload folder permissions, `upload.dir` path).
