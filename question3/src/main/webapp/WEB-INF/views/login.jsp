<%@ page contentType="text/html; charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Student Profile Portal</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/style.css">
</head>
<body class="login-page">
  <div class="login-box">
    <h1>Student Profile Portal</h1>
    <p class="muted">KPR Institute of Engineering and Technology</p>

    <%-- Conditional logic: show a message only when it exists --%>
    <c:if test="${not empty error}">
      <div class="alert alert-error"><c:out value="${error}"/></div>
    </c:if>
    <c:if test="${param.loggedout == '1' and empty error}">
      <div class="alert alert-success">You have been logged out.</div>
    </c:if>

    <form method="post" action="${pageContext.request.contextPath}/login" autocomplete="off">
      <input type="hidden" name="token" value="<c:out value='${sessionScope.loginToken}'/>">

      <label for="username">Username</label>
      <input type="text" id="username" name="username" maxlength="50" required
             value="<c:out value='${username}'/>">

      <label for="password">Password</label>
      <input type="password" id="password" name="password" maxlength="100" required>

      <button type="submit" class="btn">Login</button>
    </form>
  </div>
</body>
</html>
