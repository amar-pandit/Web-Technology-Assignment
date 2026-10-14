package com.kpr.portal.servlet;

import com.kpr.portal.dao.StudentDAO;
import com.kpr.portal.model.Student;
import java.io.IOException;
import java.sql.SQLException;
import java.util.Collections;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * Loads students from the database and forwards them to students.jsp.
 * Access is protected by AuthFilter.
 */
@WebServlet("/students")
public class StudentListServlet extends HttpServlet {

    private static final Logger LOG = Logger.getLogger(StudentListServlet.class.getName());
    private final StudentDAO studentDAO = new StudentDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String search = request.getParameter("q");
        search = (search == null) ? "" : search.trim();
        if (search.length() > 50) {
            search = search.substring(0, 50);   // limit input length
        }

        List<Student> students;
        try {
            students = studentDAO.findAll(search);
        } catch (SQLException e) {
            LOG.log(Level.SEVERE, "Could not load students", e);
            students = Collections.emptyList();
            request.setAttribute("error", "Could not load students from the database.");
        }

        // These attributes are read by the JSP using Expression Language
        request.setAttribute("students", students);
        request.setAttribute("search", search);
        request.getRequestDispatcher("/WEB-INF/views/students.jsp").forward(request, response);
    }
}
