package com.kpr.portal.util;

import java.io.IOException;
import java.io.InputStream;
import java.io.UncheckedIOException;
import java.util.Properties;

/**
 * Loads db.properties from the classpath once and exposes the values.
 */
public final class AppConfig {

    private static final Properties PROPS = new Properties();

    static {
        try (InputStream in = AppConfig.class.getClassLoader().getResourceAsStream("db.properties")) {
            if (in == null) {
                throw new IllegalStateException("db.properties not found on the classpath");
            }
            PROPS.load(in);
        } catch (IOException e) {
            throw new UncheckedIOException("Could not read db.properties", e);
        }
    }

    private AppConfig() { }

    /** Returns a property value, expanding ${user.home}. */
    public static String get(String key) {
        String value = PROPS.getProperty(key, "");
        return value.replace("${user.home}", System.getProperty("user.home"));
    }
}
