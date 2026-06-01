USE sales_dw;
INSERT INTO Dim_Customer (CustomerID, Name, AgeGroup, IsRegular, StartDate, EndDate)
SELECT 
    id, 
    name, 
    CASE 
        WHEN age < 18 THEN 'Youth'
        WHEN age BETWEEN 18 AND 60 THEN 'Adult'
        ELSE 'Senior'
    END AS AgeGroup,
    is_loyalty_member,
    CURDATE(),
    '9999-12-31'
FROM pharmatrack.Customers;
USE sales_dw;

INSERT INTO sales_dw.Dim_Customer (CustomerID, Name, AgeGroup, IsRegular, StartDate, EndDate)
SELECT 
    CustomerID, 
    Name, 
    CASE 
        WHEN TIMESTAMPDIFF(YEAR, DateOfBirth, CURDATE()) < 18 THEN 'Youth'
        WHEN TIMESTAMPDIFF(YEAR, DateOfBirth, CURDATE()) BETWEEN 18 AND 60 THEN 'Adult'
        ELSE 'Senior'
    END AS AgeGroup,
    IsRegular,
    CURDATE(),
    '9999-12-31'
FROM pharmatrack.Customers;
SHOW TABLES FROM pharmatrack;

SHOW TABLES FROM sales_dw;