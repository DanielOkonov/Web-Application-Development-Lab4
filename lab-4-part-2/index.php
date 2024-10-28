<?php
function loadEnv($filePath)
{
    if (!file_exists($filePath)) {
        throw new Exception('.env file not found');
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $value = trim($value, '"');
        putenv("$name=$value");
    }
}

loadEnv(__DIR__ . '/.env');
$dsn = 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME') . ';charset=' . getenv('DB_CHARSET');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add']) && !empty($_POST['item_name']) && !empty($_POST['quantity'])) {
        $item_name = $_POST['item_name'];
        $quantity = (int) $_POST['quantity'];
        $stmt = $pdo->prepare("INSERT INTO inventory (item_name, quantity) VALUES (?, ?)");
        $stmt->execute([$item_name, $quantity]);
    }

    if (isset($_POST['delete'])) {
        $id = (int) $_POST['delete'];
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
        $stmt->execute([$id]);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$items = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search_term']) && !empty($_GET['search_term'])) {
    $searchTerm = '%' . $_GET['search_term'] . '%';
    $stmt = $pdo->prepare("SELECT * FROM inventory WHERE item_name LIKE ?");
    $stmt->execute([$searchTerm]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("SELECT * FROM inventory");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery Inventory</title>
</head>
<body>
    <h2>Grocery Inventory</h2>

    <form method="post" action="">
        <label for="item_name">Grocery:</label>
        <input type="text" id="item_name" name="item_name" placeholder="eg. Pineapples" required>
        <label for="quantity">Quantity</label>
        <input type="number" id="quantity" name="quantity" required>
        <button type="submit" name="add">Add</button>
    </form>

    <h3>Inventory</h3>

    <form method="get" action="">
        <input type="text" name="search_term" placeholder="Search" value="<?php echo isset($_GET['search_term']) ? htmlspecialchars($_GET['search_term']) : ''; ?>">
        <button type="submit" name="search">Search</button>
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>"><button type="button">Reset</button></a>
    </form>

    <ul>
        <?php foreach ($items as $item): ?>
            <li>
                <?php echo htmlspecialchars($item['item_name']); ?> - <?php echo htmlspecialchars($item['quantity']); ?>
                <form method="post" action="" style="display:inline;">
                    <button type="submit" name="delete" value="<?php echo $item['id']; ?>">Delete</button>
                </form>
            </li>
        <?php endforeach;?>
    </ul>
</body>
</html>
