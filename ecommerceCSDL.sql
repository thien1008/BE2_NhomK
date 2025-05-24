CREATE DATABASE ECommerceDB;
USE ECommerceDB;
-- Bảng Người Dùng
CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    Email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    Phone VARCHAR(15) UNIQUE,
    UserType ENUM('Regular', 'VIP', 'Admin') DEFAULT 'Regular',
    GoogleID VARCHAR(255) NULL UNIQUE;
    FacebookID VARCHAR(255) NULL UNIQUE;
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bảng Danh Mục Sản Phẩm
CREATE TABLE Categories (
    CategoryID INT AUTO_INCREMENT PRIMARY KEY,
    CategoryName VARCHAR(100) NOT NULL UNIQUE,
    Description TEXT
);

-- Bảng Sản Phẩm
CREATE TABLE Products (
    ProductID INT AUTO_INCREMENT PRIMARY KEY,
    ProductName VARCHAR(255) NOT NULL,
    CategoryID INT NOT NULL,
    Price DECIMAL(10,2) NOT NULL,
    Stock INT NOT NULL,
    Description TEXT,
    ImageURL VARCHAR(255),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CategoryID) REFERENCES Categories(CategoryID) ON DELETE CASCADE
);

-- Bảng Giỏ Hàng
CREATE TABLE Cart (
    CartID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    ProductID INT NOT NULL,
    Quantity INT NOT NULL CHECK (Quantity > 0),
    AddedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (ProductID) REFERENCES Products(ProductID) ON DELETE CASCADE
);

-- Bảng Đơn Hàng
CREATE TABLE Orders (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    TotalPrice DECIMAL(18,2) NOT NULL,
    Status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending',
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

-- Bảng Chi Tiết Đơn Hàng
CREATE TABLE OrderDetails (
    OrderDetailID INT AUTO_INCREMENT PRIMARY KEY,
    OrderID INT NOT NULL,
    ProductID INT NOT NULL,
    Quantity INT NOT NULL CHECK (Quantity > 0),
    Price DECIMAL(18,2) NOT NULL,
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID) ON DELETE CASCADE,
    FOREIGN KEY (ProductID) REFERENCES Products(ProductID) ON DELETE CASCADE
);

-- Bảng Giảm Giá Cho Sản Phẩm
CREATE TABLE ProductDiscounts (
    DiscountID INT AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    DiscountPercentage DECIMAL(5,2) CHECK (DiscountPercentage BETWEEN 0 AND 100),
    StartDate DATETIME NOT NULL,
    EndDate DATETIME NOT NULL,
    FOREIGN KEY (ProductID) REFERENCES Products(ProductID) ON DELETE CASCADE
);

-- Bảng Mã Giảm Giá (Voucher)
CREATE TABLE Coupons (
    CouponID INT AUTO_INCREMENT PRIMARY KEY,
    Code VARCHAR(50) UNIQUE NOT NULL,
    DiscountPercentage DECIMAL(5,2) CHECK (DiscountPercentage BETWEEN 0 AND 100),
    ValidFrom DATETIME NOT NULL,
    ValidTo DATETIME NOT NULL,
    UsageLimit INT NOT NULL DEFAULT 1,
    UsedCount INT NOT NULL DEFAULT 0,
    UserLimit INT NOT NULL DEFAULT 1
);

-- Bảng Lưu Mã Giảm Giá Đã Dùng
CREATE TABLE UserCoupons (
    UserCouponID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    CouponID INT NOT NULL,
    UsedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (CouponID) REFERENCES Coupons(CouponID) ON DELETE CASCADE
);

CREATE TABLE password_resets (
  email VARCHAR(255) NOT NULL,
  token VARCHAR(255) NOT NULL,
  create_at DATETIME NOT NULL
);


INSERT INTO Categories (CategoryName, Description) VALUES
('Mac', 'Dòng laptop của Apple'),
('iPhone', 'Dòng điện thoại thông minh của Apple'),
('AirPods', 'Tai nghe không dây của Apple'),
('Watch', 'Đồng hồ thông minh của Apple');

INSERT INTO Products (ProductName, CategoryID, Price, Stock, Description, ImageURL) VALUES
('MacBook Air M2', 1, 1199.99, 10, 'Laptop siêu nhẹ, mạnh mẽ với chip M2.', 'macbook_air_m2.jpg'),
('MacBook Pro 16', 1, 2499.99, 5, 'Dành cho dân chuyên nghiệp với màn hình Retina.', 'macbook_pro_16.jpg'),
('iPhone 14 Pro', 2, 1099.99, 15, 'Smartphone cao cấp với camera Pro.', 'iphone_14_pro.jpg'),
('iPhone SE 2022', 2, 429.99, 20, 'Giá rẻ nhưng mạnh mẽ với chip A15.', 'iphone_se_2022.jpg'),
('Apple Watch Ultra', 4, 799.99, 8, 'Đồng hồ thông minh bền bỉ cho dân thể thao.', 'apple_watch_ultra.jpg'),
('AirPods Pro 2', 3, 249.99, 18, 'Tai nghe chống ồn với chất lượng âm thanh tuyệt vời.', 'airpods_pro_2.jpg');
