CREATE DATABASE IF NOT EXISTS pharmatrack;
USE pharmatrack;

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO users (username, password, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

CREATE TABLE IF NOT EXISTS products (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category     VARCHAR(50)  NOT NULL,
    quantity     INT          NOT NULL DEFAULT 0,
    price        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status       ENUM('In Stock','Low Stock','Out of Stock') NOT NULL DEFAULT 'In Stock',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS stock_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    product_id  INT          NOT NULL,
    type        ENUM('in','out') NOT NULL,
    quantity    INT          NOT NULL,
    date        DATE         NOT NULL,
    expiry_date DATE         NULL,
    notes       TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS Medicines (
    MedicineID              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Name                    VARCHAR(100) NOT NULL,
    Category                VARCHAR(50),
    Manufacturer            VARCHAR(100),
    UnitPrice               DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ReorderLevel            INT          NOT NULL DEFAULT 0,
    IsPrescriptionRequired  BOOLEAN      DEFAULT FALSE,
    CreatedAt               TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Inventory (
    InventoryID   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    MedicineID    INT UNSIGNED NOT NULL,
    BatchNumber   VARCHAR(50),
    StockQuantity INT          NOT NULL DEFAULT 0,
    ExpiryDate    DATE         NOT NULL,
    PurchasePrice DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (MedicineID) REFERENCES Medicines(MedicineID)
);

CREATE TABLE IF NOT EXISTS ExpiredStock (
    LogID          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    InventoryID    INT UNSIGNED NOT NULL,
    MedicineID     INT UNSIGNED NOT NULL,
    BatchNumber    VARCHAR(50),
    StockQuantity  INT          NOT NULL,
    ExpiryDate     DATE         NOT NULL,
    DisposalMethod ENUM('returned','destroyed','donated','other') NOT NULL,
    DateRemoved    DATE         NOT NULL,
    Notes          TEXT,
    RemovedAt      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (MedicineID) REFERENCES Medicines(MedicineID)
);

CREATE TABLE IF NOT EXISTS reservations (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT          NOT NULL,
    product_id  INT          NOT NULL,
    quantity    INT          NOT NULL DEFAULT 1,
    status      ENUM('Pending','Approved','Cancelled') NOT NULL DEFAULT 'Pending',
    reserved_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
