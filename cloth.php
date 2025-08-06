<!-- cloth.php (Product Display Page) -->

<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "shop_db";

$conn = new mysqli($servername, $username, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Add to Cart functionality (for guest users)
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Fetch product details
    $sql = "SELECT * FROM product WHERE code = '$product_id'";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();

    // Check if cart already exists in session, if not, create one
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if the product is already in the cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity; // Update quantity
    } else {
        $_SESSION['cart'][$product_id] = [
            'id' => $product['code'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'image' => $product['image'],
            'discount' => $product['discount']
        ];
    }

    header("Location: cloth.php"); // Redirect back to product page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Product Catalog</h1>
        <nav>
            <a href="cart.php">View Cart</a>
        </nav>
    </header>

    <section>
        <h2>Available Products</h2>

        <?php
        // Fetch all products from the database
        $sql = "SELECT * FROM product";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<div class='product-container'>";

            while ($row = $result->fetch_assoc()) {
                $discounted_price = $row['price'] - ($row['price'] * $row['discount'] / 100);
                echo "<div class='product-card'>";
                echo "<img src='uploads/" . $row['image'] . "' alt='" . $row['name'] . "' class='product-image'>";
                echo "<h3>" . $row['name'] . "</h3>";
                echo "<p>Price: <span class='original-price'>$" . number_format($row['price'], 2) . "</span></p>";
                echo "<p>Discounted Price: <span class='discounted-price'>$" . number_format($discounted_price, 2) . "</span></p>";
                echo "<form action='cloth.php' method='POST'>";
                echo "<input type='hidden' name='product_id' value='" . $row['code'] . "'>";
                echo "<label for='quantity'>Quantity:</label>";
                echo "<input type='number' name='quantity' id='quantity' value='1' min='1' required>";
                echo "<button type='submit' name='add_to_cart'>Add to Cart</button>";
                echo "</form>";
                echo "</div>";
            }

            echo "</div>";
        } else {
            echo "<p>No products available.</p>";
        }
        ?>

    </section>
</body>
</html>

<?php
$conn->close();
?>
