<?php
include 'session.php';

$conn = new mysqli("localhost", "root", "", "inventory");
if ($conn->connect_error) die("Connection failed");
$conn->set_charset("utf8mb4");

$message = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_id = (int)$_POST['product_id'];
    $quantity   = (int)$_POST['quantity'];

    if ($product_id > 0 && $quantity > 0) {
        // Start transaction to ensure stock update + sale record
        $conn->begin_transaction();

        try {
          
            $stock_result = $conn->query("SELECT stock, name FROM products WHERE id = $product_id");
            $product = $stock_result->fetch_assoc();

            if ($product && $product['stock'] >= $quantity) {
                
                $conn->query("UPDATE products SET stock = stock - $quantity WHERE id = $product_id");

                
                $conn->query("INSERT INTO sales (product_id, quantity, sale_date) 
                              VALUES ($product_id, $quantity, NOW())");

                $conn->commit();
                $message = "<span style='color:green;'>Sale recorded: {$quantity} × {$product['name']}</span>";
            } else {
                $conn->rollback();
                $message = "<span style='color:red;'>Not enough stock!</span>";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<span style='color:red;'>Error recording sale.</span>";
        }
    }
}


$products = $conn->query("SELECT id, name, stock FROM products WHERE stock > 0 ORDER BY name");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Sales - Pharmacy Inventory Management System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h2>Sales</h2>

    <?php if ($message): ?>
      <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
      <select name="product_id" required>
        <option value="">Select Medicine</option>
        <?php while($p = $products->fetch_assoc()): ?>
          <option value="<?= $p['id'] ?>">
            <?= htmlspecialchars($p['name']) ?> (Stock: <?= $p['stock'] ?>)
          </option>
        <?php endwhile; ?>
      </select>

      <input type="number" name="quantity" placeholder="Quantity Sold" min="1" required>

      <button type="submit" onclick="return confirm('Record this sale?')">Record Sale</button>
    </form>

    <p><a href="dashboard.php">← Back to Dashboard</a> | <a href="reports.php">View Sales Report</a></p>
  </div>
    <script src="script.js"></script>
</body>
</html>

<?php $conn->close(); ?>