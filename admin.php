<!-- admin.php (Admin Panel) -->

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();


$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "shop_db";

$conn = new mysqli($servername, $username, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Add Product Form Submission
if (isset($_POST['add_product'])) {
    $product_name = $conn->real_escape_string($_POST['product_name']);
    $product_code = $conn->real_escape_string($_POST['product_code']);
    $price = floatval($_POST['price']);
    $discount = intval($_POST['discount']);
    
    // Handle Image Upload
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $image = "";
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];
        
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)) {
            die("Error: Please select a valid image format.");
        }
        
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize) {
            die("Error: File size is larger than 5MB.");
        }
        
        // Generate unique filename
        $image = uniqid() . "." . $ext;
        $target_file = $target_dir . $image;
        
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // File uploaded successfully
        } else {
            die("Error: Unable to upload file.");
        }
    } else {
        die("Error: No file uploaded.");
    }

    // Insert product into database
    $sql = "INSERT INTO product (name, code, price, discount, image) VALUES ('$product_name', '$product_code', '$price', '$discount', '$image')";
    if ($conn->query($sql) === TRUE) {
        echo "New product added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Handle Delete Product (by code)
if (isset($_POST['delete_product'])) {
    $product_code = $conn->real_escape_string($_POST['product_code']);
    
    // First get the image name to delete
    $sql = "SELECT image FROM product WHERE code = '$product_code'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_path = "uploads/" . $row['image'];
        if (file_exists($image_path)) {
            unlink($image_path);  // Delete the image file
        }
    }

    $sql = "DELETE FROM product WHERE code = '$product_code'";
    if ($conn->query($sql) === TRUE) {
        echo "Product deleted successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Admin Panel</h1>
        <a href="admin_logout.php">Logout</a>
    </header>

    <section>
        <!-- Add New Product Form -->
        <h2>Add New Product</h2>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <label for="product_name">Product Name:</label>
            <input type="text" name="product_name" id="product_name" required><br><br>

            <label for="product_code">Product Code:</label>
            <input type="text" name="product_code" id="product_code" required><br><br>

            <label for="price">Price:</label>
            <input type="number" name="price" id="price" required><br><br>

            <label for="discount">Discount:</label>
            <input type="number" name="discount" id="discount"><br><br>

            <label for="image">Product Image:</label>
            <input type="file" name="image" id="image" accept="image/*" required><br><br>

            <button type="submit" name="add_product">Add Product</button>
        </form>

        <hr>

        <!-- Delete Product Form -->
        <h2>Delete Product by Code</h2>
        <form action="admin.php" method="POST">
            <label for="delete_product_code">Product Code:</label>
            <input type="text" name="product_code" id="delete_product_code" required><br><br>

            <button type="submit" name="delete_product">Delete Product</button>
        </form>
    </section>

    <hr>

    <!-- Display All Products -->
    <section>
        <h2>All Products</h2>
        <?php
        $sql = "SELECT * FROM product";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr><th>Name</th><th>Code</th><th>Price</th><th>Discount</th><th>Image</th><th>Action</th></tr>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['name'] . "</td>";
                echo "<td>" . $row['code'] . "</td>";
                echo "<td>" . $row['price'] . "</td>";
                echo "<td>" . $row['discount'] . "%</td>";
                echo "<td><img src='uploads/" . $row['image'] . "' alt='" . $row['name'] . "' width='100'></td>";
                echo "<td><a href='edit_product.php?id=" . $row['code'] . "'>Edit</a></td>";
                echo "</tr>";
            }

            echo "</table>";
        } else {
            echo "No products found.";
        }

        if ($result === false) {
            echo "Query error: " . $conn->error;
        }
        ?>
    </section>
</body>
</html>

<?php
$conn->close();
?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

CREATE TABLE IF NOT EXISTS product (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL,
    discount INT DEFAULT 0,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
