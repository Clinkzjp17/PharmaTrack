USE pharmatrack;

CREATE TABLE IF NOT EXISTS ExpiredStock (
    LogID          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    InventoryID    INT UNSIGNED NOT NULL,        
    MedicineID     INT UNSIGNED NOT NULL,
    BatchNumber    VARCHAR(50),
    StockQuantity  INT NOT NULL,
    ExpiryDate     DATE NOT NULL,
    DisposalMethod ENUM('returned','destroyed','donated','other') NOT NULL,
    DateRemoved    DATE NOT NULL,
    Notes          TEXT,
    RemovedAt      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (MedicineID) REFERENCES Medicines(MedicineID)
);
