CREATE TABLE Dim_Medicine (
    MedicineKey INT PRIMARY KEY AUTO_INCREMENT,
    MedicineID INT,
    Name VARCHAR(200),
    GenericName VARCHAR(200),
    Category VARCHAR(100),
    Manufacturer VARCHAR(150),
    IsPrescriptionRequired BOOLEAN,
    StartDate DATE,
    EndDate DATE DEFAULT '9999-12-31',
    IsCurrent BOOLEAN DEFAULT TRUE
);

CREATE TABLE Dim_Customer (
    CustomerKey INT PRIMARY KEY AUTO_INCREMENT,
    CustomerID INT,
    Name VARCHAR(150),
    AgeGroup VARCHAR(20),
    IsRegular BOOLEAN,
    StartDate DATE,
    EndDate DATE DEFAULT '9999-12-31'
);

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

CREATE TABLE Dim_Supplier (
    SupplierKey INT PRIMARY KEY AUTO_INCREMENT,
    SupplierID INT,
    Name VARCHAR(150),
    City VARCHAR(100)
);

CREATE TABLE Fact_Sales (
    SalesKey INT PRIMARY KEY AUTO_INCREMENT,
    DateKey INT,
    MedicineKey INT,
    CustomerKey INT,
    EmployeeID INT,
    Quantity INT NOT NULL,
    UnitPrice DECIMAL(10,2),
    TotalAmount DECIMAL(12,2),
    CostAmount DECIMAL(12,2),
    Profit DECIMAL(12,2) GENERATED ALWAYS AS (TotalAmount - CostAmount) STORED,
    FOREIGN KEY (DateKey) REFERENCES Dim_Date(DateKey),
    FOREIGN KEY (MedicineKey) REFERENCES Dim_Medicine(MedicineKey),
    FOREIGN KEY (CustomerKey) REFERENCES Dim_Customer(CustomerKey)
);

CREATE TABLE Fact_Inventory_Snapshot (
    SnapshotKey INT PRIMARY KEY AUTO_INCREMENT,
    DateKey INT,
    MedicineKey INT,
    StockQuantity INT,
    ExpiringIn30Days INT,
    FOREIGN KEY (DateKey) REFERENCES Dim_Date(DateKey),
    FOREIGN KEY (MedicineKey) REFERENCES Dim_Medicine(MedicineKey)
);

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
