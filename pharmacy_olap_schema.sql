CREATE DATABASE IF NOT EXISTS sales_dw;
USE sales_dw;
DELIMITER $$

CREATE PROCEDURE PopulateDimDate(IN start_date DATE, IN end_date DATE)
BEGIN
    DECLARE current_dt DATE;
    SET current_dt = start_date;
    
    WHILE current_dt <= end_date DO
        INSERT IGNORE INTO Dim_Date (DateKey, FullDate, Year, Month, MonthName, Quarter, DayOfWeek, IsWeekend, FiscalYear)

        VALUES (
            CAST(DATE_FORMAT(current_dt, '%Y%m%d') AS UNSIGNED),
            current_dt,
            YEAR(current_dt),
            MONTH(current_dt),
            MONTHNAME(current_dt),
            QUARTER(current_dt),
            DAYOFWEEK(current_dt),
            IF(DAYOFWEEK(current_dt) IN (1, 7), 1, 0),
            YEAR(current_dt));
        SET current_dt = DATE_ADD(current_dt, INTERVAL 1 DAY);
    END WHILE;
END$$

DELIMITER ;

CREATE TABLE Dim_Medicine ( MedicineKey INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    MedicineID INT UNSIGNED NOT NULL,
    Name VARCHAR(25),
    GenericName VARCHAR(25),
    Category VARCHAR(25),
    Manufacturer VARCHAR(25),
    IsPrescriptionRequired BOOLEAN,
    StartDate DATE NOT NULL,
    EndDate DATE DEFAULT '9999-12-31',
    IsCurrent BOOLEAN DEFAULT TRUE);

CREATE TABLE Dim_Customer ( CustomerKey INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    CustomerID INT UNSIGNED NOT NULL,
    Name VARCHAR(25),
    AgeGroup VARCHAR(20),
    IsRegular BOOLEAN,
    StartDate DATE NOT NULL,
    EndDate DATE DEFAULT '9999-12-31'
);

CREATE TABLE Dim_Date ( DateKey INT UNSIGNED NOT NULL PRIMARY KEY,
    FullDate DATE NOT NULL,
    Year INT NOT NULL,
    Month INT NOT NULL,
    MonthName VARCHAR(20) NOT NULL,
    Quarter INT NOT NULL,
    DayOfWeek INT NOT NULL,
    IsWeekend BOOLEAN NOT NULL,
    FiscalYear INT NOT NULL);

CREATE TABLE Dim_Supplier ( SupplierKey INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    SupplierID INT UNSIGNED NOT NULL,
    Name VARCHAR(25),
    City VARCHAR(25));

CREATE TABLE Fact_Sales ( SalesKey INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    DateKey INT UNSIGNED NOT NULL,
    MedicineKey INT UNSIGNED NOT NULL,
    CustomerKey INT UNSIGNED NOT NULL,
    EmployeeID INT UNSIGNED NOT NULL,
    Quantity INT NOT NULL,
    UnitPrice DECIMAL(10,2) NOT NULL,
    TotalAmount DECIMAL(12,2) NOT NULL,
    CostAmount DECIMAL(12,2) NOT NULL,
    Profit DECIMAL(12,2) GENERATED ALWAYS AS (TotalAmount - CostAmount) STORED,
    FOREIGN KEY (DateKey) REFERENCES Dim_Date(DateKey),
    FOREIGN KEY (MedicineKey) REFERENCES Dim_Medicine(MedicineKey),
    FOREIGN KEY (CustomerKey) REFERENCES Dim_Customer(CustomerKey));

CREATE TABLE Fact_Inventory_Snapshot (SnapshotKey INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    DateKey INT UNSIGNED NOT NULL,
    MedicineKey INT UNSIGNED NOT NULL,
    StockQuantity INT NOT NULL,
    ExpiringIn30Days INT NOT NULL,
    FOREIGN KEY (DateKey) REFERENCES Dim_Date(DateKey),
    FOREIGN KEY (MedicineKey) REFERENCES Dim_Medicine(MedicineKey));

CREATE TABLE Fact_Purchases ( PurchaseKey INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    DateKey INT UNSIGNED NOT NULL,
    MedicineKey INT UNSIGNED NOT NULL,
    SupplierKey INT UNSIGNED NOT NULL,
    Quantity INT NOT NULL,
    TotalCost DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (DateKey) REFERENCES Dim_Date(DateKey),
    FOREIGN KEY (MedicineKey) REFERENCES Dim_Medicine(MedicineKey),
    FOREIGN KEY (SupplierKey) REFERENCES Dim_Supplier(SupplierKey));
SHOW PROCEDURE STATUS WHERE Db = 'sales_dw';
USE sales_dw;

USE sales_dw;
CALL PopulateDimDate('2020-01-01', '2030-12-31');
SELECT * FROM Dim_Date LIMIT 10;