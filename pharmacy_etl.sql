USE sales_dw;

INSERT INTO sales_dw.Dim_Medicine (MedicineID, Name, GenericName, Category, Manufacturer, IsPrescriptionRequired, StartDate, EndDate, IsCurrent)
SELECT m.MedicineID, m.Name, m.GenericName, m.Category, m.Manufacturer, m.IsPrescriptionRequired, CURDATE() AS StartDate, '9999-12-31'  AS EndDate,
TRUE AS IsCurrent FROM pharmatrack.Medicines m WHERE NOT EXISTS ( SELECT 1 FROM   sales_dw.Dim_Medicine dm WHERE  dm.MedicineID = m.MedicineID AND  dm.IsCurrent  = TRUE);

INSERT INTO sales_dw.Dim_Customer ( CustomerID, Name, AgeGroup, IsRegular, StartDate, EndDate)
SELECT 0, 'Walk-in', 'Unknown', FALSE, '2000-01-01', '9999-12-31'
FROM (SELECT 1 AS dummy) x
WHERE NOT EXISTS ( SELECT 1 FROM sales_dw.Dim_Customer WHERE CustomerID = 0);

INSERT INTO sales_dw.Dim_Customer ( CustomerID, Name, AgeGroup, IsRegular, StartDate, EndDate)
SELECT c.CustomerID, c.Name, CASE WHEN c.DateOfBirth IS NULL THEN 'Unknown' WHEN TIMESTAMPDIFF(YEAR, c.DateOfBirth, CURDATE()) < 18 THEN 'Youth'
WHEN TIMESTAMPDIFF(YEAR, c.DateOfBirth, CURDATE()) BETWEEN 18 AND 60 THEN 'Adult' ELSE 'Senior' END AS AgeGroup, c.IsRegular, CURDATE() AS StartDate,
'9999-12-31' AS EndDate FROM pharmatrack.Customers c WHERE NOT EXISTS ( SELECT 1 FROM   sales_dw.Dim_Customer dc
 WHERE  dc.CustomerID = c.CustomerID AND dc.EndDate = '9999-12-31');


INSERT INTO sales_dw.Dim_Supplier (SupplierID, Name, City) SELECT s.SupplierID, s.Name, NULL AS City FROM pharmatrack.Suppliers s
WHERE NOT EXISTS ( SELECT 1 FROM   sales_dw.Dim_Supplier ds WHERE  ds.SupplierID = s.SupplierID);

INSERT INTO sales_dw.Fact_Sales ( DateKey, MedicineKey, CustomerKey, EmployeeID, Quantity, UnitPrice, TotalAmount, CostAmount)
SELECT YEAR(s.SaleDate) * 10000 + MONTH(s.SaleDate) * 100 + DAY(s.SaleDate) AS DateKey, dm.MedicineKey,
IFNULL(dc.CustomerKey, (SELECT CustomerKey FROM sales_dw.Dim_Customer WHERE CustomerID = 0 LIMIT 1)) AS CustomerKey,
IFNULL(s.PharmacistID, 0) AS EmployeeID, si.Quantity, si.UnitPrice, si.Subtotal AS TotalAmount,
IFNULL(si.Quantity * inv.PurchasePrice, 0.00) AS CostAmount FROM pharmatrack.SaleItems si
JOIN pharmatrack.Sales s ON si.SaleID = s.SaleID JOIN sales_dw.Dim_Medicine dm ON si.MedicineID = dm.MedicineID AND dm.IsCurrent = TRUE
LEFT JOIN sales_dw.Dim_Customer dc ON s.CustomerID = dc.CustomerID AND dc.EndDate = '9999-12-31'
LEFT JOIN pharmatrack.Inventory inv ON si.MedicineID = inv.MedicineID AND si.BatchNumber = inv.BatchNumber;

INSERT INTO sales_dw.Fact_Inventory_Snapshot ( DateKey, MedicineKey, StockQuantity, ExpiringIn30Days)
SELECT YEAR(CURDATE()) * 10000 + MONTH(CURDATE()) * 100 + DAY(CURDATE()) AS DateKey, dm.MedicineKey,
SUM(inv.StockQuantity) AS StockQuantity, SUM( CASE WHEN inv.ExpiryDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
THEN inv.StockQuantity ELSE 0 END) AS ExpiringIn30Days FROM pharmatrack.Inventory inv
JOIN sales_dw.Dim_Medicine dm ON inv.MedicineID = dm.MedicineID AND dm.IsCurrent = TRUE
WHERE NOT EXISTS ( SELECT 1 FROM   sales_dw.Fact_Inventory_Snapshot fis
WHERE  fis.DateKey = YEAR(CURDATE()) * 10000 + MONTH(CURDATE()) * 100 + DAY(CURDATE()) AND  fis.MedicineKey = dm.MedicineKey)
GROUP BY dm.MedicineKey;

INSERT INTO sales_dw.Fact_Purchases ( DateKey, MedicineKey, SupplierKey, Quantity, TotalCost)
SELECT
YEAR(p.PurchaseDate) * 10000 + MONTH(p.PurchaseDate) * 100 + DAY(p.PurchaseDate) AS DateKey, dm.MedicineKey, ds.SupplierKey,
pi.Quantity, IFNULL(pi.Quantity * pi.UnitCost, 0.00) AS TotalCost FROM pharmatrack.PurchaseItems pi
JOIN pharmatrack.Purchases p ON pi.PurchaseID = p.PurchaseID
JOIN sales_dw.Dim_Medicine dm ON pi.MedicineID = dm.MedicineID AND dm.IsCurrent = TRUE
JOIN sales_dw.Dim_Supplier ds ON p.SupplierID = ds.SupplierID;