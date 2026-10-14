# Question 3 - Java Servlet Student Profile Portal

Technology: Java 11, Servlet 4.0 / JSP 2.3 (`javax.*`), JSTL 1.2, JDBC, MySQL, Maven, Apache Tomcat 9.

## Setup
1. Create the database: `mysql -u root -p < sql/schema.sql`
2. Edit `src/main/resources/db.properties` (JDBC URL, user, password, `upload.dir`).
3. Create the upload folders (default is `~/student-portal-uploads`):
   ```
   mkdir -p ~/student-portal-uploads/photos ~/student-portal-uploads/documents
   ```
   Copy your own `.jpg/.png` photos and `.pdf` documents into them, then link them to students
   with the `UPDATE` statement shown at the bottom of `sql/schema.sql`.
4. Build: `mvn clean package` (needs internet access to Maven Central the first time)
5. Deploy `target/student-portal.war` into Tomcat 9's `webapps` folder and start Tomcat.
6. Open `http://localhost:8080/student-portal/`

Sample login: `admin` / `Admin@123` (change it for real use).

## Create another user / hash
```
mvn compile
java -cp target/classes com.kpr.portal.util.PasswordUtil MyNewPassword
```
Paste the printed value into `users.password_hash`.

## Structure
```
pom.xml
sql/schema.sql
src/main/java/com/kpr/portal/
  util/     AppConfig, DBConnection, PasswordUtil
  model/    User, Student
  dao/      UserDAO, StudentDAO
  filter/   AuthFilter            (session check for /students, /file, /logout)
  servlet/  LoginServlet, StudentListServlet, FileServlet, LogoutServlet
src/main/resources/db.properties
src/main/webapp/
  index.jsp, css/style.css
  WEB-INF/web.xml
  WEB-INF/views/ login.jsp, students.jsp, error.jsp   (not directly reachable)
```

## Security features
- JDBC prepared statements everywhere
- PBKDF2-HMAC-SHA256 salted password hashes, constant-time comparison
- Session fixation protection (new session after login), 30 minute timeout, HttpOnly cookie
- Login form CSRF token; logout is POST-only
- AuthFilter protects every page and the file servlet
- FileServlet: file name read from the database, whitelist regex, `normalize()` + `startsWith(base)` path check, no symlinks, `nosniff`
- JSP output escaped with `<c:out>`
