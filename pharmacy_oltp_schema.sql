CREATE TABLE Medicines (
    MedicineID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(25) NOT NULL,
    GenericName VARCHAR(25),
    Category VARCHAR(25),
    Manufacturer VARCHAR(25),
    UnitPrice DECIMAL(10,2) NOT NULL,
    ReorderLevel INT NOT NULL,
    ExpiryDate DATE,
    IsPrescriptionRequired BOOLEAN DEFAULT FALSE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Inventory (
    InventoryID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    MedicineID INT,
    BatchNumber VARCHAR(50),
    StockQuantity INT NOT NULL,
    ExpiryDate DATE NOT NULL,
    PurchasePrice DECIMAL(10,2),
    FOREIGN KEY (MedicineID) REFERENCES Medicines(MedicineID)
);

CREATE TABLE Suppliers (
    SupplierID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(25) NOT NULL,
    ContactPerson VARCHAR(100),
    Phone VARCHAR(20),
    Email VARCHAR(100),
    Address TEXT
);

CREATE TABLE Customers (
    CustomerID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(25),
    Phone VARCHAR(20),
    Email VARCHAR(100),
    DateOfBirth DATE,
    IsRegular BOOLEAN DEFAULT FALSE
);

CREATE TABLE Prescriptions (
    PrescriptionID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    CustomerID INT,
    DoctorName VARCHAR(150),
    IssueDate DATE NOT NULL,
    Notes TEXT,
    FOREIGN KEY (CustomerID) REFERENCES Customers(CustomerID)
);

CREATE TABLE PrescriptionItems (
    ItemID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    PrescriptionID INT NOT NULL,
    MedicineID INT NOT NULL,
    Quantity INT NOT NULL,
    FOREIGN KEY (PrescriptionID) REFERENCES Prescriptions(PrescriptionID),
    FOREIGN KEY (MedicineID) REFERENCES Medicines(MedicineID)
);

CREATE TABLE Sales (
    SaleID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    SaleDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CustomerID INT,
    PrescriptionID INT NULL,
    TotalAmount DECIMAL(12,2) NOT NULL,
    PaymentMethod ENUM('Cash', 'Card', 'GCash', 'Maya', 'Credit') NOT NULL,
    PharmacistID INT,
    FOREIGN KEY (CustomerID) REFERENCES Customers(CustomerID),
    FOREIGN KEY (PrescriptionID) REFERENCES Prescriptions(PrescriptionID)
);

CREATE TABLE SaleItems (
    SaleItemID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    SaleID INT NOT NULL,
    MedicineID INT NOT NULL,
    BatchNumber VARCHAR(50),
    Quantity INT NOT NULL,
    UnitPrice DECIMAL(10,2) NOT NULL,
    Subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (SaleID) REFERENCES Sales(SaleID),
    FOREIGN KEY (MedicineID) REFERENCES Medicines(MedicineID)
);

CREATE TABLE Purchases (
    PurchaseID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    SupplierID INT NOT NULL,
    PurchaseDate DATETIME NOT NULL,
    TotalAmount DECIMAL(12,2),
    FOREIGN KEY (SupplierID) REFERENCES Suppliers(SupplierID)
);

CREATE TABLE PurchaseItems (
    PurchaseItemID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    PurchaseID INT NOT NULL,
    MedicineID INT NOT NULL,
    BatchNumber VARCHAR(50),
    Quantity INT NOT NULL,
    UnitCost DECIMAL(10,2),
    FOREIGN KEY (PurchaseID) REFERENCES Purchases(PurchaseID),
    FOREIGN KEY (MedicineID) REFERENCES Medicines(MedicineID)
);

CREATE TABLE Employees (
    EmployeeID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(150) NOT NULL,
    Role ENUM('Pharmacist', 'Cashier', 'Admin', 'Manager'),
    Phone VARCHAR(20)
);
