-- Dimension: Medicine
CREATE TABLE Dim_Medicine (
    MedicineKey INT PRIMARY KEY AUTO_INCREMENT,
    MedicineID INT,                    -- from OLTP
    Name VARCHAR(200),
    GenericName VARCHAR(200),
    Category VARCHAR(100),
    Manufacturer VARCHAR(150),
    IsPrescriptionRequired BOOLEAN,
    StartDate DATE,
    EndDate DATE DEFAULT '9999-12-31',
    IsCurrent BOOLEAN DEFAULT TRUE
);

-- Dimension: Customer
CREATE TABLE Dim_Customer (
    CustomerKey INT PRIMARY KEY AUTO_INCREMENT,
    CustomerID INT,
    Name VARCHAR(150),
    AgeGroup VARCHAR(20),              -- e.g., Child, Adult, Senior
    IsRegular BOOLEAN,
    StartDate DATE,
    EndDate DATE DEFAULT '9999-12-31'
);

-- Dimension: Date
CREATE TABLE Dim_Date (
    DateKey INT PRIMARY KEY,
    FullDate DATE NOT NULL,
    Year INT,
    Month INT,
    MonthName VARCHAR(20),
    Quarter INT,
    DayOfWeek INT,
    IsWeekend BOOLEAN,
    FiscalYear INT
);

-- Dimension: Supplier
CREATE TABLE Dim_Supplier (
    SupplierKey INT PRIMARY KEY AUTO_INCREMENT,
    SupplierID INT,
    Name VARCHAR(150),
    City VARCHAR(100)
);

-- Fact: Sales
CREATE TABLE Fact_Sales (
    SalesKey INT PRIMARY KEY AUTO_INCREMENT,
    DateKey INT,
    MedicineKey INT,
    CustomerKey INT,
    EmployeeID INT,
    Quantity INT NOT NULL,
    UnitPrice DECIMAL(10,2),
    TotalAmount DECIMAL(12,2),
    CostAmount DECIMAL(12,2),           -- for profit calculation
    Profit DECIMAL(12,2) GENERATED ALWAYS AS (TotalAmount - CostAmount) STORED,
    FOREIGN KEY (DateKey) REFERENCES Dim_Date(DateKey),
    FOREIGN KEY (MedicineKey) REFERENCES Dim_Medicine(MedicineKey),
    FOREIGN KEY (CustomerKey) REFERENCES Dim_Customer(CustomerKey)
);

-- Fact: Inventory Snapshot (for stock trend analysis)
CREATE TABLE Fact_Inventory_Snapshot (
    SnapshotKey INT PRIMARY KEY AUTO_INCREMENT,
    DateKey INT,
    MedicineKey INT,
    StockQuantity INT,
    ExpiringIn30Days INT,
    FOREIGN KEY (DateKey) REFERENCES Dim_Date(DateKey),
    FOREIGN KEY (MedicineKey) REFERENCES Dim_Medicine(MedicineKey)
);

-- Fact: Purchases
CREATE TABLE Fact_Purchases (
    PurchaseKey INT PRIMARY KEY AUTO_INCREMENT,
    DateKey INT,
    MedicineKey INT,
    SupplierKey INT,
    Quantity INT,
    TotalCost DECIMAL(12,2),
    FOREIGN KEY (DateKey) REFERENCES Dim_Date(DateKey),
    FOREIGN KEY (MedicineKey) REFERENCES Dim_Medicine(MedicineKey),
    FOREIGN KEY (SupplierKey) REFERENCES Dim_Supplier(SupplierKey)
);