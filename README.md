# E-commerce Shop

A simple e-commerce website built with PHP and MySQL.

## Features

- User Authentication (Login/Register)
- User Profile Management
- Admin Panel
  - Product Management (Add/Edit/Delete)
  - Image Upload
- Shopping Cart
- Responsive Design

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache Server
- mod_rewrite enabled

## Installation

1. Clone this repository to your local machine:
```bash
git clone https://github.com/YOUR_USERNAME/shop.git
```

2. Create a MySQL database named `shop_db`

3. Import the database structure:
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    location VARCHAR(255),
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE product (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL,
    discount INT DEFAULT 0,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

4. Configure your database connection in `db.php`

5. Make sure the `uploads` directory has write permissions

## Usage

1. Start your Apache server and MySQL
2. Visit `http://localhost/shop` in your browser
3. Register a new account or login
4. For admin access, use the admin credentials

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

[MIT](https://choosealicense.com/licenses/mit/)
