<!-- cart.php (Cart Page) -->

<?php
session_start();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Your cart is empty.";
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shop_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Fetch products from the database for cart display
$product_ids = implode(",", array_keys($_SESSION['cart']));
$sql = "SELECT * FROM product WHERE id IN ($product_ids)";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Your Shopping Cart</h1>
        <a href="index.php">Continue Shopping</a>
    </header>
    <div class="cart-container">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                while ($row = $result->fetch_assoc()) {
                    $quantity = $_SESSION['cart'][$row['id']];
                    $discount_price = isset($row['discount']) ? $row['price'] * (1 - $row['discount'] / 100) : $row['price'];
                    $total_price = $discount_price * $quantity;
                    $total += $total_price;
                    ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td>$<?php echo number_format($discount_price, 2); ?></td>
                        <td><?php echo $quantity; ?></td>
                        <td>$<?php echo number_format($total_price, 2); ?></td>
                        <td>
                            <a href="remove_from_cart.php?id=<?php echo $row['id']; ?>">Remove</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <h3>Total: $<?php echo number_format($total, 2); ?></h3>
        <a href="checkout.php">Proceed to Checkout</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>
