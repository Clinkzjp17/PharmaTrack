package pharmatracksystem;

import java.awt.*;
import java.sql.*;
import javax.swing.*;
import javax.swing.border.TitledBorder;
import javax.swing.table.DefaultTableModel;

public class MainDashboard extends JFrame {

    // UI Components
    private JTextField txtId, txtName, txtDob, txtSearch;
    private JCheckBox chkRegular;
    private JTable tblCustomers;
    private DefaultTableModel tableModel;
    private JLabel lblStatus;

    public MainDashboard() {
        // Window Settings
        setTitle("PharmaTrack System - Customer Management");
        setSize(1000, 600);
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLocationRelativeTo(null);
        setLayout(new BorderLayout(10, 10));

        // Color Palette
        Color primaryDark = new Color(11, 79, 90);      // Deep Teal Navbar
        Color accentTeal = new Color(92, 142, 152);     // Slate Teal Panel Background
        Color lightBg = new Color(245, 247, 248);       // App Background Light Gray

        // 1. TOP NAVBAR PANEL
        JPanel pnlNavbar = new JPanel(new BorderLayout());
        pnlNavbar.setBackground(primaryDark);
        pnlNavbar.setPreferredSize(new Dimension(1000, 60));
        
        JLabel lblTitle = new JLabel("  PharmaTrack Management System");
        lblTitle.setFont(new Font("Segoe UI", Font.BOLD, 18));
        lblTitle.setForeground(Color.WHITE);
        pnlNavbar.add(lblTitle, BorderLayout.WEST);
        add(pnlNavbar, BorderLayout.NORTH);

        // MAIN CONTENT CONTAINER
        JPanel pnlMain = new JPanel(new GridLayout(1, 2, 15, 0));
        pnlMain.setBackground(lightBg);
        pnlMain.setBorder(BorderFactory.createEmptyBorder(15, 15, 15, 15));

        // 2. LEFT SIDE: MANAGEMENT REGISTRY FORM PANEL
        JPanel pnlForm = new JPanel(new GridBagLayout());
        pnlForm.setBackground(Color.WHITE);
        TitledBorder formBorder = BorderFactory.createTitledBorder(
                BorderFactory.createLineBorder(accentTeal, 2), " Manage Customer Registry ");
        formBorder.setTitleFont(new Font("Segoe UI", Font.BOLD, 14));
        formBorder.setTitleColor(primaryDark);
        pnlForm.setBorder(formBorder);

        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(10, 15, 10, 15);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        // Form Fields
        gbc.gridx = 0; gbc.gridy = 0;
        JLabel lblId = new JLabel("Customer ID:");
        lblId.setFont(new Font("Segoe UI", Font.BOLD, 12));
        pnlForm.add(lblId, gbc);

        gbc.gridx = 1;
        txtId = new JTextField(15);
        txtId.setEditable(false);
        txtId.setBackground(new Color(235, 240, 241));
        txtId.setText("(Auto-Increment)");
        pnlForm.add(txtId, gbc);

        gbc.gridx = 0; gbc.gridy = 1;
        JLabel lblName = new JLabel("Full Name:");
        lblName.setFont(new Font("Segoe UI", Font.BOLD, 12));
        pnlForm.add(lblName, gbc);

        gbc.gridx = 1;
        txtName = new JTextField(15);
        pnlForm.add(txtName, gbc);

        gbc.gridx = 0; gbc.gridy = 2;
        JLabel lblDob = new JLabel("Date of Birth:");
        lblDob.setFont(new Font("Segoe UI", Font.BOLD, 12));
        pnlForm.add(lblDob, gbc);

        gbc.gridx = 1;
        txtDob = new JTextField(15);
        pnlForm.add(txtDob, gbc);

        gbc.gridx = 1; gbc.gridy = 3;
        JLabel lblHint = new JLabel("*(Format: YYYY-MM-DD)*");
        lblHint.setFont(new Font("Segoe UI", Font.ITALIC, 11));
        lblHint.setForeground(Color.GRAY);
        pnlForm.add(lblHint, gbc);

        gbc.gridx = 1; gbc.gridy = 4;
        chkRegular = new JCheckBox("Mark as Regular Customer");
        chkRegular.setBackground(Color.WHITE);
        pnlForm.add(chkRegular, gbc);

        // BUTTONS SUB-PANEL
        gbc.gridx = 0; gbc.gridy = 5; gbc.gridwidth = 2;
        gbc.insets = new Insets(25, 10, 10, 10);
        JPanel pnlButtons = new JPanel(new FlowLayout(FlowLayout.CENTER, 10, 0));
        pnlButtons.setBackground(Color.WHITE);

        JButton btnAdd = new JButton("ADD");
        JButton btnUpdate = new JButton("UPDATE");
        JButton btnDelete = new JButton("DELETE");
        JButton btnClear = new JButton("CLEAR");

        for (JButton btn : new JButton[]{btnAdd, btnUpdate, btnDelete, btnClear}) {
            btn.setBackground(primaryDark);
            btn.setForeground(Color.WHITE);
            btn.setFocusPainted(false);
            btn.setFont(new Font("Segoe UI", Font.BOLD, 12));
            btn.setPreferredSize(new Dimension(85, 32));
        }
        btnClear.setBackground(Color.DARK_GRAY);

        pnlButtons.add(btnAdd);
        pnlButtons.add(btnUpdate);
        pnlButtons.add(btnDelete);
        pnlButtons.add(btnClear);
        pnlForm.add(pnlButtons, gbc);

        pnlMain.add(pnlForm);

        // 3. RIGHT SIDE: LIVE DATABASE GRID PANEL
        JPanel pnlRight = new JPanel(new BorderLayout(0, 10));
        pnlRight.setBackground(lightBg);

        // Search Bar
        JPanel pnlSearch = new JPanel(new FlowLayout(FlowLayout.LEFT, 5, 5));
        pnlSearch.setBackground(lightBg);
        JLabel lblSearch = new JLabel("Search Name: ");
        lblSearch.setFont(new Font("Segoe UI", Font.BOLD, 12));
        txtSearch = new JTextField(20);
        pnlSearch.add(lblSearch);
        pnlSearch.add(txtSearch);
        pnlRight.add(pnlSearch, BorderLayout.NORTH);

        // Table Model Setup
        String[] columns = {"ID", "Full Name", "Date of Birth", "Membership Status"};
        tableModel = new DefaultTableModel(columns, 0) {
            @Override
            public boolean isCellEditable(int row, int column) { return false; }
        };
        
        tblCustomers = new JTable(tableModel);
        tblCustomers.getTableHeader().setFont(new Font("Segoe UI", Font.BOLD, 12));
        tblCustomers.getTableHeader().setBackground(accentTeal);
        tblCustomers.getTableHeader().setForeground(Color.WHITE);
        tblCustomers.setRowHeight(25);
        tblCustomers.setSelectionBackground(new Color(212, 230, 233));

        JScrollPane scrollPane = new JScrollPane(tblCustomers);
        pnlRight.add(scrollPane, BorderLayout.CENTER);
        
        pnlMain.add(pnlRight);
        add(pnlMain, BorderLayout.CENTER);

        // 4. BOTTOM STATUS BAR
        lblStatus = new JLabel(" Status: Initializing connection...");
        lblStatus.setPreferredSize(new Dimension(1000, 25));
        lblStatus.setBackground(new Color(230, 235, 235));
        lblStatus.setOpaque(true);
        lblStatus.setFont(new Font("Segoe UI", Font.PLAIN, 11));
        add(lblStatus, BorderLayout.SOUTH);

        // ACTION Listeners Setup
        btnClear.addActionListener(e -> clearFields());
        btnAdd.addActionListener(e -> insertRecord());
        btnUpdate.addActionListener(e -> updateRecord());
        btnDelete.addActionListener(e -> deleteRecord());
        tblCustomers.addMouseListener(new java.awt.event.MouseAdapter() {
            @Override
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                tableMouseClicked();
            }
        });
        loadTableData();
        // Search Bar Listener
        txtSearch.getDocument().addDocumentListener(new javax.swing.event.DocumentListener() {
            public void insertUpdate(javax.swing.event.DocumentEvent e) { filterTable(); }
            public void removeUpdate(javax.swing.event.DocumentEvent e) { filterTable(); }
            public void changedUpdate(javax.swing.event.DocumentEvent e) { filterTable(); }
        });

        loadTableData();
    } // This bracket closes your MainDashboard() constructor
    

    private void clearFields() {
        txtId.setText("(Auto-Increment)");
        txtName.setText("");
        txtDob.setText("");
        chkRegular.setSelected(false);
    }

    private void loadTableData() {
        tableModel.setRowCount(0);
        String sql = "SELECT * FROM customers"; 
        
        try (Connection conn = DBConnection.connect();
             PreparedStatement ps = conn.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            
            while (rs.next()) {
                Object[] row = {
                    rs.getInt("CustomerID"),
                    rs.getString("Name"),
                    rs.getDate("DateOfBirth"),
                    rs.getByte("IsRegular") == 1 ? "Regular Customer" : "Standard Tier"
                };
                tableModel.addRow(row);
            }
            lblStatus.setText("SUCCESS! Loaded customer application records seamlessly from MySQL.");
            lblStatus.setForeground(new Color(27, 94, 32));
        } catch (SQLException e) {
            lblStatus.setText("FETCH FAILURE: " + e.getMessage());
            lblStatus.setForeground(Color.RED);
        }
    }
private void filterTable() {
        String query = txtSearch.getText().trim();
        tableModel.setRowCount(0);
        String sql = "SELECT * FROM customers WHERE Name LIKE ?";
        
        try (Connection conn = DBConnection.connect();
             PreparedStatement ps = conn.prepareStatement(sql)) {
            
            ps.setString(1, "%" + query + "%");
            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    Object[] row = {
                        rs.getInt("CustomerID"),
                        rs.getString("Name"),
                        rs.getDate("DateOfBirth"),
                        rs.getByte("IsRegular") == 1 ? "Regular Customer" : "Standard Tier"
                    };
                    tableModel.addRow(row);
                }
            }
        } catch (SQLException ex) {
            lblStatus.setText("Search Error: " + ex.getMessage());
        }
    }
    private void insertRecord() {
        if (txtName.getText().trim().isEmpty() || txtDob.getText().trim().isEmpty()) {
            JOptionPane.showMessageDialog(this, "Please enter all required text inputs.", "Validation Error", JOptionPane.WARNING_MESSAGE);
            return;
        }

        String sql = "INSERT INTO customers (Name, DateOfBirth, IsRegular) VALUES (?, ?, ?)";
        try (Connection conn = DBConnection.connect();
             PreparedStatement ps = conn.prepareStatement(sql)) {
            
            ps.setString(1, txtName.getText().trim());
            ps.setString(2, txtDob.getText().trim());
            ps.setInt(3, chkRegular.isSelected() ? 1 : 0);
            
            ps.executeUpdate();
            lblStatus.setText(" 🎉 RECORD ADDED! Customer registry updated dynamically inside MySQL.");
            clearFields();
            loadTableData();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Database Write Error: " + e.getMessage(), "Error", JOptionPane.ERROR_MESSAGE);
        }
    }

    private void tableMouseClicked() {
        int selectedRow = tblCustomers.getSelectedRow();
        if (selectedRow != -1) {
            txtId.setText(tableModel.getValueAt(selectedRow, 0).toString());
            txtName.setText(tableModel.getValueAt(selectedRow, 1).toString());
            txtDob.setText(tableModel.getValueAt(selectedRow, 2).toString());
            
            String status = tableModel.getValueAt(selectedRow, 3).toString();
            chkRegular.setSelected(status.equals("Regular Customer"));
        }
    }

    private void updateRecord() {
        if (txtId.getText().equals("(Auto-Increment)")) {
            JOptionPane.showMessageDialog(this, "Please select a customer from the table first.", "Selection Required", JOptionPane.WARNING_MESSAGE);
            return;
        }

        String sql = "UPDATE customers SET Name = ?, DateOfBirth = ?, IsRegular = ? WHERE CustomerID = ?";
        try (Connection conn = DBConnection.connect();
             PreparedStatement ps = conn.prepareStatement(sql)) {
            
            ps.setString(1, txtName.getText().trim());
            ps.setString(2, txtDob.getText().trim());
            ps.setInt(3, chkRegular.isSelected() ? 1 : 0);
            ps.setInt(4, Integer.parseInt(txtId.getText()));
            
            ps.executeUpdate();
            lblStatus.setText(" 🎉 RECORD UPDATED! Database modifications saved successfully.");
            clearFields();
            loadTableData();
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Update Error: " + e.getMessage(), "Error", JOptionPane.ERROR_MESSAGE);
        }
    }

    private void deleteRecord() {
        if (txtId.getText().equals("(Auto-Increment)")) {
            JOptionPane.showMessageDialog(this, "Please select a customer from the table first.", "Selection Required", JOptionPane.WARNING_MESSAGE);
            return;
        }

        int confirm = JOptionPane.showConfirmDialog(this, "Are you sure you want to delete this record?", "Confirm Deletion", JOptionPane.YES_NO_OPTION);
        if (confirm == JOptionPane.YES_OPTION) {
            String sql = "DELETE FROM customers WHERE CustomerID = ?";
            try (Connection conn = DBConnection.connect();
                 PreparedStatement ps = conn.prepareStatement(sql)) {
                
                ps.setInt(1, Integer.parseInt(txtId.getText()));
                ps.executeUpdate();
                lblStatus.setText(" 🗑️ RECORD DELETED! Row removed from database successfully.");
                clearFields();
                loadTableData();
            } catch (SQLException e) {
                JOptionPane.showMessageDialog(this, "Delete Error: " + e.getMessage(), "Error", JOptionPane.ERROR_MESSAGE);
            }
        }
    }

    public static void main(String[] args) {
        SwingUtilities.invokeLater(() -> {
            new MainDashboard().setVisible(true);
        });
    }
}