<?php
include 'session.php';

$conn = new mysqli("localhost", "root", "", "inventory");
if ($conn->connect_error) die("Connection failed");
$conn->set_charset("utf8mb4");


$sales = $conn->query("
    SELECT p.name AS product_name, 
           s.quantity, 
           s.sale_date,
           p.price,
           (s.quantity * p.price) AS total
    FROM sales s
    JOIN products p ON s.product_id = p.id
    ORDER BY s.sale_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales Report - Pharmacy Inventory Management System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h2>Sales Report</h2>

    <table>
      <thead>
        <tr>
          <th>Medicine Name</th>
          <th>Quantity Sold</th>
          <th>Price per Unit</th>
          <th>Total</th>
          <th>Date of Sale</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($sales && $sales->num_rows > 0): ?>
          <?php while($row = $sales->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['product_name']) ?></td>
              <td><?= $row['quantity'] ?></td>
              <td>$<?= number_format($row['price'], 2) ?></td>
              <td>$<?= number_format($row['total'], 2) ?></td>
              <td><?= date('M j, Y - g:i A', strtotime($row['sale_date'])) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="no-data">No sales recorded yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php
    $total_revenue = 0;
    if ($sales && $sales->num_rows > 0) {
        $sales->data_seek(0); // Reset pointer
        while($row = $sales->fetch_assoc()) {
            $total_revenue += $row['total'];
        }
    }
    ?>

    <div style="text-align: center; margin-top: 20px; font-size: 1.2em;">
        <strong>Total Revenue: $<?= number_format($total_revenue, 2) ?></strong>
    </div>

    <p style="margin-top: 30px; text-align: center;">
      <a href="dashboard.php">Back to Dashboard</a>
    </p>
  </div>
    <script src="script.js"></script>
</body>
</html>

<?php $conn->close(); ?>