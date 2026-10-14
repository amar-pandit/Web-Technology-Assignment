<%@ page contentType="text/html; charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="ctx" value="${pageContext.request.contextPath}"/>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Profiles | Student Profile Portal</title>
  <link rel="stylesheet" href="${ctx}/css/style.css">
</head>
<body>
<header class="topbar">
  <span class="brand">Student Profile Portal</span>
  <div class="user">
    Signed in as <strong><c:out value="${sessionScope.user.fullName}"/></strong>
    <form method="post" action="${ctx}/logout" class="inline">
      <button type="submit" class="btn btn-small">Logout</button>
    </form>
  </div>
</header>

<main>
  <h1>Student Profiles</h1>

  <form method="get" action="${ctx}/students" class="search">
    <input type="text" name="q" maxlength="50" placeholder="Search by name, roll number or email"
           value="<c:out value='${search}'/>">
    <button type="submit" class="btn">Search</button>
    <c:if test="${not empty search}"><a class="btn btn-light" href="${ctx}/students">Clear</a></c:if>
  </form>

  <c:if test="${not empty error}">
    <div class="alert alert-error"><c:out value="${error}"/></div>
  </c:if>

  <c:choose>
    <%-- Conditional: nothing to show --%>
    <c:when test="${empty students}">
      <p class="muted">No students found.</p>
    </c:when>

    <c:otherwise>
      <p class="muted">Showing ${students.size()} student(s).</p>
      <div class="grid">
        <%-- Loop: one profile card per student --%>
        <c:forEach var="s" items="${students}" varStatus="loop">
          <article class="card">
            <div class="photo">
              <c:choose>
                <c:when test="${s.hasPhoto}">
                  <%-- The image bytes come from FileServlet, not from a static folder --%>
                  <img src="${ctx}/file?type=photo&amp;id=${s.id}"
                       alt="Photo of <c:out value='${s.name}'/>">
                </c:when>
                <c:otherwise>
                  <div class="initial"><c:out value="${s.initial}"/></div>
                </c:otherwise>
              </c:choose>
            </div>

            <h2><c:out value="${loop.count}. ${s.name}"/></h2>
            <dl>
              <dt>Roll No.</dt><dd><c:out value="${s.rollNumber}"/></dd>
              <dt>Email</dt><dd><c:out value="${s.email}"/></dd>
              <dt>Course</dt><dd><c:out value="${s.course}"/></dd>
            </dl>

            <div class="links">
              <c:if test="${s.hasDocument}">
                <a href="${ctx}/file?type=doc&amp;id=${s.id}" target="_blank" rel="noopener">View document</a>
                <a href="${ctx}/file?type=doc&amp;id=${s.id}&amp;download=1">Download</a>
              </c:if>
              <c:if test="${not s.hasDocument}">
                <span class="muted">No document</span>
              </c:if>
            </div>
          </article>
        </c:forEach>
      </div>
    </c:otherwise>
  </c:choose>
</main>
</body>
</html>
