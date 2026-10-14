<%@ page contentType="text/html; charset=UTF-8" language="java" isErrorPage="true" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Error | Student Profile Portal</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/style.css">
</head>
<body class="login-page">
  <div class="login-box">
    <h1>Something went wrong</h1>
    <p class="muted">The page you requested could not be shown. No technical details are displayed for security reasons.</p>
    <a class="btn" href="${pageContext.request.contextPath}/students">Back to students</a>
  </div>
</body>
</html>
