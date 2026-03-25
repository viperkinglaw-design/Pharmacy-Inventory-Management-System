<?php
include 'session.php'; 

$conn = new mysqli("localhost", "root", "", "inventory");
if ($conn->connect_error) {
    die("Connection failed. Please try again later.");
}
$conn->set_charset("utf8mb4");

$stats = [];

$stats['products'] = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0] ?? 0;

$stats['sales'] = $conn->query("SELECT COUNT(*) FROM sales")->fetch_row()[0] ?? 0;

$stats['low_stock'] = $conn->query("SELECT COUNT(*) FROM products WHERE stock > 0 AND stock <= 10")->fetch_row()[0] ?? 0;

$stats['out_of_stock'] = $conn->query("SELECT COUNT(*) FROM products WHERE stock = 0")->fetch_row()[0] ?? 0;

$today = date('Y-m-d');
$expiry_soon = date('Y-m-d', strtotime('+30 days'));
$stats['expiring_soon'] = $conn->query("SELECT COUNT(*) FROM products WHERE expiry_date BETWEEN '$today' AND '$expiry_soon' AND stock > 0")->fetch_row()[0] ?? 0;

$today = date('Y-m-d');
$revenue_query = $conn->query("
    SELECT COALESCE(SUM(s.quantity * p.price), 0) AS revenue_today
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE DATE(s.sale_date) = '$today'
");
$stats['revenue_today'] = number_format($revenue_query->fetch_assoc()['revenue_today'], 2);

$total_revenue_query = $conn->query("
    SELECT COALESCE(SUM(s.quantity * p.price), 0) AS total
    FROM sales s
    JOIN products p ON s.product_id = p.id
");
$stats['total_revenue'] = number_format($total_revenue_query->fetch_assoc()['total'], 2);

$welcome_name = $_SESSION['username'] ?? 'User';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Pharmacy Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            font-size: 1.1em;
            text-align: center;
        }
        .stats-bar strong { font-size: 1.3em; }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome back, <strong><?= htmlspecialchars($welcome_name) ?></strong>!</h2>

    <div class="stats-bar">
        <strong><?= $stats['products'] ?></strong> Products &nbsp;|&nbsp;
        <strong><?= $stats['sales'] ?></strong> Sales Recorded &nbsp;|&nbsp;
        <strong style="color: orange;"><?= $stats['low_stock'] ?></strong> Low Stock (≤10) &nbsp;|&nbsp;
        <strong style="color: red;"><?= $stats['out_of_stock'] ?></strong> Out of Stock &nbsp;|&nbsp;
        <strong style="color: orange;"><?= $stats['expiring_soon'] ?></strong> Expiring Soon (30 days) &nbsp;|&nbsp;
        <strong style="color: green;">$<?= $stats['revenue_today'] ?></strong> Earned Today
        <br><small style="color:#666;">Total Revenue: <strong>$<?= $stats['total_revenue'] ?></strong></small>
    </div>

    <div class="dashboard-cards">
        <a href="products.php" class="card">
            <h3>Manage Medicines</h3>
            <p>Add, edit, delete and check stock levels</p>
        </a>
        <a href="sales.php" class="card">
            <h3>Record Sale</h3>
            <p>Quickly sell products & update stock</p>
        </a>
        <a href="reports.php" class="card">
            <h3>Sales Report</h3>
            <p>View all transactions & revenue</p>
        </a>
        <a href="logout.php" class="card logout">
            <h3>Logout</h3>
            <p>Exit the system securely</p>
        </a>
    </div>
</div>
    <script src="script.js"></script>
</body>
</html>