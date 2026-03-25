<?php
include 'session.php';

$conn = new mysqli("localhost", "root", "", "inventory");
if ($conn->connect_error) die("Connection failed");
$conn->set_charset("utf8mb4");

$search = '';
$edit = null;
$message = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_product'])) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $expiry_date = $_POST['expiry_date'] ?? '';
    $errors = [];

    if (empty($name)) $errors[] = "Medicine name is required";
    if ($stock < 0) $errors[] = "Stock cannot be negative";
    if ($price < 0) $errors[] = "Price cannot be negative";

    if (empty($errors)) {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, stock=?, price=?, expiry_date=? WHERE id=?");
            $stmt->bind_param("ssidsi", $name, $description, $stock, $price, $expiry_date, $id);
            $msg = "Medicine updated successfully!";
        } else {
            $stmt = $conn->prepare("INSERT INTO products (name, description, stock, price, expiry_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssids", $name, $description, $stock, $price, $expiry_date);
            $msg = "Medicine added successfully!";
        }
        
        if ($stmt->execute()) {
            $_SESSION['message'] = $msg;
        } else {
            $_SESSION['message'] = "Database error: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }
        $stmt->close();
        
        header("Location: products.php");
        exit;
    } else {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "error";
        header("Location: products.php");
        exit;
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Product deleted successfully!";
    } else {
        $_SESSION['message'] = "Error deleting product!";
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    
    header("Location: products.php");
    exit;
}

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $edit = $result->fetch_assoc();
    }
    $stmt->close();
}

$where_clause = "";
$params = [];
$types = "";

if (!empty($search)) {
    $where_clause = "WHERE name LIKE ? OR description LIKE ?";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types = "ss";
}

$query = "SELECT * FROM products $where_clause ORDER BY name";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$products_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medicine Management - Pharmacy Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div>
    <h2>Medicine Management</h2>

    <?php if (!empty($message)): ?>
        <div>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="GET">
        <input type="text" name="search" placeholder="Search by name or description..." 
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
        <?php if($search): ?>
            <a href="products.php">Clear</a>
        <?php endif; ?>
    </form>

    <div>
        <div>
            <h3><?= $edit ? 'Edit Medicine' : 'Add New Medicine' ?></h3>
            <?php if($edit): ?>
                <a href="products.php">Cancel Edit</a>
            <?php endif; ?>
        </div>
        
        <form method="POST">
            <input type="hidden" name="id" value="<?= isset($edit['id']) ? $edit['id'] : '' ?>">
            
            <input type="text" name="name" placeholder="Medicine Name *" required 
                   value="<?= isset($edit['name']) ? htmlspecialchars($edit['name']) : '' ?>"><br>
            
            <input type="text" name="description" placeholder="Description" 
                   value="<?= isset($edit['description']) ? htmlspecialchars($edit['description'] ?? '') : '' ?>"><br>
            
            <input type="number" name="stock" placeholder="Stock Quantity *" min="0" required 
                   value="<?= isset($edit['stock']) ? $edit['stock'] : '' ?>"><br>
            
            <input type="number" step="0.01" name="price" placeholder="Price *" min="0" required 
                   value="<?= isset($edit['price']) ? $edit['price'] : '' ?>"><br>
            
            <input type="date" name="expiry_date" placeholder="Expiry Date" 
                   value="<?= isset($edit['expiry_date']) ? $edit['expiry_date'] : '' ?>"><br>
            
            <button type="submit" name="save_product">
                <?= $edit ? 'Update Medicine' : 'Add Medicine' ?>
            </button>
        </form>
    </div>

    
    <h3>Medicine List (<?= $products_result->num_rows ?> medicines)</h3>
    
    <?php if ($products_result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Stock</th>
                    <th>Price</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $products_result->fetch_assoc()): 
                    $product_id = $product['id'] ?? '';
                    $product_name = htmlspecialchars($product['name'] ?? '');
                ?>
                <tr>
                    <td><?= $product_name ?></td>
                    <td><?= htmlspecialchars($product['description'] ?? '—') ?></td>
                    <td>
                        <?= $product['stock'] ?? 0 ?>
                    </td>
                    <td>$<?= number_format($product['price'] ?? 0, 2) ?></td>
                    <td><?= $product['expiry_date'] ? date('M j, Y', strtotime($product['expiry_date'])) : 'N/A' ?></td>
                    <td>
                        <?php if (!empty($product_id)): ?>
                            <a href="products.php?edit=<?= $product_id ?>">Edit</a>
                            <a href="products.php?delete=<?= $product_id ?>" 
                               onclick="return confirm('Delete <?= addslashes($product_name) ?>?')">
                                Delete
                            </a>
                        <?php else: ?>
                            <span>No actions available</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div>
            <?= empty($search) ? 'No medicines found. Add your first medicine!' : 'No medicines match your search.' ?>
        </div>
    <?php endif; ?>

    <div>
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</div>
    <script src="script.js"></script>
</body>
</html>
<?php 
if (isset($products_result)) $products_result->free();
if (isset($stmt)) $stmt->close();
$conn->close(); 
?>