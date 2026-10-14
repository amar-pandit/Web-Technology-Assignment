package com.kpr.portal.servlet;

import com.kpr.portal.dao.StudentDAO;
import com.kpr.portal.model.Student;
import com.kpr.portal.util.AppConfig;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.sql.SQLException;
import java.util.Locale;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.util.regex.Pattern;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * Streams a student's photo or document from the upload folder.
 *   /file?type=photo&id=3           -> image shown inside an <img> tag
 *   /file?type=doc&id=3             -> PDF opened in the browser
 *   /file?type=doc&id=3&download=1  -> PDF forced to download
 *
 * The browser only sends a student id; the file name always comes from
 * the database, and the final path is checked to stay inside the upload folder.
 */
@WebServlet("/file")
public class FileServlet extends HttpServlet {

    private static final Logger LOG = Logger.getLogger(FileServlet.class.getName());
    // Only simple file names with a known extension are ever accepted
    private static final Pattern SAFE_NAME = Pattern.compile("^[A-Za-z0-9][A-Za-z0-9._-]{0,99}\\.(jpg|jpeg|png|pdf)$");
    private final StudentDAO studentDAO = new StudentDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // 1. Validate request parameters
        String type = request.getParameter("type");
        int id;
        try {
            id = Integer.parseInt(request.getParameter("id"));
        } catch (NumberFormatException e) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid id");
            return;
        }
        if (!"photo".equals(type) && !"doc".equals(type)) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid type");
            return;
        }

        // 2. Get the stored file name from the database
        String fileName;
        try {
            Student s = studentDAO.findById(id);
            if (s == null) {
                response.sendError(HttpServletResponse.SC_NOT_FOUND);
                return;
            }
            fileName = "photo".equals(type) ? s.getPhotoFile() : s.getDocumentFile();
        } catch (SQLException e) {
            LOG.log(Level.SEVERE, "File lookup failed", e);
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            return;
        }
        if (fileName == null || !SAFE_NAME.matcher(fileName).matches()) {
            response.sendError(HttpServletResponse.SC_NOT_FOUND);
            return;
        }

        // 3. Build the path and make sure it cannot escape the upload folder
        Path base = Paths.get(AppConfig.get("upload.dir"),
                "photo".equals(type) ? "photos" : "documents").toAbsolutePath().normalize();
        Path file = base.resolve(fileName).normalize();
        if (!file.startsWith(base) || !Files.isRegularFile(file) || Files.isSymbolicLink(file)) {
            response.sendError(HttpServletResponse.SC_NOT_FOUND);
            return;
        }

        // 4. Send headers and stream the bytes
        String lower = fileName.toLowerCase(Locale.ROOT);
        String contentType = lower.endsWith(".png") ? "image/png"
                : lower.endsWith(".pdf") ? "application/pdf" : "image/jpeg";
        boolean download = "1".equals(request.getParameter("download"));

        response.setContentType(contentType);
        response.setContentLengthLong(Files.size(file));
        response.setHeader("X-Content-Type-Options", "nosniff");
        response.setHeader("Content-Disposition",
                (download ? "attachment" : "inline") + "; filename=\"" + fileName + "\"");

        try (InputStream in = Files.newInputStream(file);
             OutputStream out = response.getOutputStream()) {
            byte[] buffer = new byte[8192];
            int read;
            while ((read = in.read(buffer)) != -1) {
                out.write(buffer, 0, read);
            }
        }
    }
}
