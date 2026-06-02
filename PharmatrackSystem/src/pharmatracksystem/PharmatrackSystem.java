/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Main.java to edit this template
 */
package pharmatracksystem;

/**
 *
 * @author Admin
 */
public class PharmatrackSystem {

    /**
     * @param args the command line arguments
     */
    public static void main(String[] args) {
        try {
            java.sql.Connection conn = DBConnection.connect();
            if (conn != null) {
                System.out.println("SUCCESS! Connected to the pharmatrack database successfully!");
            }
        } catch (java.sql.SQLException e) {
            System.out.println("CONNECTION FAILED: " + e.getMessage());
        }
    }
    
}
