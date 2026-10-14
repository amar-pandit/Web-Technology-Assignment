package com.kpr.portal.util;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/**
 * JDBC connection helper. Every DAO method opens a connection with
 * getConnection() inside try-with-resources so it is always closed.
 */
public final class DBConnection {

    static {
        try {
            // Registers the MySQL driver (needed inside Tomcat's class loader)
            Class.forName("com.mysql.cj.jdbc.Driver");
        } catch (ClassNotFoundException e) {
            throw new ExceptionInInitializerError("MySQL JDBC driver not found: " + e.getMessage());
        }
    }

    private DBConnection() { }

    public static Connection getConnection() throws SQLException {
        return DriverManager.getConnection(
                AppConfig.get("db.url"),
                AppConfig.get("db.user"),
                AppConfig.get("db.password"));
    }
}
