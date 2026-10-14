package com.kpr.portal.servlet;

import com.kpr.portal.dao.UserDAO;
import com.kpr.portal.model.User;
import com.kpr.portal.util.PasswordUtil;
import java.io.IOException;
import java.math.BigInteger;
import java.security.MessageDigest;
import java.security.SecureRandom;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

/**
 * Handles login: GET shows the form, POST checks the credentials.
 */
@WebServlet("/login")
public class LoginServlet extends HttpServlet {

    private static final Logger LOG = Logger.getLogger(LoginServlet.class.getName());
    private static final String LOGIN_VIEW = "/WEB-INF/views/login.jsp";
    private static final SecureRandom RANDOM = new SecureRandom();
    private final UserDAO userDAO = new UserDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // Already logged in? go straight to the student list
        HttpSession existing = request.getSession(false);
        if (existing != null && existing.getAttribute("user") != null) {
            response.sendRedirect(request.getContextPath() + "/students");
            return;
        }

        // Create a CSRF token that must come back with the POST
        HttpSession session = request.getSession(true);
        session.setAttribute("loginToken", new BigInteger(130, RANDOM).toString(32));
        request.getRequestDispatcher(LOGIN_VIEW).forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");
        String username = trim(request.getParameter("username"));
        String password = request.getParameter("password");
        String sentToken = request.getParameter("token");

        // 1. CSRF token check
        HttpSession old = request.getSession(false);
        String expected = old == null ? null : (String) old.getAttribute("loginToken");
        if (expected == null || sentToken == null
                || !MessageDigest.isEqual(expected.getBytes(), sentToken.getBytes())) {
            response.sendError(HttpServletResponse.SC_FORBIDDEN, "Invalid form token. Reload the login page.");
            return;
        }

        // 2. Required fields
        if (username.isEmpty() || password == null || password.isEmpty()) {
            fail(request, response, "Username and password are required.", username);
            return;
        }

        try {
            // 3. Look the user up and verify the salted hash
            User user = userDAO.findByUsername(username);
            if (user == null || !PasswordUtil.verify(password, user.getPasswordHash())) {
                // Same message for both cases so attackers cannot learn which usernames exist
                fail(request, response, "Invalid username or password.", username);
                return;
            }

            // 4. Success: replace the old session (prevents session fixation)
            old.invalidate();
            HttpSession session = request.getSession(true);
            user.setPasswordHash(null);                 // never keep the hash in the session
            session.setAttribute("user", user);
            session.setMaxInactiveInterval(30 * 60);    // 30 minutes
            response.sendRedirect(request.getContextPath() + "/students");

        } catch (SQLException e) {
            LOG.log(Level.SEVERE, "Login query failed", e);
            fail(request, response, "Service unavailable. Please try again later.", username);
        }
    }

    // Shows the login form again with an error message
    private void fail(HttpServletRequest request, HttpServletResponse response,
                      String message, String username) throws ServletException, IOException {
        request.setAttribute("error", message);
        request.setAttribute("username", username);
        response.setStatus(HttpServletResponse.SC_UNAUTHORIZED);
        request.getRequestDispatcher(LOGIN_VIEW).forward(request, response);
    }

    private static String trim(String s) {
        return s == null ? "" : s.trim();
    }
}
