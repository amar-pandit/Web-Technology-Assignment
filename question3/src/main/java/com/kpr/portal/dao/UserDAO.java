package com.kpr.portal.dao;

import com.kpr.portal.model.User;
import com.kpr.portal.util.DBConnection;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/** Data access for the users table. */
public class UserDAO {

    /** Returns the user with the given username, or null if none exists. */
    public User findByUsername(String username) throws SQLException {
        String sql = "SELECT id, username, full_name, password_hash FROM users WHERE username = ?";
        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setString(1, username);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    User u = new User();
                    u.setId(rs.getInt("id"));
                    u.setUsername(rs.getString("username"));
                    u.setFullName(rs.getString("full_name"));
                    u.setPasswordHash(rs.getString("password_hash"));
                    return u;
                }
                return null;
            }
        }
    }
}
