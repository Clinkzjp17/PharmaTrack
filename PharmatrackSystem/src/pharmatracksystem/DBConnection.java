package pharmatracksystem;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class DBConnection {
    // This points directly to your MySQL database server
    private static final String URL = "jdbc:mysql://localhost:3306/pharmatrack"; 
    private static final String USER = "root"; 
    private static final String PASSWORD = ""; // Keep this blank if you don't use a XAMPP/MySQL password

    public static Connection connect() throws SQLException {
        return DriverManager.getConnection(URL, USER, PASSWORD);
    }
}