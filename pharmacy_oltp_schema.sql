-- 1. Products / Medicines
CREATE TABLE Medicines (
    MedicineID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(200) NOT NULL,
    GenericName VARCHAR(200),
    Category VARCHAR(100),           -- e.g., Antibiotic, Analgesic
    Manufacturer VARCHAR(150),
    UnitPrice DECIMAL(10,2) NOT NULL,
    ReorderLevel INT NOT NULL,
    ExpiryDate DATE,
    IsPrescriptionRequired BOOLEAN DEFAULT FALSE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Inventory
CREATE TABLE Inventory (
    InventoryID INT PRIMARY KEY AUTO_INCREMENT,
    MedicineID INT NOT NULL,
    BatchNumber VARCHAR(50),
    StockQuantity INT NOT NULL,
    ExpiryDate DATE NOT NULL,
    PurchasePrice DECIMAL(10,2),
    FOREIGN KEY (MedicineID) REFERENCES Medicines(MedicineID)
);

-- 3. Suppliers
CREATE TABLE Suppliers (
    SupplierID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(150) NOT NULL,
    ContactPerson VARCHAR(100),
    Phone VARCHAR(20),
    Email VARCHAR(100),
    Address TEXT
);

-- 4. Customers / Patients
CREATE TABLE Customers (
    CustomerID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(150),
    Phone VARCHAR(20),
    Email VARCHAR(100),
    DateOfBirth DATE,
    IsRegular BOOLEAN DEFAULT FALSE
);

-- 5. Prescriptions
CREATE TABLE Prescriptions (
    PrescriptionID INT PRIMARY KEY AUTO_INCREMENT,
    CustomerID INT,
    DoctorName VARCHAR(150),
    IssueDate DATE NOT NULL,
    Notes TEXT,
    FOREIGN KEY (CustomerID) REFERENCES Customers(CustomerID)
);

-- 6. Prescription Items
CREATE TABLE PrescriptionItems (
    ItemID INT PRIMARY KEY AUTO_INCREMENT,
    PrescriptionID INT NOT NULL,
    MedicineID INT NOT NULL,
    Quantity INT NOT NULL,
    FOREIGN KEY (PrescriptionID) REFERENCES Prescriptions(PrescriptionID),
    FOREIGN KEY (MedicineID) REFERENCES Medicines(MedicineID)
);

-- 7. Sales (Main Transaction Table)
CREATE TABLE Sales (
    SaleID INT PRIMARY KEY AUTO_INCREMENT,
    SaleDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CustomerID INT,
    PrescriptionID INT NULL,
    TotalAmount DECIMAL(12,2) NOT NULL,
    PaymentMethod ENUM('Cash', 'Card', 'GCash', 'Maya', 'Credit') NOT NULL,
    PharmacistID INT,
    FOREIGN KEY (CustomerID) REFERENCES Customers(CustomerID),
    FOREIGN KEY (PrescriptionID) REFERENCES Prescriptions(PrescriptionID)
);

-- 8. Sale Items
CREATE TABLE SaleItems (
    SaleItemID INT PRIMARY KEY AUTO_INCREMENT,
    SaleID INT NOT NULL,
    MedicineID INT NOT NULL,
    BatchNumber VARCHAR(50),
    Quantity INT NOT NULL,
    UnitPrice DECIMAL(10,2) NOT NULL,
    Subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (SaleID) REFERENCES Sales(SaleID),
    FOREIGN KEY (MedicineID) REFERENCES Medicines(MedicineID)
);

-- 9. Purchases (Stock In)
CREATE TABLE Purchases (
    PurchaseID INT PRIMARY KEY AUTO_INCREMENT,
    SupplierID INT NOT NULL,
    PurchaseDate DATETIME NOT NULL,
    TotalAmount DECIMAL(12,2),
    FOREIGN KEY (SupplierID) REFERENCES Suppliers(SupplierID)
);

CREATE TABLE PurchaseItems (
    PurchaseItemID INT PRIMARY KEY AUTO_INCREMENT,
    PurchaseID INT NOT NULL,
    MedicineID INT NOT NULL,
    BatchNumber VARCHAR(50),
    Quantity INT NOT NULL,
    UnitCost DECIMAL(10,2),
    FOREIGN KEY (PurchaseID) REFERENCES Purchases(PurchaseID)
);

-- 10. Employees / Pharmacists
CREATE TABLE Employees (
    EmployeeID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(150) NOT NULL,
    Role ENUM('Pharmacist', 'Cashier', 'Admin', 'Manager'),
    Phone VARCHAR(20)
);