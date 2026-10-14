package com.kpr.portal.dao;

import com.kpr.portal.model.Student;
import com.kpr.portal.util.DBConnection;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

/** Data access for the students table. All queries use prepared statements. */
public class StudentDAO {

    private static final String COLUMNS =
            "id, roll_number, name, email, course, photo_file, document_file";

    /** Returns all students, optionally filtered by name / roll number / email. */
    public List<Student> findAll(String search) throws SQLException {
        String sql = "SELECT " + COLUMNS + " FROM students "
                   + "WHERE name LIKE ? OR roll_number LIKE ? OR email LIKE ? "
                   + "ORDER BY name";
        String like = "%" + (search == null ? "" : search) + "%";

        List<Student> list = new ArrayList<>();
        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setString(1, like);
            ps.setString(2, like);
            ps.setString(3, like);
            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    list.add(map(rs));
                }
            }
        }
        return list;
    }

    /** Returns one student by primary key, or null if not found. */
    public Student findById(int id) throws SQLException {
        String sql = "SELECT " + COLUMNS + " FROM students WHERE id = ?";
        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, id);
            try (ResultSet rs = ps.executeQuery()) {
                return rs.next() ? map(rs) : null;
            }
        }
    }

    // Converts the current ResultSet row to a Student object
    private Student map(ResultSet rs) throws SQLException {
        Student s = new Student();
        s.setId(rs.getInt("id"));
        s.setRollNumber(rs.getString("roll_number"));
        s.setName(rs.getString("name"));
        s.setEmail(rs.getString("email"));
        s.setCourse(rs.getString("course"));
        s.setPhotoFile(rs.getString("photo_file"));
        s.setDocumentFile(rs.getString("document_file"));
        return s;
    }
}
